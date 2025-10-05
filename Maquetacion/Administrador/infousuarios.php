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

    // OJO: si quieres máxima seguridad, aplica mysqli_real_escape_string a los textos
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Info de Usuario · <?= $usuario ?></title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css"><!-- se mantiene, pero nuestros estilos usan clases propias -->
  <style>
    /* ================== COLORES (referencia) ==================
       vino: #6b0014, fondo: #fbeaec, borde: #efd0d5, texto: #2e0f13
       ========================================================= */

    /* ======== BASE DE PÁGINA ======== */
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #fbeaec;
      color: #2e0f13;
      font-size: 17px;
      line-height: 1.45;
    }

    /* ======== BARRA BAJA DEL HEADER GLOBAL ======== */
    .barra-regreso {
      background: #fbeaec;
      border-bottom: 1px solid #efd0d5;
      padding: 10px 16px;
    }
    .barra-regreso a { color: #6b0014; font-weight: 700; text-decoration: none; }
    .barra-regreso a:hover { text-decoration: underline; }

    /* ======== ENCABEZADO LOCAL DE ESTA PÁGINA (no usa <header>) ======== */
    .encabezado-pagina {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      background: #ffffff;
      border-bottom: 1px solid #efd0d5;
    }
    .encabezado-pagina h1 {
      margin: 0;
      font-size: 1.6rem;
      color: #6b0014;
    }
    .enlace-simple { color: #6b0014; text-decoration: none; font-weight: 700; }
    .enlace-simple:hover { text-decoration: underline; }

    /* ======== CONTENEDOR GENERAL ======== */
    .contenedor {
      width: 100%;
      max-width: 1080px;
      margin: 18px auto 28px;
      padding: 0 16px;
    }

    /* ======== TARJETA ======== */
    .tarjeta {
      background: #fff;
      border: 1px solid #efd0d5;
      border-radius: 12px;
      padding: 18px;
      margin-bottom: 18px;
      box-shadow: 0 2px 6px rgba(0,0,0,.06);
    }
    .tarjeta h2 {
      margin: 0 0 12px 0;
      color: #6b0014;
      font-size: 1.25rem;
    }

    /* ======== FILAS DE FORMULARIO ======== */
    .fila {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 12px;
    }
    label { font-weight: 700; display: block; margin-bottom: 6px; }
    input, select {
      padding: 12px;
      border: 1px solid #efd0d5;
      border-radius: 10px;
      width: 100%;
      font-size: 1rem;
      background: #fff;
    }
    .caja-boton { margin-top: 14px; }

    /* ======== BOTONES ======== */
    .boton {
      background: #6b0014;
      color: #fff;
      border: 0;
      border-radius: 10px;
      padding: 12px 18px;
      cursor: pointer;
      font-weight: 700;
      font-size: 1rem;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    .boton.alerta { background: #8b0020; }

    /* ======== TABLA ======== */
    .caja-tabla { overflow-x: auto; }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
    }
    th, td {
      border: 1px solid #e9d7da;
      padding: 12px;
      text-align: left;
      font-size: 1rem;
      vertical-align: top;
    }
    thead th { background: #f7eef0; }

    /* =================== MEDIAS RESPONSIVAS =================== */

    /* Tablet: agranda un poco todo y mejora espacios */
    @media (max-width: 900px) {
      body { font-size: 18px; }
      .encabezado-pagina { padding: 18px; }
      .encabezado-pagina h1 { font-size: 1.8rem; }

      .contenedor { max-width: 920px; padding: 0 14px; }
      .tarjeta { padding: 16px; border-radius: 12px; }
      .tarjeta h2 { font-size: 1.3rem; }

      input, select { padding: 14px; font-size: 1.05rem; }
      .boton { padding: 14px 18px; font-size: 1.05rem; }
      th, td { padding: 14px; font-size: 1.05rem; }
    }

    /* Celular: header alto, textos y controles grandes, 100% ancho */
    @media (max-width: 600px) {
      body { font-size: 19px; }

      .encabezado-pagina {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding: 26px 16px;
      }
      .encabezado-pagina h1 { font-size: 2rem; }

      .contenedor {
        max-width: 100%;
        margin: 14px 0 22px;
        padding: 0 12px;
      }

      .tarjeta {
        padding: 20px;
        border-radius: 14px;
      }
      .tarjeta h2 { font-size: 1.4rem; }

      .fila {
        grid-template-columns: 1fr; /* una sola columna */
        gap: 12px;
      }

      label { font-size: 1.1rem; }
      input, select {
        font-size: 1.15rem;
        padding: 16px;
      }

      .boton {
        width: 100%;
        font-size: 1.2rem;
        padding: 16px;
        border-radius: 12px;
      }

      th, td {
        font-size: 1.15rem;
        padding: 16px;
      }
    }

    /* Móviles muy pequeños */
    @media (max-width: 380px) {
      body { font-size: 20px; }
      .encabezado-pagina { padding: 30px 14px; }
      .encabezado-pagina h1 { font-size: 2.2rem; }
      .boton { font-size: 1.25rem; padding: 18px; }
    }
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <div class="barra-regreso">
    <a href="usuarios.php">← Volver a Usuarios</a>
  </div>

  <div class="encabezado-pagina">
    <h1>Usuario: <?= htmlspecialchars($usuario) ?></h1>
    <div><a class="enlace-simple" href="usuarios.php">Lista de usuarios</a></div>
  </div>

  <main class="contenedor">
    <!-- Cuenta -->
    <section class="tarjeta">
      <h2>Cuenta</h2>
      <p style="margin:8px 0 16px">
        <strong>Rol actual:</strong> <?= htmlspecialchars($cuenta['Rol']) ?> ·
        <strong>Bloqueado:</strong> <?= ($cuenta['Bloqueado']==='1') ? 'Sí' : 'No' ?>
      </p>

      <form method="post" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:10px">
        <input type="hidden" name="accion" value="cambiar_rol">
        <label for="nuevoRol">Cambiar rol</label>
        <select id="nuevoRol" name="nuevoRol">
          <?php
            $roles = ['Administrador','Profesor','Estudiante'];
            foreach ($roles as $r) {
              $sel = ($cuenta['Rol'] === $r) ? 'selected' : '';
              echo "<option $sel value=\"$r\">$r</option>";
            }
          ?>
        </select>
        <button class="boton" type="submit">Guardar rol</button>
      </form>

      <form method="post">
        <input type="hidden" name="accion" value="toggle_bloqueo">
        <button class="boton alerta" type="submit"><?= ($cuenta['Bloqueado']==='1') ? 'Desbloquear' : 'Bloquear' ?></button>
      </form>
    </section>

    <!-- Datos personales -->
    <section class="tarjeta">
      <h2>Datos personales</h2>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_info">
        <div class="fila">
          <div>
            <label for="ci">CI</label>
            <input id="ci" name="CI" type="number" value="<?= htmlspecialchars($info['CI'] ?? '') ?>">
          </div>
          <div>
            <label for="nombres">Nombres</label>
            <input id="nombres" name="Nombres" value="<?= htmlspecialchars($info['Nombres'] ?? '') ?>">
          </div>
          <div>
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="Apellidos" value="<?= htmlspecialchars($info['Apellidos'] ?? '') ?>">
          </div>
          <div>
            <label for="direccion">Dirección</label>
            <input id="direccion" name="Direccion" value="<?= htmlspecialchars($info['Direccion'] ?? '') ?>">
          </div>
          <div>
            <label for="nacimiento">Fecha de nacimiento</label>
            <input id="nacimiento" name="Nacimiento" type="date" value="<?= htmlspecialchars($info['Nacimiento'] ?? '') ?>">
          </div>
          <div>
            <label for="telefono">Teléfono</label>
            <input id="telefono" name="Telefono" value="<?= htmlspecialchars($info['Telefono'] ?? '') ?>">
          </div>
          <div>
            <label for="curso">Curso</label>
            <input id="curso" name="Curso" value="<?= htmlspecialchars($info['Curso'] ?? '') ?>">
          </div>
          <div>
            <label for="rude">Rude</label>
            <input id="rude" name="Rude" type="number" value="<?= htmlspecialchars($info['Rude'] ?? '') ?>">
          </div>
        </div>
        <div class="caja-boton">
          <button class="boton" type="submit">Guardar cambios</button>
        </div>
      </form>
    </section>

    <!-- Clases -->
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
            <?php if (!$clases): ?>
              <tr><td colspan="4">No participa en ninguna clase.</td></tr>
            <?php else: foreach ($clases as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['id_clase']) ?></td>
                <td><?= htmlspecialchars($c['nombreClase']) ?></td>
                <td><?= htmlspecialchars($c['nomProfe']) ?></td>
                <td><?= htmlspecialchars($c['codigoClase']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
