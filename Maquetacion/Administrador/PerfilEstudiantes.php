<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión (simple) ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { echo "Error en la conexion".mysqli_error($conexion); die(); }
mysqli_set_charset($conexion, "utf8");

// --- Parámetros ---
$usuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : 0;
$claseId = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : 0;
if ($usuario <= 0) { die("Usuario inválido."); }

// ---------- Acciones POST (simple) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

  if ($accion === 'cambiar_a_profesor') {
    mysqli_query($conexion, "UPDATE cuenta SET Rol='Profesor' WHERE Usuario=$usuario");

  } elseif ($accion === 'toggle_bloqueo') {
    $res = mysqli_query($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=$usuario");
    $fila = $res ? mysqli_fetch_assoc($res) : null;
    $nuevo = ($fila && $fila['Bloqueado'] === '1') ? "NULL" : "'1'";
    mysqli_query($conexion, "UPDATE cuenta SET Bloqueado=$nuevo WHERE Usuario=$usuario");

  } elseif ($accion === 'eliminar_de_clase' && $claseId > 0) {
    mysqli_query($conexion, "DELETE FROM cuenta_has_clase WHERE Cuenta_Usuario=$usuario AND Clase_id_clase=$claseId");
    header("Location: Estudiantes.php?clase_id=".$claseId);
    exit;

  } elseif ($accion === 'guardar_info') {
    // ¿Existe ya informacion?
    $resSel = mysqli_query($conexion, "SELECT CI FROM informacion WHERE Cuenta_Usuario=$usuario");
    $existe = ($resSel && mysqli_fetch_row($resSel)) ? true : false;

    // Numéricos (permitir NULL)
    $CI   = (isset($_POST['CI'])   && $_POST['CI']   !== '') ? intval($_POST['CI'])   : "NULL";
    $Rude = (isset($_POST['Rude']) && $_POST['Rude'] !== '') ? intval($_POST['Rude']) : "NULL";

    // Strings (sin escape)
    $Nombres   = isset($_POST['Nombres'])   ? $_POST['Nombres']   : '';
    $Apellidos = isset($_POST['Apellidos']) ? $_POST['Apellidos'] : '';
    $Direccion = isset($_POST['Direccion']) ? $_POST['Direccion'] : '';
    $Telefono  = isset($_POST['Telefono'])  ? $_POST['Telefono']  : '';
    $Curso     = isset($_POST['Curso'])     ? $_POST['Curso']     : '';

    // Fecha (permitir NULL)
    $Nacimiento = (isset($_POST['Nacimiento']) && $_POST['Nacimiento'] !== '') ? "'".$_POST['Nacimiento']."'" : "NULL";

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

  // PRG
  $self = "PerfilEstudiantes.php?usuario=".$usuario.($claseId>0 ? "&clase_id=".$claseId : "");
  header("Location: ".$self);
  exit;
}

// ---------- Datos para mostrar ----------
$resC = mysqli_query($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=$usuario");
$cuenta = $resC ? mysqli_fetch_assoc($resC) : null;
if (!$cuenta) { die("Cuenta no encontrada."); }

$resI = mysqli_query($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=$usuario");
$info = $resI ? mysqli_fetch_assoc($resI) : null;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil · <?= $usuario ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <style>
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin:20px auto;max-width:900px}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    label{font-weight:bold}
    input,select{padding:8px;border:1px solid #ddd;border-radius:6px;width:100%}
    .btn{background:#6b0014;color:#fff;border:0;border-radius:8px;padding:8px 14px;cursor:pointer}
    .btn.warn{background:#8b0020}
    .btn.gray{background:#666}
    a{color:#6b0014;text-decoration:none}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:10px 20px}
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <header class="topbar">
    <h1 style="margin:0;">Perfil del estudiante</h1>
    <img class="logo-colegio" src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="logo" style="height:36px">
  </header>

  <div class="menu-top" style="padding:0 20px;">
    <a href="cursos.php">Cursos</a>
    <?php if ($claseId>0): ?>
      &nbsp;·&nbsp;<a href="Estudiantes.php?clase_id=<?= $claseId ?>">Estudiantes</a>
    <?php endif; ?>
  </div>

  <section class="card">
    <h2>Cuenta</h2>
    <p><b>Usuario:</b> <?= $cuenta['Usuario'] ?> ·
       <b>Rol:</b> <?= $cuenta['Rol'] ?: '—' ?> ·
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
          <input name="CI" type="number" value="<?= $info['CI'] ?? '' ?>">
        </div>
        <div>
          <label>Nombres</label>
          <input name="Nombres" value="<?= $info['Nombres'] ?? '' ?>">
        </div>
        <div>
          <label>Apellidos</label>
          <input name="Apellidos" value="<?= $info['Apellidos'] ?? '' ?>">
        </div>
        <div>
          <label>Dirección</label>
          <input name="Direccion" value="<?= $info['Direccion'] ?? '' ?>">
        </div>
        <div>
          <label>Fecha de nacimiento</label>
          <input name="Nacimiento" type="date" value="<?= $info['Nacimiento'] ?? '' ?>">
        </div>
        <div>
          <label>Teléfono</label>
          <input name="Telefono" value="<?= $info['Telefono'] ?? '' ?>">
        </div>
        <div>
          <label>Curso</label>
          <input name="Curso" value="<?= $info['Curso'] ?? '' ?>">
        </div>
        <div>
          <label>Rude</label>
          <input name="Rude" type="number" value="<?= $info['Rude'] ?? '' ?>">
        </div>
      </div>
      <div style="margin-top:14px;">
        <button class="btn" type="submit">Guardar cambios</button>
      </div>
    </form>
  </section>
</body>
</html>
