<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Editar Clase</title>

  <!-- Conectamos el archivo CSS para que tenga diseño -->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/editar_Usuario.css">
</head>
<body>

  <!-- Título arriba como cabecera -->
  <div class="header">Editar Clase</div>

  <!-- Contenedor principal donde está todo el contenido centrado -->
  <div class="container">
    <h1>Modificar Nombre de Clase</h1>

    <!-- Formulario para enviar los datos al archivo PHP que hará el cambio en la BD -->
    <form action="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/modificarC.php" method="post">

      <!-- Campo donde se pone el código de la clase que se quiere cambiar -->
      <label for="codigo">Código de la Clase a modificar</label>
      <input type="text" id="codigo" name="codigoClase" required />

      <!-- Campo para escribir el nuevo nombre de esa clase -->
      <label for="nuevo_nombre">Nuevo nombre de la clase</label>
      <input type="text" id="nuevo_nombre" name="nuevoNombre" required />

      <!-- Botón para enviar todo al PHP -->
      <input type="submit" value="Modificar" />
    </form>

    <!-- Botón para volver al panel del profesor -->
    <a href="Panel_principal_de_profesor.php" class="boton">Volver a la Clase</a>
  </div>
</body>
</html>
