<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  
  <?php
  include '../header.php';
  // Verificar si se envió el formulario
  if (isset($_POST["Preguntita"])) {
      $mensaje = $_POST["Preguntita"];
      $archivo = fopen("mensaje.txt", "a");
      fwrite($archivo, $mensaje . PHP_EOL);
      fclose($archivo);
  }

  // Mostrar el contenido del archivo
  $archivo = fopen("mensaje.txt", "r");
  while (!feof($archivo)) {
      $leer = fgets($archivo);
      $ver = nl2br($leer);
      echo $ver;
  }
  fclose($archivo);
  ?>
</body>
</html>
