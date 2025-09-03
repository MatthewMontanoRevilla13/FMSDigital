<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Editar Usuario</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/EditarUsuario.css">
</head>
<body>

  <!-- Encabezado con logo y nombre del cole -->
  <div class="header">
    <div class="logo-nombre">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="Logo" class="logo">
      <span class="nombre-colegio">Julio Mendez</span>
    </div>
  </div>

  <!-- Contenedor del formulario -->
  <div class="container">
    <h1>Modificar Datos de Usuario</h1>

    <!-- Formulario que envía los datos a modificar.php -->
    <form action="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/modificar.php" method="post">

      <!-- Campo para ingresar el CI del usuario -->
      <label for="ci">CI del usuario a modificar</label>
      <input type="text" id="ci" name="CI" required />

      <!-- Campo para elegir qué dato quieres cambiar -->
      <label for="campo">¿Qué campo deseas modificar?</label>
      <select id="campo" name="campo" required>
        <option value="Nombres">Nombres</option>
        <option value="Apellidos">Apellidos</option>
        <option value="Telefono">Numero de Celular</option>
        <option value="Nacimiento">Fecha de Nacimiento</option>
        <option value="Direccion">Direccion</option>
      </select>

      <!-- Aquí va el nuevo valor que reemplazará al anterior -->
      <label for="nuevo_valor">Nuevo valor</label>
      <input type="text" id="nuevo_valor" name="nuevo_valor" required />

      <!-- Botón para enviar el formulario -->
      <input type="submit" value="Modificar" />
    </form>

    <!-- Botón para volver al login -->
    <a href="FormularioLogin.php" class="boton">Volver al Login</a>
  </div>
</body>
</html>