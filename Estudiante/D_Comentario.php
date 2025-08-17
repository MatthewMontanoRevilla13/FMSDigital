<?php
session_start();
$id_clase = $_GET['id_clase'];
$id = $_POST['id'];
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verifica si el usuario es profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    echo "No tienes permiso para eliminar comentarios.";
    exit;
}
 // Ejecuta la eliminación
    $sql = "DELETE FROM Comentario WHERE id = $id";
    if (mysqli_query($conexion, $sql)) {
        header("Location: clase_de_profesor.php?id_clase=$id_clase");
        exit;
    } else {
        echo "Error al eliminar comentario: " . mysqli_error($conexion);
    }
?>