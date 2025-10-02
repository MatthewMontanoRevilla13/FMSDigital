<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
  // Borrar solo avisos generales
  $borrar = "DELETE FROM comentario WHERE id = $id AND Clase_id_clase = 0";
  mysqli_query($conexion, $borrar);
}

header("Location: Avisos.php");
exit;
