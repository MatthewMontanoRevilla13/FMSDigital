<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$rol    = $_SESSION['rol'] ?? null;
$logged = isset($_SESSION['usu']);

// ===== Links según rol =====
if ($rol === 'Administrador') {
  $panelText = "Panel de control";
  $panelLink = "/FMSdigital/Maquetacion/Administrador/admin.php";
} elseif ($rol === 'Profesor') {
  $panelText = "Mis clases";
  $panelLink = "/FMSDIGITAL/Maquetacion/Profesor/PanelPrincipalDeProfesor.php";
} elseif ($rol === 'Estudiante') {
  $panelText = "Mis clases";
  $panelLink = "/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.php";
} else {
  $panelText = null;
  $panelLink = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Plataforma Escolar</title>
  <style>
    header {
      background-color: #6b0014;
      color: white;
      padding: 16px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    .logo-nombre { display: flex; align-items: center; gap: 15px; }
    .logo { height: 50px; }
    header h1 { margin: 0; font-size: 24px; }
    header nav { display: flex; flex-wrap: wrap; gap: 15px; }
    header nav a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }
    header nav a:hover { color: #ffcdd0; }
    @media (max-width: 600px) {
      header { flex-direction: column; align-items: flex-start; gap: 10px; }
      .logo { height: 40px; }
      header h1 { font-size: 20px; }
      header nav { justify-content: center; gap: 10px; flex-wrap: wrap; }
      header nav a { font-size: 14px; }
    }
  </style>
</head>
<body>
<header>
  <div class="logo-nombre">
    <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="Logo del colegio" class="logo">
    <h1>Julio Méndez</h1>
  </div>
  <nav>
    <!-- Siempre disponible -->
    <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/PaginaPrincipal.php">Inicio</a>

    <?php if ($logged && $panelText): ?>
      <!-- Solo aparece si hay sesión -->
      <a href="<?= $panelLink ?>"><?= $panelText ?></a>
    <?php endif; ?>

    <!-- Menú público -->
    <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/noticias.php">Noticias</a>
    <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/galeria.php">Galería</a>
    <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/Horario.php">Horarios</a>
    <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/contacto.php">Contacto</a>

    <?php if ($logged): ?>
      <a href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/cerrarL.php">Cerrar sesión</a>
    <?php else: ?>
      <a href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php">Iniciar sesión</a>
    <?php endif; ?>
  </nav>
</header>
