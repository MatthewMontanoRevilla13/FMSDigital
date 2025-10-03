<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" /> 
  <title>Formulario Login</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
</head>
<body>
  <!-- Encabezado de la página, donde va el logo y el nombre del colegio -->
  <div class="header">
    <div class="logo-nombre">
      <!-- Imagen del logo -->
      <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="Logo" class="logo">
      <!-- Texto con el nombre del colegio -->
      <span class="nombre-colegio">Julio Mendez</span>
    </div>
  </div>
  <!-- Caja principal que contiene el formulario de login -->
  <div class="container">
    <h1>Inicio de Sesión</h1>
    <!-- Formulario que pide CI y contraseña. Al enviarlo, manda los datos al archivo datosL.php -->
    <form action="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/datosL.php" method="post" id="login">
      <!-- Campo para el usuario (CI) -->
      <label for="Usuario">Tu cédula de identidad:</label>
      <input type="text" name="Usuario" required>
      <!-- Campo para la contraseña -->
      <label for="Contraseña">Tu contraseña:</label>
      <input type="password" name="Contraseña" required>
      <!-- Botón de enviar -->
      <input type="submit" value="ENVIAR">
    </form>
    <!-- Botón enlace para ir al registro de usuario -->
    <a href="FormularioRegistro.php" class="boton">Registrarse</a>
  </div>
  <!-- Sección informativa debajo del formulario, con ventajas de registrarse -->
  <div class="extra-section">
    <h2>Unirse a FMSDIGITAL</h2>
    <!-- Tres bloques con textos para que la gente pueda animarse mas a unirse -->
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

  <!-- Script que valida el formulario usando jQuery -->
  <script>
    $().ready(function () {
      // Activamos la validación para el formulario con login
      $("#login").validate({
        rules: {
          Usuario: {
            required: true, // no puede estar vacío
            digits: true,   // solo acepta números
            minlength: 5    // al menos 5 dígitos
          },
          Contraseña: {
            required: true, // no puede estar vacío
            minlength: 3    // al menos 3 caracteres
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
          // Si todo está correcto, se envía el formulario
          form.submit(); 
        }
      });
    });
  </script>
</body>
</html>