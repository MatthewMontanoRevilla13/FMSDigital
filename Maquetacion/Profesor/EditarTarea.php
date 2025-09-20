<?php
session_start();
$conexion = mysqli_connect("localhost","root","","RegistroP6");
if(!$conexion){ die("Error en la conexión: ".mysqli_connect_error()); }

$id_tarea = isset($_GET['id_tarea']) ? intval($_GET['id_tarea']) : 0;

// Cargar tarea
$sql = "SELECT id, Clase_id_clase, Titulo, Descripcion, Tema, FechaLimite, Archivo FROM tarea WHERE id=?";
$st  = mysqli_prepare($conexion,$sql);
mysqli_stmt_bind_param($st,"i",$id_tarea);
mysqli_stmt_execute($st);
$rs  = mysqli_stmt_get_result($st);
$tarea = mysqli_fetch_assoc($rs);
mysqli_stmt_close($st);

if(!$tarea){ die("Tarea no encontrada."); }
$id_clase = intval($tarea['Clase_id_clase']);

/* ===== Utils subida ===== */
function sanitize_file_name($name){
  $name = preg_replace('/[^A-Za-z0-9._-]/','_', $name);
  $name = preg_replace('/[.]{2,}/','.', $name);
  return trim($name, '.');
}
function ensure_dir($dir){
  if (!is_dir($dir)) { @mkdir($dir,0777,true); }
  return is_dir($dir) && is_writable($dir);
}
function guardarAdjuntoTarea(&$nombreFinal, &$errorMsg, $oldName=null, $id_clase=0){
  $nombreFinal = $oldName; // por defecto mantener
  $errorMsg = null;

  if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] === UPLOAD_ERR_NO_FILE) {
    return true; // no sube nada = ok
  }
  if ($_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = "Error al subir el archivo (código ".$_FILES['fileToUpload']['error'].").";
    return false;
  }

  $tmp  = $_FILES['fileToUpload']['tmp_name'];
  $orig = sanitize_file_name(basename($_FILES['fileToUpload']['name']));
  $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
  $permitidas = ["pdf","jpg","jpeg","png","gif","webp","doc","docx","xls","xlsx","ppt","pptx","txt","zip","rar","7z"];
  $pesoMax = 20*1024*1024;
  $destDir = __DIR__."/media/tareas";

  if (!in_array($ext,$permitidas)) { $errorMsg="Tipo de archivo no permitido."; return false; }
  if (filesize($tmp) > $pesoMax)  { $errorMsg="El archivo supera 20 MB."; return false; }
  if (!ensure_dir($destDir))      { $errorMsg="No se pudo preparar la carpeta de destino."; return false; }

  // Generar nombre único
  $uniq = bin2hex(random_bytes(4));
  $nuevo = "T_".$id_clase."_".time()."_".$uniq.".".$ext;

  // Mover
  if (!move_uploaded_file($tmp, $destDir."/".$nuevo)) {
    $errorMsg = "No se pudo mover el archivo.";
    return false;
  }

  // Si había archivo viejo y lo estamos reemplazando, eliminarlo
  if ($oldName && is_file($destDir."/".$oldName)) { @unlink($destDir."/".$oldName); }

  $nombreFinal = $nuevo;
  return true;
}

/* ===== POST: editar ===== */
$alerta = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['accion']??'')==='editar_tarea') {
  $titulo      = trim($_POST['titulo'] ?? "");
  $descripcion = trim($_POST['descripcion'] ?? "");
  $tema        = trim($_POST['tema'] ?? "");
  $fechaLimite = trim($_POST['fechaLimite'] ?? "");
  $eliminarArchivo = isset($_POST['eliminar_archivo']);

  if ($titulo==="" || mb_strlen($titulo)>90) {
    $alerta=["type"=>"error","msg"=>"El título es requerido (máx. 90)."];
  } elseif ($descripcion==="" || mb_strlen($descripcion)>90) {
    $alerta=["type"=>"error","msg"=>"La descripción es requerida (máx. 90)."];
  } elseif (mb_strlen($tema)>90) {
    $alerta=["type"=>"error","msg"=>"El tema no puede superar 90 caracteres."];
  } elseif ($fechaLimite==="" || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fechaLimite)) {
    $alerta=["type"=>"error","msg"=>"Fecha límite inválida (AAAA-MM-DD)."];
  } else {
    $archivoActual = $tarea['Archivo'];
    if ($eliminarArchivo && $archivoActual) {
      $pathOld = __DIR__."/media/tareas/".$archivoActual;
      if (is_file($pathOld)) @unlink($pathOld);
      $archivoActual = null;
    }
    $nuevoNombre = $archivoActual; $err = null;
    if (!guardarAdjuntoTarea($nuevoNombre, $err, $archivoActual, $id_clase)) {
      $alerta = ["type"=>"error","msg"=>$err ?: "No se pudo subir el archivo."];
    } else {
      $sql = "UPDATE tarea SET Titulo=?, Descripcion=?, Tema=?, FechaLimite=?, Archivo=? WHERE id=?";
      $st  = mysqli_prepare($conexion,$sql);
      mysqli_stmt_bind_param($st,"sssssi",$titulo,$descripcion,$tema,$fechaLimite,$nuevoNombre,$id_tarea);
      if (mysqli_stmt_execute($st)) {
        mysqli_stmt_close($st);
        header("Location: TareasDeClase.php?id_clase=".$id_clase);
        exit;
      } else {
        $alerta=["type"=>"error","msg"=>"Error al guardar: ".mysqli_error($conexion)];
        mysqli_stmt_close($st);
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Editar tarea</title>

  <link rel="stylesheet" href="ClaseDeProfesor.css"/>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

  <!-- Override para ocupar todo el ancho en este formulario -->
  <style>
    /* La base limita .shell a 1200px (ver tu CSS). Lo anulamos aquí solo para esta página. */ /* :contentReference[oaicite:2]{index=2} */
    .page--wide .shell{max-width:none;margin:18px 0;padding:0 28px;display:grid;grid-template-columns:380px 1fr;gap:26px}
    @media (max-width:992px){ .page--wide .shell{grid-template-columns:1fr;padding:0 16px} }
    /* Maquetado del form */
    .side-card{background:#fbeaec;border:1px solid #efd0d5;border-radius:18px;padding:22px;box-shadow:0 2px 8px rgba(107,0,20,.1);height:fit-content;position:sticky;top:86px}
    .side-card h1{margin:0 0 10px;color:#6b0014;font-weight:900;font-size:2rem}
    .side-ill{width:100%;height:180px;border-radius:12px;background:#8c1b15;box-shadow:0 6px 16px rgba(0,0,0,.12);margin-top:12px}
    .card{background:#fff;border:1px solid #efd0d5;border-radius:16px;padding:18px;box-shadow:0 2px 6px rgba(107,0,20,.08)}
    .edit-grid{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:16px}
    .form-group{display:grid;gap:6px}
    .form-group label{font-weight:700;color:#6b0014}
    .file-box{padding:10px;border:1px solid #efd0d5;border-radius:10px;background:#fff}
    .actions-row{display:flex;gap:10px;flex-wrap:wrap}
    label.error{color:#b00020;font-size:.9rem;margin-top:2px}
    input.error,textarea.error,select.error{border:1px solid #b00020!important;background:#fff5f6}
    @media (max-width:768px){ .edit-grid{grid-template-columns:1fr} .actions-row .btn{width:100%} }
  </style>
</head>
<body class="page--wide">
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">NOMBRE DEL COLEGIO</div>
    <nav class="topnav"><a href="TareasDeClase.php?id_clase=<?php echo $id_clase; ?>">← Volver a Tareas</a></nav>
  </div>
</header>

<div class="shell">
  <aside class="side-card">
    <h1>Editar tarea</h1>
    <p class="muted">Modifica datos, fecha límite y archivo adjunto.</p>
    <div class="side-ill" aria-hidden="true"></div>
  </aside>

  <main class="content">
    <?php if($alerta): ?>
      <div class="alert <?php echo $alerta['type']==='error'?'alert-error':'alert-success'; ?>"><?php echo htmlspecialchars($alerta['msg']); ?></div>
    <?php endif; ?>

    <section class="card">
      <form id="FormEditarTarea" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="accion" value="editar_tarea"/>

        <div class="edit-grid">
          <div class="form-group"><label for="titulo">Título (máx. 90)</label><input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($tarea['Titulo']); ?>"></div>
          <div class="form-group"><label for="tema">Tema (máx. 90)</label><input type="text" id="tema" name="tema" value="<?php echo htmlspecialchars($tarea['Tema']); ?>"></div>
          <div class="form-group"><label for="descripcion">Descripción (máx. 90)</label><input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($tarea['Descripcion']); ?>"></div>
          <div class="form-group"><label for="fechaLimite">Fecha límite</label><input type="date" id="fechaLimite" name="fechaLimite" value="<?php echo htmlspecialchars($tarea['FechaLimite']); ?>"></div>
        </div>

        <div class="form-group" style="margin-top:12px">
          <label>Archivo actual</label>
          <div class="file-box">
            <?php
            if (!empty($tarea['Archivo'])) {
              $rutaArchivo = "media/tareas/" . rawurlencode($tarea['Archivo']);
              $ext = strtolower(pathinfo($tarea['Archivo'], PATHINFO_EXTENSION));
              if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
                echo "<img src='{$rutaArchivo}' alt='Adjunto' width='260' style='border-radius:8px;display:block;margin-bottom:6px;'>";
              } elseif ($ext === "pdf") {
                echo "<embed src='{$rutaArchivo}' type='application/pdf' width='520' height='340' style='border-radius:8px;display:block;margin-bottom:6px;'>";
              } else {
                echo "<a href='{$rutaArchivo}' download>📥 Descargar archivo actual</a><br>";
              }
              echo '<label style="display:flex;gap:8px;align-items:center;margin-top:6px"><input type="checkbox" name="eliminar_archivo"> Eliminar archivo</label>';
            } else {
              echo "<span class='muted'>Sin archivo</span>";
            }
            ?>
          </div>
        </div>

        <div class="form-group">
          <label for="fileToUpload">Reemplazar archivo (opcional)</label>
          <input type="file" id="fileToUpload" name="fileToUpload" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.7z">
          <small class="muted">Si subes uno nuevo, reemplazará al actual. Máx. 20 MB.</small>
        </div>

        <div class="actions-row" style="margin-top:10px">
          <button type="submit" class="btn">Guardar cambios</button>
          <a class="btn btn-ghost" href="TareasDeClase.php?id_clase=<?php echo $id_clase; ?>">Cancelar</a>
        </div>
      </form>
    </section>
  </main>
</div>

<script>
$(function(){
  $("#FormEditarTarea").validate({
    ignore: [],
    rules:{
      titulo:{ required:true, minlength:3, maxlength:90 },
      descripcion:{ required:true, minlength:3, maxlength:90 },
      tema:{ maxlength:90 },
      fechaLimite:{ required:true, dateISO:true }
    },
    messages:{
      titulo:{ required:"Ingresa el título", minlength:"Mínimo 3", maxlength:"Máx. 90" },
      descripcion:{ required:"Ingresa la descripción", minlength:"Mínimo 3", maxlength:"Máx. 90" },
      tema:{ maxlength:"Máx. 90" },
      fechaLimite:{ required:"Selecciona la fecha límite", dateISO:"Formato AAAA-MM-DD" }
    },
    submitHandler:function(form){
      const $btn=$(form).find("[type='submit']");
      $btn.prop("disabled",true).text("Guardando...");
      form.submit();
    }
  });
});
</script>
</body>
</html>
