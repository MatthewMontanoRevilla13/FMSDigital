<?php
// SIN RESTRICCIÓN DE ROL

$pdo = new PDO("mysql:host=127.0.0.1;dbname=registrop6;charset=utf8","root","",[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
]);

$usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$claseId = isset($_GET['clase_id']) ? (int)$_GET['clase_id'] : 0;
if ($usuario <= 0) { die("Usuario inválido."); }

// ---------- Acciones POST (en la misma página) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';
  try {
    if ($accion === 'cambiar_a_profesor') {
      $q = $pdo->prepare("UPDATE cuenta SET Rol='Profesor' WHERE Usuario=?");
      $q->execute([$usuario]);
    } elseif ($accion === 'toggle_bloqueo') {
      $cur = $pdo->prepare("SELECT Bloqueado FROM cuenta WHERE Usuario=?");
      $cur->execute([$usuario]);
      $actual = $cur->fetchColumn();
      $nuevo = ($actual === '1') ? NULL : '1';
      $u = $pdo->prepare("UPDATE cuenta SET Bloqueado=? WHERE Usuario=?");
      $u->execute([$nuevo, $usuario]);
    } elseif ($accion === 'eliminar_de_clase' && $claseId > 0) {
      $d = $pdo->prepare("DELETE FROM cuenta_has_clase WHERE Cuenta_Usuario=? AND Clase_id_clase=?");
      $d->execute([$usuario, $claseId]);
      header("Location: admin_estudiantes.php?clase_id=".$claseId);
      exit;
    } elseif ($accion === 'guardar_info') {
      $sel = $pdo->prepare("SELECT CI FROM informacion WHERE Cuenta_Usuario=?");
      $sel->execute([$usuario]);
      $existe = $sel->fetchColumn();

      $CI = ($_POST['CI'] !== '') ? (int)$_POST['CI'] : null;
      $Nombres = $_POST['Nombres'] ?? '';
      $Apellidos = $_POST['Apellidos'] ?? '';
      $Direccion = $_POST['Direccion'] ?? '';
      $Nacimiento = $_POST['Nacimiento'] ?: null;
      $Telefono = $_POST['Telefono'] ?? '';
      $Curso = $_POST['Curso'] ?? '';
      $Rude = ($_POST['Rude'] !== '') ? (int)$_POST['Rude'] : null;

      if ($existe) {
        $sql = "UPDATE informacion SET CI=?, Nombres=?, Apellidos=?, Direccion=?, Nacimiento=?, Telefono=?, Curso=?, Rude=? WHERE Cuenta_Usuario=?";
        $pdo->prepare($sql)->execute([$CI, $Nombres, $Apellidos, $Direccion, $Nacimiento, $Telefono, $Curso, $Rude, $usuario]);
      } else {
        $sql = "INSERT INTO informacion (CI, Nombres, Apellidos, Direccion, Nacimiento, Telefono, Curso, Rude, Cuenta_Usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$CI, $Nombres, $Apellidos, $Direccion, $Nacimiento, $Telefono, $Curso, $Rude, $usuario]);
      }
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo "Error: ".$e->getMessage();
    exit;
  }

  // PRG
  $self = "admin_estudiante.php?usuario=".$usuario.($claseId>0 ? "&clase_id=".$claseId : "");
  header("Location: ".$self); exit;
}

// ---------- Datos para mostrar ----------
$cuenta = $pdo->prepare("SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=?");
$cuenta->execute([$usuario]);
$cuenta = $cuenta->fetch() ?: die("Cuenta no encontrada.");

$info = $pdo->prepare("SELECT * FROM informacion WHERE Cuenta_Usuario=?");
$info->execute([$usuario]);
$info = $info->fetch();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil · <?= htmlspecialchars($usuario) ?></title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin:20px auto;max-width:900px}
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
    <img class="logo-colegio" src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="logo">
  </header>

  <div class="menu-top">
    <a href="admin_cursos.php">Cursos</a>
    <?php if ($claseId>0): ?>
      <a href="admin_estudiantes.php?clase_id=<?= urlencode($claseId) ?>">Estudiantes</a>
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
