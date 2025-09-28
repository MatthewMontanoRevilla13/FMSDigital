<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { echo "Error en la conexion".mysqli_error($conexion); die(); }
mysqli_set_charset($conexion, "utf8");

// --- Parámetro ---
$usuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : 0;
if ($usuario <= 0) { die("Usuario inválido."); }

// ---------- Acciones POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'];

  if ($accion === 'cambiar_rol') {
    $nuevoRol = $_POST['nuevoRol'];
    if (in_array($nuevoRol, ['Administrador','Profesor','Estudiante'])) {
      mysqli_query($conexion, "UPDATE cuenta SET Rol='$nuevoRol' WHERE Usuario=$usuario");
    }

  } elseif ($accion === 'toggle_bloqueo') {
    $res = mysqli_query($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=$usuario");
    $fila = $res ? mysqli_fetch_assoc($res) : null;
    $nuevo = ($fila && $fila['Bloqueado'] === '1') ? "NULL" : "'1'";
    mysqli_query($conexion, "UPDATE cuenta SET Bloqueado=$nuevo WHERE Usuario=$usuario");

  } elseif ($accion === 'guardar_info') {
    $res = mysqli_query($conexion, "SELECT CI FROM informacion WHERE Cuenta_Usuario=$usuario");
    $existe = ($res && mysqli_fetch_row($res)) ? true : false;

    $CI   = ($_POST['CI']   !== '') ? intval($_POST['CI'])   : "NULL";
    $Rude = ($_POST['Rude'] !== '') ? intval($_POST['Rude']) : "NULL";

    $Nombres   = $_POST['Nombres'];
    $Apellidos = $_POST['Apellidos'];
    $Direccion = $_POST['Direccion'];
    $Telefono  = $_POST['Telefono'];
    $Curso     = $_POST['Curso'];
    $Nacimiento = ($_POST['Nacimiento'] !== '') ? "'".$_POST['Nacimiento']."'" : "NULL";

    if ($existe) {
      $sql = "UPDATE informacion SET 
                CI=$CI,
                Nombres='$Nombres',
                Apellidos='$Apellidos',
                Direccion='$Direccion',
                Nacimiento=$Nacimiento,
                Telefono='$Telefono',
                Curso='$Curso',
                Rude=$Rude
              WHERE Cuenta_Usuario=$usuario";
    } else {
      $sql = "INSERT INTO informacion
                (CI, Nombres, Apellidos, Direccion, Nacimiento, Telefono, Curso, Rude, Cuenta_Usuario)
              VALUES
                ($CI, '$Nombres', '$Apellidos', '$Direccion', $Nacimiento, '$Telefono', '$Curso', $Rude, $usuario)";
    }
    mysqli_query($conexion, $sql);
  }

  header("Location: infousuarios.php?usuario=".$usuario);
  exit;
}

// ---------- Datos ----------
$resC = mysqli_query($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=$usuario");
$cuenta = $resC ? mysqli_fetch_assoc($resC) : null;
if (!$cuenta) { die("Cuenta no encontrada."); }

$resI = mysqli_query($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=$usuario");
$info = $resI ? mysqli_fetch_assoc($resI) : null;

$sqlCl = "
  SELECT c.id_clase, c.nombreClase, c.nomProfe, c.codigoClase
  FROM (
    SELECT Clase_id_clase AS id_clase FROM cuenta_has_clase WHERE Cuenta_Usuario = $usuario
    UNION
    SELECT id_clase FROM clase WHERE Cuenta_Usuario = $usuario
  ) t
  JOIN clase c ON c.id_clase = t.id_clase
  ORDER BY c.nombreClase
";
$resCl = mysqli_query($conexion, $sqlCl);
$clases = [];
if ($resCl) { while ($r = mysqli_fetch_assoc($resCl)) { $clases[] = $r; } }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Info de Usuario · <?= $usuario ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin:20px auto;max-width:1000px}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    label{font-weight:bold}
    input,select{padding:8px;border:1px solid #ddd;border-radius:6px;width:100%}
    .btn{background:#6b0014;color:#fff;border:0;border-radius:8px;padding:8px 14px;cursor:pointer}
    .btn.warn{background:#8b0020}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #e5e5e5;padding:8px;text-align:left}
    th{background:#f8f8f8}
    a{color:#6b0014;text-decoration:none}
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <header style="padding: 10px 20px;">
    <h1>Usuario: <?= $usuario ?></h1>
    <div><a href="usuarios.php">← Volver a Usuarios</a></div>
  </header>

  <section class="card">
    <h2>Cuenta</h2>
    <p>
      <b>Rol actual:</b> <?= $cuenta['Rol'] ?> ·
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
        <div><label>CI</label><input name="CI" type="number" value="<?= $info['CI'] ?? '' ?>"></div>
        <div><label>Nombres</label><input name="Nombres" value="<?= $info['Nombres'] ?? '' ?>"></div>
        <div><label>Apellidos</label><input name="Apellidos" value="<?= $info['Apellidos'] ?? '' ?>"></div>
        <div><label>Dirección</label><input name="Direccion" value="<?= $info['Direccion'] ?? '' ?>"></div>
        <div><label>Fecha de nacimiento</label><input name="Nacimiento" type="date" value="<?= $info['Nacimiento'] ?? '' ?>"></div>
        <div><label>Teléfono</label><input name="Telefono" value="<?= $info['Telefono'] ?? '' ?>"></div>
        <div><label>Curso</label><input name="Curso" value="<?= $info['Curso'] ?? '' ?>"></div>
        <div><label>Rude</label><input name="Rude" type="number" value="<?= $info['Rude'] ?? '' ?>"></div>
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
        <?php else: foreach ($clases as $c): ?>
          <tr>
            <td><?= $c['id_clase'] ?></td>
            <td><?= $c['nombreClase'] ?></td>
            <td><?= $c['nomProfe'] ?></td>
            <td><?= $c['codigoClase'] ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </section>
</body>
</html>
