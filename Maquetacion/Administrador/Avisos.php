<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }

$consulta = "SELECT id, contenido, fechaEdi, Cuenta_Usuario FROM comentario WHERE Clase_id_clase = 0 ORDER BY id DESC";
$avisos = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Avisos </title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/Avisos.css">
</head>
<body>
<header>
  <div><strong>Panel Admin</strong> · Avisos</div>
  <nav>
    <a href="/FMSDIGITAL/Maquetacion/Administrador/Admin.php">Volver al panel</a>
  </nav>
</header>

<div class="contenedor">
  <div class="barra">
    <h2>Listado de avisos</h2>
    <div>
      <a class="boton" href="/FMSDIGITAL/Maquetacion/Administrador/AvisoForm.php"> Nuevo aviso</a>
    </div>
  </div>

  <table class="tabla">
    <thead>
      <tr>
        <th>Contenido</th>
        <th>Autor (Usuario)</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while ($fila = mysqli_fetch_assoc($avisos)) {
        echo "<tr>";
        echo "<td>".$fila['contenido']."</td>";
        echo "<td>".$fila['Cuenta_Usuario']."</td>";
        echo "<td>".$fila['fechaEdi']."</td>";
        echo "<td>";
        echo "<a class='boton' href='AvisoEliminar.php?id=".$fila['id']."' onclick='return confirm(\"¿Eliminar aviso?\")'>Eliminar</a>";
        echo "</td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</body>
</html>
