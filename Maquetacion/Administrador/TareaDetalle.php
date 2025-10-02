<?php
// ===================== SOLO ADMIN =====================
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  http_response_code(403);
  echo "Acceso denegado.";
  exit;
}

// ===================== CONEXIÓN MYSQLI =====================
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}
mysqli_set_charset($conexion, "utf8");

// ===================== PARAMETROS =====================
$idTarea = isset($_GET['tarea']) ? (int)$_GET['tarea'] : 0;

// ===================== GUARDAR NOTA (POST) =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_entrega'], $_POST['nota'])) {
  $idEntrega = (int)$_POST['id_entrega'];
  $notaTexto = trim($_POST['nota']);

  if ($notaTexto === '') {
    // Dejar nota en NULL
    $sqlUpdate = "UPDATE entrega SET Nota = NULL WHERE id_entrega = $idEntrega AND Tarea_id = $idTarea";
  } else {
    $nota = (int)$notaTexto;
    if ($nota < 0)   $nota = 0;
    if ($nota > 100) $nota = 100;
    $sqlUpdate = "UPDATE entrega SET Nota = $nota WHERE id_entrega = $idEntrega AND Tarea_id = $idTarea";
  }

  mysqli_query($conexion, $sqlUpdate);
  header("Location: admin_tarea_detalle.php?tarea=" . $idTarea);
  exit;
}

// ===================== CONSULTAR TAREA + CLASE =====================
$tareaDatos = null;
if ($idTarea > 0) {
  $sqlTarea = "
    SELECT t.id, t.Titulo, t.Descripcion, t.Tema, t.Clase_id_clase,
           c.nombreClase, c.nomProfe, c.codigoClase
    FROM tarea t
    JOIN clase c ON c.id_clase = t.Clase_id_clase
    WHERE t.id = $idTarea
    LIMIT 1
  ";
  $resTarea = mysqli_query($conexion, $sqlTarea);
  if ($resTarea && mysqli_num_rows($resTarea) > 0) {
    $tareaDatos = mysqli_fetch_assoc($resTarea);
    mysqli_free_result($resTarea);
  }
}

// ===================== CONSULTAR ENTREGAS =====================
$listaEntregas = [];
if ($tareaDatos) {
  $sqlEntregas = "
    SELECT e.id_entrega, e.Cuenta_Usuario, e.FechaEntrega, e.contenido, e.Archivo, e.Nota,
           i.Nombres, i.Apellidos, i.Curso
    FROM entrega e
    LEFT JOIN informacion i ON i.Cuenta_Usuario = e.Cuenta_Usuario
    WHERE e.Tarea_id = $idTarea
    ORDER BY e.FechaEntrega DESC, e.id_entrega DESC
  ";
  $resEntregas = mysqli_query($conexion, $sqlEntregas);
  if ($resEntregas) {
    while ($fila = mysqli_fetch_assoc($resEntregas)) {
      $listaEntregas[] = $fila;
    }
    mysqli_free_result($resEntregas);
  }
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

    <?php if(!$tareaDatos): ?>
      <div class="panel"><p>No se encontró la tarea solicitada.</p></div>
    <?php else: ?>
      <div class="titulo">Detalle de la Tarea</div>

      <div class="panel" style="margin-top:12px;">
        <div class="sub">Tarea</div>
        <div><b>Título:</b> <?= ($tareaDatos['Titulo'] ?: '—') ?></div>
        <div><b>Tema:</b> <?= ($tareaDatos['Tema'] ?: '—') ?></div>
        <div><b>Descripción:</b> <?= ($tareaDatos['Descripcion'] ?: '—') ?></div>

        <div class="sub" style="margin-top:12px;">Clase</div>
        <div><b>Nombre:</b> <?= ($tareaDatos['nombreClase'] ?: '—') ?></div>
        <div><b>Profesor:</b> <?= ($tareaDatos['nomProfe'] ?: '—') ?></div>
        <div><b>Código:</b> <?= ($tareaDatos['codigoClase'] ?: '—') ?></div>

        <div class="acciones">
          <a class="btn-sec" href="TareasClase.php?clase=<?= (int)$tareaDatos['Clase_id_clase'] ?>">← Volver a tareas de la clase</a>
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
          <?php if(empty($listaEntregas)): ?>
            <tr><td data-label="Alumno" colspan="5">No hay entregas aún.</td></tr>
          <?php else: foreach($listaEntregas as $entrega): ?>
            <tr>
              <td data-label="Alumno">
                <?php
                  $nombres   = $entrega['Nombres']   ?? '';
                  $apellidos = $entrega['Apellidos'] ?? '';
                  $nombreCompleto = trim($nombres . ' ' . $apellidos);
                  echo ($nombreCompleto !== '' ? $nombreCompleto : '—');
                ?>
                <div class="mini">CI/Usuario: <?= (int)$entrega['Cuenta_Usuario'] ?></div>
              </td>
              <td data-label="Curso"><?= ($entrega['Curso'] ?? '—') ?></td>
              <td data-label="Fecha"><?= ($entrega['FechaEntrega'] ?? '—') ?></td>
              <td data-label="Contenido / Archivo">
                <?php
                  $contenido = $entrega['contenido'] ?? '';
                  $archivo   = $entrega['Archivo']   ?? '';
                  echo ($contenido !== '' ? $contenido : '—');
                  if ($archivo !== '') {
                    echo '<div class="mini"><a href="'.$archivo.'" target="_blank" rel="noopener">Ver archivo</a></div>';
                  }
                ?>
              </td>
              <td data-label="Nota">
                <form class="nota-form" method="post" action="?tarea=<?= (int)$idTarea ?>">
                  <input type="hidden" name="id_entrega" value="<?= (int)$entrega['id_entrega'] ?>">
                  <input class="nota-input" type="number" name="nota" min="0" max="100"
                         value="<?= ($entrega['Nota'] === null ? '' : (int)$entrega['Nota']) ?>"
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
<?php
mysqli_close($conexion);
