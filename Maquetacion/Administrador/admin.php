<?php
// --- SOLO ADMIN ---
session_start();

// Si NO hay sesión o el rol no es Administrador, redirigimos al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrador</title>
  <!-- Archivo CSS para estilos-->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
</head>
<body>
  <!-- header con logo y nombre del colegio -->
  <?php include '../header.php'; ?>

  <header>
    <div style="display: flex; align-items: center; gap: 12px;">
 
    </div>
  </header>

  <!-- Menú principal con enlaces -->
  <div class="menu-top">
    <a href="cursos.php">Cursos</a>
  </div>

  <!-- ====== ADMIN: GRID DE FUNCIONES ====== -->
  <div class="grid-container">

    <!-- Personas -->
    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/usu.png" alt="Usuarios">
      <a href="/FMSDIGITAL/Maquetacion/Administrador/usuarios.php">Usuarios</a>
      <p>ABM, roles, restablecer contraseñas, importación CSV.</p>
    </div>

    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/schedule_118702.png" alt="Horarios">
      <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/Horario.php">Horarios & Aulas</a>
      <p>Planificar por paralelo, exportar PDF.</p>
    </div>

    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/avisos.png" alt="Avisos">
      <a href="/FMSDIGITAL/Maquetacion/Administrador/mensajeria.php">Avisos & Mensajería</a>
      <p>Comunicados por rol/curso; historial de envíos.</p>
    </div>

    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/soporte.png" alt="Soporte">
      <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/MensajitoFopen.php">Soporte / Tickets</a>
      <p>Reportes de problemas; estados y seguimiento.</p>
    </div>

    <!-- Evaluación -->
    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/tareas.png" alt="Tareas">
      <a href="/FMSDIGITAL/Maquetacion/Administrador/tareas.php">Tareas & Entregas</a>
      <p>Calendario, plazos y control de retrasos.</p>
    </div>

    <div class="item">
      <img src="/FMSDIGITAL/Maquetacion/imagenes/cal.png" alt="Calificaciones">
      <a href="/FMSDIGITAL/Maquetacion/Administrador/calificaciones.php">Calificaciones</a>
      <p>Registros por periodo, exportar a Excel/PDF.</p>
    </div>
    <!-- Cerrar sesión -->
   <div class="item">
  <img src="/FMSDIGITAL/Maquetacion/imagenes/cerrar.png" alt="Cerrar sesión">
<a href="/FMSDIGITAL/Maquetacion/Administrador/CerrarSesion.php">Cerrar Sesión</a>
  <p>Salir de la plataforma de forma segura.</p>
</div>
  </div>

  <footer>
    Actividad Reciente: hoy se registraron 3 usuarios, 1 curso nuevo y 5 entregas.
  </footer>
</body>
</html>
