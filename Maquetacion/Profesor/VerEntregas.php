<?php
session_start();

/* ====== Seguridad: solo Profesor ====== */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  http_response_code(403);
  echo "No tienes permiso para ver entregas.";
  exit;
}

/* ====== Conexión ====== */
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  die("Error: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

/* ====== Parámetro: id_tarea ====== */
$id_tarea = 0;
if (isset($_GET['id_tarea'])) {
  $id_tarea = intval($_GET['id_tarea']);
}
if ($id_tarea <= 0) {
  die("Tarea inválida.");
}

/* ====== Tarea ====== */
$tarea = null;
$resTarea = mysqli_query($conexion, "SELECT * FROM tarea WHERE id=" . $id_tarea . " LIMIT 1");
if ($resTarea) {
  $tarea = mysqli_fetch_assoc($resTarea);
  mysqli_free_result($resTarea);
}
if (!$tarea) {
  die("No se encontró la tarea.");
}

/* ====== Entregas ====== */
$sql = "
  SELECT e.id_entrega,
         e.Cuenta_Usuario,
         e.contenido,
         e.FechaEntrega,
         e.Nota,
         e.Archivo,
         c.Usuario
  FROM entrega e
  JOIN cuenta c ON e.Cuenta_Usuario = c.Usuario
  WHERE e.Tarea_id = " . $id_tarea . "
  ORDER BY e.FechaEntrega DESC, e.id_entrega DESC
";
$entregas = mysqli_query($conexion, $sql);

/* ====== Carpeta pública donde están las entregas ====== */
$webUploadsBase = "/FMSDIGITAL/Maquetacion/media/entregas/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Entregas · <?php echo htmlspecialchars($tarea['Titulo']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    /* ====== Estilos generales ====== */
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f7f7fb;
      color: #2e0f13;
    }

    .page {
      max-width: 1100px;
      margin: 22px auto;
      padding: 0 16px;
      box-sizing: border-box;
    }

    .page-title {
      background: #6b0014;
      color: #fff;
      padding: 14px;
      border-radius: 8px;
      font-weight: 700;
      text-align: center;
      margin-bottom: 16px;
    }

    .actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 12px;
    }

    .btn {
      display: inline-block;
      background: #8b0020;
      color: #fff;
      text-decoration: none;
      padding: 8px 12px;
      border-radius: 8px;
      font-weight: 700;
      border: 1px solid #8b0020;
    }

    .btn:hover {
      background: #a6192e;
      border-color: #a6192e;
    }

    .btn.ghost {
      background: #fff;
      color: #6b0014;
      border: 1px solid #6b0014;
    }

    .btn.ghost:hover {
      background: #f3e4e7;
    }

    .entregas-list {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
    }

    .entrega-card {
      background: #fff;
      border-radius: 10px;
      border: 1px solid #e9d4d7;
      box-shadow: 0 2px 8px rgba(107, 0, 20, 0.08);
      padding: 14px;
    }

    .entrega-row { margin: 6px 0; }
    .entrega-label { font-weight: 700; color: #6b0014; }

    .entrega-contenido {
      white-space: pre-wrap;
      line-height: 1.4;
      margin-top: 6px;
    }

    .archivo-area {
      margin-top: 8px;
      font-size: 0.95rem;
    }

    .archivo-link {
      color: #8b0020;
      text-decoration: none;
      font-weight: 700;
    }

    .archivo-link:hover { text-decoration: underline; }

    .nota-form {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
      flex-wrap: wrap;
    }

    .nota-input {
      width: 90px;
      padding: 8px 10px;
      border: 1px solid #6b0014;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }

    @media (max-width: 700px) {
      .page { padding: 0 12px; }
    }
  </style>
</head>
<body>

  <?php include '../header.php'; ?>

  <div class="page">
    <div class="page-title">Entregas de la tarea: <?php echo htmlspecialchars($tarea['Titulo']); ?></div>

    <div class="actions">
      <?php
        $idClase = 0;
        if (isset($tarea['Clase_id_clase'])) {
          $idClase = intval($tarea['Clase_id_clase']);
        }
      ?>
      <a class="btn ghost" href="ClaseDeProfesor.php?id_clase=<?php echo $idClase; ?>">← Volver a la clase</a>
    </div>

    <div class="entregas-list">
      <?php if (!$entregas || mysqli_num_rows($entregas) === 0): ?>
        <div class="entrega-card">No hay entregas todavía.</div>
      <?php else: ?>
        <?php while ($fila = mysqli_fetch_assoc($entregas)): ?>
          <?php
            /* ===== Datos del alumno y entrega ===== */
            $alumnoUsuario = isset($fila['Usuario']) ? $fila['Usuario'] : '';
            $contenido = isset($fila['contenido']) ? $fila['contenido'] : '';
            $fecha = isset($fila['FechaEntrega']) ? $fila['FechaEntrega'] : '';
            $notaValue = (isset($fila['Nota']) && $fila['Nota'] !== null) ? (int)$fila['Nota'] : '';
            $idEntrega = isset($fila['id_entrega']) ? (int)$fila['id_entrega'] : 0;

            /* ===== Ruta del archivo ===== */
            $archivo = '';
            if (isset($fila['Archivo'])) {
              $archivo = $fila['Archivo'];
            }

            $archivoUrl = '';
            if ($archivo !== '') {
              $archivo = str_replace('\\', '/', $archivo);
              if (strpos($archivo, 'http://') === 0 || strpos($archivo, 'https://') === 0) {
                $archivoUrl = $archivo;
              } else {
                if ($archivo[0] === '/') {
                  $archivoUrl = $archivo;
                } else {
                  $archivoUrl = $webUploadsBase . $archivo;
                }
              }
            }
          ?>
          <div class="entrega-card">
            <div class="entrega-row">
              <span class="entrega-label">Alumno:</span>
              <?php echo htmlspecialchars($alumnoUsuario); ?>
            </div>

            <div class="entrega-row">
              <span class="entrega-label">Fecha:</span>
              <?php echo htmlspecialchars($fecha); ?>
            </div>

            <div class="entrega-row">
              <span class="entrega-label">Entrega:</span>
              <div class="entrega-contenido"><?php echo nl2br(htmlspecialchars($contenido)); ?></div>
            </div>

            <div class="entrega-row archivo-area">
              <?php if ($archivoUrl !== ''): ?>
                <a class="archivo-link" href="<?php echo htmlspecialchars($archivoUrl); ?>" target="_blank" rel="noopener">Ver archivo</a>
              <?php else: ?>
                <span class="entrega-label">Archivo:</span> —
              <?php endif; ?>
            </div>

            <form class="nota-form" action="CalificarEntrega.php" method="POST">
              <input type="hidden" name="id_entrega" value="<?php echo $idEntrega; ?>">
              <label for="nota_<?php echo $idEntrega; ?>"><strong>Nota:</strong></label>
              <input id="nota_<?php echo $idEntrega; ?>" class="nota-input" type="number" name="nota" min="0" max="100" value="<?php echo $notaValue; ?>" placeholder="—" required>
              <button class="btn" type="submit">Guardar</button>
            </form>
          </div>
        <?php endwhile; mysqli_free_result($entregas); ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
<?php
mysqli_close($conexion);
