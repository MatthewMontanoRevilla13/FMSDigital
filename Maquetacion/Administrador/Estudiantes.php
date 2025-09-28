<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión (simple) ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { echo "Error en la conexion".mysqli_error($conexion); die(); }
mysqli_set_charset($conexion, "utf8");

// --- Parámetro ---
$claseId = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : 0;
if ($claseId <= 0) { die("Clase inválida."); }

// --- Info de la clase (simple) ---
$resClase = mysqli_query($conexion, "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase WHERE id_clase=$claseId");
$clase = $resClase ? mysqli_fetch_assoc($resClase) : null;
if (!$clase) { die("Clase no encontrada."); }

// --- Estudiantes inscritos (solo Rol=Estudiante) ---
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
$alumnos = [];
if ($resAlu) { while ($r = mysqli_fetch_assoc($resAlu)) { $alumnos[] = $r; } }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Estudiantes · <?= $clase['nombreClase'] ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin:20px auto;max-width:1000px}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    .btn{background:#6b0014;color:#fff;border:0;border-radius:8px;padding:8px 14px;cursor:pointer}
    .btn.gray{background:#666}
    a{color:#6b0014;text-decoration:none}
    table{width:100%;border-collapse:collapse;background:#fff}
    th,td{border:1px solid #e5e5e5;padding:8px;text-align:left}
    th{background:#f8f8f8}
    .topbar{display:flex;align-items:center;gap:10px;justify-content:space-between;padding:10px 20px}
    .left-actions{display:flex;gap:8px;align-items:center}
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <header class="topbar">
    <div class="left-actions">
      <button class="btn gray" onclick="history.back()">⬅ Volver atrás</button>
      <a href="cursos.php">Cursos</a>
    </div>
    <div>
      <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="logo" style="height:36px">
    </div>
  </header>

  <section class="card">
    <h1 style="margin:0 0 6px 0;">Clase: <?= $clase['nombreClase'] ?></h1>
    <p style="margin:0;">
      <b>Profesor:</b> <?= $clase['nomProfe'] ?> ·
      <b>Código:</b> <?= $clase['codigoClase'] ?>
    </p>
  </section>

  <section class="card">
    <h2 style="margin-top:0;">Estudiantes inscritos</h2>
    <table>
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
        <?php if (!$alumnos): ?>
          <tr><td colspan="6">No hay estudiantes inscritos en esta clase.</td></tr>
        <?php else: foreach ($alumnos as $a): ?>
          <tr>
            <td><?= $a['Usuario'] ?></td>
            <td><?= trim(($a['Apellidos'] ?? '').' '.($a['Nombres'] ?? '')) ?></td>
            <td><?= $a['Curso'] ?: '—' ?></td>
            <td><?= $a['Telefono'] ?: '—' ?></td>
            <td><?= ($a['Bloqueado'] === '1') ? 'Sí' : 'No' ?></td>
            <td>
              <a href="PerfilEstudiantes.php?usuario=<?= $a['Usuario'] ?>&clase_id=<?= $claseId ?>">Ver / Editar</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    <p style="margin-top:14px;"><a href="cursos.php">← Volver a cursos</a></p>
  </section>
</body>
</html>
