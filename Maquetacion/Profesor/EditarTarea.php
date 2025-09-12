<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para editar tareas.");
}

$id_tarea = $_POST['id_tarea'];
$nuevo_titulo = $_POST['titulo'];
$nueva_descripcion = $_POST['descripcion'];
$nuevo_tema = $_POST['tema'];

$sql = "UPDATE Tarea 
        SET Titulo='$nuevo_titulo', Descripcion='$nueva_descripcion', Tema='$nuevo_tema'
        WHERE id=$id_tarea";

if (mysqli_query($conexion, $sql)) {
    echo "Tarea actualizada correctamente.";
} else {
    echo "Error al actualizar tarea: " . mysqli_error($conexion);
}
?>