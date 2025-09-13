<?php
session_start();
if ($_SESSION['rol'] !== 'Profesor') {
    die("No tienes permiso para calificar entregas.");
}

$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error: ".mysqli_connect_error()); }

if (!isset($_POST['id_entrega'], $_POST['nota'])) {
    die("Datos incompletos.");
}

$id_entrega = intval($_POST['id_entrega']);
$nota = intval($_POST['nota']);

$sql = "UPDATE entrega SET nota = ? WHERE id_entrega = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $nota, $id_entrega);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Entrega calificada correctamente.";
} else {
    echo "❌ Error: " . mysqli_error($conexion);
}

mysqli_stmt_close($stmt);
?>