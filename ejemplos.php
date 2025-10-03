<?php
session_start();

// Validar si hay sesión activa
if (!isset($_SESSION['rol'])) {
    header("Location: FormularioLogin.php");
    exit;
}

// Ejemplo: solo Administrador puede entrar
if ($_SESSION['rol'] !== 'Administrador') {
    echo "Acceso restringido para este rol.";
    exit;
}
?>





<?php
$idClase = (int) $_GET['id_clase'];
$usuario = $_SESSION['usu'];

// Consulta para saber si el estudiante pertenece a la clase
$sql = "SELECT * FROM cuenta_has_clase 
        WHERE Clase_id_clase = $idClase 
        AND Cuenta_Usuario = $usuario";
$res = mysqli_query($conexion, $sql);

if (mysqli_num_rows($res) === 0) {
    echo "No puedes acceder a esta clase.";
    exit;
}
?>


<?php
$idClase = (int) $_GET['id_clase'];
$usuario = $_SESSION['usu'];

// Comprobar que la clase pertenece al profesor logueado
$sql = "SELECT * FROM clase 
        WHERE id_clase = $idClase 
        AND Cuenta_Usuario = $usuario";
$res = mysqli_query($conexion, $sql);

if (mysqli_num_rows($res) === 0) {
    echo "No puedes modificar esta clase.";
    exit;
}
?>


<?php
$idComentario = (int) $_GET['id'];
$usuario = $_SESSION['usu'];

// Validar si el comentario es del usuario actual
$sql = "SELECT * FROM comentario 
        WHERE id = $idComentario 
        AND Cuenta_Usuario = $usuario";
$res = mysqli_query($conexion, $sql);

if (mysqli_num_rows($res) === 0 && $_SESSION['rol'] !== 'Administrador') {
    echo "No puedes editar o eliminar este comentario.";
    exit;
}
?>


<?php
// Revisar si el usuario está bloqueado
if ($_SESSION['bloqueado'] === "si") {
    session_destroy();
    header("Location: FormularioLogin.php?e=bloqueado");
    exit;
}
?>