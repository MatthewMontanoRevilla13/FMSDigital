<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formulario Login</title>
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/formulario_login.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
</head>
<body>

  <!-- Parte de arriba con el logo y el nombre del cole -->
  <div class="header">
    <div class="logo-nombre">
      <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="Logo" class="logo">
      <span class="nombre-colegio">Julio Mendez</span>
    </div>
  </div>

  <!-- Contenedor blanco del login -->
  <div class="container">
    <h1>Inicio de Sesión</h1>

    <!-- Formulario que envía usuario y contraseña a datosL.php -->
    <form action="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/datosL.php" method="post" id="login">
      <label for="Usuario">Tu cédula de identidad:</label>
      <input type="text" name="Usuario" required>

      <label for="Contraseña">Tu contraseña:</label>
      <input type="password" name="Contraseña" required>

      <input type="submit" value="ENVIAR">
    </form>

    <!-- Link para registrarse que se ve como botón -->
    <a href="formulario_registro.php" class="boton">Registrarse</a>
  </div>

  <!-- Sección extra con info bonita abajo del login -->
  <div class="extra-section">
    <h2>Unirse a Zentry</h2>

    <div class="features">
      <div class="feature">
        <h3>Sigue las actualizaciones</h3>
        <p>Regístrate para estar al tanto de todas las novedades de la plataforma.</p>
      </div>
      <div class="feature">
        <h3>Acceso total</h3>
        <p>Accede a todo el contenido educativo sin restricciones.</p>
      </div>
      <div class="feature">
        <h3>Soporte directo</h3>
        <p>Haz tus consultas directamente y obtén respuestas rápidas.</p>
      </div>
    </div>
  </div>

  <!-- Validación con jQuery para evitar campos vacíos o mal escritos -->
  <script>
    $().ready(function () {
      $("#login").validate({
        rules: {
          Usuario: {
            required: true,
            digits: true, // solo números
            minlength: 5
          },
          Contraseña: {
            required: true,
            minlength: 3
          }
        },
        messages: {
          Usuario: {
            required: "Por favor ingresa tu cédula de identidad",
            digits: "Solo se permiten números",
            minlength: "Debe tener al menos 5 dígitos"
          },
          Contraseña: {
            required: "Por favor ingresa tu contraseña",
            minlength: "Debe tener al menos 3 caracteres"
          }
        },
        submitHandler: function (form) {
          form.submit(); // Si todo está bien, se envía el formulario
        }
      });
    });
  </script>
</body>
</html>
