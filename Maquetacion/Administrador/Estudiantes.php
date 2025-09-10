<?php
// --- Solo ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  http_response_code(403);
  echo "Acceso restringido: solo Administrador.";
  exit;
}

// --- Conexión mysqli ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}
mysqli_set_charset($conexion, "utf8");

// --- Parámetros ---
$claseId = isset($_GET['clase_id']) ? (int)$_GET['clase_id'] : 0;
if ($claseId <= 0) { die("Clase inválida."); }

// --- Info de la clase ---
$st = mysqli_prepare($conexion, "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase WHERE id_clase=?");
mysqli_stmt_bind_param($st, "i", $claseId);
mysqli_stmt_execute($st);
$claseRes = mysqli_stmt_get_result($st);
$clase = mysqli_fetch_assoc($claseRes);
if (!$clase) { die("Clase no encontrada."); }
mysqli_stmt_close($st);

// --- Estudiantes inscritos (solo Rol=Estudiante) ---
$sql = "
  SELECT cu.Usuario, cu.Rol, cu.Bloqueado,
         i.Nombres, i.Apellidos, i.Curso, i.Telefono
  FROM cuenta_has_clase chc
  JOIN cuenta cu ON cu.Usuario = chc.Cuenta_Usuario
  LEFT JOIN informacion i ON i.Cuenta_Usuario = cu.Usuario
  WHERE chc.Clase_id_clase = ? AND cu.Rol = 'Estudiante'
  ORDER BY i.Apellidos, i.Nombres
";
$st2 = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($st2, "i", $claseId);
mysqli_stmt_execute($st2);
$alumnosRes = mysqli_stmt_get_result($st2);
$alumnos = $alumnosRes ? mysqli_fetch_all($alumnosRes, MYSQLI_ASSOC) : [];
mysqli_stmt_close($st2);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Estudiantes · <?= htmlspecialchars($clase['nombreClase']) ?></title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header>
    <h1>Clase: <?= htmlspecialchars($clase['nombreClase']) ?></h1>
    <img class="logo-colegio" src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="logo">
  </header>

  <div class="menu-top">
    <a href="cursos.php">Cursos</a>
  </div>

  <main style="padding:30px;">
    <p><b>Profesor:</b> <?= htmlspecialchars($clase['nomProfe']) ?> · <b>Código:</b> <?= htmlspecialchars($clase['codigoClase']) ?></p>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;background:#fff;">
      <thead>
        <tr>
          <th>Usuario</th><th>Nombre</th><th>Curso</th><th>Teléfono</th><th>Bloqueado</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alumnos as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['Usuario']) ?></td>
            <td><?= htmlspecialchars(trim(($a['Apellidos'] ?? '').' '.($a['Nombres'] ?? ''))) ?></td>
            <td><?= htmlspecialchars($a['Curso'] ?? '—') ?></td>
            <td><?= htmlspecialchars($a['Telefono'] ?? '—') ?></td>
            <td><?= ($a['Bloqueado'] === '1') ? 'Sí' : 'No' ?></td>
            <td>
              <a href="PerfilEstudiantes.php?usuario=<?= urlencode($a['Usuario']) ?>&clase_id=<?= urlencode($claseId) ?>">Ver / Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p style="margin-top:16px;"><a href="cursos.php">← Volver a cursos</a></p>
  </main>
</body>
</html>