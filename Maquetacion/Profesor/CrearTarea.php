<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Solo un profesor debería poder crear
if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para crear tareas.");
}

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$tema = $_POST['tema'];
$id_clase = $_POST['id_clase']; // viene del formulario

$sql = "INSERT INTO Tarea (titulo, descripcion, tema, Clase_id_clase)
        VALUES ('$titulo', '$descripcion', '$tema', $id_clase)";

if (mysqli_query($conexion, $sql)) {
    echo "Tarea creada correctamente.";
    header("Location: ClaseDeProfesor.php?id_clase=$id_clase");
} else {
    echo "Error al crear tarea: " . mysqli_error($conexion);
}
?>