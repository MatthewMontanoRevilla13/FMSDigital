<?php
session_start();
$conexion = mysqli_connect("localhost","root","","RegistroP6");
if(!$conexion){ die("Error en la conexión: ".mysqli_connect_error()); }

$id_tarea = isset($_GET['id_tarea']) ? intval($_GET['id_tarea']) : 0;

// para volver al índice correcto luego
$id_clase = 0;
$st = mysqli_prepare($conexion,"SELECT Clase_id_clase FROM tarea WHERE id=?");
mysqli_stmt_bind_param($st,"i",$id_tarea);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
if($row = mysqli_fetch_assoc($rs)){ $id_clase = intval($row['Clase_id_clase']); }
mysqli_stmt_close($st);

if ($id_tarea>0) {
  // eliminar entregas primero
  $st = mysqli_prepare($conexion,"DELETE FROM entrega WHERE Tarea_id=?");
  mysqli_stmt_bind_param($st,"i",$id_tarea);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);

  // eliminar tarea
  $st = mysqli_prepare($conexion,"DELETE FROM tarea WHERE id=?");
  mysqli_stmt_bind_param($st,"i",$id_tarea);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);
}

header("Location: TareasDeClase.php?id_clase=".$id_clase);
exit;