<?php
// --- SOLO ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// --- Conexión mysqli (simple) ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { echo "Error en la conexion".mysqli_error($conexion); die(); }
mysqli_set_charset($conexion, "utf8");

// --- Búsqueda opcional ---
$term = isset($_GET['q']) ? trim($_GET['q']) : "";

// --- Subconjunto de usuarios que participan en clases (miembros o dueños) ---
if ($term !== "") {
  // Con filtro por usuario/nombres/apellidos (simple, sin prepare)
  $sqlUsuariosBase = "
    SELECT DISTINCT u.Usuario
    FROM (
      SELECT Cuenta_Usuario AS Usuario FROM cuenta_has_clase
      UNION
      SELECT Cuenta_Usuario AS Usuario FROM clase
    ) AS u
    LEFT JOIN informacion i ON i.Cuenta_Usuario = u.Usuario
    LEFT JOIN cuenta c ON c.Usuario = u.Usuario
    WHERE CAST(u.Usuario AS CHAR) LIKE '%$term%'
       OR i.Nombres  LIKE '%$term%'
       OR i.Apellidos LIKE '%$term%'
  ";
} else {
  $sqlUsuariosBase = "
    SELECT DISTINCT u.Usuario
    FROM (
      SELECT Cuenta_Usuario AS Usuario FROM cuenta_has_clase
      UNION
      SELECT Cuenta_Usuario AS Usuario FROM clase
    ) AS u
  ";
}

$resBase = mysqli_query($conexion, $sqlUsuariosBase);
$usuarios = [];
if ($resBase) {
  while ($row = mysqli_fetch_assoc($resBase)) {
    $usuarios[] = $row['Usuario'];
  }
}

// Si no hay usuarios vinculados, mostramos vacío
$rows = [];
if ($usuarios) {
  foreach ($usuarios as $u) {
    $u = intval($u);

    // cuenta
    $rC = mysqli_query($conexion, "SELECT Usuario, Rol, Bloqueado FROM cuenta WHERE Usuario=$u");
    $cuenta = ($rC && mysqli_num_rows($rC)) ? mysqli_fetch_assoc($rC) : ["Usuario"=>$u, "Rol"=>null, "Bloqueado"=>null];

    // informacion
    $rI = mysqli_query($conexion, "SELECT Nombres, Apellidos, Curso, Telefono FROM informacion WHERE Cuenta_Usuario=$u");
    $info = ($rI && mysqli_num_rows($rI)) ? mysqli_fetch_assoc($rI) : ["Nombres"=>"", "Apellidos"=>"", "Curso"=>"", "Telefono"=>""];

    // clases (concat nombres)
    $sqlCl = "
      SELECT GROUP_CONCAT(DISTINCT c.nombreClase ORDER BY c.nombreClase SEPARATOR ', ') AS lista
      FROM (
        SELECT Clase_id_clase AS id_clase FROM cuenta_has_clase WHERE Cuenta_Usuario = $u
        UNION
        SELECT id_clase FROM clase WHERE Cuenta_Usuario = $u
      ) t
      JOIN clase c ON c.id_clase = t.id_clase
    ";
    $rCL = mysqli_query($conexion, $sqlCl);
    $clasesStr = "—";
    if ($rCL && mysqli_num_rows($rCL)) {
      $rowCL = mysqli_fetch_assoc($rCL);
      if ($rowCL && $rowCL['lista']) { $clasesStr = $rowCL['lista']; }
    }

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
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Usuarios · Admin</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">

  <style>
    /* ====== ESTILOS BASE ====== */
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff7f8;
      color: #2b2b2b;
      font-size: 18px; /* base más grande */
    }

    /* HEADER */
    header {
      background-color: #6b0014;
      color: white;
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    header h1 {
      margin: 0;
      font-size: 2em;
      letter-spacing: 1px;
    }

    /* MENÚ SUPERIOR */
    .menu-top {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      padding: 8px 0;
    }

    .menu-top a {
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1em;
      padding: 8px 12px;
      transition: color 0.3s ease;
    }

    .menu-top a:hover,
    .menu-top a.active {
      text-decoration: underline;
    }

    /* TARJETAS / CONTENEDOR */
    .card {
      margin: 20px;
      padding: 20px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,.15);
      max-width: 100%;
      overflow-x: auto;
      font-size: 1.05em;
    }

    /* TABLA */
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }

    th, td {
      padding: 14px 18px;
      border-bottom: 1px solid #ddd;
      text-align: left;
      font-size: 1.05em;
    }

    th {
      background-color: #f4e1e4;
      color: #6b0014;
      font-weight: bold;
    }

    /* BOTONES */
    .btn {
      display: inline-block;
      font-size: 1em;
      font-weight: bold;
      padding: 12px 20px;
      background-color: #6b0014;
      color: white;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .btn:hover { background-color: #990033; }

    /* INPUTS */
    .input {
      font-size: 1em;
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    /* SECCIÓN DE BÚSQUEDA */
    .search {
      position: relative;
      right: 7cm;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin: 20px;
    }

    /* ====== MEDIA QUERIES ====== */
    @media (max-width: 1400px) {
      .menu-top { justify-content: center; gap: 16px; }
    }

    @media (max-width: 992px) {
      header { flex-direction: column; text-align: center; }
      .card { margin: 16px; max-width: 100%; }
      .search { padding: 0 16px; max-width: 100%; }
      th, td { padding: 10px 14px; }
    }

    @media (max-width: 768px) {
      .search { flex-direction: column; align-items: stretch; gap: 8px; }
      .input { width: 100%; }
      .btn { width: 100%; text-align: center; }
      .card { overflow-x: auto; -webkit-overflow-scrolling: touch; }
      table { min-width: 720px; }
      .menu-top { justify-content: center; gap: 12px; padding: 6px 0; }
    }

    @media (max-width: 576px) {
      th, td { font-size: 15px; }
      .btn { padding: 12px; font-size: 0.95em; }
    }

    @media (max-width: 400px) {
      table { min-width: 600px; }
    }
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
