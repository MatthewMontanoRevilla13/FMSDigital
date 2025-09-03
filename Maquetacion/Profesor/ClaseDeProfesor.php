<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel del Profesor</title>
  <!-- Aquí conectamos el CSS -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/profesor/ClaseDeProfesor.css" />
</head>
<body>
  <!-- Cabecera arriba con el logo y nombre del colegio -->
  <!-- Header -->
  <?php include '../header.php'; ?>

  <!-- Menú lateral para navegar rápido por secciones del panel -->
  <div class="sidebar">
    <a href="#inicio">Inicio</a>
    <a href="#anuncios">Anuncios</a>
    <a href="#materiales">Materiales</a>
    <a href="#tareas">Tareas</a>
    <a href="#calificaciones">Calificaciones</a>
    <a href="#expedientes">Expedientes</a>
    <a href="#configuracion">Configuración</a>
  </div>

  <!-- Parte principal donde está todo lo que el profe usa -->
  <div class="main">

    <!-- Sección donde se publican comentarios en clase -->
    <section class="section" id="tablero">
      <h2>Tablón de Publicaciones</h2>

      <!-- Formulario para que el profe publique un comentario -->
      <form action="/1r Sprint-FMSDigital/Maquetacion/Estudiante/Ccomentario.php" method="POST">
        <!-- Este input guarda el ID de la clase -->
        <input type="hidden" name="id_clase" value="<?php echo $_GET['id_clase']; ?>">
        <textarea name="contenido" rows="3" cols="60" placeholder="Escribe algo para tu clase..." required></textarea><br>
        <button type="submit">Publicar</button>
      </form>

      <?php
      // Iniciamos la sesión para saber quién está conectado
      session_start();

      // Obtenemos el ID de la clase desde la URL
      $id_clase = $_GET['id_clase'];

      // Conectamos a la base de datos
      $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

      // Si falla la conexión, mostramos un mensaje
      if (!$conexion) {
        echo "Error en la conexión: " . mysqli_connect_error();
        exit;
      }

      // Buscamos los comentarios de la clase y los mostramos
      $query = "SELECT co.id, co.contenido, co.fechaPub, co.fechaEdi, cu.Usuario
        FROM Comentario co JOIN Cuenta cu ON co.Cuenta_Usuario = cu.Usuario
        WHERE co.Clase_id_clase = $id_clase
        ORDER BY co.fechaPub DESC";

      $comentarios = mysqli_query($conexion, $query);

      // Recorremos todos los comentarios uno por uno
      while ($fila = mysqli_fetch_assoc($comentarios)) {
        echo "<div class='comentario'>";
        echo "<strong>{$fila['Usuario']}</strong><br>";
        echo "<small>Publicado el: {$fila['fechaPub']}</small><br>";

        // Si el comentario fue editado, también mostramos esa fecha
        if (!empty($fila['fechaEdi'])) {
          echo "<small>Última edición: {$fila['fechaEdi']}</small><br>";
        }

        // Mostramos el contenido del comentario
        echo "<p>{$fila['contenido']}</p>";

        // Si el comentario es del usuario actual, puede editarlo
        if ($_SESSION['usu'] === $fila['Usuario']) {
          echo "<form action='E_comentario.php' method='POST' style='display:inline;'>
                  <input type='hidden' name='id' value='{$fila['id']}'>
                  <textarea name='nuevo_texto' rows='3' cols='60' placeholder='Escribe algo para tu clase...' required></textarea><br>
                  <button type='submit'>Editar</button>
              </form>";
        }

        // Si el usuario es profe, puede eliminar cualquier comentario
        if ($_SESSION['rol'] === 'Profesor') {
          echo "<form action='D_comentario.php' method='POST' style='display:inline;'>
                  <input type='hidden' name='id' value='{$fila['id']}'>
                  <input type='hidden' name='id_clase' value='$id_clase'>
                  <button type='submit'>Eliminar</button>
              </form>";
        }

        echo "</div><hr>";
      }
      ?>
    </section>

    <!-- Anuncios del profesor a los alumnos -->
    <section class="section" id="anuncios">
      <h2>Publicar Anuncios</h2>
      <p>Escriba mensajes para mantener informados a sus alumnos.</p>
      <a href="#" class="btn">Crear Anuncio</a>
    </section>

    <!-- Materiales que el profesor puede compartir -->
    <section class="section" id="materiales">
      <h2>Subir Materiales</h2>
      <p>Comparta archivos, enlaces o recursos con los estudiantes.</p>
      <a href="#" class="btn">Subir Material</a>
    </section>

    <!-- Tareas asignadas por el profesor -->
    <section class="section" id="tareas">
      <h2>Gestionar Tareas</h2>
      <p>Asigne nuevas tareas y revise las entregas de los estudiantes.</p>
      <a href="#" class="btn">Crear Tarea</a>
      <a href="#" class="btn">Ver Entregas</a>
    </section>

    <!-- Ver o editar notas -->
    <section class="section" id="calificaciones">
      <h2>Calificaciones</h2>
      <p>Revise y edite las notas de los alumnos.</p>
      <a href="#" class="btn">Ver Calificaciones</a>
    </section>

    <!-- Información del estudiante -->
    <section class="section" id="expedientes">
      <h2>Expediente de Estudiantes</h2>
      <p>Consulte los historiales académicos y comportamentales.</p>
      <a href="#" class="btn">Ver Expedientes</a>
    </section>

    <!-- Configuración del perfil del profe o de la clase -->
    <section class="section" id="configuracion">
      <h2>Configuración</h2>
      <p>Actualice su perfil o los parámetros de la clase.</p>
      <a href="#" class="btn">Editar Configuración</a>
    </section>
  </div>
  <!-- Footer -->
  <?php include '../footer.php'; ?>
</body>
</html>
