<?php
// ====== INICIO PHP (antes de cualquier HTML) ======
session_start();

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$id_clase = isset($_GET['id_clase']) ? (int)$_GET['id_clase'] : 0;
$usuario  = isset($_SESSION['usu']) ? (int)$_SESSION['usu'] : 0;

// Conexión única
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexión: " . mysqli_connect_error()); }

// Comentarios de la clase (ajusta nombres de tablas/campos a tu esquema real)
$comentarios = [];
if ($id_clase > 0) {
  // Trae también 'archivo' si tu tabla lo tiene
  $sql = "SELECT co.id, co.contenido, co.fechaPub, co.fechaEdi, co.archivo, cu.Usuario
          FROM comentario co
          JOIN cuenta cu ON co.Cuenta_Usuario = cu.Usuario
          WHERE co.Clase_id_clase = ?
          ORDER BY co.fechaPub DESC";
  $st = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($st, "i", $id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  while ($row = mysqli_fetch_assoc($rs)) { $comentarios[] = $row; }
  mysqli_stmt_close($st);
}

// Tareas de la clase
$tareas = [];
if ($id_clase > 0) {
  $sql = "SELECT id, Titulo, Descripcion FROM tarea WHERE Clase_id_clase = ? ORDER BY id DESC";
  $st = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($st, "i", $id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  while ($row = mysqli_fetch_assoc($rs)) { $tareas[] = $row; }
  mysqli_stmt_close($st);
}

// Entregas del usuario por tarea (lo resolvemos al volcar cada tarjeta)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clase de Alumno</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.css">
</head>
<body>
 <?php include '../header.php'; ?>
<header>
  <div class="header-content">
    <span class="nombre-usuario"><?php echo h($_SESSION['nom'] ?? 'Alumno'); ?></span>
  </div>
</header>

<main class="container">
  <nav>
    <a href="#tablero">Tablero</a>
    <a href="#trabajo">Trabajo de clase</a>
    <a href="#materiales">Materiales</a>
    <!-- AHORA $id_clase EXISTE -->
    <a href="/FMSDIGITAL/Maquetacion/Profesor/ListaEstudiantes.php?id_clase=<?php echo $id_clase; ?>">Lista de estudiantes</a>
  </nav>

  <!-- TABLERO / COMENTARIOS -->
  <section class="section" id="tablero">
    <h2>Tablón de Publicaciones</h2>

    <!-- Publicar comentario del alumno (el upload lo procesa Ccomentario.php) -->
    <form action="/FMSDIGITAL/Maquetacion/Estudiante/Ccomentario.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_clase" value="<?php echo $id_clase; ?>">
      <textarea name="contenido" rows="3" cols="60" placeholder="Escribe algo para tu clase..." required maxlength="500"></textarea><br>
      <label>Adjuntar archivo:</label>
      <input type="file" name="fileUpload"><br><br>
      <button type="submit">Publicar</button>
    </form>

    <?php foreach ($comentarios as $fila): ?>
      <div class='comentario'>
        <strong><?php echo h($fila['Usuario']); ?></strong><br>
        <small>Publicado el: <?php echo h($fila['fechaPub']); ?></small><br>
        <?php if (!empty($fila['fechaEdi'])): ?>
          <small>Última edición: <?php echo h($fila['fechaEdi']); ?></small><br>
        <?php endif; ?>
        <p><?php echo nl2br(h($fila['contenido'])); ?></p>

        <?php if (!empty($fila['archivo'])):
          $rutaArchivo = "/FMSDIGITAL/Maquetacion/media/comentarios/" . rawurlencode($fila['archivo']);
          $ext = strtolower(pathinfo($fila['archivo'], PATHINFO_EXTENSION));
          if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
            echo "<img src='{$rutaArchivo}' alt='Adjunto' width='200'><br>";
          } elseif ($ext === "pdf") {
            echo "<embed src='{$rutaArchivo}' type='application/pdf' width='400' height='300'><br>";
          } else {
            echo "<a href='{$rutaArchivo}' download>📥 Descargar archivo</a><br>";
          }
        endif; ?>

        <!-- Mostrar editar solo si el autor es el mismo (igualamos por valor) -->
        <?php if ((int)($_SESSION['usu'] ?? 0) == (int)$fila['Usuario']): ?>
          <form action='Ecomentario.php' method='POST' style='display:inline;'>
            <input type='hidden' name='id' value='<?php echo (int)$fila['id']; ?>'>
            <textarea name='nuevo_texto' rows='3' cols='60' placeholder='Editar...' required></textarea><br>
            <button type='submit'>Editar</button>
          </form>
        <?php endif; ?>

        <!-- Eliminar solo para profesor -->
        <?php if (($_SESSION['rol'] ?? '') === 'Profesor'): ?>
          <form action='Dcomentario.php' method='POST' style='display:inline;' onsubmit="return confirm('¿Eliminar comentario?')">
            <input type='hidden' name='id' value='<?php echo (int)$fila['id']; ?>'>
            <button type='submit'>Eliminar</button>
          </form>
        <?php endif; ?>
      </div>
      <hr>
    <?php endforeach; ?>
  </section>

  <!-- TRABAJO DE CLASE -->
  <section class="section" id="trabajo">
    <h2>Trabajo de Clase</h2>
    <p>Aquí podrás ver y entregar tus tareas asignadas.</p>

    <div class="lista-tareas" id="tareas">
      <h3>Mis Tareas</h3>
      <?php foreach ($tareas as $t): ?>
        <div class='tarea'>
          <strong><?php echo h($t['Titulo']); ?></strong><br>
          <p><?php echo h($t['Descripcion']); ?></p>

          <?php
          // ¿Ya entregó?
          $entrega = null;
          $sqlE = "SELECT id_entrega, Tarea_id, contenido, Nota
                   FROM entrega
                   WHERE Tarea_id = ? AND Cuenta_Usuario = ?";
          $stE = mysqli_prepare($conexion, $sqlE);
          mysqli_stmt_bind_param($stE, "ii", $t['id'], $usuario);
          mysqli_stmt_execute($stE);
          $rsE = mysqli_stmt_get_result($stE);
          $entrega = mysqli_fetch_assoc($rsE);
          mysqli_stmt_close($stE);
          ?>

          <?php if ($entrega): ?>
            <p><strong>Tu entrega:</strong> <?php echo nl2br(h($entrega['contenido'])); ?></p>
            <p><strong>Nota:</strong> <?php echo h($entrega['Nota'] ?? 'Pendiente'); ?></p>
            <form action='../Profesor/EditarEntrega.php' method='POST'>
              <input type='hidden' name='id_entrega' value='<?php echo (int)$entrega['id_entrega']; ?>'>
              <input type='hidden' name='id_tarea' value='<?php echo (int)$entrega['Tarea_id']; ?>'>
              <textarea name='contenido' required><?php echo h($entrega['contenido']); ?></textarea>
              <button type='submit'>Editar Entrega</button>
            </form>
          <?php else: ?>
            <form action='../Profesor/SubirEntrega.php' method='POST'>
              <input type='hidden' name='id_tarea' value='<?php echo (int)$t['id']; ?>'>
              <textarea name='contenido' placeholder='Escribe tu tarea aquí...' required></textarea>
              <button type='submit'>Subir Tarea</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- MATERIALES -->
  <section class="section" id="materiales">
    <h2>Materiales</h2>
    <p>Consulta y descarga los materiales proporcionados.</p>
  </section>
</main>

</body>
</html>
