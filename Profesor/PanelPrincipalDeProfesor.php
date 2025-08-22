<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel del Profesor</title>
  <!-- Estilos del panel principal y del formulario para crear clases -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Profesor/PanelPrincipalDeProfesor.css">
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Profesor/FormularioCrearClase.css">
  
  <!-- Librerías para validaciones con jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
</head>
<body>
  <!-- CABECERA CON LOGO Y NOMBRE DEL PROFESOR -->
  <header>
    <div class="logo-nombre">
      <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="Logo del colegio" class="logo">
      <div class="titulo-colegio">JULIO MENDEZ</div>
    </div>

    <!-- Nombre del profe desde sesión -->
    <div class="nombre-alumno">
      <?php
      session_start(); // iniciamos sesión
      echo $_SESSION['nom'] . " " . $_SESSION['apes']; // mostramos nombre y apellidos
      ?>
    </div>

    <!-- Menú principal -->
    <nav>
      <a href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/PaginaPrincipal.php">Inicio</a>
      <a href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/Noticias.php">Noticias</a>
      <a href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/Galeria.php">Galería</a>
      <a href="#">Documentos</a>
      <a href="/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/Contacto.php">Contacto</a>
    </nav>
  </header>

  <!-- Menú secundario debajo del header -->
  <div class="menu-secundario">
    <a href="#">Calendario</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Profesor/FormularioEditarClase.php">Editar cursos</a>
    <a href="#" id="mostrarFormulario">Crear una clase</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/CuentasDeUsuario/cerrarL.php">Cerrar Sesion</a>
  </div>

  <!-- MOSTRAR TODAS LAS CLASES DEL PROFESOR -->
  <div class="contenedor-clases">
    <?php
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
    if (!$conexion) {
        echo "<p>Error en la conexión: " . mysqli_connect_error() . "</p>";
        exit;
    }

    $usuario = $_SESSION['usu']; // usuario activo

    // Trae todas las clases creadas por ese profe
    $sql = "SELECT id_clase, nombreClase, codigoClase FROM Clase WHERE Cuenta_Usuario = '$usuario'";
    $resultado = mysqli_query($conexion, $sql);

    // Si hay clases, las muestra en tarjetas
    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            echo "<div class='clase' onclick=\"window.location.href='ClaseDeProfesor.php?id_clase=" . $fila['id_clase'] . "'\" style='cursor:pointer;'>";
            echo "<img src='/1r Sprint-FMSDigital/Maquetacion/imagenes/imagen fisica.png'/>";
            echo "<span>" . $fila['nombreClase'] . "</span>";
            echo "</div>";
        }
    } else {
        echo '<p style="padding: 20px;">Aún no has creado ninguna clase.</p>';
    }
    ?>
  </div>

  <!-- FORMULARIO PARA CREAR UNA CLASE (OCULTO AL INICIO) -->
  <div id="formularioCrearClase" style="display:none; padding: 20px; border: 2px solid #ccc; margin: 20px; background-color: #f9f9f9;">
    <h2>CREA TU CLASE</h2>
    <form action="/1r Sprint-FMSDigital/Maquetacion/Profesor/DatosCrearClase.php" method="post" id="CrearClase">
      <label>Nombre de su clase:</label>
      <input type="text" name="nombreClase"><br>
      <label>Codigo de clase:</label>
      <input type="text" name="codigoClase"><br>
      <label>Tu nombre completo:</label>
      <input type="text" name="nomProfe"><br>
      <input type="submit" value="ENVIAR">
    </form>
  </div>

  <!-- SCRIPT PARA MOSTRAR/OCULTAR FORMULARIO Y VALIDARLO -->
  <script>
    // Mostrar u ocultar el formulario al hacer clic en "Crear una clase"
    $("#mostrarFormulario").on("click", function (e) {
      e.preventDefault();
      $("#formularioCrearClase").slideToggle();
    });

    // Validaciones del formulario con jQuery
    $(document).ready(function () {
      $("#CrearClase").validate({
        rules: {
          nombreClase: {
            required: true,
            minlength: 3
          },
          codigoClase: {
            required: true,
            minlength: 6,
            maxlength: 15
          },
          nomProfe: {
            required: true,
            minlength: 5
          }
        },
        messages: {
          nombreClase: {
            required: "Por favor ingrese el nombre de la clase",
            minlength: "Debe tener al menos 3 caracteres"
          },
          codigoClase: {
            required: "Por favor ingrese su codigo de clase",
            minlength: "Debe tener al menos 6 caracteres",
            maxlength: "No debe superar los 15 caracteres"
          },
          nomProfe: {
            required: "Por favor ingrese su nombre completo",
            minlength: "Debe tener al menos 5 caracteres"
          }
        },
        submitHandler: function (form) {
          form.submit();
        }
      });
    });
  </script>
</body>
</html>
