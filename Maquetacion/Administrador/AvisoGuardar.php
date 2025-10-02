<?php
// ========== Guardar aviso como comentario con Clase_id_clase = 0 ==========
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }

$contenido = isset($_POST['contenido']) ? $_POST['contenido'] : '';
$archivoNombre = '';

// Directorio simple para archivos de avisos
$carpeta = "media/avisos/";
if (!is_dir($carpeta)) { @mkdir($carpeta, 0777, true); }

if (isset($_FILES['archivo']) && $_FILES['archivo']['name'] != '') {
  $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
  $archivoNombre = "AVISO_".time().".".$ext;
  move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta.$archivoNombre);
}

$usuario = isset($_SESSION['usu']) ? (int)$_SESSION['usu'] : 0;
if ($usuario <= 0) { header("Location: FormularioLogin.php"); exit; }

$sql = "INSERT INTO comentario (contenido, fechaEdi, Clase_id_clase, Cuenta_Usuario, archivo)
        VALUES ('$contenido', NOW(), 0, $usuario, '$archivoNombre')";
mysqli_query($conexion, $sql);

header("Location: Avisos.php");
exit;
