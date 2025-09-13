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

/* Clases */
$clases = [];
$sql = "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase ORDER BY nombreClase ASC";
$res = mysqli_query($conexion, $sql);
if ($res) {
  while ($row = mysqli_fetch_assoc($res)) { $clases[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tareas y Entregas · Clases</title>
  <style>
    body{ margin:0; font-family:'Segoe UI',sans-serif; background-color:#fdf9f9; color:#2e0f13; }
    header{ background-color:#6b0014; color:white; padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .menu-top{ background-color:#6b0014; padding:10px; display:flex; justify-content:center; flex-wrap:wrap; gap:20px; }
    .menu-top a{ color:white; background-color:#8b0020; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:14px; }
    .menu-top a:hover{ background-color:#a6192e; }

    .wrap{ max-width:1100px; margin:24px auto; padding:0 16px; }
    .titulo{ text-align:center; padding:14px; background:#6b0014; color:#fff; border-radius:8px; font-weight:bold; }

    .grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:18px; }
    .card{ background:#fff5f7; border:1px solid #ffdde0; border-radius:10px; padding:16px; box-shadow:0 4px 8px rgba(107,0,20,0.1); }
    .card h3{ margin:0 0 6px; color:#6b0014; font-size:1.05rem; }
    .meta{ font-size:.95rem; margin:3px 0; }
    .btn{ display:inline-block; margin-top:10px; background:#a30c2c; color:#fff; text-decoration:none; padding:10px 12px; border-radius:8px; font-weight:bold; }
    .btn:hover{ background:#7a0820; }

    @media (max-width:1024px){ .grid{ grid-template-columns:repeat(2,1fr);} }
    @media (max-width:768px){ header{ flex-direction:column; text-align:center; } }
    @media (max-width:600px){ .grid{ grid-template-columns:1fr; } }
  </style>
</head>
<body>

  <?php include '../header.php'; ?>

  <div class="wrap">
    <div class="titulo">Tareas y Entregas · Clases</div>

    <div class="grid">
      <?php if (empty($clases)): ?>
        <div class="card"><p>No hay clases registradas.</p></div>
      <?php else: foreach ($clases as $c): ?>
        <div class="card">
          <h3><?= htmlspecialchars($c['nombreClase'] ?: 'Clase sin nombre') ?></h3>
          <div class="meta"><b>Profesor:</b> <?= htmlspecialchars($c['nomProfe'] ?: '—') ?></div>
          <div class="meta"><b>Código:</b> <?= htmlspecialchars($c['codigoClase'] ?: '—') ?></div>
          <a class="btn" href="TareasClase.php?clase=<?= (int)$c['id_clase'] ?>">Ver tareas</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</body>
</html>
