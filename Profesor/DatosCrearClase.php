<?php
session_start(); // Inicia la sesión para poder usar $_SESSION

// Verificamos que el usuario sea profesor antes de seguir
if ($_SESSION['rol'] === 'Profesor') {

    $usuario = $_SESSION['usu']; // Guardamos el nombre de usuario actual
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6"); // Conectamos a la base de datos

    // Si falla la conexión, mostramos un error y detenemos todo
    if (!$conexion) {
        echo "Error en la conexion: " . mysqli_connect_error();
        die();
    }

    // Obtenemos los datos enviados por el formulario
    $nomC = $_POST['nombreClase'];     // Nombre de la clase
    $codigoC = $_POST['codigoClase'];  // Código único de la clase
    $nomP = $_POST['nomProfe'];        // Nombre del profesor (escrito)

    // Creamos el query SQL para insertar una nueva clase en la tabla "Clase"
    $sql = "INSERT INTO Clase (codigoClase, nombreClase, nomProfe, Cuenta_Usuario)
            VALUES ('$codigoC', '$nomC', '$nomP', '$usuario')";

    // Si se ejecuta bien el INSERT, redirige al panel del profesor
    if (mysqli_query($conexion, $sql)) {
        header('Location:/1r Sprint-FMSDigital/Maquetacion/Profesor/ClaseDeProfesor.php');
        exit;
    } else {
        // Si algo falla al guardar, se muestra un mensaje de error
        echo "Error al crear tu clase: " . mysqli_error($conexion);
    }

} else {
    // Si alguien intenta entrar sin ser profesor
    echo "No tienes permiso para crear clases.";
}
?>
