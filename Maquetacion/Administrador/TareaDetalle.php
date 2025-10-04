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
$idTarea = 0;
if (isset($_GET['tarea'])) {
  $idTarea = (int)$_GET['tarea'];
}

/* URL del propio script (evita 404 por nombres/ubicaciones distintas) */
$selfUrl = $_SERVER['PHP_SELF'];

// ===================== GUARDAR NOTA (POST) =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_entrega']) && isset($_POST['nota'])) {
  $idEntrega = (int)$_POST['id_entrega'];
  $notaTexto = trim($_POST['nota']);

  if ($notaTexto === '') {
    $sqlUpdate = "UPDATE entrega SET Nota = NULL WHERE id_entrega = ".$idEntrega." AND Tarea_id = ".$idTarea;
  } else {
    $nota = (int)$notaTexto;
    if ($nota < 0) { $nota = 0; }
    if ($nota > 100) { $nota = 100; }
    $sqlUpdate = "UPDATE entrega SET Nota = ".$nota." WHERE id_entrega = ".$idEntrega." AND Tarea_id = ".$idTarea;
  }

  mysqli_query($conexion, $sqlUpdate);

  /* Redirección PRG a la MISMA página sin hardcodear el nombre */
  header("Location: " . $selfUrl . "?tarea=" . $idTarea);
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
    WHERE t.id = ".$idTarea."
    LIMIT 1
  ";
  $resTarea = mysqli_query($conexion, $sqlTarea);
  if ($resTarea && mysqli_num_rows($resTarea) > 0) {
    $tareaDatos = mysqli_fetch_assoc($resTarea);
    mysqli_free_result($resTarea);
  }
}

// Precalcular textos seguros para la vista
$tituloTxt = '—';
$temaTxt = '—';
$descripcionTxt = '—';
$claseNombreTxt = '—';
$claseProfeTxt = '—';
$claseCodigoTxt = '—';
$idClaseDeTarea = 0;

if ($tareaDatos) {
  if (isset($tareaDatos['Titulo']) && $tareaDatos['Titulo'] !== '') { $tituloTxt = $tareaDatos['Titulo']; }
  if (isset($tareaDatos['Tema']) && $tareaDatos['Tema'] !== '') { $temaTxt = $tareaDatos['Tema']; }
  if (isset($tareaDatos['Descripcion']) && $tareaDatos['Descripcion'] !== '') { $descripcionTxt = $tareaDatos['Descripcion']; }
  if (isset($tareaDatos['nombreClase']) && $tareaDatos['nombreClase'] !== '') { $claseNombreTxt = $tareaDatos['nombreClase']; }
  if (isset($tareaDatos['nomProfe']) && $tareaDatos['nomProfe'] !== '') { $claseProfeTxt = $tareaDatos['nomProfe']; }
  if (isset($tareaDatos['codigoClase']) && $tareaDatos['codigoClase'] !== '') { $claseCodigoTxt = $tareaDatos['codigoClase']; }
  if (isset($tareaDatos['Clase_id_clase'])) { $idClaseDeTarea = (int)$tareaDatos['Clase_id_clase']; }
}

// ===================== CONSULTAR ENTREGAS =====================
$listaEntregas = array();
if ($tareaDatos) {
  $sqlEntregas = "
    SELECT e.id_entrega, e.Cuenta_Usuario, e.FechaEntrega, e.contenido, e.Archivo, e.Nota,
           i.Nombres, i.Apellidos, i.Curso
    FROM entrega e
    LEFT JOIN informacion i ON i.Cuenta_Usuario = e.Cuenta_Usuario
    WHERE e.Tarea_id = ".$idTarea."
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

/* === BASE WEB de archivos de entregas (tu carpeta real) === */
$webUploadsBase = "/FMSDIGITAL/Maquetacion/media/entregas/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detalle de Tarea</title>
  <style>
    body{
      margin:0;
      font-family:'Segoe UI',sans-serif;
      background:#fdf9f9;
      color:#2e0f13;
    }
    header{
      background:#6b0014;
      color:white;
      padding:20px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
    }
    .menu-top{
      background:#6b0014;
      padding:10px;
      display:flex;
      justify-content:center;
      flex-wrap:wrap;
      gap:20px;
    }
    .menu-top a{
      color:white;
      background:#8b0020;
      padding:8px 14px;
      border-radius:6px;
      text-decoration:none;
      font-weight:bold;
      font-size:14px;
    }
    .menu-top a:hover{ background:#a6192e; }
    .wrap{ max-width:1100px; margin:24px auto; padding:0 16px; }
    .panel{
      background:#fff5f7;
      border:1px solid #ffdde0;
      border-radius:10px;
      padding:16px;
      box-shadow:0 4px 8px rgba(107,0,20,0.1);
    }
    .titulo{
      text-align:center;
      padding:14px;
      background:#6b0014;
      color:#fff;
      border-radius:8px;
      font-weight:bold;
    }
    .sub{ color:#6b0014; font-weight:bold; margin:10px 0 6px; }
    .acciones{ margin:12px 0; display:flex; gap:10px; flex-wrap:wrap; }
    .btn{ background:#a30c2c; color:#fff; text-decoration:none; padding:10px 12px; border-radius:8px; font-weight:bold; }
    .btn:hover{ background:#7a0820; }
    .btn-sec{
      background:#fff; color:#6b0014; border:1px solid #6b0014;
      text-decoration:none; padding:9px 12px; border-radius:8px; font-weight:bold;
    }
    .tabla{
      width:100%; border-collapse:collapse; background:#fff; border-radius:10px;
      overflow:hidden; box-shadow:0 4px 8px rgba(107,0,20,0.1);
    }
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

    <?php if (!$tareaDatos): ?>
      <div class="panel"><p>No se encontró la tarea solicitada.</p></div>
    <?php else: ?>
      <div class="titulo">Detalle de la Tarea</div>

      <div class="panel" style="margin-top:12px;">
        <div class="sub">Tarea</div>
        <div><b>Título:</b> <?php echo htmlspecialchars($tituloTxt); ?></div>
        <div><b>Tema:</b> <?php echo htmlspecialchars($temaTxt); ?></div>
        <div><b>Descripción:</b> <?php echo nl2br(htmlspecialchars($descripcionTxt)); ?></div>

        <div class="sub" style="margin-top:12px;">Clase</div>
        <div><b>Nombre:</b> <?php echo htmlspecialchars($claseNombreTxt); ?></div>
        <div><b>Profesor:</b> <?php echo htmlspecialchars($claseProfeTxt); ?></div>
        <div><b>Código:</b> <?php echo htmlspecialchars($claseCodigoTxt); ?></div>

        <div class="acciones">
          <a class="btn-sec" href="TareasClase.php?clase=<?php echo (int)$idClaseDeTarea; ?>">← Volver a tareas de la clase</a>
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
          <?php if (empty($listaEntregas)): ?>
            <tr><td data-label="Alumno" colspan="5">No hay entregas aún.</td></tr>
          <?php else: ?>
            <?php foreach ($listaEntregas as $entrega): ?>
              <?php
                $nombresFila = isset($entrega['Nombres']) ? $entrega['Nombres'] : '';
                $apellidosFila = isset($entrega['Apellidos']) ? $entrega['Apellidos'] : '';
                $nombreCompleto = trim($nombresFila.' '.$apellidosFila);
                if ($nombreCompleto === '') { $nombreCompleto = '—'; }

                $cursoFila = '—';
                if (isset($entrega['Curso']) && $entrega['Curso'] !== '') { $cursoFila = $entrega['Curso']; }

                $fechaFila = '—';
                if (isset($entrega['FechaEntrega']) && $entrega['FechaEntrega'] !== '') { $fechaFila = $entrega['FechaEntrega']; }

                $contenidoFila = '—';
                if (isset($entrega['contenido']) && $entrega['contenido'] !== '') { $contenidoFila = $entrega['contenido']; }

                // Normalizar archivo a URL pública /FMSDIGITAL/Maquetacion/media/entregas/
                $archivoFila = '';
                if (isset($entrega['Archivo']) && $entrega['Archivo'] !== '') { $archivoFila = $entrega['Archivo']; }

                $archivoUrlFila = '';
                if ($archivoFila !== '') {
                  $archivoFila = str_replace('\\', '/', $archivoFila);
                  if (strpos($archivoFila, 'http://') === 0 || strpos($archivoFila, 'https://') === 0) {
                    $archivoUrlFila = $archivoFila;
                  } else {
                    if ($archivoFila[0] === '/') { $archivoUrlFila = $archivoFila; }
                    else { $archivoUrlFila = $webUploadsBase . $archivoFila; }
                  }
                }

                $notaValue = '';
                if (array_key_exists('Nota', $entrega) && $entrega['Nota'] !== null) {
                  $notaValue = (int)$entrega['Nota'];
                }

                $cuentaUsuarioFila = isset($entrega['Cuenta_Usuario']) ? (int)$entrega['Cuenta_Usuario'] : 0;
                $idEntregaFila = isset($entrega['id_entrega']) ? (int)$entrega['id_entrega'] : 0;
              ?>
              <tr>
                <td data-label="Alumno">
                  <?php echo htmlspecialchars($nombreCompleto); ?>
                  <div class="mini">CI/Usuario: <?php echo $cuentaUsuarioFila; ?></div>
                </td>
                <td data-label="Curso"><?php echo htmlspecialchars($cursoFila); ?></td>
                <td data-label="Fecha"><?php echo htmlspecialchars($fechaFila); ?></td>
                <td data-label="Contenido / Archivo">
                  <?php echo nl2br(htmlspecialchars($contenidoFila)); ?>
                  <?php if ($archivoUrlFila !== ''): ?>
                    <div class="mini">
                      <a href="<?php echo htmlspecialchars($archivoUrlFila); ?>" target="_blank" rel="noopener">Ver archivo</a>
                    </div>
                  <?php endif; ?>
                </td>
                <td data-label="Nota">
                  <form class="nota-form" method="post" action="<?php echo htmlspecialchars($selfUrl) . '?tarea=' . (int)$idTarea; ?>">
                    <input type="hidden" name="id_entrega" value="<?php echo $idEntregaFila; ?>">
                    <input class="nota-input" type="number" name="nota" min="0" max="100" value="<?php echo $notaValue; ?>" placeholder="—">
                    <button class="btn" type="submit">Guardar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>
</body>
</html>
<?php
mysqli_close($conexion);
