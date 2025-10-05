<?php
// --- Solo ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  http_response_code(403);
  echo "Acceso restringido: solo Administrador.";
  exit;
}

// --- Conexión mysqli (tu formato) ---
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}
mysqli_set_charset($conexion, "utf8");

// Cursos + conteo de estudiantes
$sql = "
  SELECT  c.id_clase, c.nombreClase, c.nomProfe, c.codigoClase,
          COUNT(cu.Usuario) AS total_estudiantes
  FROM clase c
  LEFT JOIN cuenta_has_clase chc ON chc.Clase_id_clase = c.id_clase
  LEFT JOIN cuenta cu ON cu.Usuario = chc.Cuenta_Usuario AND cu.Rol = 'Estudiante'
  GROUP BY c.id_clase, c.nombreClase, c.nomProfe, c.codigoClase
  ORDER BY c.id_clase DESC
";
$res = mysqli_query($conexion, $sql);
$cursos = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Admin · Cursos</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin_cursos.css">
</head>
<body>
  <?php include '../header.php'; ?>

  <main class="admin-main">
    <button onclick="window.history.back()" class="btn-volver">⬅ Volver atrás</button>

    <div class="menu-top">
      <a href="cursos.php">Cursos</a>
    </div>

    <main class="grid-container">
      <?php foreach ($cursos as $c): ?>
        <div class="item">
          <img src="/FMSDIGITAL/Maquetacion/imagenes/clases.png" alt="">
          <a href="Estudiantes.php?clase_id=<?= urlencode($c['id_clase']) ?>">
            <?= htmlspecialchars($c['nombreClase']) ?>
          </a>
          <p>Profe: <b><?= htmlspecialchars($c['nomProfe']) ?></b></p>
          <p>Código: <?= htmlspecialchars($c['codigoClase']) ?></p>
          <p>Estudiantes: <?= (int)$c['total_estudiantes'] ?></p>
          <p>
            <a href="Estudiantes.php?clase_id=<?= urlencode($c['id_clase']) ?>">Ver estudiantes</a>
          </p>
        </div>
      <?php endforeach; ?>
    </main>
  </main>
</body>
</html>
