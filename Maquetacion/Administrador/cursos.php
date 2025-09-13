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

// Cursos + conteo de estudiantes (misma consulta que usabas con PDO)
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
</head>
<body>
  <style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: #fff7f8; /* rosita claro */
  color: #2b2b2b;
}

header {
  background: #6b0014;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
}

header h1 {
  margin: 0;
  font-size: 1.4em;
}

.logo-colegio {
  height: 40px;
}

/* Menú superior */
.menu-top {
  text-align: center;
  padding: 10px;
  background: #fdf9f9;
}
.menu-top a {
  text-decoration: none;
  color: #6b0014;
  font-weight: bold;
  margin: 0 10px;
}

/* ===== GRID DE CURSOS ===== */
.grid-container {
  display: grid;
  gap: 20px;
  max-width: 1100px;
  margin: 20px auto;
  padding: 0 15px;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.item {
  background: #fff;
  border: 1px solid #ffdde0;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  padding: 15px;
  text-align: center;
}

.item img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 10px;
}

.item a {
  display: block;
  font-weight: bold;
  font-size: 1.1em;
  color: #6b0014;
  margin: 8px 0;
}

.item p {
  margin: 4px 0;
  font-size: 0.95em;
}

/* Botón "Ver estudiantes" */
.item p:last-of-type a {
  display: inline-block;
  background: #a30c2c;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
}
.item p:last-of-type a:hover {
  background: #7a0820;
}

/* ===== RESPONSIVE ===== */

/* Tablet: máximo 2 columnas */
@media (max-width: 900px) {
  .grid-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Celular: 1 curso debajo del otro (legible) */
@media (max-width: 600px) {
  .grid-container {
    grid-template-columns: 1fr;
  }
  header {
    flex-direction: column;
    text-align: center;
  }
  header h1 { font-size: 1.2em; }
  .item a { font-size: 1em; }
}
  </style>
  <header>
    <h1>Panel · Cursos</h1>
    <img class="logo-colegio" src="/FMSDIGITAL/Maquetacion/imagenes/logo.png" alt="logo">
  </header>

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
</body>
</html>