<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Librerías de validación jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>

  <title>Formulario crear clase</title>

  <!-- Estilos externos del formulario -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Profesor/FormularioCrearClase.css">

  <!-- Título del colegio -->
  <div class="header">Julio Mendez</div>
</head>

<body>
  <div class="container">
    <h1>CREA TU CLASE</h1>

    <!-- Formulario para enviar datos de clase al archivo PHP -->
    <form action="/1r Sprint-FMSDigital/Maquetacion/Profesor/DatosCrearClase.php" method="post" id="CrearClase">
        <label for="">Nombre de su clase: </label>
        <input type="text" name="nombreClase"><br>

        <label for="">Codigo de clase: </label>
        <input type="text" name="codigoClase"><br>

        <label for="">Tu nombre completo: </label>
        <input type="text" name="nomProfe"><br>

        <input type="submit" value="ENVIAR"><br>
    </form>
  </div>

  <h2>UNIRSE A Zentry</h2>
</body>

<!-- Validación del formulario con jQuery -->
<script>
  $().ready(function () {
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
        form.submit(); // Si todo está bien, se envía el formulario
      }
    });
  });
</script>
</html>
