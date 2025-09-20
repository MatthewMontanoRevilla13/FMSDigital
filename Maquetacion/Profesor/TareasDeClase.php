<?php
session_start();
$usuario = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;
$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;

$conexion = mysqli_connect("localhost","root","","RegistroP6");
if(!$conexion){ die("Error en la conexión: ".mysqli_connect_error()); }

// Info clase
$clase = null;
if ($id_clase > 0) {
  $sql = "SELECT id_clase, nombreClase, codigoClase FROM clase WHERE id_clase = ?";
  $st = mysqli_prepare($conexion,$sql);
  mysqli_stmt_bind_param($st,"i",$id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  $clase = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);
}

// Traer tareas ordenadas por Tema y fecha/id
$tareas = [];
if ($id_clase > 0) {
  $sql = "SELECT id, Titulo, Descripcion, Tema, FechaLimite, Archivo
          FROM tarea
          WHERE Clase_id_clase = ?
          ORDER BY COALESCE(NULLIF(TRIM(Tema),''),'~zzz') ASC, id DESC";
  $st = mysqli_prepare($conexion,$sql);
  mysqli_stmt_bind_param($st,"i",$id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  while($row = mysqli_fetch_assoc($rs)){ $tareas[] = $row; }
  mysqli_stmt_close($st);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Tareas de la clase</title>
  <link rel="stylesheet" href="ClaseDeProfesor.css"/>
  <style>
    .tema-header{
        margin:18px 0 8px;
        font-weight:900;
        color:#6b0014
    }
    .task-card{
        border:1px solid #efd0d5;
        border-radius:14px;
        padding:12px;
        background:#fff;
        margin-bottom:10px}
    .task-card .head{
        display:flex;
        justify-content:space-between;
        gap:8px;
        align-items:center}
    .muted{
        color:#6e5960}
  </style>
</head>
<body>
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">NOMBRE DEL COLEGIO</div>
    <nav class="topnav">
      <a href="ClaseDeProfesor.php?id_clase=<?php echo $id_clase; ?>">Volver a la clase</a>
      <a class="btn btn-ghost" href="tareas.php?id_clase=<?php echo $id_clase; ?>">➕ Crear tarea</a>
    </nav>
  </div>
</header>

<div class="shell">
  <aside class="sidebar">
    <span class="menu-title">Clase</span>
    <nav class="sidenav">
      <a href="ClaseDeProfesor.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Resumen</a>
      <a class="active" href="#"><span class="icon"></span> Tareas</a>
      <a href="ListaEstudiantes.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Lista de estudiantes</a>
      <a href="calificaciones.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Calificaciones</a>
      <a href="cerrarL.php"><span class="icon"></span> Cerrar Sesión</a>
    </nav>
  </aside>

  <main class="content">
    <section class="hero">
      <div>
        <h1>Tareas — <?php echo $clase ? htmlspecialchars($clase['nombreClase']) : "Clase"; ?></h1>
        <?php if($clase && !empty($clase['codigoClase'])): ?>
          <p class="muted">Código: <?php echo htmlspecialchars($clase['codigoClase']); ?></p>
        <?php endif; ?>
      </div>
      <div class="hero-ill" aria-hidden="true"></div>
    </section>

    <section class="card">
      <div class="actions" style="justify-content:space-between;align-items:center">
        <h2 style="margin:0">Todas las tareas</h2>
        <a class="btn" href="tareas.php?id_clase=<?php echo $id_clase; ?>">➕ Crear tarea</a>
      </div>

      <?php
      if (empty($tareas)) {
        echo '<p class="empty">No hay tareas todavía. Crea la primera.</p>';
      } else {
        // Agrupar por tema
        $temaActual = null;
        foreach ($tareas as $t) {
          $tema = trim($t['Tema'] ?? "");
          if ($tema === "") $tema = "Sin tema";

          if ($tema !== $temaActual) {
            $temaActual = $tema;
            echo '<h3 class="tema-header">Tema: '.htmlspecialchars($tema).'</h3>';
          }

          echo '<div class="task-card">';
            echo '<div class="head">';
              echo '<strong>'.htmlspecialchars($t['Titulo']).'</strong>';
              echo '<div class="actions">';
                echo '<a class="btn btn-ghost" href="EditarTarea.php?id_tarea='.intval($t['id']).'">Editar</a> ';
                echo '<a class="btn btn-ghost" href="VerEntregas.php?id_tarea='.intval($t['id']).'">Calificar</a> ';
                echo '<a class="btn btn-danger" href="EliminarTarea.php?id_tarea='.intval($t['id']).'" onclick="return confirm(\'¿Eliminar esta tarea?\')">Eliminar</a>';
              echo '</div>';
            echo '</div>';

            if (!empty($t['Descripcion'])) {
              echo '<p>'.htmlspecialchars($t['Descripcion']).'</p>';
            }

            // Fecha límite
            if (!empty($t['FechaLimite'])) {
              echo '<p class="muted">Fecha límite: '.htmlspecialchars($t['FechaLimite']).'</p>';
            }

            // Adjunto (usa tu mini-snippet adaptado a /media/tareas)
            if (!empty($t['Archivo'])) {
              $rutaArchivo = "media/tareas/" . rawurlencode($t['Archivo']);
              $ext = strtolower(pathinfo($t['Archivo'], PATHINFO_EXTENSION));
              if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
                echo "<img src='{$rutaArchivo}' alt='Adjunto' width='200'><br>";
              } elseif ($ext === "pdf") {
                echo "<embed src='{$rutaArchivo}' type='application/pdf' width='400' height='300'><br>";
              } else {
                echo "<a href='{$rutaArchivo}' download>📥 Descargar archivo</a><br>";
              }
            }

          echo '</div>';
        }
      }
      ?>
    </section>
  </main>
</div>
</body>
</html>