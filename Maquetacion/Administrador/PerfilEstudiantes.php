<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  die("Error en la conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

// --- Parámetros ---
$idUsuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : 0;
$idClase   = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : 0;

if ($idUsuario <= 0) {
  die("Usuario inválido.");
}

/* ================== ACCIONES POST ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

  if ($accion === 'cambiar_a_profesor') {
    $sql = "UPDATE cuenta SET Rol='Profesor' WHERE Usuario=$idUsuario";
    mysqli_query($conexion, $sql);
  }

  if ($accion === 'toggle_bloqueo') {
    $res  = mysqli_query($conexion, "SELECT Bloqueado FROM cuenta WHERE Usuario=$idUsuario");
    $fila = $res ? mysqli_fetch_assoc($res) : null;
    $nuevo = "1";
    if ($fila && $fila['Bloqueado'] == '1') { $nuevo = "NULL"; }
    mysqli_query($conexion, "UPDATE cuenta SET Bloqueado=$nuevo WHERE Usuario=$idUsuario");
  }

  if ($accion === 'eliminar_de_clase' && $idClase > 0) {
    $sql = "DELETE FROM cuenta_has_clase WHERE Cuenta_Usuario=$idUsuario AND Clase_id_clase=$idClase";
    mysqli_query($conexion, $sql);
    header("Location: Estudiantes.php?clase_id=".$idClase);
    exit;
  }

  if ($accion === 'guardar_info') {
    // Sanitizar entradas
    $CI         = isset($_POST['CI']) && $_POST['CI'] !== '' ? intval($_POST['CI']) : null;
    $Nombres    = mysqli_real_escape_string($conexion, $_POST['Nombres'] ?? '');
    $Apellidos  = mysqli_real_escape_string($conexion, $_POST['Apellidos'] ?? '');
    $Direccion  = mysqli_real_escape_string($conexion, $_POST['Direccion'] ?? '');
    $Telefono   = mysqli_real_escape_string($conexion, $_POST['Telefono'] ?? '');
    $Curso      = mysqli_real_escape_string($conexion, $_POST['Curso'] ?? '');
    $Rude       = isset($_POST['Rude']) && $_POST['Rude'] !== '' ? intval($_POST['Rude']) : null;
    $Nacimiento = isset($_POST['Nacimiento']) && $_POST['Nacimiento'] !== '' ? $_POST['Nacimiento'] : null;

    // Fallbacks por restricción: CI (PK, NOT NULL) y Nacimiento (NOT NULL en tu dump)
    if ($CI === null) { $CI = $idUsuario; }
    if ($Nacimiento === null) { $Nacimiento = '0000-00-00'; }

    // ¿Existe fila en informacion?
    $existe = false;
    $chk = mysqli_query($conexion, "SELECT 1 FROM informacion WHERE Cuenta_Usuario=$idUsuario LIMIT 1");
    if ($chk && mysqli_fetch_row($chk)) { $existe = true; }

    if ($existe) {
      $sql = "UPDATE informacion SET 
                CI=$CI,
                Nombres='$Nombres',
                Apellidos='$Apellidos',
                Direccion='$Direccion',
                Nacimiento='$Nacimiento',
                Telefono='$Telefono',
                Curso='$Curso',
                Rude=".($Rude !== null ? $Rude : "NULL")."
              WHERE Cuenta_Usuario=$idUsuario";
    } else {
      $sql = "INSERT INTO informacion
                (CI, Nombres, Apellidos, Direccion, Nacimiento, Telefono, Curso, Rude, Cuenta_Usuario)
              VALUES
                ($CI, '$Nombres', '$Apellidos', '$Direccion', '$Nacimiento', '$Telefono', '$Curso', ".($Rude !== null ? $Rude : "NULL").", $idUsuario)";
    }
    mysqli_query($conexion, $sql);
  }

  // Redirect de vuelta al perfil
  $self = "PerfilEstudiantes.php?usuario=".$idUsuario;
  if ($idClase > 0) { $self .= "&clase_id=".$idClase; }
  header("Location: ".$self);
  exit;
}

/* ================== DATOS PARA MOSTRAR ================== */

// Cuenta
$cuenta = null;
$resCuenta = mysqli_query($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=$idUsuario");
if ($resCuenta) { $cuenta = mysqli_fetch_assoc($resCuenta); }
if (!$cuenta) { die("Cuenta no encontrada."); }

// Información personal (puede no existir)
$info = null;
$resInfo = mysqli_query($conexion, "SELECT * FROM informacion WHERE Cuenta_Usuario=$idUsuario");
if ($resInfo) { $info = mysqli_fetch_assoc($resInfo); }

// Clases donde participa o que posee (UNION)
$clases = [];
$sqlCl = "
  SELECT c.id_clase, c.nombreClase, c.nomProfe, c.codigoClase
  FROM (
    SELECT Clase_id_clase AS id_clase FROM cuenta_has_clase WHERE Cuenta_Usuario = $idUsuario
    UNION
    SELECT id_clase FROM clase WHERE Cuenta_Usuario = $idUsuario
  ) t
  JOIN clase c ON c.id_clase = t.id_clase
  ORDER BY c.nombreClase
";
$resCl = mysqli_query($conexion, $sqlCl);
if ($resCl) {
  while ($r = mysqli_fetch_assoc($resCl)) { $clases[] = $r; }
}

// Textos derivados
$tituloPagina   = "Perfil · " . $idUsuario;
$textoRol       = ($cuenta['Rol'] !== null && $cuenta['Rol'] !== '') ? $cuenta['Rol'] : '—';
$textoBloqueado = ($cuenta['Bloqueado'] === '1') ? 'Sí' : 'No';
$textoBotonBloq = ($textoBloqueado === 'Sí') ? 'Desbloquear' : 'Bloquear';

// Valores seguros para inputs
$valCI         = htmlspecialchars($info['CI']         ?? '');
$valNombres    = htmlspecialchars($info['Nombres']    ?? '');
$valApellidos  = htmlspecialchars($info['Apellidos']  ?? '');
$valDireccion  = htmlspecialchars($info['Direccion']  ?? '');
$valNacimiento = htmlspecialchars($info['Nacimiento'] ?? '');
$valTelefono   = htmlspecialchars($info['Telefono']   ?? '');
$valCurso      = htmlspecialchars($info['Curso']      ?? '');
$valRude       = htmlspecialchars($info['Rude']       ?? '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $tituloPagina; ?></title>

  <!-- Tu CSS global si es necesario -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <!-- CSS responsivo de esta pantalla (lo que ya dijiste que está perfecto) -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/PerfilEstudiantes.css">
</head>
<body>
  <?php include '../header.php'; ?>

  <!-- Migas -->
  <div class="migas">
    <a href="cursos.php">Cursos</a>
    <?php if ($idClase > 0) { ?>
      &nbsp;·&nbsp;<a href="Estudiantes.php?clase_id=<?php echo $idClase; ?>">Estudiantes</a>
    <?php } ?>
  </div>

  <main class="contenedor">
    <!-- Tarjeta: Cuenta -->
    <section class="tarjeta">
      <h2>Cuenta</h2>
      <p style="margin:8px 0 16px">
        <b>Usuario:</b> <?php echo htmlspecialchars($cuenta['Usuario']); ?> &nbsp;·&nbsp;
        <b>Rol:</b> <?php echo htmlspecialchars($textoRol); ?> &nbsp;·&nbsp;
        <b>Bloqueado:</b> <?php echo htmlspecialchars($textoBloqueado); ?>
      </p>

      <div class="grupo-botones">
        <form method="post">
          <input type="hidden" name="accion" value="cambiar_a_profesor">
          <button class="boton" type="submit">Cambiar a Profesor</button>
        </form>

        <form method="post">
          <input type="hidden" name="accion" value="toggle_bloqueo">
          <button class="boton alerta" type="submit"><?php echo htmlspecialchars($textoBotonBloq); ?></button>
        </form>

        <?php if ($idClase > 0) { ?>
          <form method="post" onsubmit="return confirm('¿Eliminar de esta clase?');">
            <input type="hidden" name="accion" value="eliminar_de_clase">
            <button class="boton gris" type="submit">Eliminar de la clase</button>
          </form>
        <?php } ?>
      </div>
    </section>

    <!-- Tarjeta: Datos personales -->
    <section class="tarjeta">
      <h2>Datos personales</h2>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_info">
        <div class="fila">
          <div>
            <label for="ci">CI</label>
            <input id="ci" name="CI" type="number" value="<?php echo $valCI; ?>">
          </div>
          <div>
            <label for="nombres">Nombres</label>
            <input id="nombres" name="Nombres" value="<?php echo $valNombres; ?>">
          </div>
          <div>
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="Apellidos" value="<?php echo $valApellidos; ?>">
          </div>
          <div>
            <label for="direccion">Dirección</label>
            <input id="direccion" name="Direccion" value="<?php echo $valDireccion; ?>">
          </div>
          <div>
            <label for="nacimiento">Fecha de nacimiento</label>
            <input id="nacimiento" name="Nacimiento" type="date" value="<?php echo $valNacimiento; ?>">
          </div>
          <div>
            <label for="telefono">Teléfono</label>
            <input id="telefono" name="Telefono" value="<?php echo $valTelefono; ?>">
          </div>
          <div>
            <label for="curso">Curso</label>
            <input id="curso" name="Curso" value="<?php echo $valCurso; ?>">
          </div>
          <div>
            <label for="rude">Rude</label>
            <input id="rude" name="Rude" type="number" value="<?php echo $valRude; ?>">
          </div>
        </div>

        <div style="margin-top:12px;">
          <button class="boton" type="submit">Guardar cambios</button>
        </div>
      </form>
    </section>

    <!-- Tarjeta: Clases -->
    <section class="tarjeta">
      <h2>Clases donde participa</h2>
      <div class="caja-tabla">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Clase</th>
              <th>Profesor (dueño)</th>
              <th>Código</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (empty($clases)) {
              echo '<tr><td colspan="4">No participa en ninguna clase.</td></tr>';
            } else {
              foreach ($clases as $c) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($c['id_clase']) . '</td>';
                echo '<td>' . htmlspecialchars($c['nombreClase']) . '</td>';
                echo '<td>' . htmlspecialchars($c['nomProfe']) . '</td>';
                echo '<td>' . htmlspecialchars($c['codigoClase']) . '</td>';
                echo '</tr>';
              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
