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

// --- Búsqueda opcional ---
$term = trim($_GET['q'] ?? "");

// --- Subconjunto de usuarios que participan en clases (profes y/o estudiantes) ---
/*
  Conjunto U:
    - Usuarios de cuenta_has_clase (miembros de clase)
    - Usuarios propietarios de clase (profes dueños)
*/
$sqlUsuariosBase = "
  SELECT DISTINCT u.Usuario
  FROM (
     SELECT Cuenta_Usuario AS Usuario FROM cuenta_has_clase
     UNION
     SELECT Cuenta_Usuario AS Usuario FROM clase
  ) AS u
";

// Aplica filtro por término (en nombre/apellido/usuario)
if ($term !== "") {
  // Hacemos join con cuenta/informacion para filtrar
  $sqlUsuariosBase = "
    SELECT DISTINCT u.Usuario
    FROM (
       SELECT Cuenta_Usuario AS Usuario FROM cuenta_has_clase
       UNION
       SELECT Cuenta_Usuario AS Usuario FROM clase
    ) AS u
    LEFT JOIN informacion i ON i.Cuenta_Usuario = u.Usuario
    LEFT JOIN cuenta c ON c.Usuario = u.Usuario
    WHERE
      CAST(u.Usuario AS CHAR) LIKE CONCAT('%', ?, '%')
      OR i.Nombres LIKE CONCAT('%', ?, '%')
      OR i.Apellidos LIKE CONCAT('%', ?, '%')
  ";
  $stBase = mysqli_prepare($conexion, $sqlUsuariosBase);
  mysqli_stmt_bind_param($stBase, "sss", $term, $term, $term);
} else {
  $stBase = mysqli_prepare($conexion, $sqlUsuariosBase);
}

mysqli_stmt_execute($stBase);
$resBase = mysqli_stmt_get_result($stBase);
$usuarios = [];
while ($row = mysqli_fetch_assoc($resBase)) {
  $usuarios[] = $row['Usuario'];
}
mysqli_stmt_close($stBase);

// Si no hay usuarios vinculados, mostramos vacío
$rows = [];
if ($usuarios) {
  // Preparamos un listado detallado con cuenta + informacion + clases (concat)
  // Lo haremos user-by-user para mantenerlo simple y robusto.
  $qCuenta = mysqli_prepare($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=?");
  $qInfo   = mysqli_prepare($conexion, "SELECT Nombres, Apellidos, Curso, Telefono FROM informacion WHERE Cuenta_Usuario=?");
  $qClases = mysqli_prepare($conexion,
    "SELECT GROUP_CONCAT(DISTINCT c.nombreClase ORDER BY c.nombreClase SEPARATOR ', ')
     FROM (
        SELECT Clase_id_clase AS id_clase FROM cuenta_has_clase WHERE Cuenta_Usuario = ?
        UNION
        SELECT id_clase FROM clase WHERE Cuenta_Usuario = ?
     ) t
     JOIN clase c ON c.id_clase = t.id_clase"
  );

  foreach ($usuarios as $u) {
    // cuenta
    mysqli_stmt_bind_param($qCuenta, "i", $u);
    mysqli_stmt_execute($qCuenta);
    $rC = mysqli_stmt_get_result($qCuenta);
    $cuenta = mysqli_fetch_assoc($rC) ?: ["Usuario"=>$u, "Rol"=>null, "Bloqueado"=>null];

    // informacion
    mysqli_stmt_bind_param($qInfo, "i", $u);
    mysqli_stmt_execute($qInfo);
    $rI = mysqli_stmt_get_result($qInfo);
    $info = mysqli_fetch_assoc($rI) ?: ["Nombres"=>"", "Apellidos"=>"", "Curso"=>"", "Telefono"=>""];

    // clases
    mysqli_stmt_bind_param($qClases, "ii", $u, $u);
    mysqli_stmt_execute($qClases);
    $rCL = mysqli_stmt_get_result($qClases);
    $clases = mysqli_fetch_row($rCL);
    $clasesStr = $clases && $clases[0] ? $clases[0] : "—";

    $rows[] = [
      "Usuario"   => $cuenta["Usuario"],
      "Rol"       => $cuenta["Rol"] ?: "—",
      "Bloqueado" => ($cuenta["Bloqueado"] === '1') ? 'Sí' : 'No',
      "Nombre"    => trim(($info["Apellidos"] ?? "")." ".($info["Nombres"] ?? "")) ?: "—",
      "Curso"     => $info["Curso"] ?: "—",
      "Telefono"  => $info["Telefono"] ?: "—",
      "Clases"    => $clasesStr,
    ];
  }

  mysqli_stmt_close($qCuenta);
  mysqli_stmt_close($qInfo);
  mysqli_stmt_close($qClases);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Usuarios · Admin</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">

  <style>
    .menu-top {
        position: relative; 
        left: 25cm;
    }
    .card{
        background:#fff;
        padding:18px;
        border-radius:10px;
        box-shadow:0 2px 6px rgba(0,0,0,.1);
        margin:20px auto;
        max-width:1100px
    }
    table{
        width:100%;
        border-collapse:collapse
    }
    th,td{
        border:1px solid #e5e5e5;
        padding:10px;
        text-align:left
    }
    th{
        background:#f8f8f8
    }
    .search{
        margin: 0 auto 10px; 
        display:flex;
        gap:8px;
        max-width:1100px}
    .btn{
        background:#6b0014;
        color:#fff;
        border:0;
        border-radius:8px;
        padding:8px 14px;
        cursor:pointer;
        text-decoration:none;
        display:inline-block}
    .input{
        flex:1;
        padding:8px;
        border:1px solid #ddd;
        border-radius:6px}
  </style>
</head>
<body>
  <?php include '../header.php'; ?>

  <header>
    <div class="menu-top">
      <a href="cursos.php">Cursos</a>
      <a href="usuarios.php" class="active">Usuarios</a>
    </div>
  </header>

  <form class="search" method="get">
    <input class="input" type="text" name="q" placeholder="Buscar por usuario, nombre o apellido" value="<?= htmlspecialchars($term) ?>">
    <button class="btn" type="submit">Buscar</button>
  </form>

  <section class="card">
    <table>
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Nombre</th>
          <th>Rol</th>
          <th>Bloqueado</th>
          <th>Curso</th>
          <th>Teléfono</th>
          <th>Clases</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="8">No hay usuarios vinculados a clases.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r["Usuario"]) ?></td>
              <td><?= htmlspecialchars($r["Nombre"]) ?></td>
              <td><?= htmlspecialchars($r["Rol"]) ?></td>
              <td><?= htmlspecialchars($r["Bloqueado"]) ?></td>
              <td><?= htmlspecialchars($r["Curso"]) ?></td>
              <td><?= htmlspecialchars($r["Telefono"]) ?></td>
              <td><?= htmlspecialchars($r["Clases"]) ?></td>
              <td>
                <a class="btn" href="infousuarios.php?usuario=<?= urlencode($r['Usuario']) ?>">Ver / Editar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</body>
</html>