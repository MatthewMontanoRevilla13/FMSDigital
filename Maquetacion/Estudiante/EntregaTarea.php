<?php
session_start();
$usuario = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;
if (!$usuario) { die("Sesión no válida."); }

$id_tarea = isset($_GET['id_tarea']) ? intval($_GET['id_tarea']) : 0;

$cn = mysqli_connect("localhost","root","","RegistroP6");
if (!$cn) { die("Error en la conexión: ".mysqli_connect_error()); }

// ====== Cargar tarea ======
$tarea = null;
if ($id_tarea > 0) {
  $sql = "SELECT t.id, t.Titulo, t.Descripcion, t.Tema, t.FechaLimite, t.Archivo, t.Clase_id_clase,
                 c.nombreClase, c.codigoClase
          FROM tarea t
          JOIN clase c ON c.id_clase = t.Clase_id_clase
          WHERE t.id=?";
  $st = mysqli_prepare($cn,$sql);
  mysqli_stmt_bind_param($st,"i",$id_tarea);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  $tarea = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);
}
if (!$tarea) { die("Tarea no válida."); }

$id_clase = intval($tarea['Clase_id_clase']);

// ====== Entrega existente del alumno ======
$entrega = null;
$sql = "SELECT id_entrega, Archivo, contenido
        FROM entrega
        WHERE Tarea_id=? AND Cuenta_Usuario=?
        LIMIT 1";
$st = mysqli_prepare($cn,$sql);
mysqli_stmt_bind_param($st,"ii",$id_tarea,$usuario);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
$entrega = mysqli_fetch_assoc($rs);
mysqli_stmt_close($st);

// ====== Subida/Reemplazo ======
$alerta = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['accion']) && $_POST['accion']==='entregar') {

  // Comentario opcional
  $coment = trim($_POST['contenido'] ?? "");

  // Validación del archivo (opcional pero si viene, validar)
  $fileOk = true; $nuevoNombre = null;

  if (!empty($_FILES['fileToUpload']['name'])) {

    $origName = $_FILES['fileToUpload']['name'];
    $tmp      = $_FILES['fileToUpload']['tmp_name'];
    $size     = intval($_FILES['fileToUpload']['size']);

    // Límite 100 KB
    if ($size > 102400) {
      $fileOk = false;
      $alerta = ["type"=>"error", "msg"=>"El archivo supera 100 KB."];
    }

    // Extensiones permitidas
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $permitidas = ["jpg","jpeg","png","gif","webp","pdf","doc","docx","zip"];
    if ($fileOk && !in_array($ext,$permitidas)) {
      $fileOk = false;
      $alerta = ["type"=>"error","msg"=>"Tipo de archivo no permitido."];
    }

    // Mover a /media/entregas
    if ($fileOk) {
      $webDir = "/FMSDIGITAL/Maquetacion/media/entregas/";
      $fsDir  = rtrim($_SERVER['DOCUMENT_ROOT'].$webDir,"/")."/";
      if (!is_dir($fsDir)) { @mkdir($fsDir,0775,true); }

      // Nombre: E_<clase>_U<user>_<ts>.<ext>
      $nuevoNombre = "E_".$id_clase."_U".$usuario."_".time().".".$ext;
      $destinoFS   = $fsDir.$nuevoNombre;

      if (!move_uploaded_file($tmp, $destinoFS)) {
        $fileOk = false;
        $alerta = ["type"=>"error","msg"=>"No se pudo guardar el archivo."];
      }
    }
  }

  if ($fileOk) {
    // Insertar o actualizar entrega
    if ($entrega) {
      // Reemplazo: si hay nuevo archivo, actualizamos Archivo; siempre actualizamos contenido
      if ($nuevoNombre) {
        $sql = "UPDATE entrega SET Archivo=?, contenido=? WHERE id_entrega=?";
        $st  = mysqli_prepare($cn,$sql);
        mysqli_stmt_bind_param($st,"ssi",$nuevoNombre,$coment,$entrega['id_entrega']);
      } else {
        $sql = "UPDATE entrega SET contenido=? WHERE id_entrega=?";
        $st  = mysqli_prepare($cn,$sql);
        mysqli_stmt_bind_param($st,"si",$coment,$entrega['id_entrega']);
      }
      if (mysqli_stmt_execute($st)) {
        $alerta = ["type"=>"success","msg"=>"Entrega actualizada correctamente."];
      } else {
        $alerta = ["type"=>"error","msg"=>"Error al actualizar: ".mysqli_error($cn)];
      }
      mysqli_stmt_close($st);
    } else {
      // Nueva entrega: requiere al menos comentario o archivo
      if (!$nuevoNombre && $coment==="") {
        $alerta = ["type"=>"error","msg"=>"Debes adjuntar un archivo o escribir un comentario."];
      } else {
        $sql = "INSERT INTO entrega (Tarea_id, Cuenta_Usuario, contenido, Archivo) VALUES (?,?,?,?)";
        $st  = mysqli_prepare($cn,$sql);
        mysqli_stmt_bind_param($st,"iiss",$id_tarea,$usuario,$coment,$nuevoNombre);
        if (mysqli_stmt_execute($st)) {
          $alerta = ["type"=>"success","msg"=>"Entrega enviada correctamente."];
        } else {
          $alerta = ["type"=>"error","msg"=>"Error al guardar: ".mysqli_error($cn)];
        }
        mysqli_stmt_close($st);
      }
    }

    // Recargar entrega
    $sql = "SELECT id_entrega, Archivo, contenido
            FROM entrega
            WHERE Tarea_id=? AND Cuenta_Usuario=?
            LIMIT 1";
    $st = mysqli_prepare($cn,$sql);
    mysqli_stmt_bind_param($st,"ii",$id_tarea,$usuario);
    mysqli_stmt_execute($st);
    $rs = mysqli_stmt_get_result($st);
    $entrega = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($st);
  }
}

// ====== Helper vista adjuntos ======
function renderAdjunto($rutaWeb, $ext){
  $ext = strtolower($ext);
  if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
    return "<img src='{$rutaWeb}' alt='Adjunto' style='max-width:380px;border-radius:8px'>";
  } elseif ($ext==="pdf") {
    return "<embed src='{$rutaWeb}' type='application/pdf' width='480' height='340' style='border-radius:8px'>";
  } else {
    return "<a class='btn-ghost' href='{$rutaWeb}' download>📥 Descargar archivo</a>";
  }
}

// ====== Late flag ======
$hoy = date('Y-m-d');
$tarde = (!empty($tarea['FechaLimite']) && $hoy > $tarea['FechaLimite']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Entrega de Tarea</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
</head>
<body>
<div class="container">
  <nav>
    <a class="btn-ghost" href="/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.php?id_clase=<?php echo intval($id_clase); ?>&sec=trabajo">← Volver a Trabajo de clase</a>
  </nav>

  <section class="section">
    <h2><?php echo htmlspecialchars($tarea['Titulo']); ?></h2>
    <p class="muted">
      Clase: <strong><?php echo htmlspecialchars($tarea['nombreClase']); ?></strong>
      <?php if(!empty($tarea['Tema'])): ?> • Tema: <strong><?php echo htmlspecialchars($tarea['Tema']); ?></strong><?php endif; ?>
      <?php if(!empty($tarea['FechaLimite'])): ?>
        • Fecha límite: <strong><?php echo htmlspecialchars($tarea['FechaLimite']); ?></strong>
        <?php if ($tarde): ?><span class="anuncio" style="display:inline-block;margin-left:6px;">Entregas ahora se marcan como <strong>TARDE</strong></span><?php endif; ?>
      <?php endif; ?>
    </p>
    <?php if(!empty($tarea['Descripcion'])): ?><p><?php echo htmlspecialchars($tarea['Descripcion']); ?></p><?php endif; ?>

    <?php if(!empty($tarea['Archivo'])):
      $rutaT = "/FMSDIGITAL/Maquetacion/media/tareas/".rawurlencode($tarea['Archivo']);
      $extT  = strtolower(pathinfo($tarea['Archivo'], PATHINFO_EXTENSION));
      echo "<div style='margin-top:8px'><span class='muted'>Adjunto del profesor:</span><br>".renderAdjunto($rutaT,$extT)."</div>";
    endif; ?>
  </section>

  <?php if ($alerta): ?>
    <div class="section" style="border-left:6px solid <?php echo $alerta['type']==='success'?'#2e7d32':'#b00020'; ?>">
      <strong><?php echo $alerta['type']==='success'?'✔':'✖'; ?></strong> <?php echo htmlspecialchars($alerta['msg']); ?>
    </div>
  <?php endif; ?>

  <section class="section">
    <h2>Mi entrega</h2>
    <?php if ($entrega): ?>
      <?php if(!empty($entrega['Archivo'])):
        $rutaE = "/FMSDIGITAL/Maquetacion/media/entregas/".rawurlencode($entrega['Archivo']);
        $extE  = strtolower(pathinfo($entrega['Archivo'], PATHINFO_EXTENSION));
        echo "<div class='anuncio' style='background:#fff; border:1px solid #f3d7dc;'>";
        echo "<div class='muted' style='margin-bottom:6px;'>Archivo enviado:</div>";
        echo renderAdjunto($rutaE,$extE);
        echo "</div>";
      else: ?>
        <div class="anuncio">Aún no subiste archivo. Puedes subir uno abajo.</div>
      <?php endif; ?>
      <?php if($entrega['contenido']!==""): ?>
        <p><strong>Comentario:</strong> <?php echo nl2br(htmlspecialchars($entrega['contenido'])); ?></p>
      <?php endif; ?>
    <?php else: ?>
      <div class="anuncio">Todavía no realizaste la entrega.</div>
    <?php endif; ?>

    <form id="FormEntrega" method="post" enctype="multipart/form-data">
      <input type="hidden" name="accion" value="entregar"/>
      <div>
        <label class="muted">Adjuntar/Reemplazar archivo (máx. 100 KB; jpg, png, webp, gif, pdf, doc, docx, zip)</label>
        <input type="file" name="fileToUpload" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.zip"/>
      </div>
      <div>
        <label class="muted">Comentario (opcional)</label>
        <textarea name="contenido" rows="3" maxlength="500" placeholder="Escribe un comentario si quieres..."></textarea>
      </div>
      <div class="right">
        <button type="submit" class="btn"><?php echo $entrega ? "Actualizar entrega" : "Enviar entrega"; ?></button>
      </div>
    </form>
  </section>
</div>

<script>
$(function(){
  $("#FormEntrega").validate({
    rules:{
      contenido:{ maxlength:500 }
    },
    messages:{
      contenido:{ maxlength:"Máx. 500 caracteres" }
    },
    submitHandler:function(form){ form.submit(); }
  });
});
</script>
</body>
</html>
