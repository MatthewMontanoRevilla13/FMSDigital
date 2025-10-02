<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }

$id_clase = isset($_POST['id_clase']) ? (int)$_POST['id_clase'] : 0;
$texto    = isset($_POST['texto']) ? $_POST['texto'] : '';

// ⚠️ USAR EL CAMPO DE SESIÓN QUE COINCIDE CON cuenta.Usuario
$usuario = isset($_SESSION['usu']) ? (int)$_SESSION['usu'] : 0;  // <-- clave
if ($usuario <= 0) { header("Location: FormularioLogin.php"); exit; }

// archivo opcional (igual que ya lo tenías)
$archivoNombre = '';
$carpeta = "media/clases/";
if (!is_dir($carpeta)) { @mkdir($carpeta, 0777, true); }

if (isset($_FILES['archivo']) && $_FILES['archivo']['name'] != '') {
  $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
  $archivoNombre = "MSG_C".$id_clase."_U".$usuario."_".time().".".$ext;
  move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta.$archivoNombre);
}

// Insert simple
$sql = "INSERT INTO comentario (contenido, fechaEdi, Clase_id_clase, Cuenta_Usuario, archivo)
        VALUES ('$texto', NOW(), $id_clase, $usuario, '$archivoNombre')";
mysqli_query($conexion, $sql);

header("Location: Mensajeria.php?clase=".$id_clase);
exit;

