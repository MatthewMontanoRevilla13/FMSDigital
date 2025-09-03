<?php
// SIN RESTRICCIÓN DE ROL

// Conexión PDO
$pdo = new PDO("mysql:host=127.0.0.1;dbname=registrop6;charset=utf8","root","",[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
]);

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
$cursos = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Admin · Cursos</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header>
    <h1>Panel · Cursos</h1>
    <img class="logo-colegio" src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="logo">
  </header>

  <div class="menu-top">
    <a href="cursos.php">Cursos</a>
  </div>

  <main class="grid-container">
    <?php foreach ($cursos as $c): ?>
      <div class="item">
        <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/clases.png" alt="">
        <a href="admin_estudiantes.php?clase_id=<?= urlencode($c['id_clase']) ?>">
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
</body>
</html>
