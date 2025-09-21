<?php
// ====== Sesión ======
session_start();
$nombreAlumno = isset($_SESSION['nom']) ? ($_SESSION['nom']." ".$_SESSION['apes']) : "Estudiante";
$usuario      = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;

// ====== Parámetros ======
$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;
$sec      = isset($_GET['sec']) ? $_GET['sec'] : "tablero"; // tablero | trabajo

// ====== Conexión BD ======
$cn = mysqli_connect("localhost","root","","RegistroP6");
if (!$cn) { die("Error en la conexión: ".mysqli_connect_error()); }

// ====== Datos de la clase (con fallback a la 1ª clase del alumno) ======
$clase = null;

// 1) Si llega id_clase, intentamos cargarla
if ($id_clase > 0) {
  $sql = "SELECT id_clase, nombreClase, codigoClase FROM clase WHERE id_clase=?";
  $st  = mysqli_prepare($cn,$sql);
  mysqli_stmt_bind_param($st,"i",$id_clase);
  mysqli_stmt_execute($st);
  $rs  = mysqli_stmt_get_result($st);
  $clase = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);
}

// 2) Si no llegó o no existe, tomamos la primera clase del alumno
if (!$clase) {
  $sql = "SELECT c.id_clase, c.nombreClase, c.codigoClase
          FROM cuenta_has_clase ch
          JOIN clase c ON c.id_clase = ch.Clase_id_clase
          WHERE ch.Cuenta_Usuario = ?
          ORDER BY c.id_clase ASC
          LIMIT 1";
  $st  = mysqli_prepare($cn,$sql);
  mysqli_stmt_bind_param($st,"i",$usuario);
  mysqli_stmt_execute($st);
  $rs  = mysqli_stmt_get_result($st);
  $clase = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);

  if ($clase) { $id_clase = intval($clase['id_clase']); }
}

// 3) Si no hay ninguna clase, mensaje amable y salir
if (!$clase) {
  echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Clase del Alumno</title>
        <link rel='stylesheet' href='/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.css'></head><body>
        <div class='container'>
        <section class='section'>
        <h2>No estás inscrito en una clase</h2>
        <p class='muted'>Ingresa desde tu Panel de Estudiante o solicita al profesor un código de clase.</p>
        </section></div></body></html>";
  exit;
}

// ========= Helpers de archivos para el tablón =========
function buscarAdjuntoComentario($id_clase, $id_pub) {
  $webDir = "/FMSDIGITAL/Maquetacion/media/comentarios/";
  $fsDir  = rtrim($_SERVER['DOCUMENT_ROOT'].$webDir, "/")."/";
  $posibles = ["jpg","jpeg","png","gif","webp","pdf","doc","docx","zip"];
  foreach ($posibles as $ext) {
    $rutaFS = $fsDir."P-".$id_clase."-".$id_pub.".".$ext;
    if (file_exists($rutaFS)) {
      return [$webDir."P-".$id_clase."-".$id_pub.".".$ext, $ext];
    }
  }
  return [null,null];
}
function etiquetaAdjunto($rutaWeb, $ext){
  if (!$rutaWeb) return "";
  $ext = strtolower($ext);
  if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
    return "<img src='{$rutaWeb}' alt='Adjunto' style='max-width:380px;border-radius:8px'>";
  } elseif ($ext === "pdf") {
    return "<embed src='{$rutaWeb}' type='application/pdf' width='480' height='340' style='border-radius:8px'>";
  } else {
    return "<a class='btn-ghost' href='{$rutaWeb}' download>📥 Descargar archivo</a>";
  }
}

// ========= Listados para la vista =========

// Últimos anuncios (comentario de la clase)
$anuncios = [];
$sql = "SELECT id, contenido, fechaPub, fechaEdi, Cuenta_Usuario 
        FROM comentario 
        WHERE Clase_id_clase=?
        ORDER BY id DESC
        LIMIT 10";
$st = mysqli_prepare($cn,$sql);
mysqli_stmt_bind_param($st,"i",$id_clase);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
while ($row = mysqli_fetch_assoc($rs)) { $anuncios[] = $row; }
mysqli_stmt_close($st);

// Tareas agrupadas por Tema (para la sección Trabajo)
$tareasPorTema = [];
if ($sec === "trabajo") {
  $sql = "SELECT id, Titulo, Descripcion, Tema, FechaLimite, Archivo
          FROM tarea
          WHERE Clase_id_clase=?
          ORDER BY COALESCE(Tema,''), id DESC";
  $st = mysqli_prepare($cn,$sql);
  mysqli_stmt_bind_param($st,"i",$id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  while ($t = mysqli_fetch_assoc($rs)) {
    $tema = trim($t["Tema"] ?? "");
    if ($tema === "") { $tema = "(Sin tema)"; }
    if (!isset($tareasPorTema[$tema])) { $tareasPorTema[$tema] = []; }
    $tareasPorTema[$tema][] = $t;
  }
  mysqli_stmt_close($st);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clase del Alumno</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
</head>
<body>
 <?php include '../header.php'; ?>
<header>
  <div class="header-content">

    <div>
      <div class="nombre-usuario"><?php echo htmlspecialchars($nombreAlumno); ?></div>
      <div style="opacity:.85;font-size:.95rem;">
        <?php echo htmlspecialchars($clase['nombreClase']); ?> 
        <?php if(!empty($clase['codigoClase'])): ?> • Código: <?php echo htmlspecialchars($clase['codigoClase']); ?><?php endif; ?>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <nav>
    <a class="btn-ghost" href="ClaseDeAlumno.php?id_clase=<?php echo $id_clase; ?>">Tablero</a>
    <a class="btn-ghost" href="ClaseDeAlumno.php?id_clase=<?php echo $id_clase; ?>&sec=trabajo">Trabajo de clase</a>
    <a class="btn-ghost" href="#">Materiales</a>
    <a href="/FMSDIGITAL/Maquetacion/Profesor/ListaEstudiantes.php?id_clase=<?php echo $id_clase; ?>">Lista de estudiantes</a>
  </nav>

  <?php if ($sec === "tablero"): ?>
    <!-- ========= TABLERO ========= -->
    <section class="section">
      <h2>Tablón de Publicaciones</h2>
      <form id="FormComentario" method="post" enctype="multipart/form-data" action="/FMSDIGITAL/Maquetacion/Estudiante/Ccomentario.php">
        <input type="hidden" name="id_clase" value="<?php echo $id_clase; ?>"/>
        <textarea name="contenido" placeholder="Escribe algo para tu clase..." rows="4" maxlength="500"></textarea>
        <div>
          <label class="muted">Adjuntar archivo (opcional, máx. 100 KB)</label>
          <input type="file" name="fileToUpload" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.zip"/>
        </div>
        <div class="right">
          <button type="submit" class="btn">Publicar</button>
        </div>
      </form>
      <hr/>
      <?php if (empty($anuncios)): ?>
        <div class="anuncio">Aún no hay publicaciones en esta clase.</div>
      <?php else: ?>
        <?php foreach ($anuncios as $a): ?>
          <article class="comentario">
            <div>
              <strong>#<?php echo intval($a['id']); ?></strong>
              <small>Publicado: <?php echo htmlspecialchars($a['fechaPub']); ?></small>
              <?php if (!empty($a['fechaEdi'])): ?>
                <small>Editado: <?php echo htmlspecialchars($a['fechaEdi']); ?></small>
              <?php endif; ?>
            </div>
            <p><?php echo nl2br(htmlspecialchars($a['contenido'])); ?></p>
            <?php
              list($rutaAdj, $extAdj) = buscarAdjuntoComentario($id_clase, intval($a['id']));
              if ($rutaAdj) { echo etiquetaAdjunto($rutaAdj,$extAdj); }
            ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <section class="section">
      <h2>Trabajo de Clase</h2>
      <p class="muted">Consulta y entrega tus tareas asignadas por el profesor.</p>
      <div class="right">
        <a class="btn" href="ClaseDeAlumno.php?id_clase=<?php echo $id_clase; ?>&sec=trabajo">Ir a las tareas →</a>
      </div>
    </section>

  <?php else: ?>
    <!-- ========= TRABAJO DE CLASE ========= -->
    <section class="section">
      <h2>Trabajo de clase</h2>
      <p class="muted">Tareas organizadas por <strong>Tema</strong>. Haz clic en “Ver y entregar”.</p>
      <hr/>
      <?php if (empty($tareasPorTema)): ?>
        <div class="anuncio">No hay tareas creadas por el profesor aún.</div>
      <?php else: ?>
        <?php foreach ($tareasPorTema as $tema => $items): ?>
          <h3 style="margin:14px 0 8px;color:#6b0014;"><?php echo htmlspecialchars($tema); ?></h3>
          <div class="lista-tareas">
            <?php foreach ($items as $t): ?>
              <div class="tarea">
                <div><strong><?php echo htmlspecialchars($t['Titulo']); ?></strong></div>
                <?php if (!empty($t['Tema'])): ?>
                  <div class="muted">Tema: <?php echo htmlspecialchars($t['Tema']); ?></div>
                <?php endif; ?>
                <?php if (!empty($t['Descripcion'])): ?>
                  <p><?php echo htmlspecialchars($t['Descripcion']); ?></p>
                <?php endif; ?>

                <?php if (!empty($t['FechaLimite'])): ?>
                  <div class="muted">Fecha límite: <strong><?php echo htmlspecialchars($t['FechaLimite']); ?></strong></div>
                <?php endif; ?>

                <?php if (!empty($t['Archivo'])):
                  $rutaT = "/FMSDIGITAL/Maquetacion/media/tareas/".rawurlencode($t['Archivo']);
                  $extT  = strtolower(pathinfo($t['Archivo'], PATHINFO_EXTENSION));
                ?>
                  <div style="margin-top:6px">
                    <span class="muted">Adjunto del profesor:</span><br>
                    <?php
                      if (in_array($extT,["jpg","jpeg","png","gif","webp"])) {
                        echo "<img src='{$rutaT}' alt='Adjunto' style='max-width:320px;border-radius:8px'>";
                      } elseif ($extT==="pdf") {
                        echo "<embed src='{$rutaT}' type='application/pdf' width='460' height='320' style='border-radius:8px'>";
                      } else {
                        echo "<a class='btn-ghost' href='{$rutaT}' download>📥 Descargar archivo</a>";
                      }
                    ?>
                  </div>
                <?php endif; ?>

                <div class="right" style="margin-top:6px">
                  <a class="btn" href="/FMSDIGITAL/Maquetacion/Estudiante/EntregaTarea.php?id_tarea=<?php echo intval($t['id']); ?>">Ver y entregar</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</div>

<script>
// Validación del formulario del tablón
$(function(){
  $("#FormComentario").validate({
    rules:{ contenido:{ required:true, maxlength:500, minlength:3 } },
    messages:{
      contenido:{
        required:"Escribe un mensaje",
        maxlength:"Máx. 500 caracteres",
        minlength:"Mínimo 3"
      }
    },
    submitHandler:function(form){ form.submit(); }
  });
});
</script>

</body>
</html>
