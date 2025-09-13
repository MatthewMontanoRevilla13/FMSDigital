<?php
// --- Solo ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  http_response_code(403);
  echo "Acceso denegado.";
  exit;
}

/* === CONEXIÓN MYSQLI (tu bloque) === */
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}

$tareaId = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;

/* Guardar nota (opcional) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_entrega'], $_POST['nota'])) {
  $idEntrega = (int)$_POST['id_entrega'];
  $notaStr = trim($_POST['nota']);
  if ($notaStr === '') {
    // Nota NULL
    $stmtU = mysqli_prepare($conexion, "UPDATE entrega SET Nota=NULL WHERE id_entrega=? AND Tarea_id=?");
    mysqli_stmt_bind_param($stmtU, "ii", $idEntrega, $tareaId);
  } else {
    $nota = (int)$notaStr;
    if ($nota < 0) $nota = 0;
    if ($nota > 100) $nota = 100;
    $stmtU = mysqli_prepare($conexion, "UPDATE entrega SET Nota=? WHERE id_entrega=? AND Tarea_id=?");
    mysqli_stmt_bind_param($stmtU, "iii", $nota, $idEntrega, $tareaId);
  }
  if (isset($stmtU)) {
    mysqli_stmt_execute($stmtU);
    mysqli_stmt_close($stmtU);
  }
  header("Location: admin_tarea_detalle.php?tarea=".$tareaId);
  exit;
}

/* Tarea + clase */
$tarea = null;
$stmt = mysqli_prepare($conexion, "
  SELECT t.id, t.Titulo, t.Descripcion, t.Tema, t.Clase_id_clase,
         c.nombreClase, c.nomProfe, c.codigoClase
  FROM tarea t
  JOIN clase c ON c.id_clase = t.Clase_id_clase
  WHERE t.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $tareaId);
mysqli_stmt_execute($stmt);
$resT = mysqli_stmt_get_result($stmt);
if ($resT) { $tarea = mysqli_fetch_assoc($resT); }
mysqli_stmt_close($stmt);

$entregas = [];
if ($tarea) {
  $stmt2 = mysqli_prepare($conexion, "
    SELECT e.id_entrega, e.Cuenta_Usuario, e.FechaEntrega, e.contenido, e.Archivo, e.Nota,
           i.Nombres, i.Apellidos, i.Curso
    FROM entrega e
    LEFT JOIN informacion i ON i.Cuenta_Usuario = e.Cuenta_Usuario
    WHERE e.Tarea_id = ?
    ORDER BY e.FechaEntrega DESC, e.id_entrega DESC
  ");
  mysqli_stmt_bind_param($stmt2, "i", $tareaId);
  mysqli_stmt_execute($stmt2);
  $resE = mysqli_stmt_get_result($stmt2);
  if ($resE) { while ($row = mysqli_fetch_assoc($resE)) { $entregas[] = $row; } }
  mysqli_stmt_close($stmt2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detalle de Tarea</title>
  <style>
    body{ margin:0; font-family:'Segoe UI',sans-serif; background:#fdf9f9; color:#2e0f13; }
    header{ background:#6b0014; color:white; padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .menu-top{ background:#6b0014; padding:10px; display:flex; justify-content:center; flex-wrap:wrap; gap:20px; }
    .menu-top a{ color:white; background:#8b0020; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:14px; }
    .menu-top a:hover{ background:#a6192e; }

    .wrap{ max-width:1100px; margin:24px auto; padding:0 16px; }

    .panel{ background:#fff5f7; border:1px solid #ffdde0; border-radius:10px; padding:16px; box-shadow:0 4px 8px rgba(107,0,20,0.1); }
    .titulo{ text-align:center; padding:14px; background:#6b0014; color:#fff; border-radius:8px; font-weight:bold; }
    .sub{ color:#6b0014; font-weight:bold; margin:10px 0 6px; }

    .acciones{ margin:12px 0; display:flex; gap:10px; flex-wrap:wrap; }
    .btn{ background:#a30c2c; color:#fff; text-decoration:none; padding:10px 12px; border-radius:8px; font-weight:bold; }
    .btn:hover{ background:#7a0820; }
    .btn-sec{ background:#fff; color:#6b0014; border:1px solid #6b0014; text-decoration:none; padding:9px 12px; border-radius:8px; font-weight:bold; }

    .tabla{ width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 8px rgba(107,0,20,0.1); }
    .tabla th, .tabla td{ padding:12px; border-bottom:1px solid #ffdde0; text-align:left; }
    .tabla th{ background:#6b0014; color:#fff; }
    .nota-form{ display:flex; gap:8px; align-items:center; }
    .nota-input{ width:70px; padding:6px 8px; border:1px solid #6b0014; border-radius:6px; }
    .mini{ font-size:.92rem; opacity:.9; }

    @media (max-width:768px){
      header{ flex-direction:column; text-align:center; }
      .tabla thead{ display:none; }
      .tabla tr{ display:block; border-bottom:1px solid #ffdde0; }
      .tabla td{ display:flex; justify-content:space-between; gap:10px; }
      .tabla td::before{ content: attr(data-label); font-weight:bold; color:#6b0014; }
    }
  </style>
</head>
<body>

  <?php include '../header.php'; ?>

  <div class="wrap">

    <?php if(!$tarea): ?>
      <div class="panel"><p>No se encontró la tarea solicitada.</p></div>
    <?php else: ?>
      <div class="titulo">Detalle de la Tarea</div>

      <div class="panel" style="margin-top:12px;">
        <div class="sub">Tarea</div>
        <div><b>Título:</b> <?= htmlspecialchars($tarea['Titulo'] ?: '—') ?></div>
        <div><b>Tema:</b> <?= htmlspecialchars($tarea['Tema'] ?: '—') ?></div>
        <div><b>Descripción:</b> <?= htmlspecialchars($tarea['Descripcion'] ?: '—') ?></div>

        <div class="sub" style="margin-top:12px;">Clase</div>
        <div><b>Nombre:</b> <?= htmlspecialchars($tarea['nombreClase'] ?: '—') ?></div>
        <div><b>Profesor:</b> <?= htmlspecialchars($tarea['nomProfe'] ?: '—') ?></div>
        <div><b>Código:</b> <?= htmlspecialchars($tarea['codigoClase'] ?: '—') ?></div>

        <div class="acciones">
          <a class="btn-sec" href="TareasClase.php?clase=<?= (int)$tarea['Clase_id_clase'] ?>">← Volver a tareas de la clase</a>
          <a class="btn-sec" href="Tareas.php">← Volver a clases</a>
        </div>
      </div>

      <div class="sub" style="margin-top:16px;">Entregas</div>
      <table class="tabla">
        <thead>
          <tr>
            <th>Alumno</th>
            <th>Curso</th>
            <th>Fecha</th>
            <th>Contenido / Archivo</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($entregas)): ?>
            <tr><td data-label="Alumno" colspan="5">No hay entregas aún.</td></tr>
          <?php else: foreach($entregas as $e): ?>
            <tr>
              <td data-label="Alumno">
                <?= htmlspecialchars(trim(($e['Nombres'] ?? '').' '.($e['Apellidos'] ?? '')) ?: '—') ?>
                <div class="mini">CI/Usuario: <?= (int)$e['Cuenta_Usuario'] ?></div>
              </td>
              <td data-label="Curso"><?= htmlspecialchars($e['Curso'] ?? '—') ?></td>
              <td data-label="Fecha"><?= htmlspecialchars($e['FechaEntrega'] ?? '—') ?></td>
              <td data-label="Contenido / Archivo">
                <?php
                  $cont = htmlspecialchars($e['contenido'] ?? '');
                  $arch = htmlspecialchars($e['Archivo'] ?? '');
                  echo ($cont !== '' ? $cont : '—');
                  if ($arch !== '') {
                    echo '<div class="mini"><a href="'. $arch .'" target="_blank">Ver archivo</a></div>';
                  }
                ?>
              </td>
              <td data-label="Nota">
                <form class="nota-form" method="post" action="?tarea=<?= (int)$tareaId ?>">
                  <input type="hidden" name="id_entrega" value="<?= (int)$e['id_entrega'] ?>">
                  <input class="nota-input" type="number" name="nota" min="0" max="100"
                         value="<?= ($e['Nota'] === null ? '' : (int)$e['Nota']) ?>"
                         placeholder="—">
                  <button class="btn" type="submit">Guardar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>
</body>
</html>
