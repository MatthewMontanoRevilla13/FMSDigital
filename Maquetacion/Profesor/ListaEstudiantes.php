<?php
session_start();
$nombreProfesor = isset($_SESSION['nom']) ? ($_SESSION['nom']." ".$_SESSION['apes']) : "Profesor/a";

$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;
$q        = isset($_GET['q']) ? trim($_GET['q']) : "";

// Conexión
$cn = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$cn) { die("Error en la conexión: " . mysqli_connect_error()); }

// Info de la clase (para encabezado)
$clase = null;
if ($id_clase > 0) {
  $sql = "SELECT id_clase, nombreClase, codigoClase FROM clase WHERE id_clase = ?";
  $st  = mysqli_prepare($cn, $sql);
  mysqli_stmt_bind_param($st, "i", $id_clase);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  $clase = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);
}

// Listado de estudiantes inscritos en la clase
$estudiantes = [];
if ($id_clase > 0) {
  $like = "%".$q."%";
  $sql = "
    SELECT i.CI, i.Nombres, i.Apellidos, i.Curso, i.Telefono, c.Usuario
    FROM cuenta_has_clase chc
    INNER JOIN cuenta c      ON c.Usuario = chc.Cuenta_Usuario
    INNER JOIN informacion i ON i.Cuenta_Usuario = c.Usuario
    WHERE chc.Clase_id_clase = ?
      AND (c.Rol = 'Estudiante' OR c.Rol IS NULL)
      AND (? = '' OR i.Nombres LIKE ? OR i.Apellidos LIKE ?)
    ORDER BY i.Apellidos ASC, i.Nombres ASC
  ";
  $st = mysqli_prepare($cn, $sql);
  mysqli_stmt_bind_param($st, "isss", $id_clase, $q, $like, $like);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);
  while ($row = mysqli_fetch_assoc($rs)) { $estudiantes[] = $row; }
  mysqli_stmt_close($st);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Estudiantes</title>
  <!-- Reutilizamos estilos base -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Profesor/ClaseDeProfesor.css">
  <style>
    /* ====== Layout de 1 columna para esta vista ====== */
    .shell.single { grid-template-columns: 1fr; max-width: 1200px; margin: 18px auto; padding: 0 16px; }
    .shell.single .sidebar { display: none; }
    .shell.single .content { grid-column: 1; }
    .shell.single .hero { grid-template-columns: 1.2fr 0.8fr; }
    @media (max-width: 1024px){ .shell.single .hero { grid-template-columns: 1fr; } }

    /* Barra superior secundaria */
    .topbar .topnav a.btn-link { padding: 6px 10px; background: rgba(255,255,255,.15); border-radius: 8px; text-decoration: none; }
    .topbar .topnav a.btn-link:hover { background: rgba(255,255,255,.25); }

    /* Cabecera del listado y buscador */
    .header-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
    .muted { color:#666; font-size:.95rem; }
    .search { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .search input[type="text"]{ padding: 10px 12px; border: 1px solid #efd0d5; border-radius: 10px; min-width: 260px; }

    /* Tabla responsive */
    .table-wrap { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 10px 12px; border-bottom: 1px solid #f3d7dc; text-align: left; font-size: .95rem; }
    .table th { background: #fbeaec; color: #6b0014; font-weight: 800; }
    .table tr:hover td { background: #fff7f8; }
  </style>
</head>
<body>

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">NOMBRE DEL COLEGIO</div>
      <nav class="topnav">
        <a class="btn-link" href="/FMSDIGITAL/Maquetacion/Profesor/ClaseDeProfesor.php?id_clase=<?php echo $id_clase; ?>">Volver a la Clase</a>
      </nav>
    </div>
  </header>

  <!-- Contenedor a UNA sola columna -->
  <div class="shell single">
    <main class="content">
      <!-- Hero -->
      <section class="hero">
        <div>
          <h1>Estudiantes de la clase</h1>
          <p>
            Profesor: <?php echo htmlspecialchars($nombreProfesor); ?>
            <?php if ($clase): ?>
              • <?php echo htmlspecialchars($clase['nombreClase']); ?>
              (Código: <?php echo htmlspecialchars($clase['codigoClase']); ?>)
            <?php endif; ?>
          </p>
        </div>
        <div class="hero-ill" aria-hidden="true"></div>
      </section>

      <!-- Listado -->
      <section class="card">
        <div class="header-bar">
          <div>
            <h2>Listado</h2>
            <div class="muted">
              <?php echo count($estudiantes); ?> estudiante(s) encontrado(s)
              <?php if ($q !== ""): ?> • filtro: “<?php echo htmlspecialchars($q); ?>”<?php endif; ?>
            </div>
          </div>
          <form class="search" method="get" action="">
            <input type="hidden" name="id_clase" value="<?php echo $id_clase; ?>">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar por nombre o apellido">
            <button class="btn" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="?id_clase=<?php echo $id_clase; ?>">Limpiar</a>
          </form>
        </div>

        <?php if ($id_clase <= 0): ?>
          <div class="empty">Falta el parámetro <strong>id_clase</strong>.</div>
        <?php elseif (empty($estudiantes)): ?>
          <div class="empty">No hay estudiantes registrados en esta clase.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>CI</th>
                  <th>Nombres</th>
                  <th>Apellidos</th>
                  <th>Curso</th>
                  <th>Teléfono</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($estudiantes as $e): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($e['Usuario']); ?></td>
                    <td><?php echo htmlspecialchars($e['CI']); ?></td>
                    <td><?php echo htmlspecialchars($e['Nombres']); ?></td>
                    <td><?php echo htmlspecialchars($e['Apellidos']); ?></td>
                    <td><?php echo htmlspecialchars($e['Curso']); ?></td>
                    <td><?php echo htmlspecialchars($e['Telefono']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <div class="actions" style="margin-top:10px;">
        <a class="btn" href="/FMSDIGITAL/Maquetacion/Profesor/ClaseDeProfesor.php?id_clase=<?php echo $id_clase; ?>">← Volver a la clase</a>
      </div>
    </main>
  </div>
</body>
</html>
