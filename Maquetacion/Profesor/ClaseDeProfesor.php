<?php
session_start();
$nombreProfesor = isset($_SESSION['nom']) ? ($_SESSION['nom']." ".$_SESSION['apes']) : "Profesor/a";
$usuario        = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;

$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;

$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexión: " . mysqli_connect_error()); }

// Info clase
$clase = null;
if ($id_clase > 0) {
  $sql = "SELECT id_clase, nombreClase, codigoClase FROM clase WHERE id_clase = ?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $clase = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
}

// Manejo de anuncio (se mantiene)
$alerta = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_anuncio') {
  if (!$usuario || $id_clase <= 0) {
    $alerta = ["type" => "error", "msg" => "Sesión o clase no válidas."];
  } else {
    $contenido = trim($_POST['contenido'] ?? "");
    if ($contenido === "" || mb_strlen($contenido) > 500) {
      $alerta = ["type" => "error", "msg" => "El contenido es requerido (máx. 500 caracteres)."];
    } else {
      $sql = "INSERT INTO comentario (contenido, fechaEdi, Clase_id_clase, Cuenta_Usuario) VALUES (?, NOW(), ?, ?)";
      $stmt = mysqli_prepare($conexion, $sql);
      mysqli_stmt_bind_param($stmt, "sii", $contenido, $id_clase, $usuario);
      if (mysqli_stmt_execute($stmt)) {
        $alerta = ["type" => "success", "msg" => "Anuncio publicado correctamente."];
      } else {
        $alerta = ["type" => "error", "msg" => "Error al publicar anuncio: ".mysqli_error($conexion)];
      }
      mysqli_stmt_close($stmt);
    }
  }
}

// Listados recientes
$anuncios = [];
$tareas   = [];
if ($id_clase > 0) {
  $sql = "SELECT id, contenido, fechaPub FROM comentario WHERE Clase_id_clase = ? ORDER BY id DESC LIMIT 5";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $anuncios[] = $row; }
  mysqli_stmt_close($stmt);

  $sql = "SELECT id, Titulo, Descripcion, Tema FROM tarea WHERE Clase_id_clase = ? ORDER BY id DESC LIMIT 5";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $tareas[] = $row; }
  mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clase del Profesor</title>
  <link rel="stylesheet" href="ClaseDeProfesor.css"/>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
  <style>
    label.error{
      color:#b00020;
      font-size:.9rem;
      margin-top:6px;
      display:block
    }
    input.error , textarea.error{
      border:1px solid #b00020
      !important;
      background:#fff5f6}
    .alert{
      margin:12px 0;
      padding:10px 12px;
      border-radius:10px
    }
    .alert-success{
      background:#e9f7ef;
      border:1px solid #a5d6a7;
      color:#1b5e20
    }
    .alert-error{
      background:#ffebee;
      border:1px solid #ef9a9a;
      color:#b71c1c
    }
    .pill{
      padding:4px 8px;
      border-radius:999px;
      background:#fbeaec;
      border:1px solid #efd0d5;
      font-size:.85rem}
    .empty{
      color:#6e5960
    }
    .task-item , .list-item{
      display:grid;
      grid-template-columns:1fr 
      auto;gap:12px;
      align-items:center
    }
    .task-item .title{
      font-weight:700;
      color:#6b0014
    }
    .task-item .meta{
      display:flex;
      gap:8px;
      flex-wrap:wrap}
  </style>
</head>
<body>
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">NOMBRE DEL COLEGIO</div>
    <nav class="topnav">
      <a href="#">Inicio</a><a href="#">Noticias</a><a href="#">Galería</a><a href="#">Documentos</a><a href="#">Contacto</a>
    </nav>
  </div>
</header>

<div class="shell">
  <aside class="sidebar">
    <span class="menu-title">Clase</span>
    <nav class="sidenav">
      <a class="active" href="#"><span class="icon"></span> Resumen</a>
      <!-- ahora lleva al nuevo índice -->
      <a href="TareasDeClase.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Tareas</a>
      <a href="ListaEstudiantes.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Lista de estudiantes</a>
      <a href="calificaciones.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Calificaciones</a>
      <a href="cerrarL.php"><span class="icon"></span> Cerrar Sesión</a>
    </nav>
  </aside>

  <main class="content">
    <section class="hero">
      <div>
        <h1><?php echo $clase ? htmlspecialchars($clase['nombreClase']) : "Clase"; ?></h1>
        <p>Profesor: <?php echo htmlspecialchars($nombreProfesor); ?>
          <?php if ($clase && !empty($clase['codigoClase'])): ?> • Código: <?php echo htmlspecialchars($clase['codigoClase']); ?><?php endif; ?>
        </p>
      </div>
      <div class="hero-ill" aria-hidden="true"></div>
    </section>

    <?php if ($alerta): ?>
      <div class="alert <?php echo $alerta['type']==='success'?'alert-success':'alert-error'; ?>">
        <?php echo htmlspecialchars($alerta['msg']); ?>
      </div>
    <?php endif; ?>

    <!-- Publicar anuncio (se mantiene igual) -->
    <section id="form-anuncio" class="card">
      <h2>Publicar anuncio</h2>
      <form id="FormAnuncio" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="accion" value="crear_anuncio"/>
        <div class="form-group">
          <label for="contenido">Contenido (máx. 500)</label>
          <textarea id="contenido" name="contenido" placeholder="Escribe el mensaje para tu clase..."></textarea>
        </div>
        <div class="actions">
          <button type="submit" class="btn">Publicar</button>
          <button type="button" class="btn btn-ghost" onclick="document.getElementById('FormAnuncio').reset()">Limpiar</button>
        </div>
      </form>
    </section>

    <!-- Recientes -->
    <section class="card">
      <h2>Anuncios recientes</h2>
      <?php if (empty($anuncios)): ?>
        <div class="empty">Aún no publicaste anuncios para esta clase.</div>
      <?php else: ?>
        <div class="list">
          <?php foreach ($anuncios as $a): ?>
            <div class="list-item">
              <span><?php echo htmlspecialchars($a['contenido']); ?></span>
              <div class="actions"><span class="pill">#<?php echo intval($a['id']); ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Tareas recientes</h2>
      <?php if (empty($tareas)): ?>
        <div class="empty">Aún no creaste tareas para esta clase. Entra a <strong>Tareas</strong> para gestionarlas.</div>
      <?php else: ?>
        <div class="task-list">
          <?php foreach ($tareas as $t): ?>
            <div class="task-item">
              <div class="info">
                <div class="title"><?php echo htmlspecialchars($t['Titulo']); ?></div>
                <div class="meta">
                  <?php if (!empty($t['Tema'])): ?><span class="pill"><?php echo htmlspecialchars($t['Tema']); ?></span><?php endif; ?>
                  <span class="pill">#<?php echo intval($t['id']); ?></span>
                </div>
                <?php if (!empty($t['Descripcion'])): ?><p><?php echo htmlspecialchars($t['Descripcion']); ?></p><?php endif; ?>
              </div>
              <div class="actions">
                <a class="btn btn-ghost" href="EditarTarea.php?id_tarea=<?php echo intval($t['id']); ?>">Editar</a>
                <a class="btn btn-ghost" href="VerEntregas.php?id_tarea=<?php echo intval($t['id']); ?>">Calificar</a>
                <a class="btn btn-danger" href="EliminarTarea.php?id_tarea=<?php echo intval($t['id']); ?>" onclick="return confirm('¿Eliminar esta tarea?')">Eliminar</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>

<script>
$(function(){
  $("#FormAnuncio").validate({
    ignore: [],
    rules:{ contenido:{ required:true, minlength:3, maxlength:500 } },
    messages:{
      contenido:{ required:"Escribe el anuncio", minlength:"Mínimo 3", maxlength:"Máx. 500" }
    },
    submitHandler:function(form){
      const $btn=$(form).find("[type='submit']");
      $btn.prop("disabled",true).text("Publicando...");
      form.submit();
    }
  });
});
</script>
</body>
</html>