<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/CuentasDeUsuario/FormularioRegistro.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>

    <title>formulario registro</title>
</head>

<body>

  <!-- Parte superior con logo y nombre del cole -->
  <div class="header">
    <div class="logo-nombre">
      <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="Logo" class="logo">
      <span class="nombre-colegio">Zentry - Julio Mendez</span>
    </div>
  </div>
  
  <!-- Contenedor del formulario de registro -->
  <div class="container">
    <h1>REGISTRO</h1>
    <form action="/1r Sprint-FMSDigital/Maquetacion/CuentasDeUsuario/datosR.php" method="post" id="registro">
        <label>Tus nombres: </label>
        <input type="text" name="Nombres"><br>

        <label>Tus apellidos</label>
        <input type="text" name="Apellidos"><br>

        <label>Tu cédula de identidad: </label>
        <input type="text" name="CI"><br>

        <label>Tu número de celular: </label>
        <input type="text" name="Telefono"><br>

        <label>Tu contraseña: </label>
        <input type="password" name="Contraseña" required><br>

        <label>Tu fecha de nacimiento: </label>
        <input type="date" name="Nacimiento"><br>        

        <label>Tu dirección: </label>
        <input type="text" name="Direccion"><br>

        <label>Tu curso: </label>
        <input type="text" name="Curso"><br>

        <label>Tu RUDE: </label>
        <input type="password" name="Rude"><br>

        <!-- Botón para enviar el formulario -->
        <input type="submit" value="ENVIAR"><br>
    </form>

    <!-- Botón para ir al login -->
    <a href="FormularioLogin.php" class="boton">Login</a>
  </div>

  <!-- Beneficios que se muestran abajo del formulario -->
  <div class="extra-section">
    <h2>UNIRSE A Zentry</h2>
    <div class="features">
      <div class="feature">
        <h3>Siga las actualizaciones de la página</h3>
        <p>Registrándose podrá estar al tanto de todas las actualizaciones de la página.</p>
      </div>
      <div class="feature">
        <h3>Acceso a toda la página</h3>
        <p>Podrá acceder a todas las partes sin ninguna restricción.</p>
      </div>
      <div class="feature">
        <h3>Consulte cualquier pregunta</h3>
        <p>Puede escribirnos y le contestamos directamente.</p>
      </div>
    </div>
  </div>

  <!-- Validación del formulario con jQuery -->
  <script>
    $().ready(function () {
      $("#registro").validate({
        rules: {
          Nombres: {
            required: true,
            minlength: 2
          },
          Apellidos: {
            required: true
          },
          CI: {
            required: true,
            digits: true,
            minlength: 5
          },
          Telefono: {
            required: true,
            digits: true,
            minlength: 6,
            maxlength: 16
          },
          Contraseña: {
            required: true,
            minlength: 3
          },
          Direccion: {
            required: true
          },
          Rude: {
            digits: true
          }
        },
        messages: {
          Nombres: {
            required: "Por favor ingresa tu nombre",
            minlength: "Debe tener al menos 2 letras"
          },
          Apellidos: "Por favor ingresa tu apellido",
          CI: {
            required: "Por favor ingresa tu cédula",
            digits: "Solo números",
            minlength: "Debe tener al menos 5 dígitos"
          },
          Telefono: {
            required: "Por favor ingresa tu número de celular",
            digits: "Solo números",
            minlength: "Debe tener 6 dígitos",
            maxlength: "Debe tener 16 dígitos"
          },
          Contraseña: {
            required: "Por favor ingresa una contraseña",
            minlength: "Mínimo 3 caracteres"
          },
          Rude: {
            digits: "Solo números"
          }
        },
        submitHandler: function (form) {
          form.submit(); // Si todo está bien, se envía
        }
      });
    });
  </script>
</body>
</html>
