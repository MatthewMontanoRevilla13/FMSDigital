<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SESSION['rol'] !== 'Estudiante') {
    die("Solo estudiantes pueden subir entregas.");
}

$id_tarea = $_POST['id_tarea'];
$usuario = $_SESSION['usu'];
$contenido = $_POST['contenido']; // puede ser texto o ruta de archivo

$sql = "INSERT INTO Entrega (Tarea_id, Cuenta_Usuario, contenido, fecha_entrega)
        VALUES ($id_tarea, '$usuario', '$contenido', NOW())";

if (mysqli_query($conexion, $sql)) {
    echo "Entrega subida correctamente.";
} else {
    echo "Error al subir entrega: " . mysqli_error($conexion);
}
?>