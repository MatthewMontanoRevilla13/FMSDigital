<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Opiniones y sugerencias</title>
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/MensajitoFopen.css" />
</head>
<body>
  <?php include '../header.php'; ?>

  <header class="titulo-seccion">
    <div class="logo-nombre">
      <h1>Todas las recomendaciones, comentarios o sugerencias que realizan las personas</h1>
    </div>
  </header>

  <main>
    <?php
      // Verificar si se envió el formulario (SIN CAMBIOS)
      if (isset($_POST["Preguntita"])) {
          $mensaje = $_POST["Preguntita"];
          $archivo = fopen("mensaje.txt", "a");
          fwrite($archivo, $mensaje . PHP_EOL);
          fclose($archivo);
      }

      // Mostrar el contenido del archivo (SIN CAMBIOS de lectura)
      $archivo = fopen("mensaje.txt", "r");
      while (!feof($archivo)) {
          $leer = fgets($archivo);
          $ver = nl2br($leer);

          // ÚNICO cambio: envolver la línea en una tarjeta bonita
          echo '<section class="comentario"><h2>Comentario</h2><p>' . $ver . '</p></section>';
      }
      fclose($archivo);
    ?>
  </main>

  <?php include '../footer.php'; ?>
</body>
</html>