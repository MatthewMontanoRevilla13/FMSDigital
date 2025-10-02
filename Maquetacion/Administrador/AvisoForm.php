<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo aviso</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/Avisos.css">
</head>
<body>
<header>
  <div><strong>Panel Admin</strong> · Nuevo aviso</div>
  <nav>
    <a href="/FMSDIGITAL/Maquetacion/Administrador/Avisos.php">Volver a avisos</a>
  </nav>
</header>

<div class="contenedor">
  <form class="form" action="AvisoGuardar.php" method="post" enctype="multipart/form-data" autocomplete="off">
    <label for="contenido">Texto del aviso</label>
    <textarea id="contenido" name="contenido" rows="4" required></textarea>

    <label for="archivo">Archivo (opcional)</label>
    <input type="file" id="archivo" name="archivo">

    <input type="submit" value="Publicar aviso">
  </form>
</div>
</body>
</html>
