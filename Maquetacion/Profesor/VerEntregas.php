<?php
session_start();
if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para ver entregas.");
}

$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error: ".mysqli_connect_error()); }

$id_tarea = isset($_GET['id_tarea']) ? intval($_GET['id_tarea']) : 0;
if ($id_tarea <= 0) { die("Tarea inválida."); }

// Traemos la tarea
$tarea = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM tarea WHERE id=$id_tarea"));

// Traemos todas las entregas con el nombre del alumno
$sql = "SELECT e.id_entrega, e.contenido, e.FechaEntrega, e.nota, c.Usuario
        FROM entrega e
        JOIN cuenta c ON e.Cuenta_Usuario = c.Usuario
        WHERE e.Tarea_id = $id_tarea";
$entregas = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entregas - <?php echo htmlspecialchars($tarea['Titulo']); ?></title>
</head>
<body>
  <h1>Entregas de la tarea: <?php echo htmlspecialchars($tarea['Titulo']); ?></h1>
  <a href="ClaseDeProfesor.php?id_clase=<?php echo $tarea['Clase_id_clase']; ?>">⬅ Volver a la clase</a>
  <hr>

  <?php while($fila = mysqli_fetch_assoc($entregas)): ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
      <p><strong>Alumno:</strong> <?php echo htmlspecialchars($fila['Usuario']); ?></p>
      <p><strong>Entrega:</strong> <?php echo nl2br(htmlspecialchars($fila['contenido'])); ?></p>
      <p><strong>Fecha:</strong> <?php echo $fila['FechaEntrega']; ?></p>
      <form action="CalificarEntrega.php" method="POST">
        <input type="hidden" name="id_entrega" value="<?php echo $fila['id_entrega']; ?>">
        <label>Nota:</label>
        <input type="number" name="nota" value="<?php echo $fila['nota'] ?? ''; ?>" min="0" max="100" required>
        <button type="submit">Guardar</button>
      </form>
    </div>
  <?php endwhile; ?>
</body>
</html>