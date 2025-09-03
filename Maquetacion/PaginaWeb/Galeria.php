<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/PaginaWeb/Galeria.css">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
 <!-- Header -->
  <?php include '../header.php'; ?>

  <div class="siete">
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 12.jpeg" alt="Imagen 1" class="uno"></div>
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 13.jpeg" alt="Imagen 2" class="dos"></div>
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 16.jpeg" alt="Imagen 3" class="tres"></div>
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 17.jpeg" alt="Imagen 4" class="cuatro"></div>
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 19.jpeg" alt="Imagen 5" class="cinco"></div>
    <div><img src="/FMSDIGITAL/Maquetacion/imagenes/imagen 20.jpeg" alt="Imagen 6" class="seis"></div>
  </div>

  <script>
    $(".uno, .dos, .tres, .cuatro, .cinco, .seis").hover(
      function () {
        $(this).animate({ margin: "20px" }, 500);
      },
      function () {
        $(this).animate({ margin: "1cm" }, 500);
      }
    );
  </script>
 <!-- Footer -->
  <?php include '../footer.php'; ?>
</body>
</html>
