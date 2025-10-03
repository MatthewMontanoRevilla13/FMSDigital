<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel del Estudiante</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
</head>
<body>
<header>
  <div class="logo-nombre">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="Logo del colegio" class="logo">
    <h1>Julio Méndez</h1>
  </div>
</header>
<!-- Menú secundario con opciones extras -->
<div class="menu-secundario">
  <a href="#">Calendario</a>
  <a href="PanelDeEstudiante.php">Mis cursos</a>
  <!-- Botón que abre el formulario para unirse -->
  <button onclick="mostrarFormulario()">Unirse a clases</button>
  <!-- Cerrar sesión -->
  <a href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/cerrarL.php">Cerrar Sesion</a>
</div>
<!-- Formulario para ingresar un código de clase -->
<div class="formulario-clase" id="formulario">
  <form action="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/datosUC.php" method="post" id="unirseclase">
    <p><label for="Codigo">Ingresa el código de clase por favor</label></p>
    <input type="text" placeholder="Código de clase" required name="Codigo"><br>
    <button type="submit">Unirse</button>
  </form>
</div>
<!-- Aquí se muestran todas las clases a las que el estudiante está inscrito -->
<div class="contenedor-clases">
  <?php
  // Se inicia la sesión para identificar al usuario
  session_start();
  // Conexión con la base de datos
  $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
  if (!$conexion) {
      echo "<p>Error en la conexión: " . mysqli_connect_error() . "</p>";
      exit;
  }
  // Usuario actual (tomado de la sesión)
  $usuario = $_SESSION['usu'];
  // Consulta SQL: busca todas las clases donde el usuario está inscrito
  $sql = "SELECT c.id_clase, c.nombreClase, c.nomProfe
          FROM Clase c
          JOIN Cuenta_has_Clase h ON c.id_clase = h.Clase_id_clase
          WHERE h.Cuenta_Usuario = '$usuario'";
  $resultado = mysqli_query($conexion, $sql);
  // Si el estudiante tiene clases, se muestran en cuadros
  if (mysqli_num_rows($resultado) > 0) {
      while ($fila = mysqli_fetch_assoc($resultado)) {
          echo "<div class='clase' onclick=\"window.location.href='/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.php?id_clase=" . $fila['id_clase'] . "'\" style='cursor:pointer;'>";
          echo "<img src='/FMSDIGITAL/Maquetacion/imagenes/imagen historia.png'/>";
          echo "<span>" . $fila['nombreClase'] . "<br>" . $fila['nomProfe'] . "</span>";
          echo "</div>";
      }
  } else {
      // Si no hay clases inscritas
      echo "<p>No estás inscrito en ninguna clase aún.</p>";
  }
  ?>
</div>
<script>
  // Función para mostrar el formulario de unirse a clases
  function mostrarFormulario() {
    document.getElementById('formulario').style.display = 'block';
  }
  // Validación del formulario con jQuery
  $().ready(function () {
    $("#unirseclase").validate({
      rules: {
        Codigo: {
          required: true,   // no puede estar vacío
          minlength: 1      // mínimo 1 caracter
        }
      },
      messages: {
        Codigo: {
          required: "Por favor ingresa tu código de clase",
          minlength: "Debe tener al menos 1 caracter"
        }
      },
      submitHandler: function (form) {
        form.submit(); // si todo está bien, se envía
      }
    });
  });
</script>
</body>
</html>