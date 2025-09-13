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

$claseId = isset($_GET['clase']) ? (int)$_GET['clase'] : 0;

/* Info clase */
$clase = null;
$stmt = mysqli_prepare($conexion, "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase WHERE id_clase=?");
mysqli_stmt_bind_param($stmt, "i", $claseId);
mysqli_stmt_execute($stmt);
$resC = mysqli_stmt_get_result($stmt);
if ($resC) { $clase = mysqli_fetch_assoc($resC); }
mysqli_stmt_close($stmt);

/* Tareas + conteos */
$tareas = [];
$stmt2 = mysqli_prepare(
  $conexion,
  "SELECT t.id, t.Titulo, t.Descripcion, t.Tema,
          (SELECT COUNT(*) FROM entrega e WHERE e.Tarea_id = t.id) AS total_entregas,
          (SELECT COUNT(*) FROM entrega e WHERE e.Tarea_id = t.id AND e.Nota IS NOT NULL) AS entregas_calificadas
   FROM tarea t
   WHERE t.Clase_id_clase = ?
   ORDER BY t.id DESC"
);
mysqli_stmt_bind_param($stmt2, "i", $claseId);
mysqli_stmt_execute($stmt2);
$resT = mysqli_stmt_get_result($stmt2);
if ($resT) { while ($row = mysqli_fetch_assoc($resT)) { $tareas[] = $row; } }
mysqli_stmt_close($stmt2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tareas · <?= htmlspecialchars($clase['nombreClase'] ?? 'Clase') ?></title>
  <style>
    body{ margin:0; font-family:'Segoe UI',sans-serif; background-color:#fdf9f9; color:#2e0f13; }
    header{ background-color:#6b0014; color:white; padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .menu-top{ background-color:#6b0014; padding:10px; display:flex; justify-content:center; flex-wrap:wrap; gap:20px; }
    .menu-top a{ color:white; background-color:#8b0020; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:14px; }
    .menu-top a:hover{ background-color:#a6192e; }

    .wrap{ max-width:1100px; margin:24px auto; padding:0 16px; }
    .titulo{ text-align:center; padding:14px; background:#6b0014; color:#fff; border-radius:8px; font-weight:bold; }

    .grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:18px; margin-top:18px; }
    .card{ background:#fff5f7; border:1px solid #ffdde0; border-radius:10px; padding:16px; box-shadow:0 4px 8px rgba(107,0,20,0.1); }
    .card h3{ margin:0 0 6px; color:#6b0014; }
    .meta{ font-size:.95rem; margin:3px 0; }
    .actions{ margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; }
    .btn{ background:#a30c2c; color:#fff; text-decoration:none; padding:10px 12px; border-radius:8px; font-weight:bold; }
    .btn:hover{ background:#7a0820; }
    .btn-sec{ background:#fff; color:#6b0014; border:1px solid #6b0014; text-decoration:none; padding:9px 12px; border-radius:8px; font-weight:bold; }

    @media (max-width:1024px){ .grid{ grid-template-columns:1fr; } }
    @media (max-width:768px){ header{ flex-direction:column; text-align:center; } }
  </style>
</head>
<body>

  <?php include '../header.php'; ?>

  <div class="wrap">
    <div class="titulo">
      Tareas · <?= htmlspecialchars(($clase['nombreClase'] ?? 'Clase sin nombre')) ?>
      <div style="font-size:.95rem; margin-top:6px;">
        Prof.: <b><?= htmlspecialchars($clase['nomProfe'] ?? '—') ?></b> &nbsp;|&nbsp; Código: <?= htmlspecialchars($clase['codigoClase'] ?? '—') ?>
      </div>
    </div>

    <div style="margin-top:12px;">
      <a class="btn-sec" href="Tareas.php">← Volver a clases</a>
    </div>

    <div class="grid">
      <?php if (empty($tareas)): ?>
        <div class="card"><p>No hay tareas registradas en esta clase.</p></div>
      <?php else: foreach ($tareas as $t): ?>
        <div class="card">
          <h3><?= htmlspecialchars($t['Titulo'] ?: 'Tarea sin título') ?></h3>
          <div class="meta"><b>Tema:</b> <?= htmlspecialchars($t['Tema'] ?: '—') ?></div>
          <div class="meta"><b>Descripción:</b> <?= htmlspecialchars($t['Descripcion'] ?: '—') ?></div>
          <div class="meta"><b>Entregas:</b> <?= (int)$t['total_entregas'] ?> &nbsp;|&nbsp; <b>Calificadas:</b> <?= (int)$t['entregas_calificadas'] ?></div>
          <div class="actions">
            <a class="btn" href="TareaDetalle.php?tarea=<?= (int)$t['id'] ?>">Ver detalle</a>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</body>
</html>
