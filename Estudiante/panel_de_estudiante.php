<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Configuraciones básicas -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel del Estudiante</title>

  <!-- CSS y librerías para validación -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Estudiante/panel_de_estudiante.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
</head>

<body>
  <!-- Encabezado con logo, nombre y datos del alumno -->
  <!-- header -->
  <?php include '../header.php'; ?>

  <!-- Segundo menú con opciones extra -->
  <div class="menu-secundario">
    <a href="#">Calendario</a>
    <a href="#">Mis cursos</a>
    <button onclick="mostrarFormulario()">Unirse a clases</button>
    <a href="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/cerrarL.php">Cerrar Sesion</a>
  </div>

  <!-- Formulario para unirse a una clase con código -->
  <div class="formulario-clase" id="formulario">
    <form action="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/datosUC.php" method="post" id="unirseclase">
      <p><label for="Codigo">Ingresa el código de clase por favor</label></p>
      <input type="text" placeholder="Código de clase" required name="Codigo"><br>
      <button type="submit">Unirse</button>
    </form>
  </div>

  <!-- Clases a las que el estudiante está inscrito -->
  <div class="contenedor-clases">
    <?php
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

    if (!$conexion) {
        echo "<p>Error en la conexión: " . mysqli_connect_error() . "</p>";
        exit;
    }

    $usuario = $_SESSION['usu'];

    $sql = "SELECT c.id_clase, c.nombreClase, c.nomProfe
            FROM Clase c
            JOIN Cuenta_has_Clase h ON c.id_clase = h.Clase_id_clase
            WHERE h.Cuenta_Usuario = '$usuario'";

    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            echo "<div class='clase' onclick=\"window.location.href='/1r Sprint-FMSDigital/Maquetacion/Estudiante/clase_de_alumno.php?id_clase=" . $fila['id_clase'] . "'\" style='cursor:pointer;'>";
            echo "<img src='/1r Sprint-FMSDigital/Maquetacion/imagenes/imagen historia.png'/>";
            echo "<span>" . $fila['nombreClase'] . "<br>" . $fila['nomProfe'] . "</span>";
            echo "</div>";
        }
    } else {
        echo "<p>No estás inscrito en ninguna clase aún.</p>";
    }
    ?>
  </div>

  <!-- Script para mostrar el formulario y validar -->
  <script>
    // Muestra el formulario de unirse
    function mostrarFormulario() {
      document.getElementById('formulario').style.display = 'block';
    }

    // Validación con jQuery
    $().ready(function () {
      $("#unirseclase").validate({
        rules: {
          Codigo: {
            required: true,
            minlength: 1
          }
        },
        messages: {
          Codigo: {
            required: "Por favor ingresa tu código de clase",
            minlength: "Debe tener al menos 1 caracter"
          }
        },
        submitHandler: function (form) {
          form.submit(); // Si todo está bien, se manda
        }
      });
    });
  </script>
</body>
</html>
