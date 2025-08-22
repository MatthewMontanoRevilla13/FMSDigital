<?php
// Iniciamos la sesión para saber qué usuario está comentando
session_start();

// Conectamos a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

// Recibimos los datos del formulario
$contenido = $_POST['contenido'];      // El texto que escribió el usuario
$id_clase = $_POST['id_clase'];        // A qué clase pertenece ese comentario
$usuario = $_SESSION['usu'];           // El usuario que está comentando (sacado de la sesión)

// Armamos la consulta para insertar el comentario en la base de datos
$sql = "INSERT INTO Comentario (contenido, Clase_id_clase, Cuenta_Usuario)
        VALUES ('$contenido', $id_clase, '$usuario')";

// Si se guarda bien, redirige a la clase correspondiente
if (mysqli_query($conexion, $sql)) {
    header("Location: ClaseDeAlumno.php?id_clase=$id_clase");
} else {
    // Si algo falla, muestra el error
    echo "Error: " . mysqli_error($conexion);
}
?>
