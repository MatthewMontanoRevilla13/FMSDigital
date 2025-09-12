<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Solo estudiantes deberían poder editar su entrega
if ($_SESSION['rol'] !== 'Estudiante') {
    die("No tienes permiso para editar esta entrega.");
}

// Recoger datos del formulario
$id_entrega = $_POST['id_entrega'] ?? null;
$nuevo_contenido = $_POST['contenido'] ?? '';

if (!$id_entrega) {
    die("No se recibió la entrega a editar.");
}

// Actualizar en la base de datos
$sql = "UPDATE Entrega 
        SET contenido='$nuevo_contenido' 
        WHERE Tarea_id=$id_entrega AND Cuenta_Usuario='{$_SESSION['usu']}'";

if (mysqli_query($conexion, $sql)) {
    echo "Entrega actualizada correctamente.";
    // Redirigir de nuevo a la página de la clase, si quieres
    header("Location: ClaseDeAlumno.php?id_clase=" . $_GET['id_clase']);
    exit;
} else {
    echo "Error al actualizar la entrega: " . mysqli_error($conexion);
}