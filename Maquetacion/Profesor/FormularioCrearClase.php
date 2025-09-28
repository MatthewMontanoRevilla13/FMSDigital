<?php
// --- Solo PROFESOR ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// Prefill del nombre completo desde la sesión (editable)
$prefillNombre = trim(
  (isset($_SESSION['nom']) ? $_SESSION['nom'] : '') . ' ' .
  (isset($_SESSION['apes']) ? $_SESSION['apes'] : '')
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Clase</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Profesor/PanelPrincipalDeProfesor.css">
  <style>
    body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #fff7f8; color: #333; }
    .container { max-width: 700px; margin: 40px auto; padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
    h1 { margin-top: 0; color: #6b0014; }
    label { display: block; font-weight: bold; margin-top: 16px; margin-bottom: 6px; }
    input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; }
    .btn { margin-top: 20px; padding: 12px 20px; background-color: #6b0014; color: #fff; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 16px; }
    .btn:hover { background-color: #990033; }
    label.error { color: red; font-size: 14px; margin-top: 4px; display: block; }
  </style>
  <!-- jQuery + jQuery Validate (+ additional-methods para 'pattern') -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/additional-methods.min.js"></script>
</head>
<body>
  <?php include '../header.php'; ?>

  <div class="container">
    <h1>Crear nueva clase</h1>

    <?php if (isset($_GET['error'])): ?>
      <p style="color:red"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="DatosCrearClase.php" method="post" id="form-crear-clase" novalidate>
      <label for="nombreCompleto">Tu nombre completo *</label>
      <input type="text" id="nombreCompleto" name="nombreCompleto" value="<?= htmlspecialchars($prefillNombre) ?>" placeholder="Ej: Juan Pérez" />

      <label for="nombreClase">Nombre de la clase *</label>
      <input type="text" id="nombreClase" name="nombreClase" placeholder="Ej: Matemática 2°B" />

      <label for="codigoClase">Código de clase (opcional)</label>
      <input type="text" id="codigoClase" name="codigoClase" placeholder="Ej: ABC123" />
      <!-- Si lo dejas vacío, el servidor genera uno único -->

      <button type="submit" class="btn">Crear clase</button>
    </form>
  </div>

  <script>
  $(function() {
    $("#form-crear-clase").validate({
      rules: {
        nombreCompleto: {
          required: true,
          minlength: 5,
          maxlength: 80
        },
        nombreClase: {
          required: true,
          minlength: 3
        },
        codigoClase: {
          minlength: 4,
          maxlength: 10,
          pattern: /^[A-Za-z0-9\-]+$/ // letras, números y guión
        }
      },
      messages: {
        nombreCompleto: {
          required: "Por favor escribe tu nombre completo.",
          minlength: "Debe tener al menos 5 caracteres.",
          maxlength: "No puede superar 80 caracteres."
        },
        nombreClase: {
          required: "Ingresa el nombre de la clase.",
          minlength: "Debe tener al menos 3 caracteres."
        },
        codigoClase: {
          minlength: "El código debe tener al menos 4 caracteres.",
          maxlength: "El código no puede superar los 10 caracteres.",
          pattern: "Solo letras, números y guiones (sin espacios)."
        }
      },
      errorPlacement: function(error, element) { error.insertAfter(element); },
      submitHandler: function(form) { form.submit(); }
    });
  });
  </script>
</body>
</html>
