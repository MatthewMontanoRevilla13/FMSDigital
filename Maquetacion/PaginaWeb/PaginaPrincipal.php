<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Plataforma Escolar</title>

  <!-- Conexión con el CSS -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/PaginaWeb/PaginaPrincipal.css">
    <style>
    .formulario-opinion{
  background-color:#4b0010;
  padding: 3px;
  position: absolute;
  color: white;
  border: 5px solid black;
  top:25.6cm;
}
.opinion:hover{
background-color: rgb(224, 224, 224);
}
  </style>
</head>
<body>

  <!-- Header con el título y menú -->
   <!-- header -->
  <?php include '../header.php'; ?>

  <!-- Banner de bienvenida -->
  <div class="banner">
    <h2>Bienvenidos a Julio Mendez</h2>
  <p>Explora noticias, tareas, documentos y más.</p>
</div>

  <!-- Carrusel de imágenes -->
  <div class="carrusel">
  <div class="imagenes">
    <!-- Lote 1 -->
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela1.JPEG" alt="Foto 1">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela2.JPEG" alt="Foto 2">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela3.JPEG" alt="Foto 3">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela4.JPEG" alt="Foto 4">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela5.JPEG" alt="Foto 5">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela6.JPEG" alt="Foto 6">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela7.JPEG" alt="Foto 7">
    <!-- Lote 2 (igual al 1) -->
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela1.JPEG" alt="Foto 1 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela2.JPEG" alt="Foto 2 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela3.JPEG" alt="Foto 3 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela4.JPEG" alt="Foto 4 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela5.JPEG" alt="Foto 5 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela6.JPEG" alt="Foto 6 repetida">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/escuela7.JPEG" alt="Foto 7 repetida">
  </div>
</div>

  <!-- Tarjetas con info extra (calendario, galería, docs) -->
<!-- Tarjetas: Visión, Misión, Documentos -->
<div class="container">
  <div class="section">
    <h3>Visión</h3>
    <p>
      La Unidad Educativa “Julio Méndez” forma estudiantes de manera integral y holística,
      con un nivel académico que le permita prepararse y desenvolverse en la vida y para la vida,
      con valores y principios sociocomunitarios, y la participación de docentes y padres de familia.
      Comprometidos con una educación desde la realidad.
    </p>
  </div>

  <div class="section">
    <h3>Misión</h3>
    <p>
      La Unidad Educativa “Julio Méndez” es una institución, legalmente constituida; forma personas íntegras,
      con valores humanos, sólida preparación académica y vocación de servicio, con la participación de docentes y
      padres de familia, comprometidos con una educación desde la realidad, con la fuerza de su carisma.
    </p>
  </div>

  <div class="section">
    <h3>Documentos</h3>
    <p><a href="/FMSDIGITAL/Maquetacion/PaginaWeb/documentos/PLAN DE CONVIVENCIA PACIFICA Y ARMONICA (2) (1).pdf" target="_blank" rel="noopener">plan de CONVIVENCIA</a></p>
    <p><a href="/FMSDIGITAL/Maquetacion/PaginaWeb/documentos/REGLAMENTO INTERNO JULIO MÉNDEZ actualizado (2).pdf" target="_blank" rel="noopener">reglamento academico</a></p>
    <p><a href="/FMSDIGITAL/Maquetacion/PaginaWeb/documentos/PSP 2025 JULIO MENDEZ (4).pdf" target="_blank" rel="noopener">PSP ANUAL</a></p>
  </div>
</div>
<div class="formulario-opinion">
  <form action="MensajitoFopen.php" method="post">
   <label for="Preguntita">¿En que aspectos crees que puede mejorar la escuela?</label>
   <input type="text" id="opinion" name="Preguntita" placeholder="Escribe tu recomendacion aca">
   <input type="submit" value="enviar">
   </form>
  </div>
  <!-- Pie de página con datos del cole -->
 <!-- Footer -->
  <?php include '../footer.php'; ?>

</body>
</html>

