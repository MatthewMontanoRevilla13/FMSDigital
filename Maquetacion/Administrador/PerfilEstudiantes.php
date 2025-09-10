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
$usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$claseId = isset($_GET['clase_id']) ? (int)$_GET['clase_id'] : 0;
if ($usuario <= 0) { die("Usuario inválido."); }

// ---------- Acciones POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';

  try {
    if ($accion === 'cambiar_a_profesor') {
      $q = mysqli_prepare($conexion, "UPDATE cuenta SET Rol='Profesor' WHERE Usuario=?");
      mysqli_stmt_bind_param($q, "i", $usuario);
      mysqli_stmt_execute($q);
      mysqli_stmt_close($q);

    } elseif ($accion === 'toggle_bloqueo') {
      // Leer estado actual
      $cur = mysqli_prepare($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=?");
      mysqli_stmt_bind_param($cur, "i", $usuario);
      mysqli_stmt_execute($cur);
      $resCur = mysqli_stmt_get_result($cur);
      $filaCur = mysqli_fetch_assoc($resCur);
      mysqli_stmt_close($cur);
      $actual = $filaCur ? $filaCur['Bloqueado'] : null;

      $nuevo = ($actual === '1') ? NULL : '1';
      $u = mysqli_prepare($conexion, "UPDATE cuenta SET Bloqueado=? WHERE Usuario=?");
      // Para permitir NULL en mysqli, usamos "s" y pasamos NULL directamente
      mysqli_stmt_bind_param($u, "si", $nuevo, $usuario);
      mysqli_stmt_execute($u);
      mysqli_stmt_close($u);

    } elseif ($accion === 'eliminar_de_clase' && $claseId > 0) {
      $d = mysqli_prepare($conexion, "DELETE FROM cuenta_has_clase WHERE Cuenta_Usuario=? AND Clase_id_clase=?");
      mysqli_stmt_bind_param($d, "ii", $usuario, $claseId);
      mysqli_stmt_execute($d);
      mysqli_stmt_close($d);
      header("Location: Estudiantes.php?clase_id=".$claseId);
      exit;

    } elseif ($accion === 'guardar_info') {
      // Verificar si existe registro en 'informacion'
      $sel = mysqli_prepare($conexion, "SELECT CI FROM informacion WHERE Cuenta_Usuario=?");
      mysqli_stmt_bind_param($sel, "i", $usuario);
      mysqli_stmt_execute($sel);
      $resSel = mysqli_stmt_get_result($sel);
      $existe = ($resSel && mysqli_fetch_row($resSel)) ? true : false;
      mysqli_stmt_close($sel);

      // Datos del formulario
      $CI         = ($_POST['CI'] !== '') ? (int)$_POST['CI'] : null;
      $Nombres    = $_POST['Nombres']   ?? '';
      $Apellidos  = $_POST['Apellidos'] ?? '';
      $Direccion  = $_POST['Direccion'] ?? '';
      $Nacimiento = $_POST['Nacimiento'] ?: null;  // 'YYYY-MM-DD' o null
      $Telefono   = $_POST['Telefono']  ?? '';
      $Curso      = $_POST['Curso']     ?? '';
      $Rude       = ($_POST['Rude'] !== '') ? (int)$_POST['Rude'] : null;

      if ($existe) {
        $sql = "UPDATE informacion 
                SET CI=?, Nombres=?, Apellidos=?, Direccion=?, Nacimiento=?, Telefono=?, Curso=?, Rude=?
                WHERE Cuenta_Usuario=?";
        $stU = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stU, "isssssisi", $CI, $Nombres, $Apellidos, $Direccion, $Nacimiento, $Telefono, $Curso, $Rude, $usuario);
        mysqli_stmt_execute($stU);
        mysqli_stmt_close($stU);
      } else {
        $sql = "INSERT INTO informacion (CI, Nombres, Apellidos, Direccion, Nacimiento, Telefono, Curso, Rude, Cuenta_Usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stI = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stI, "isssssisi", $CI, $Nombres, $Apellidos, $Direccion, $Nacimiento, $Telefono, $Curso, $Rude, $usuario);
        mysqli_stmt_execute($stI);
        mysqli_stmt_close($stI);
      }
    }
  } catch (Throwable $e) {
    http_response_code(500);
    echo "Error: ".$e->getMessage();
    exit;
  }

  // PRG
  $self = "PerfilEstudiantes.php?usuario=".$usuario.($claseId>0 ? "&clase_id=".$claseId : "");
  header("Location: ".$self);
  exit;
}

// ---------- Datos para mostrar ----------
$cuenta = null;
$stC = mysqli_prepare($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=?");
mysqli_stmt_bind_param($stC, "i", $usuario);
mysqli_stmt_execute($stC);
$resC = mysqli_stmt_get_result($stC);
$cuenta = mysqli_fetch_assoc($resC);
mysqli_stmt_close($stC);
if (!$cuenta) { die("Cuenta no encontrada."); }

$info = null;
$stI = mysqli_prepare($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=?");
mysqli_stmt_bind_param($stI, "i", $usuario);
mysqli_stmt_execute($stI);
$resI = mysqli_stmt_get_result($stI);
$info = mysqli_fetch_assoc($resI);
mysqli_stmt_close($stI);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil · <?= htmlspecialchars($usuario) ?></title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,1);margin:20px auto;max-width:900px}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    label{font-weight:bold}
    input,select{padding:8px;border:1px solid #ddd;border-radius:6px;width:100%}
    .btn{background:#6b0014;color:#fff;border:0;border-radius:8px;padding:8px 14px;cursor:pointer}
    .btn.warn{background:#8b0020}
    .btn.gray{background:#666}
  </style>
</head>
<body>
  <header>
    <h1>Perfil del estudiante</h1>
    <img class="logo-colegio" src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="logo">
  </header>

  <div class="menu-top">
    <a href="cursos.php">Cursos</a>
    <?php if ($claseId>0): ?>
      <a href="Estudiantes.php?clase_id=<?= urlencode($claseId) ?>">Estudiantes</a>
    <?php endif; ?>
  </div>

  <section class="card">
    <h2>Cuenta</h2>
    <p><b>Usuario:</b> <?= htmlspecialchars($cuenta['Usuario']) ?> ·
       <b>Rol:</b> <?= htmlspecialchars($cuenta['Rol'] ?? '—') ?> ·
       <b>Bloqueado:</b> <?= ($cuenta['Bloqueado']==='1') ? 'Sí' : 'No' ?></p>

    <form method="post" style="display:inline-block;margin-right:8px">
      <input type="hidden" name="accion" value="cambiar_a_profesor">
      <button class="btn" type="submit">Cambiar rol a Profesor</button>
    </form>

    <form method="post" style="display:inline-block;margin-right:8px">
      <input type="hidden" name="accion" value="toggle_bloqueo">
      <button class="btn warn" type="submit"><?= ($cuenta['Bloqueado']==='1') ? 'Desbloquear' : 'Bloquear' ?></button>
    </form>

    <?php if ($claseId>0): ?>
    <form method="post" onsubmit="return confirm('¿Eliminar de esta clase?');" style="display:inline-block;">
      <input type="hidden" name="accion" value="eliminar_de_clase">
      <button class="btn gray" type="submit">Eliminar de la clase</button>
    </form>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Datos personales</h2>
    <form method="post">
      <input type="hidden" name="accion" value="guardar_info">
      <div class="row">
        <div>
          <label>CI</label>
          <input name="CI" type="number" value="<?= htmlspecialchars($info['CI'] ?? '') ?>">
        </div>
        <div>
          <label>Nombres</label>
          <input name="Nombres" value="<?= htmlspecialchars($info['Nombres'] ?? '') ?>">
        </div>
        <div>
          <label>Apellidos</label>
          <input name="Apellidos" value="<?= htmlspecialchars($info['Apellidos'] ?? '') ?>">
        </div>
        <div>
          <label>Dirección</label>
          <input name="Direccion" value="<?= htmlspecialchars($info['Direccion'] ?? '') ?>">
        </div>
        <div>
          <label>Fecha de nacimiento</label>
          <input name="Nacimiento" type="date" value="<?= htmlspecialchars($info['Nacimiento'] ?? '') ?>">
        </div>
        <div>
          <label>Teléfono</label>
          <input name="Telefono" value="<?= htmlspecialchars($info['Telefono'] ?? '') ?>">
        </div>
        <div>
          <label>Curso</label>
          <input name="Curso" value="<?= htmlspecialchars($info['Curso'] ?? '') ?>">
        </div>
        <div>
          <label>Rude</label>
          <input name="Rude" type="number" value="<?= htmlspecialchars($info['Rude'] ?? '') ?>">
        </div>
      </div>
      <div style="margin-top:14px;">
        <button class="btn" type="submit">Guardar cambios</button>
      </div>
    </form>
  </section>
</body>
</html>