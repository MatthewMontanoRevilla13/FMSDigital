<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Horarios Gestión 2025</title>
  <!-- Conectamos el archivo CSS que da estilo a la página -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/PaginaWeb/horario.css" />
  <style>
    .pie{
      position: relative;
      top:12.3cm;
    }
  </style>
</head>
<body>
    <!-- Incluir el header desde header.php -->
  <?php include '../header.php'; ?>

  <!-- Sección para los cursos de nivel primario -->
  <section class="primaria">
    <h2>HORARIOS NIVEL PRIMARIO – GESTIÓN 2025</h2>

    <!-- Botones con los nombres de los cursos -->
    <div class="botonera">
      <a class="btn" href="HPrimero.php">Primero A</a>
      <a class="btn" href="HSegundo.php">Segundo A</a>
      <a class="btn" href="HTerceroA.php">Tercero A</a>
      <a class="btn" href="HTerceroB.php">Tercero B</a>
      <a class="btn" href="HCuarto.php">Cuarto A</a>
      <a class="btn" href="HQuinto.php">Quinto A</a>
      <a class="btn" href="HSexto.php|">Sexto A</a>
    </div>
  </section>

  <!-- Sección para los cursos de nivel secundario -->
  <section class="secundaria">
    <h2>HORARIOS NIVEL SECUNDARIO – GESTIÓN 2025</h2>

    <!-- Botones con los cursos de secundaria -->
    <div class="botonera">
      <a class="btn" href="HPrimeroS.php">Primero A</a>
      <a class="btn" href="HSegundoAS.php">Segundo A</a>
      <a class="btn" href="HSegundoBS.php">Segundo B</a>
      <a class="btn" href="HSegundoCS.php">Segundo C</a>
      <a class="btn" href="HTerceroAS.php">Tercero A</a>
      <a class="btn" href="HTerceroBS.php">Tercero B</a>
      <a class="btn" href="HCuartoS.php">Cuarto A</a>
      <a class="btn" href="HQuintoAS.php">Quinto A</a>
      <a class="btn" href="HQuintoBS.php">Quinto B</a>
      <a class="btn" href="HSextoAS.php">Sexto A</a>
      <a class="btn" href="HSextoBs.php">Sexto B</a>
    </div>
  </section>
 <!-- Footer -->
  <div class="pie">
      <?php include '../footer.php'; ?>
  </div>

</body>
</html>
