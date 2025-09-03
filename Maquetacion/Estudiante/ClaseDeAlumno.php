<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clase de Alumno</title>
  <!-- CSS -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Estudiante/ClaseDeAlumno.css">
</head>
<body>

<header>
  <div class="header-content">
    <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="Logo del Colegio" class="logo-colegio">
    <span class="nombre-usuario">Julio Mendez</span>
  </div>
</header>

<!-- Contenedor principal -->
<main class="container">
  <!-- Este menú te lleva rápido a distintas secciones -->
  <nav>
    <a href="#tablero">Tablero</a>
    <a href="#trabajo">Trabajo de clase</a>
    <a href="#materiales">Materiales</a>
  </nav>

    <div class="main">
      <!-- Sección bienvenida -->
      <section class="section" id="tablero">
          <h2>Tablon de Publicaciones</h2>
        <form action="/1r Sprint-FMSDigital/Maquetacion/Estudiante/Ccomentario.php" method="POST">
            <input type="hidden" name="id_clase" value="<?php echo $_GET['id_clase']; ?>">
            <textarea name="contenido" rows="3" cols="60" placeholder="Escribe algo para tu clase..." required></textarea><br>
            <button type="submit">Publicar</button>
        </form>
       <?php
       session_start();
       $id_clase = $_GET['id_clase'];
       $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
        if (!$conexion) {
          echo "Error en la conexión: " . mysqli_connect_error();
        exit;
        }       

        $query = "SELECT co.id, co.contenido, co.fechaPub, co.fechaEdi, cu.Usuario
            FROM Comentario co JOIN Cuenta cu ON co.Cuenta_Usuario = cu.Usuario
            WHERE co.Clase_id_clase = $id_clase
            ORDER BY co.fechaPub DESC";
        $comentarios = mysqli_query($conexion, $query);
          while ($fila = mysqli_fetch_assoc($comentarios)) {
             echo "<div class='comentario'>";
             echo "<strong>{$fila['Usuario']}</strong><br>";
             echo "<small>Publicado el: {$fila['fechaPub']}</small><br>";
            if (!empty($fila['fechaEdi'])) {
             echo "<small>Última edición: {$fila['fechaEdi']}</small><br>";
            }
             echo "<p>{$fila['contenido']}</p>";
            if ($_SESSION['usu'] === $fila['Usuario']) {
             echo "<form action='E_comentario.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='id' value='{$fila['id']}'>
                    <textarea name='nuevo_texto' rows='3' cols='60' placeholder='Escribe algo para tu clase...' required></textarea><br>
                    <button type='submit'>Editar</button>
                </form>";
            }
                if ($_SESSION['rol'] === 'Profesor') {
             echo "<form action='D_comentario.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='id' value='{$fila['id']}'>
                    <button type='submit'>Eliminar</button>
                </form>";
                }

             echo "</div><hr>";
          }
       ?>
      </section>
      <!-- Anuncios importantes -->
      <section class="section" id="anuncios">
        <h2>Anuncios</h2>
        <div class="anuncio">
          <strong>(Profesora)</strong>
          <p>Examen final la siguiente semana!</p>
        </div>
      </section>

      <!-- Tareas que tienes que hacer -->
      <section class="section" id="trabajo">
        <h2>Trabajo de Clase</h2>
        <p>Aquí podrás ver y entregar tus tareas asignadas.</p>

        <!-- Botones para ver tareas según estado -->
        <div class="tareas">
          <button class="estado no-entregadas">❗ No entregadas</button>
          <button class="estado por-entregar">📅 Por entregar</button>
          <button class="estado calificadas">✅ Calificadas</button>
        </div>

        <!-- Lista con tareas específicas -->
        <div class="lista-tareas">
          <div class="tarea">
            <strong>Tareita pendiente quien sabe cual</strong><br>
            <span>Fecha de entrega: 25 de junio</span>
          </div>
          <div class="tarea">
            <strong>Otra tareita pendiente quien sabe cual</strong><br>
            <span>Fecha de entrega: 25 de junio</span>
          </div>
          <div class="tarea">
            <strong>Otra tarea más pendiente, un descansito por favor...</strong><br>
            <span>Fecha de entrega: 25 de junio</span>
          </div>
        </div>
      </section>

      <!-- Materiales de clase -->
      <section class="section" id="materiales">
        <h2>Materiales</h2>
        <p>Consulta y descarga los materiales proporcionados.</p>
      </section>
    </main
  </div>
</body>
</html>
