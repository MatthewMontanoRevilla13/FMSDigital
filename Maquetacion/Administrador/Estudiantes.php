<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion: " . mysqli_connect_error()); }
mysqli_set_charset($conexion, "utf8");

// --- Parámetro ---
$claseId = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : 0;
if ($claseId <= 0) { die("Clase inválida."); }

// --- Clase ---
$clase = null;
$resClase = mysqli_query($conexion, "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase WHERE id_clase=$claseId");
if ($resClase) { $clase = mysqli_fetch_assoc($resClase); }
if (!$clase) { die("Clase no encontrada."); }

// --- Estudiantes inscritos (Rol=Estudiante) ---
$alumnos = [];
$sqlAlu = "
  SELECT cu.Usuario, cu.Rol, cu.Bloqueado,
         i.Nombres, i.Apellidos, i.Curso, i.Telefono
  FROM cuenta_has_clase chc
  JOIN cuenta cu ON cu.Usuario = chc.Cuenta_Usuario
  LEFT JOIN informacion i ON i.Cuenta_Usuario = cu.Usuario
  WHERE chc.Clase_id_clase = $claseId AND cu.Rol = 'Estudiante'
  ORDER BY i.Apellidos, i.Nombres
";
$resAlu = mysqli_query($conexion, $sqlAlu);
if ($resAlu) {
  while ($r = mysqli_fetch_assoc($resAlu)) {
    $alumnos[] = $r;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Estudiantes · <?php echo htmlspecialchars($clase['nombreClase']); ?></title>

  <!-- Tu CSS global -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <!-- CSS de esta vista -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/Estudiantes.css">
</head>
<body>
  <?php include '../header.php'; ?>

  <!-- Barra superior del módulo -->
  <div class="barra-superior">
    <div class="acciones-izquierda">
      <button class="boton-gris boton" type="button" onclick="history.back()">⬅ Volver atrás</button>
      <a class="enlace" href="cursos.php">Cursos</a>

  <main class="contenedor">
    <!-- Tarjeta: datos de la clase -->
    <section class="tarjeta">
      <h1 class="titulo-pagina">Clase: <?php echo htmlspecialchars($clase['nombreClase']); ?></h1>
      <p class="texto-suave">
        <b>Profesor:</b> <?php echo htmlspecialchars($clase['nomProfe']); ?> ·
        <b>Código:</b> <?php echo htmlspecialchars($clase['codigoClase']); ?>
      </p>
    </section>

    <!-- Tarjeta: listado de estudiantes -->
    <section class="tarjeta">
      <h2 class="titulo-pagina" style="font-size:1.2rem">Estudiantes inscritos</h2>

      <div class="caja-tabla">
        <table class="tabla">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Curso</th>
              <th>Teléfono</th>
              <th>Bloqueado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if (empty($alumnos)) {
            echo '<tr><td colspan="6">No hay estudiantes inscritos en esta clase.</td></tr>';
          } else {
            foreach ($alumnos as $a) {
              $usuario   = htmlspecialchars($a['Usuario']);
              $nombre    = htmlspecialchars(trim(($a['Apellidos'] ?? '') . ' ' . ($a['Nombres'] ?? '')));
              $curso     = htmlspecialchars($a['Curso'] ?? '—');
              $telefono  = htmlspecialchars($a['Telefono'] ?? '—');
              $bloqueado = ($a['Bloqueado'] === '1') ? 'Sí' : 'No';

              echo '<tr>';
              echo '<td>' . $usuario . '</td>';
              echo '<td>' . ($nombre !== '' ? $nombre : '—') . '</td>';
              echo '<td>' . ($curso !== '' ? $curso : '—') . '</td>';
              echo '<td>' . ($telefono !== '' ? $telefono : '—') . '</td>';
              echo '<td>' . $bloqueado . '</td>';
              echo '<td><a class="enlace" href="PerfilEstudiantes.php?usuario=' . $usuario . '&clase_id=' . intval($claseId) . '">Ver / Editar</a></td>';
              echo '</tr>';
            }
          }
          ?>
          </tbody>
        </table>
      </div>

      <p style="margin-top:14px">
        <a class="enlace" href="cursos.php">← Volver a cursos</a>
      </p>
    </section>
  </main>
</body>
</html>
