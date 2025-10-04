<?php
// ==== SOLO PROFESOR ====
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  http_response_code(403);
  echo "Acceso denegado.";
  exit;
}
$profesor = isset($_SESSION['usu']) ? (int)$_SESSION['usu'] : 0;
if ($profesor <= 0) { die("Sesión inválida"); }

// ==== CONEXIÓN ====
$cn = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$cn) { die("Error en la conexión"); }
mysqli_set_charset($cn, "utf8");

// ==== CURSO SELECCIONADO ====
$idClase = isset($_GET['clase']) ? (int)$_GET['clase'] : 0;

// ==== MIS CURSOS ====
$qCursos = "SELECT id_clase, nombreClase, codigoClase
            FROM clase
            WHERE Cuenta_Usuario = $profesor
            ORDER BY nombreClase ASC";
$cursos = mysqli_query($cn, $qCursos);

// ==== ESTUDIANTES DEL CURSO ====
$estudiantes = [];
$nombreClase = "";
if ($idClase > 0) {
  // Asegurar que la clase sea del profesor
  $rc = mysqli_query($cn, "SELECT nombreClase FROM clase WHERE id_clase = $idClase AND Cuenta_Usuario = $profesor");
  if ($rc && $row = mysqli_fetch_assoc($rc)) {
    $nombreClase = $row['nombreClase'];

    $sqlEst = "SELECT ch.Cuenta_Usuario AS usuario,
                      COALESCE(CONCAT(i.Nombres,' ',i.Apellidos), CAST(c.Usuario AS CHAR)) AS nombre
               FROM cuenta_has_clase ch
               INNER JOIN cuenta c ON c.Usuario = ch.Cuenta_Usuario
               LEFT JOIN informacion i ON i.Cuenta_Usuario = c.Usuario
               WHERE ch.Clase_id_clase = $idClase
               ORDER BY nombre ASC";
    $re = mysqli_query($cn, $sqlEst);
    while ($re && $row = mysqli_fetch_assoc($re)) { $estudiantes[] = $row; }
  } else {
    $idClase = 0; // no es su clase
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Calificaciones (Profesor)</title>
  <style>
    body{font-family:Segoe UI,Arial,sans-serif;margin:0;background:#f7f7fb}
    header{background:#6b0014;color:#fff;padding:14px 18px}
    .wrap{max-width:1100px;margin:18px auto;padding:0 16px;display:grid;gap:12px;grid-template-columns:1fr 2fr}
    .card{background:#fff;border:1px solid #eee;border-radius:12px;padding:14px}
    .titulo{margin:0 0 10px 0}
    ul{list-style:none;margin:0;padding:0}
    li{display:flex;justify-content:space-between;align-items:center;padding:10px 8px;border-bottom:1px solid #eee}
    li:last-child{border-bottom:none}
    a{color:#6b0014;text-decoration:none}
    .btn{border:1px solid #6b0014;border-radius:8px;padding:6px 10px}
    @media(max-width:900px){ .wrap{grid-template-columns:1fr} }
  </style>
</head>
<body>
<?php include '../header.php'; ?>

<div class="wrap">
  <section class="card">
    <h2 class="titulo">Mis cursos</h2>
    <ul>
      <?php while ($cursos && $c = mysqli_fetch_assoc($cursos)): ?>
        <li>
          <span><strong><?= $c['nombreClase'] ?></strong> <small style="color:#666">· <?= $c['codigoClase'] ?></small></span>
          <a class="btn" href="?clase=<?= (int)$c['id_clase'] ?>">Ver estudiantes</a>
        </li>
      <?php endwhile; ?>
    </ul>
  </section>

  <section class="card">
    <h2 class="titulo">Estudiantes <?= $idClase? "· ".$nombreClase : "" ?></h2>
    <?php if (!$idClase): ?>
      <p>Elige un curso.</p>
    <?php elseif (empty($estudiantes)): ?>
      <p>No hay estudiantes en este curso.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($estudiantes as $e): ?>
          <li>
            <span><?= $e['nombre'] ?></span>
            <a class="btn" href="ProfesorCalificacionEstudiante.php?usuario=<?= urlencode($e['usuario']) ?>&clase=<?= (int)$idClase ?>">Ver calificaciones</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
</body>
</html>
<?php mysqli_close($cn); ?>
