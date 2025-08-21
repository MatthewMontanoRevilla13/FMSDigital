<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contacto</title>
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/Contacto.css" />
</head>
<body>

  <!-- Incluir el header desde header.php -->
  <?php include '../header.php'; ?>

  <!-- Contenido principal con 2 columnas: info y mapa -->
  <main class="contenido-doble">
    
    <!-- Parte izquierda: info de contacto -->
    <div class="info-contacto">
      <h1>Contáctanos</h1>

      <section>
        <h2>Números:</h2>
        <ul>
          <li>SIE: 80980151</li>
          <li>Telf. 4231327</li>
        </ul>
      </section>

      <section>
        <h2>Dirección:</h2>
        <ul>
          <li>Heroínas y Belzu</li>                                                
          <li>Cochabamba - Bolivia</li>
        </ul>
      </section>

      <section>
        <h2>Email:</h2>
        <p>fmsdigital@gmail.com</p>
      </section>
    </div>

    <!-- Parte derecha: mapa de ubicación -->
    <div class="mapa-contacto">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3807.4691308374126!2d-66.14569772394543!3d-17.38925976424709!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93e373003cd7846b%3A0x69082a434de6c7c3!2sU.E.%20JULIO%20M%C3%89NDEZ!5e0!3m2!1ses!2sbo!4v1753894012666!5m2!1ses!2sbo" 
        width="800" height="600" style="border:0;" allowfullscreen="" 
        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>

  </main>
  <!--footer-->
    <?php include '../footer.php'; ?>
</body>
</html>
