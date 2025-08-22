<?php
// Iniciamos sesión para saber quién está intentando editar
session_start();

// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

// Si falla la conexión, mostramos el error y detenemos todo
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Recibimos los datos del formulario
$id = $_POST['id'];                     // ID del comentario que se quiere editar
$nuevo_texto = $_POST['nuevo_texto'];  // Nuevo contenido del comentario
$usuario = $_SESSION['usu'];           // Usuario actual (desde la sesión)

// Verificamos que el comentario sea del usuario que intenta editarlo
$verificacion = "SELECT * FROM comentario WHERE id = $id AND Cuenta_Usuario = '$usuario'";
$resultado = mysqli_query($conexion, $verificacion);

// Si el comentario pertenece al usuario, lo dejamos editar
if (mysqli_num_rows($resultado) > 0) {
    $sql = "UPDATE comentario 
            SET contenido = '$nuevo_texto', fechaEdi = CURRENT_TIMESTAMP 
            WHERE id = $id";
    
    // Ejecutamos la consulta para actualizar
    if (mysqli_query($conexion, $sql)) {
        echo "Comentario actualizado correctamente.";
    } else {
        echo "Error al actualizar comentario: " . mysqli_error($conexion);
    }
} else {
    // Si no es su comentario, no lo dejamos editar
    echo "No tienes permiso para editar este comentario.";
}
?>
