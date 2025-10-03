<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  die("Error en la conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

$idUsuario = 0;
if (isset($_GET['usuario'])) {
  $idUsuario = intval($_GET['usuario']);
}
$idClase = 0;
if (isset($_GET['clase_id'])) {
  $idClase = intval($_GET['clase_id']);
}

if ($idUsuario <= 0) {
  die("Usuario inválido.");
}

/* === ACCIONES POST (simple) === */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = "";
  if (isset($_POST['accion'])) { $accion = $_POST['accion']; }

  if ($accion == 'cambiar_a_profesor') {
    mysqli_query($conexion, "UPDATE cuenta SET Rol='Profesor' WHERE Usuario=$idUsuario");
  }

  if ($accion == 'toggle_bloqueo') {
    $res = mysqli_query($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=$idUsuario");
    $fila = mysqli_fetch_assoc($res);
    $nuevo = "1";
    if ($fila && $fila['Bloqueado'] == '1') {
      $nuevo = "NULL";
    }
    mysqli_query($conexion, "UPDATE cuenta SET Bloqueado=$nuevo WHERE Usuario=$idUsuario");
  }

  if ($accion == 'eliminar_de_clase' && $idClase > 0) {
    mysqli_query($conexion, "DELETE FROM cuenta_has_clase WHERE Cuenta_Usuario=$idUsuario AND Clase_id_clase=$idClase");
    header("Location: Estudiantes.php?clase_id=".$idClase);
    exit;
  }

  if ($accion == 'guardar_info') {
    $res = mysqli_query($conexion, "SELECT CI FROM informacion WHERE Cuenta_Usuario=$idUsuario");
    $existe = mysqli_fetch_row($res);

    $CI = "NULL";
    if (isset($_POST['CI']) && $_POST['CI'] !== '') { $CI = intval($_POST['CI']); }

    $Rude = "NULL";
    if (isset($_POST['Rude']) && $_POST['Rude'] !== '') { $Rude = intval($_POST['Rude']); }

    $Nombres   = isset($_POST['Nombres'])   ? $_POST['Nombres']   : '';
    $Apellidos = isset($_POST['Apellidos']) ? $_POST['Apellidos'] : '';
    $Direccion = isset($_POST['Direccion']) ? $_POST['Direccion'] : '';
    $Telefono  = isset($_POST['Telefono'])  ? $_POST['Telefono']  : '';
    $Curso     = isset($_POST['Curso'])     ? $_POST['Curso']     : '';

    $Nacimiento = "NULL";
    if (isset($_POST['Nacimiento']) && $_POST['Nacimiento'] !== '') {
      $Nacimiento = "'".$_POST['Nacimiento']."'";
    }

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
              WHERE Cuenta_Usuario=$idUsuario";
    } else {
      $sql = "INSERT INTO informacion (CI,Nombres,Apellidos,Direccion,Nacimiento,Telefono,Curso,Rude,Cuenta_Usuario)
              VALUES ($CI,'$Nombres','$Apellidos','$Direccion',$Nacimiento,'$Telefono','$Curso',$Rude,$idUsuario)";
    }
    mysqli_query($conexion, $sql);
  }

  $self = "PerfilEstudiantes.php?usuario=".$idUsuario;
  if ($idClase > 0) { $self .= "&clase_id=".$idClase; }
  header("Location: ".$self);
  exit;
}

/* === DATOS PARA MOSTRAR === */
$resCuenta = mysqli_query($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=$idUsuario");
$cuenta = mysqli_fetch_assoc($resCuenta);
if (!$cuenta) { die("Cuenta no encontrada."); }

$resInfo = mysqli_query($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=$idUsuario");
$info = mysqli_fetch_assoc($resInfo);

$tituloPagina = "Perfil · ".$idUsuario;

$textoRol = "—";
if ($cuenta && $cuenta['Rol'] != '') { $textoRol = $cuenta['Rol']; }

$textoBloqueado = "No";
$textoBotonBloq = "Bloquear";
if ($cuenta && $cuenta['Bloqueado'] == '1') {
  $textoBloqueado = "Sí";
  $textoBotonBloq = "Desbloquear";
}

$valCI = '';
if ($info && isset($info['CI'])) { $valCI = $info['CI']; }
$valNombres = '';
if ($info && isset($info['Nombres'])) { $valNombres = $info['Nombres']; }
$valApellidos = '';
if ($info && isset($info['Apellidos'])) { $valApellidos = $info['Apellidos']; }
$valDireccion = '';
if ($info && isset($info['Direccion'])) { $valDireccion = $info['Direccion']; }
$valNacimiento = '';
if ($info && isset($info['Nacimiento'])) { $valNacimiento = $info['Nacimiento']; }
$valTelefono = '';
if ($info && isset($info['Telefono'])) { $valTelefono = $info['Telefono']; }
$valCurso = '';
if ($info && isset($info['Curso'])) { $valCurso = $info['Curso']; }
$valRude = '';
if ($info && isset($info['Rude'])) { $valRude = $info['Rude']; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?php echo $tituloPagina; ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <style>
  
    :root { --vino:#6b0014; --vino-osc:#8b0020; --gris:#666; --borde:#ddd; }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: #f7f7fb;
      color: #333;
    }

    /* Barra superior del módulo */
    .topbar {
      background: var(--vino);
      color: #fff;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .topbar h1 { margin: 0; font-size: 20px; }
    .topbar img { height: 36px; }

    /* Contenedor principal */
    .wrap {
      max-width: 980px;
      margin: 20px auto;
      padding: 0 16px;
      box-sizing: border-box;
    }

    /* Migas / navegación simple */
    .breadcrumbs {
      margin: 10px 0 0;
      padding: 0 2px 12px;
    }
    .breadcrumbs a {
      color: var(--vino);
      text-decoration: none;
      font-weight: 600;
    }

    /* Tarjetas */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,.06);
      padding: 18px;
      margin: 16px 0;
    }
    .card h2 { margin-top: 0; color: var(--vino); font-size: 18px; }

    /* Grilla para el formulario */
    .row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
    }

    label { font-weight: 600; display: block; margin-bottom: 6px; }
    input, select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--borde);
      border-radius: 8px;
      box-sizing: border-box;
      font-size: 14px;
      margin-bottom: 10px;
      background: #fff;
    }

    /* Botones */
    .btn {
      background: var(--vino);
      color: #fff;
      border: 0;
      border-radius: 8px;
      padding: 10px 14px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn.warn { background: var(--vino-osc); }
    .btn.gray { background: var(--gris); }
    .btn + .btn { margin-left: 8px; }

    /* Grupo de botones en línea */
    .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    /* Responsive sencillo */
    @media (max-width: 600px) {
      .topbar { padding: 12px 16px; }
      .topbar h1 { font-size: 18px; }
      .wrap { padding: 0 12px; }
    }
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <!-- Barra del módulo -->
  <div class="topbar">
    <h1>Perfil del estudiante</h1>
    <img src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="Logo">
  </div>

  <div class="wrap">
    <!-- Breadcrumbs simples -->
    <div class="breadcrumbs">
      <a href="cursos.php">Cursos</a>
      <?php if ($idClase > 0): ?>
        &nbsp;·&nbsp;<a href="Estudiantes.php?clase_id=<?php echo intval($idClase); ?>">Estudiantes</a>
      <?php endif; ?>
    </div>

    <!-- Tarjeta: Cuenta -->
    <section class="card">
      <h2>Cuenta</h2>
      <p>
        <b>Usuario:</b> <?php echo $cuenta['Usuario']; ?> &nbsp;·&nbsp;
        <b>Rol:</b> <?php echo $textoRol; ?> &nbsp;·&nbsp;
        <b>Bloqueado:</b> <?php echo $textoBloqueado; ?>
      </p>

      <div class="actions">
        <form method="post">
          <input type="hidden" name="accion" value="cambiar_a_profesor">
          <button class="btn" type="submit">Cambiar a Profesor</button>
        </form>

        <form method="post">
          <input type="hidden" name="accion" value="toggle_bloqueo">
          <button class="btn warn" type="submit"><?php echo $textoBotonBloq; ?></button>
        </form>

        <?php if ($idClase > 0): ?>
          <form method="post" onsubmit="return confirm('¿Eliminar de esta clase?');">
            <input type="hidden" name="accion" value="eliminar_de_clase">
            <button class="btn gray" type="submit">Eliminar de la clase</button>
          </form>
        <?php endif; ?>
      </div>
    </section>

    <!-- Tarjeta: Datos personales -->
    <section class="card">
      <h2>Datos personales</h2>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_info">
        <div class="row">
          <div>
            <label>CI</label>
            <input name="CI" type="number" value="<?php echo $valCI; ?>">
          </div>
          <div>
            <label>Nombres</label>
            <input name="Nombres" value="<?php echo $valNombres; ?>">
          </div>
          <div>
            <label>Apellidos</label>
            <input name="Apellidos" value="<?php echo $valApellidos; ?>">
          </div>
          <div>
            <label>Dirección</label>
            <input name="Direccion" value="<?php echo $valDireccion; ?>">
          </div>
          <div>
            <label>Fecha de nacimiento</label>
            <input name="Nacimiento" type="date" value="<?php echo $valNacimiento; ?>">
          </div>
          <div>
            <label>Teléfono</label>
            <input name="Telefono" value="<?php echo $valTelefono; ?>">
          </div>
          <div>
            <label>Curso</label>
            <input name="Curso" value="<?php echo $valCurso; ?>">
          </div>
          <div>
            <label>Rude</label>
            <input name="Rude" type="number" value="<?php echo $valRude; ?>">
          </div>
        </div>

        <div class="actions" style="margin-top:8px;">
          <button class="btn" type="submit">Guardar cambios</button>
        </div>
      </form>
    </section>
  </div>
</body>
</html>
