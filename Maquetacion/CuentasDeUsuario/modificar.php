<?php
// Conexión a la base de datos local
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

// Recibimos los datos del formulario
$CI = $_POST['CI'];               // Cédula de identidad del usuario
$campo = $_POST['campo'];         // Campo que se quiere modificar (por ejemplo: Nombres)
$nuevo_valor = $_POST['nuevo_valor']; // Nuevo dato que se quiere guardar

// Lista de campos que sí se pueden modificar
$permitidos = ['Nombres', 'Apellidos', 'Direccion', 'Nacimiento', 'Telefono'];

// Verificamos si el campo a modificar está permitido
if (in_array($campo, $permitidos)) {
    // Si sí está permitido, se arma y ejecuta la consulta para actualizar el dato
    $sql = "UPDATE Informacion SET $campo = '$nuevo_valor' WHERE CI = '$CI'";
    mysqli_query($conexion, $sql);
    echo "Dato actualizado.";
} else {
    // Si el campo no está en la lista, no se permite modificar
    echo "No puedes modificar ese campo.";
}
?>
