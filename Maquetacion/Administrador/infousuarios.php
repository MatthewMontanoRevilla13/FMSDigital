<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión mysqli ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}
mysqli_set_charset($conexion, "utf8");

$usuario = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
if ($usuario <= 0) { die("Usuario inválido."); }

// ---------- Acciones POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';

  try {
    if ($accion === 'cambiar_rol') {
      $nuevoRol = $_POST['nuevoRol'] ?? '';
      if (!in_array($nuevoRol, ['Administrador','Profesor','Estudiante'], true)) {
        throw new Exception("Rol inválido.");
      }
      $q = mysqli_prepare($conexion, "UPDATE cuenta SET Rol=? WHERE Usuario=?");
      mysqli_stmt_bind_param($q, "si", $nuevoRol, $usuario);
      mysqli_stmt_execute($q);
      mysqli_stmt_close($q);

    } elseif ($accion === 'toggle_bloqueo') {
      $cur = mysqli_prepare($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=?");
      mysqli_stmt_bind_param($cur, "i", $usuario);
      mysqli_stmt_execute($cur);
      $resCur = mysqli_stmt_get_result($cur);
      $filaCur = mysqli_fetch_assoc($resCur);
      mysqli_stmt_close($cur);

      $actual = $filaCur ? $filaCur['Bloqueado'] : null;
      $nuevo = ($actual === '1') ? NULL : '1';

      $u = mysqli_prepare($conexion, "UPDATE cuenta SET Bloqueado=? WHERE Usuario=?");
      mysqli_stmt_bind_param($u, "si", $nuevo, $usuario);
      mysqli_stmt_execute($u);
      mysqli_stmt_close($u);

    } elseif ($accion === 'guardar_info') {
      // Verificar si existe registro en 'informacion'
      $sel = mysqli_prepare($conexion, "SELECT CI FROM informacion WHERE Cuenta_Usuario=?");
      mysqli_stmt_bind_param($sel, "i", $usuario);
      mysqli_stmt_execute($sel);
      $resSel = mysqli_stmt_get_result($sel);
      $existe = ($resSel && mysqli_fetch_row($resSel)) ? true : false;
      mysqli_stmt_close($sel);

      // Datos
      $CI         = ($_POST['CI'] !== '') ? (int)$_POST['CI'] : null;
      $Nombres    = $_POST['Nombres']   ?? '';
      $Apellidos  = $_POST['Apellidos'] ?? '';
      $Direccion  = $_POST['Direccion'] ?? '';
      $Nacimiento = $_POST['Nacimiento'] ?: null;
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
  header("Location: infousuarios.php?usuario=".$usuario);
  exit;
}

// ---------- Datos para mostrar ----------
$stC = mysqli_prepare($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=?");
mysqli_stmt_bind_param($stC, "i", $usuario);
mysqli_stmt_execute($stC);
$resC = mysqli_stmt_get_result($stC);
$cuenta = mysqli_fetch_assoc($resC);
mysqli_stmt_close($stC);
if (!$cuenta) { die("Cuenta no encontrada."); }

$stI = mysqli_prepare($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=?");
mysqli_stmt_bind_param($stI, "i", $usuario);
mysqli_stmt_execute($stI);
$resI = mysqli_stmt_get_result($stI);
$info = mysqli_fetch_assoc($resI);
mysqli_stmt_close($stI);

// Clases del usuario (como profe dueño y/o como miembro)
$stCl = mysqli_prepare($conexion,
  "SELECT c.id_clase, c.nombreClase, c.nomProfe, c.codigoClase
   FROM (
      SELECT Clase_id_clase AS id_clase FROM cuenta_has_clase WHERE Cuenta_Usuario = ?
      UNION
      SELECT id_clase FROM clase WHERE Cuenta_Usuario = ?
   ) t
   JOIN clase c ON c.id_clase = t.id_clase
   ORDER BY c.nombreClase"
);
mysqli_stmt_bind_param($stCl, "ii", $usuario, $usuario);
mysqli_stmt_execute($stCl);
$resCl = mysqli_stmt_get_result($stCl);
$clases = $resCl ? mysqli_fetch_all($resCl, MYSQLI_ASSOC) : [];
mysqli_stmt_close($stCl);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Info de Usuario · <?= htmlspecialchars($usuario) ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin:20px auto;max-width:1000px}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    label{font-weight:bold}
    input,select{padding:8px;border:1px solid #ddd;border-radius:6px;width:100%}
    .btn{background:#6b0014;color:#fff;border:0;border-radius:8px;padding:8px 14px;cursor:pointer}
    .btn.warn{background:#8b0020}
    .btn.gray{background:#666}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #e5e5e5;padding:8px;text-align:left}
    th{background:#f8f8f8}
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <header style="padding: 10px 20px;">
    <h1>Usuario: <?= htmlspecialchars($usuario) ?></h1>
    <div class="menu-top">
      <a href="usuarios.php">← Volver a Usuarios</a>
    </div>
  </header>

  <section class="card">
    <h2>Cuenta</h2>
    <p>
      <b>Rol actual:</b> <?= htmlspecialchars($cuenta['Rol'] ?? '—') ?> ·
      <b>Bloqueado:</b> <?= ($cuenta['Bloqueado']==='1') ? 'Sí' : 'No' ?>
    </p>

    <form method="post" style="display:inline-flex;gap:8px;align-items:center;margin-right:12px">
      <input type="hidden" name="accion" value="cambiar_rol">
      <label for="nuevoRol">Cambiar rol:</label>
      <select id="nuevoRol" name="nuevoRol">
        <?php
          $roles = ['Administrador','Profesor','Estudiante'];
          foreach ($roles as $r) {
            $sel = ($cuenta['Rol'] === $r) ? 'selected' : '';
            echo "<option $sel value=\"$r\">$r</option>";
          }
        ?>
      </select>
      <button class="btn" type="submit">Guardar rol</button>
    </form>

    <form method="post" style="display:inline-block;">
      <input type="hidden" name="accion" value="toggle_bloqueo">
      <button class="btn warn" type="submit"><?= ($cuenta['Bloqueado']==='1') ? 'Desbloquear' : 'Bloquear' ?></button>
    </form>
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

  <section class="card">
    <h2>Clases donde participa</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Clase</th><th>Profesor (dueño)</th><th>Código</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$clases): ?>
          <tr><td colspan="4">No participa en ninguna clase.</td></tr>
        <?php else: ?>
          <?php foreach ($clases as $c): ?>
            <tr>
              <td><?= (int)$c['id_clase'] ?></td>
              <td><?= htmlspecialchars($c['nombreClase']) ?></td>
              <td><?= htmlspecialchars($c['nomProfe']) ?></td>
              <td><?= htmlspecialchars($c['codigoClase']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</body>
</html>
