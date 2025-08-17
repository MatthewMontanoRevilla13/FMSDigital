<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrador</title>
  <!-- Archivo CSS para estilos-->
  <link rel="stylesheet" href="/1r Sprint-FMSDigital/Maquetacion/Administrador/admin.css">
</head>
<body>
  <!-- header con logo y nombre del colegio -->
   <!-- Footer -->
  <?php include '../header.php'; ?>
  <header>
    <div style="display: flex; align-items: center; gap: 12px;">
    </div>
  </header>

  <!-- Menú principal con enlaces -->
  <div class="menu-top">
    <a href="#">Estudiantes</a>
    <a href="#">Profesores</a>
    <a href="#">Cursos</a>
    <a href="#">Administración</a>
  </div>

<!-- ====== ADMIN: GRID DE FUNCIONES (manteniendo tu formato) ====== -->
<div class="grid-container">

  <!-- Panel grande de acceso rápido -->
  <!-- Personas -->
  <div class="item">
    <img src="/icons/users.svg" alt="Usuarios">
    <a href="/admin/usuarios/index.php">Usuarios</a>
    <p>ABM, roles, restablecer contraseñas, importación CSV.</p>
  </div>

  <div class="item">
    <img src="/icons/teacher.svg" alt="Docentes">
    <a href="/admin/docentes/index.php">Docentes</a>
    <p>Asignación a cursos, carga horaria y permisos.</p>
  </div>

  <div class="item">
    <img src="/icons/student.svg" alt="Estudiantes">
    <a href="/admin/estudiantes/index.php">Estudiantes</a>
    <p>Matrículas, traslados entre paralelos, listas.</p>
  </div>

  <!-- Académico -->
  <div class="item">
    <img src="/icons/book.svg" alt="Cursos">
    <a href="/admin/cursos/index.php">Cursos / Asignaturas</a>
    <p>Crear, editar, archivar; asignar docentes.</p>
  </div>

  <div class="item">
    <img src="/icons/timetable.svg" alt="Horarios">
    <a href="/admin/horarios/index.php">Horarios & Aulas</a>
    <p>Planificar por paralelo, exportar PDF.</p>
  </div>

  <div class="item">
    <img src="/icons/groups.svg" alt="Paralelos">
    <a href="/admin/grupos/index.php">Paralelos & Grupos</a>
    <p>Cupos, listas y transferencias.</p>
  </div>

  <!-- Contenido y comunicación -->
  <div class="item">
    <img src="/icons/folder.svg" alt="Materiales">
    <a href="/admin/materiales/index.php">Materiales</a>
    <p>Biblioteca (PDF, videos, enlaces) por curso/semana.</p>
  </div>

  <div class="item">
    <img src="/icons/megaphone.svg" alt="Avisos">
    <a href="/admin/avisos/index.php">Avisos & Mensajería</a>
    <p>Comunicados por rol/curso; historial de envíos.</p>
  </div>

  <div class="item">
    <img src="/icons/help.svg" alt="Soporte">
    <a href="/admin/tickets/index.php">Soporte / Tickets</a>
    <p>Reportes de problemas; estados y seguimiento.</p>
  </div>

  <!-- Evaluación -->
  <div class="item">
    <img src="/icons/tasks.svg" alt="Tareas">
    <a href="/admin/tareas/index.php">Tareas & Entregas</a>
    <p>Calendario, plazos y control de retrasos.</p>
  </div>

  <div class="item">
    <img src="/icons/exam.svg" alt="Exámenes">
    <a href="/admin/examenes/index.php">Exámenes & Banco</a>
    <p>Bancos de preguntas, versiones y seguridad.</p>
  </div>

  <div class="item">
    <img src="/icons/grades.svg" alt="Calificaciones">
    <a href="/admin/calificaciones/index.php">Calificaciones & Actas</a>
    <p>Registros por periodo, exportar a Excel/PDF.</p>
  </div>

  <!-- Analítica y configuración -->
  <div class="item">
    <img src="/icons/chart.svg" alt="Reportes">
    <a href="/admin/reportes/index.php">Reportes & Analítica</a>
    <p>Asistencia, rendimiento, participación (filtros).</p>
  </div>

  <div class="item">
    <img src="/icons/shield.svg" alt="Auditoría">
    <a href="/admin/auditoria/index.php">Auditoría</a>
    <p>Registro de acciones, IPs y dispositivos.</p>
  </div>

  <div class="item">
    <img src="/icons/settings.svg" alt="Configuración">
    <a href="/admin/configuracion/index.php">Configuración</a>
    <p>Periodos, backups, logo/colores, integraciones.</p>
  </div>

  <!-- Cerrar sesión -->
  <div class="item">
    <img src="/icons/logout.svg" alt="Cerrar sesión">
    <a href="/logout.php">Cerrar Sesión</a>
    <p>Salir de la plataforma de forma segura.</p>
  </div>

</div>

<footer>
  Actividad Reciente: hoy se registraron 3 usuarios, 1 curso nuevo y 5 entregas.
</footer>
