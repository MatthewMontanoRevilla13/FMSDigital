<?php
// Iniciamos sesión para saber quién está intentando hacer cambios
session_start();

// Verificamos si el que intenta modificar es un profesor
if ($_SESSION['rol'] === 'Profesor') {
    
    // Conectamos a la base de datos
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

    // Obtenemos los datos que vinieron del formulario
    $codigoClase = $_POST['codigoClase'];     // Código único de la clase
    $nuevoNombre = $_POST['nuevoNombre'];     // Nuevo nombre que le quieren poner a la clase

    // Armamos la consulta para actualizar el nombre
    $sql = "UPDATE Clase SET nombreClase = '$nuevoNombre' WHERE codigoClase = '$codigoClase'";

    // Si se ejecuta bien, mostramos mensaje de éxito
    if (mysqli_query($conexion, $sql)) {
        echo "Nombre de clase actualizado correctamente.";
    } else {
        // Si hubo un error en la consulta, lo mostramos
        echo "Error al modificar el nombre de la clase: " . mysqli_error($conexion);
    }

} else {
    // Si no es profesor, no tiene permiso para cambiar el nombre de la clase
    echo "No tienes permiso para modificar el nombre de la clase.";
}
?>
