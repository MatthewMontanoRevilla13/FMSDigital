<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para calificar entregas.");
}

$id_entrega = $_POST['id_entrega'];
$nota = $_POST['nota'];

$sql = "UPDATE Entrega SET nota = $nota WHERE id = $id_entrega";

if (mysqli_query($conexion, $sql)) {
    echo "Entrega calificada correctamente.";
} else {
    echo "Error al calificar: " . mysqli_error($conexion);
}
?>