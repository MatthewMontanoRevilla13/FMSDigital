<?php
// --- Solo ADMIN ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
  http_response_code(403);
  echo "Acceso denegado.";
  exit;
}

/* === CONEXIÓN MYSQLI === */
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
  echo "Error en la conexion" . mysqli_error($conexion);
  die();
}
mysqli_set_charset($conexion, "utf8");

/* === CONSULTA DE CLASES === */
$clases = array();
$sql = "SELECT id_clase, nombreClase, nomProfe, codigoClase FROM clase ORDER BY nombreClase ASC";
$res = mysqli_query($conexion, $sql);
if ($res) {
  while ($row = mysqli_fetch_assoc($res)) {
    $clases[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tareas y Entregas · Clases</title>

  <style>
    /* Estilos básicos de la página */
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fdf9f9; /* Fondo general muy claro */
      color: #2e0f13;           /* Texto en tono vino oscuro */
    }

    /* Barra superior del módulo */
    .topbar {
      background-color: #6b0014; /* Vino principal */
      color: #ffffff;            /* Texto blanco */
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
    }

    .topbar__title {
      margin: 0;
      font-size: 20px;
      font-weight: bold;
    }

    /* Contenedor general para centrar contenido */
    .page-wrapper {
      max-width: 1100px;  /* Ancho máximo */
      margin: 24px auto;  /* Centrado horizontal y espacio arriba/abajo */
      padding: 0 16px;    /* Espaciado interno a los lados */
      box-sizing: border-box;
    }

    /* Título de la sección */
    .page-title {
      text-align: center;
      padding: 14px;
      background: #6b0014;
      color: #ffffff;
      border-radius: 8px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    /* Rejilla de tarjetas de clases */
    .class-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr); /* 3 columnas iguales */
      gap: 18px; /* Espacio entre tarjetas */
      margin-top: 18px;
    }

    /* Tarjeta individual de clase */
    .class-card {
      background: #fff5f7;               /* Fondo rosadito */
      border: 1px solid #ffdde0;         /* Borde rosado claro */
      border-radius: 10px;
      padding: 16px;
      box-shadow: 0 4px 8px rgba(107,0,20,0.1); /* Sombra ligera */
    }

    /* Título dentro de la tarjeta */
    .class-card__title {
      margin: 0 0 6px 0;
      color: #6b0014;
      font-size: 1.05rem;
      font-weight: bold;
    }

    /* Datos adicionales (profesor, código) */
    .class-card__meta {
      font-size: 0.95rem;
      margin: 4px 0;
    }

    /* Botón para ver tareas */
    .btn {
      display: inline-block;
      margin-top: 10px;
      background: #a30c2c;    /* Rojo oscuro */
      color: #ffffff;
      text-decoration: none;
      padding: 10px 12px;
      border-radius: 8px;
      font-weight: bold;
    }

    .btn:hover {
      background: #7a0820; /* Más oscuro al pasar el mouse */
    }

    /*Estilos responsive (adaptable)*/
    @media (max-width: 1024px) {
      .class-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 columnas en pantallas medianas */
      }
    }

    @media (max-width: 768px) {
      .topbar {
        flex-direction: column;  /* Barra en columna */
        text-align: center;
        gap: 8px;
      }
    }

    @media (max-width: 600px) {
      .class-grid {
        grid-template-columns: 1fr; /* Solo 1 tarjeta por fila en móvil */
      }
    }
  </style>
</head>
<body>

  <?php include '../header.php'; ?>



  <!-- Contenido principal -->
  <div class="page-wrapper">
    <div class="page-title">Tareas y Entregas · Clases</div>

    <!-- Rejilla de clases -->
    <div class="class-grid">
      <?php if (empty($clases)): ?>
        <!-- Si no hay clases -->
        <div class="class-card">
          <p>No hay clases registradas.</p>
        </div>
      <?php else: ?>
        <?php foreach ($clases as $c): ?>
          <?php
            // Preparar valores sin operadores raros
            $nombreClase = "Clase sin nombre";
            if (isset($c['nombreClase']) && $c['nombreClase'] !== '') {
              $nombreClase = $c['nombreClase'];
            }

            $nombreProfe = "—";
            if (isset($c['nomProfe']) && $c['nomProfe'] !== '') {
              $nombreProfe = $c['nomProfe'];
            }

            $codigoClase = "—";
            if (isset($c['codigoClase']) && $c['codigoClase'] !== '') {
              $codigoClase = $c['codigoClase'];
            }

            $idClase = 0;
            if (isset($c['id_clase'])) {
              $idClase = (int)$c['id_clase'];
            }
          ?>
          <!-- Tarjeta de una clase -->
          <div class="class-card">
            <h3 class="class-card__title"><?php echo htmlspecialchars($nombreClase); ?></h3>
            <div class="class-card__meta"><b>Profesor:</b> <?php echo htmlspecialchars($nombreProfe); ?></div>
            <div class="class-card__meta"><b>Código:</b> <?php echo htmlspecialchars($codigoClase); ?></div>
            <a class="btn" href="TareasClase.php?clase=<?php echo $idClase; ?>">Ver tareas</a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
