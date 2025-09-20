<?php
session_start();
$usuario  = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;
$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;

$conexion = mysqli_connect("localhost","root","","RegistroP6");
if(!$conexion){ die("Error en la conexión: ".mysqli_connect_error()); }

$temas_pre = ["Álgebra","Geometría","Física","Química","Lenguaje","Historia","Sin tema"];
$alerta = null;

/* ===== Utils subida (mismas que en editar) ===== */
function sanitize_file_name($name){
  $name = preg_replace('/[^A-Za-z0-9._-]/','_', $name);
  $name = preg_replace('/[.]{2,}/','.', $name);
  return trim($name, '.');
}
function ensure_dir($dir){
  if (!is_dir($dir)) { @mkdir($dir,0777,true); }
  return is_dir($dir) && is_writable($dir);
}
function guardarAdjuntoTarea(&$nombreGuardado, &$errorMsg, $id_clase=0){
  $nombreGuardado = null; $errorMsg = null;
  if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] === UPLOAD_ERR_NO_FILE) return true;
  if ($_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK){ $errorMsg="Error al subir (código ".$_FILES['fileToUpload']['error'].")"; return false; }

  $tmp  = $_FILES['fileToUpload']['tmp_name'];
  $orig = sanitize_file_name(basename($_FILES['fileToUpload']['name']));
  $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
  $permitidas = ["pdf","jpg","jpeg","png","gif","webp","doc","docx","xls","xlsx","ppt","pptx","txt","zip","rar","7z"];
  $pesoMax = 20*1024*1024; $destDir = __DIR__."/media/tareas";
  if (!in_array($ext,$permitidas)) { $errorMsg="Tipo de archivo no permitido."; return false; }
  if (filesize($tmp) > $pesoMax)  { $errorMsg="El archivo supera 20 MB."; return false; }
  if (!ensure_dir($destDir))      { $errorMsg="No se pudo preparar la carpeta de destino."; return false; }

  $uniq = bin2hex(random_bytes(4));
  $target = "T_".$id_clase."_".time()."_".$uniq.".".$ext;
  if (!move_uploaded_file($tmp, $destDir."/".$target)) { $errorMsg="No se pudo mover el archivo."; return false; }

  $nombreGuardado = $target;
  return true;
}

/* ===== POST: crear ===== */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['accion']??'')==='crear_tarea') {
  $titulo      = trim($_POST['titulo'] ?? "");
  $descripcion = trim($_POST['descripcion'] ?? "");
  $tema        = trim($_POST['tema'] ?? "");
  $fechaLimite = trim($_POST['fechaLimite'] ?? "");

  if ($id_clase<=0) {
    $alerta = ["type"=>"error","msg"=>"Clase no válida."];
  } elseif ($titulo==="" || mb_strlen($titulo)>90) {
    $alerta = ["type"=>"error","msg"=>"El título es requerido (máx. 90)."];
  } elseif ($descripcion==="" || mb_strlen($descripcion)>90) {
    $alerta = ["type"=>"error","msg"=>"La descripción es requerida (máx. 90)."];
  } elseif (mb_strlen($tema)>90) {
    $alerta = ["type"=>"error","msg"=>"El tema no puede superar 90 caracteres."];
  } elseif ($fechaLimite==="" || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$fechaLimite)) {
    $alerta = ["type"=>"error","msg"=>"Selecciona una fecha límite válida (AAAA-MM-DD)."];
  } else {
    $archivoNombre = null; $err = null;
    if (!guardarAdjuntoTarea($archivoNombre, $err, $id_clase)) {
      $alerta = ["type"=>"error","msg"=>$err ?: "No se pudo subir el archivo."];
    } else {
      $sql = "INSERT INTO tarea (Titulo, Descripcion, Tema, FechaLimite, Archivo, Clase_id_clase)
              VALUES (?, ?, ?, ?, ?, ?)";
      $st  = mysqli_prepare($conexion,$sql);
      mysqli_stmt_bind_param($st,"sssssi",$titulo,$descripcion,$tema,$fechaLimite,$archivoNombre,$id_clase);
      if (mysqli_stmt_execute($st)) {
        mysqli_stmt_close($st);
        header("Location: TareasDeClase.php?id_clase=".$id_clase);
        exit;
      } else {
        $alerta = ["type"=>"error","msg"=>"Error al crear: ".mysqli_error($conexion)];
        mysqli_stmt_close($st);
      }
    }
  }
}

// Info de clase
$clase = null;
if ($id_clase>0) {
  $st = mysqli_prepare($conexion,"SELECT id_clase, nombreClase FROM clase WHERE id_clase=?");
  mysqli_stmt_bind_param($st,"i",$id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  $clase = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Crear tarea</title>

  <link rel="stylesheet" href="ClaseDeProfesor.css"/>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

  <!-- Override para ocupar todo el ancho en este formulario -->
  <style>
    /* La base fija .shell a 1200px; lo anulamos aquí para full-width. */ /* :contentReference[oaicite:3]{index=3} */
    .page--wide .shell{max-width:none;margin:18px 0;padding:0 28px;display:grid;grid-template-columns:380px 1fr;gap:26px}
    @media (max-width:992px){ .page--wide .shell{grid-template-columns:1fr;padding:0 16px} }
    .side-card{background:#fbeaec;border:1px solid #efd0d5;border-radius:18px;padding:22px;box-shadow:0 2px 8px rgba(107,0,20,.1);height:fit-content;position:sticky;top:86px}
    .side-card h1{margin:0 0 10px;color:#6b0014;font-weight:900;font-size:2rem}
    .side-ill{width:100%;height:180px;border-radius:12px;background:#8c1b15;box-shadow:0 6px 16px rgba(0,0,0,.12);margin-top:12px}
    .card{background:#fff;border:1px solid #efd0d5;border-radius:16px;padding:18px;box-shadow:0 2px 6px rgba(107,0,20,.08)}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .form-group{display:grid;gap:6px}
    .form-group label{font-weight:700;color:#6b0014}
    label.error{color:#b00020;font-size:.9rem;margin-top:2px}
    input.error,textarea.error,select.error{border:1px solid #b00020!important;background:#fff5f6}
    @media (max-width:768px){ .form-grid{grid-template-columns:1fr} }
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
    <h1>Crear tarea — <?php echo $clase?htmlspecialchars($clase['nombreClase']):"Clase"; ?></h1>
    <p class="muted">Define <strong>Tema</strong>, <strong>Fecha límite</strong> y (opcional) adjunta material.</p>
    <div class="side-ill" aria-hidden="true"></div>
  </aside>

  <main class="content">
    <?php if($alerta): ?>
      <div class="alert <?php echo $alerta['type']==='error'?'alert-error':'alert-success'; ?>"><?php echo htmlspecialchars($alerta['msg']); ?></div>
    <?php endif; ?>

    <section class="card">
      <form id="FormCrearTarea" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="accion" value="crear_tarea"/>

        <div class="form-grid">
          <div class="form-group">
            <label for="titulo">Título (máx. 90)</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ej. MATEMÁTICAS - Problemas 1">
          </div>
          <div class="form-group">
            <label for="tema">Tema (elige o escribe)</label>
            <input list="temas_predef" id="tema" name="tema" placeholder="Ej. Álgebra">
            <datalist id="temas_predef">
              <?php foreach($temas_pre as $tp){ echo "<option value=\"".htmlspecialchars($tp)."\">"; } ?>
            </datalist>
          </div>
        </div>

        <div class="form-group">
          <label for="descripcion">Descripción (máx. 90)</label>
          <input type="text" id="descripcion" name="descripcion" placeholder="Ej. Resolver del 1 al 10.">
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="fechaLimite">Fecha límite</label>
            <input type="date" id="fechaLimite" name="fechaLimite">
          </div>
          <div class="form-group">
            <label for="fileToUpload">Adjuntar archivo (opcional)</label>
            <input type="file" id="fileToUpload" name="fileToUpload"
                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.7z">
            <small class="muted">Máx. 20 MB. Se previsualiza imagen/PDF.</small>
          </div>
        </div>

        <div class="actions" style="margin-top:10px">
          <button type="submit" class="btn">Crear tarea</button>
          <a class="btn btn-ghost" href="TareasDeClase.php?id_clase=<?php echo $id_clase; ?>">Cancelar</a>
        </div>
      </form>
    </section>
  </main>
</div>

<script>
jQuery.validator.addMethod("filesize", function (value, element, param) {
  if (this.optional(element) || !element.files || !element.files.length) return true;
  return element.files[0].size <= param;
}, "Archivo demasiado grande.");

$(function(){
  $("#FormCrearTarea").validate({
    ignore: [],
    rules:{
      titulo:{ required:true, minlength:3, maxlength:90 },
      descripcion:{ required:true, minlength:3, maxlength:90 },
      tema:{ maxlength:90 },
      fechaLimite:{ required:true, dateISO:true },
      fileToUpload:{ extension:"pdf|jpg|jpeg|png|gif|webp|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar|7z", filesize: 20*1024*1024 }
    },
    messages:{
      titulo:{ required:"Ingresa el título", minlength:"Mínimo 3", maxlength:"Máx. 90" },
      descripcion:{ required:"Ingresa la descripción", minlength:"Mínimo 3", maxlength:"Máx. 90" },
      tema:{ maxlength:"Máx. 90" },
      fechaLimite:{ required:"Selecciona la fecha límite", dateISO:"Formato AAAA-MM-DD" },
      fileToUpload:{ extension:"Tipo no permitido", filesize:"Máx. 20 MB" }
    },
    submitHandler:function(form){
      const $btn=$(form).find("[type='submit']");
      $btn.prop("disabled",true).text("Creando...");
      form.submit();
    }
  });
});
</script>
</body>
</html>
