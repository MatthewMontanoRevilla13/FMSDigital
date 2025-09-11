<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para eliminar tareas.");
}

$id_tarea = $_POST['id_tarea'];

$sql = "DELETE FROM Tarea WHERE id = $id_tarea";

if (mysqli_query($conexion, $sql)) {
    echo "Tarea eliminada correctamente.";
} else {
    echo "Error al eliminar tarea: " . mysqli_error($conexion);
}
?>