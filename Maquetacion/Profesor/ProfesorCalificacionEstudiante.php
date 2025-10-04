<?php
// ==== SOLO PROFESOR ====
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  http_response_code(403);
  echo "Acceso denegado.";
  exit;
}
$profesor = isset($_SESSION['usu']) ? (int)$_SESSION['usu'] : 0;
if ($profesor <= 0) { die("Sesión inválida"); }

// ==== CONEXIÓN ====
$cn = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$cn) { die("Error en la conexión"); }
mysqli_set_charset($cn, "utf8");

// ==== PARÁMETROS ====
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : "";
$idClase = isset($_GET['clase']) ? (int)$_GET['clase'] : 0;
if ($usuario === "" || $idClase <= 0) { echo "Parámetros inválidos."; exit; }

// Asegurar que la clase es del profesor
$esMia = false;
$rc = mysqli_query($cn, "SELECT 1 FROM clase WHERE id_clase=$idClase AND Cuenta_Usuario=$profesor");
if ($rc && mysqli_fetch_row($rc)) { $esMia = true; }
if (!$esMia) { http_response_code(403); die("No puedes ver esta clase."); }

// ==== INFO CABECERA ====
$nombreEst = $usuario;
$qEst = "SELECT COALESCE(CONCAT(i.Nombres,' ',i.Apellidos), CAST(c.Usuario AS CHAR)) AS nombre
         FROM cuenta c 
         LEFT JOIN informacion i ON i.Cuenta_Usuario = c.Usuario
         WHERE c.Usuario = '$usuario'";
if ($r = mysqli_query($cn, $qEst)) { if ($row = mysqli_fetch_assoc($r)) $nombreEst = $row['nombre']; }

$nombreClase = "";
$qCla = "SELECT nombreClase FROM clase WHERE id_clase = $idClase";
if ($r = mysqli_query($cn, $qCla)) { if ($row = mysqli_fetch_assoc($r)) $nombreClase = $row['nombreClase']; }

// ==== TAREAS + ENTREGAS DEL ESTUDIANTE ====
$sql = "
SELECT 
  t.id               AS idTarea,
  t.Titulo           AS titulo,
  t.FechaLimite      AS fechaLimite,
  t.Nota             AS notaTarea,
  e.Nota             AS notaEntrega,
  e.FechaEntrega     AS fechaEntrega,
  e.Archivo          AS archivoEntrega,
  e.Comentario       AS comentarioEntrega
FROM tarea t
LEFT JOIN entrega e 
  ON e.Tarea_id = t.id AND e.Cuenta_Usuario = '$usuario'
WHERE t.Clase_id_clase = $idClase
ORDER BY t.FechaLimite DESC, t.id DESC";

$res = mysqli_query($cn, $sql);
$filas = [];
$entregadas = 0; $total = 0;
while ($res && $row = mysqli_fetch_assoc($res)) {
  $total++;
  $entrego = $row['archivoEntrega'] || $row['notaEntrega'] || $row['comentarioEntrega'] || $row['fechaEntrega'];
  if ($entrego) $entregadas++;

  // nota visible: prioridad a entrega, si no existe usar la de tarea
  $notaVisible = ($row['notaEntrega'] !== null && $row['notaEntrega'] !== '') ? $row['notaEntrega'] : $row['notaTarea'];

  $filas[] = [
    'titulo'   => $row['titulo'],
    'flimite'  => $row['fechaLimite'],
    'entrego'  => $entrego,
    'fentrega' => $row['fechaEntrega'],
    'archivo'  => $row['archivoEntrega'],
    'nota'     => $notaVisible,
    'coment'   => $row['comentarioEntrega']
  ];
}

// ==== PROMEDIO ====
$prom = null;
if (!empty($filas)) {
  $s=0; $c=0;
  foreach ($filas as $f) {
    if ($f['nota'] !== null && $f['nota'] !== '') { $s += floatval($f['nota']); $c++; }
  }
  if ($c>0) $prom = round($s/$c, 2);
}
$porc = ($total>0) ? round(($entregadas/$total)*100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Calificaciones de <?= htmlspecialchars($nombreEst) ?></title>
  <style>
    body{font-family:Segoe UI,Arial,sans-serif;margin:0;background:#f7f7fb}
    header{background:#6b0014;color:#fff;padding:14px 18px}
    .wrap{max-width:1100px;margin:18px auto;padding:0 16px}
    .card{background:#fff;border:1px solid #eee;border-radius:12px;padding:14px}
    .sub{color:#6b0014;margin:0 0 8px 0}
    .meta{color:#555;margin:4px 0 12px 0}
    table{width:100%;border-collapse:collapse;background:#fff}
    th,td{padding:10px 8px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
    th{background:#faf7f8}
    .badge{display:inline-block;padding:4px 8px;border:1px solid #6b0014;border-radius:999px}
    .ok{color:#0a7a2f;font-weight:600}
    .no{color:#b00020;font-weight:600}
    a.link{color:#6b0014;text-decoration:none}
    .small{font-size:12px;color:#666}
    .volver{display:inline-block;margin-top:14px;text-decoration:none;color:#6b0014}
  </style>
</head>
<body>
<?php include '../header.php'; ?>

<div class="wrap">
  <div class="card">
    <h2 class="sub">Estudiante</h2>
    <div class="meta"><?= htmlspecialchars($nombreEst) ?></div>

    <h2 class="sub">Curso</h2>
    <div class="meta"><?= htmlspecialchars($nombreClase) ?></div>

    <p class="meta">
      <span class="badge">Entregadas: <?= $entregadas ?>/<?= $total ?> (<?= $porc ?>%)</span>
      <?php if ($prom !== null): ?>
        &nbsp; <span class="badge">Promedio: <?= $prom ?></span>
      <?php endif; ?>
    </p>

    <?php if (empty($filas)): ?>
      <p>No hay tareas ni entregas registradas.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Tarea</th>
            <th>Fecha límite</th>
            <th>¿Entregó?</th>
            <th>Fecha entrega</th>
            <th>Archivo</th>
            <th>Nota</th>
            <th>Comentario</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach ($filas as $f): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($f['titulo']) ?></td>
              <td><?= $f['flimite'] ?></td>
              <td><?= $f['entrego'] ? "<span class='ok'>Sí</span>" : "<span class='no'>No</span>" ?></td>
              <td><?= $f['fentrega'] ?></td>
              <td>
                <?php if (!empty($f['archivo'])): ?>
                  <a class="link" href="/FMSDIGITAL/media/entregas/<?= htmlspecialchars($f['archivo']) ?>" target="_blank">Ver archivo</a>
                  <div class="small"><?= htmlspecialchars($f['archivo']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= ($f['nota']==='' || $f['nota']===null) ? '-' : $f['nota'] ?></td>
              <td><?= htmlspecialchars($f['coment']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <a class="volver" href="ProfesorCalificaciones.php?clase=<?= (int)$idClase ?>">← Volver a estudiantes</a>
  </div>
</div>
</body>
</html>
<?php mysqli_close($cn); ?>
