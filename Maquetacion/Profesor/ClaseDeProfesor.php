<?php
// ===== Sesión =====
session_start();
$nombreProfesor = isset($_SESSION['nom']) ? ($_SESSION['nom']." ".$_SESSION['apes']) : "Profesor/a";
$usuario        = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : null;

// ===== Parámetro de clase =====
$id_clase = isset($_GET['id_clase']) ? intval($_GET['id_clase']) : 0;

// ===== Conexión BD =====
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexión: " . mysqli_connect_error()); }

// ===== Info de la clase =====
$clase = null;
if ($id_clase > 0) {
  $sql = "SELECT id_clase, nombreClase, codigoClase FROM clase WHERE id_clase = ?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $clase = mysqli_fetch_assoc($res);
  mysqli_stmt_close($stmt);
}

// ===== Manejo de formularios (POST) =====
$alerta = null;

// Crear Publicación (comentario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_anuncio') {
  if (!$usuario || $id_clase <= 0) {
    $alerta = ["type" => "error", "msg" => "Sesión o clase no válidas."];
  } else {
    $contenido = trim($_POST['contenido'] ?? "");
    if ($contenido === "" || mb_strlen($contenido) > 500) {
      $alerta = ["type" => "error", "msg" => "El contenido es requerido (máx. 500 caracteres)."];
    } else {
      $sql = "INSERT INTO comentario (contenido, fechaEdi, Clase_id_clase, Cuenta_Usuario)
              VALUES (?, NOW(), ?, ?)";
      $stmt = mysqli_prepare($conexion, $sql);
      mysqli_stmt_bind_param($stmt, "sii", $contenido, $id_clase, $usuario);
      if (mysqli_stmt_execute($stmt)) {
        $alerta = ["type" => "success", "msg" => "Anuncio publicado correctamente."];
      } else {
        $alerta = ["type" => "error", "msg" => "Error al publicar anuncio: ".mysqli_error($conexion)];
      }
      mysqli_stmt_close($stmt);
    }
  }
}

// Crear Tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_tarea') {
  if ($id_clase <= 0) {
    $alerta = ["type" => "error", "msg" => "Clase no válida."];
  } else {
    $titulo      = trim($_POST['titulo'] ?? "");
    $tema        = trim($_POST['tema'] ?? "");
    $descripcion = trim($_POST['descripcion'] ?? "");

    // La tabla tarea tiene longitudes 90 para Titulo/Descripcion/Tema
    if ($titulo === "" || mb_strlen($titulo) > 90) {
      $alerta = ["type" => "error", "msg" => "El título es requerido (máx. 90 caracteres)."];
    } elseif ($descripcion === "" || mb_strlen($descripcion) > 90) {
      $alerta = ["type" => "error", "msg" => "La descripción es requerida (máx. 90 caracteres)."];
    } elseif (mb_strlen($tema) > 90) {
      $alerta = ["type" => "error", "msg" => "El tema no puede superar 90 caracteres."];
    } else {
      $sql = "INSERT INTO tarea (Titulo, Descripcion, Tema, Clase_id_clase)
              VALUES (?, ?, ?, ?)";
      $stmt = mysqli_prepare($conexion, $sql);
      mysqli_stmt_bind_param($stmt, "sssi", $titulo, $descripcion, $tema, $id_clase);
      if (mysqli_stmt_execute($stmt)) {
        $alerta = ["type" => "success", "msg" => "Tarea creada correctamente."];
      } else {
        $alerta = ["type" => "error", "msg" => "Error al crear tarea: ".mysqli_error($conexion)];
      }
      mysqli_stmt_close($stmt);
    }
  }
}

// ===== Listados recientes (para la vista) =====
$anuncios = [];
$tareas   = [];

if ($id_clase > 0) {
  // Últimos 5 anuncios (comentario)
  $sql = "SELECT id, contenido, fechaPub
          FROM comentario
          WHERE Clase_id_clase = ?
          ORDER BY id DESC
          LIMIT 5";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $anuncios[] = $row; }
  mysqli_stmt_close($stmt);

  // Últimas 5 tareas
  $sql = "SELECT id, Titulo, Descripcion, Tema
          FROM tarea
          WHERE Clase_id_clase = ?
          ORDER BY id DESC
          LIMIT 5";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id_clase);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $tareas[] = $row; }
  mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clase del Profesor</title>

  <!-- Librerías (validación opcional si quieres) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

  <!-- Estilos (gama vino/rosado) -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Profesor/ClaseDeProfesor.css"/>
</head>
<body>

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">NOMBRE DEL COLEGIO</div>
      <nav class="topnav">
        <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/PaginaPrincipal.php">Inicio</a>
        <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/Noticias.php">Noticias</a>
        <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/Galeria.php">Galería</a>
        <a href="#">Documentos</a>
        <a href="/FMSDIGITAL/Maquetacion/PaginaWeb/Contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <div class="shell">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <span class="menu-title">Clase</span>
      <nav class="sidenav">
        <a class="active" href="#"><span class="icon"></span> Resumen</a>
        <a href="/FMSDIGITAL/Maquetacion/Profesor/tareas.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Tareas</a>
        <a href="/FMSDIGITAL/Maquetacion/Profesor/materiales.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Materiales</a>
       <a href="/FMSDIGITAL/Maquetacion/Profesor/ListaEstudiantes.php?id_clase=<?php echo $id_clase; ?>">Lista de estudiantes</a>
        <a href="/FMSDIGITAL/Maquetacion/Profesor/calificaciones.php?id_clase=<?php echo $id_clase; ?>"><span class="icon"></span> Calificaciones</a>
        <a href="/FMSDIGITAL/Maquetacion/CuentasDeUsuario/cerrarL.php"><span class="icon"></span> Cerrar Sesión</a>
      </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">
      <!-- HERO -->
      <section class="hero">
        <div>
          <h1><?php echo $clase ? htmlspecialchars($clase['nombreClase']) : "Clase"; ?></h1>
          <p>
            Profesor: <?php echo htmlspecialchars($nombreProfesor); ?>
            <?php if ($clase && !empty($clase['codigoClase'])): ?>
              • Código: <?php echo htmlspecialchars($clase['codigoClase']); ?>
            <?php endif; ?>
          </p>
        </div>
        <div class="hero-ill" aria-hidden="true"></div>
      </section>

      <!-- ALERTAS -->
      <?php if ($alerta): ?>
        <div class="alert <?php echo $alerta['type']==='success'?'alert-success':'alert-error'; ?>">
          <?php echo htmlspecialchars($alerta['msg']); ?>
        </div>
      <?php endif; ?>

      <!-- ACCIONES RÁPIDAS -->
    
      <!-- FORM: PUBLICAR ANUNCIO -->
      <section id="form-anuncio" class="card">
        <h2>Publicar anuncio</h2>
        <form class="form" id="FormAnuncio" method="post" enctype="multipart/form-data">
          <input type="hidden" name="accion" value="crear_anuncio"/>
          <div class="form-group">
            <label>Contenido (máx. 500)</label>
            <textarea name="contenido" placeholder="Escribe el mensaje para tu clase..."></textarea>
          </div>
          <div class="actions">
            <button type="submit" class="btn">Publicar</button>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('FormAnuncio').reset()">Limpiar</button>
          </div>
        </form>
      </section>

      <!-- FORM: CREAR TAREA -->
      <section id="form-tarea" class="card">
        <h2>Crear tarea</h2>
        <form class="form" id="FormTarea" method="post">
          <input type="hidden" name="accion" value="crear_tarea"/>
          <div class="form-grid task-form">
            <div class="form-group">
              <label>Título (máx. 90)</label>
              <input type="text" name="titulo" placeholder="Ej. MATEMÁTICAS - Problemas 1"/>
            </div>
            <div class="form-group">
              <label>Tema (opcional, máx. 90)</label>
              <input type="text" name="tema" placeholder="Unidad 1 / Álgebra"/>
            </div>
          </div>
          <div class="form-group">
            <label>Descripción (máx. 90)</label>
            <input type="text" name="descripcion" placeholder="Ej. Entrega hasta el viernes. Resolver 1-10."/>
          </div>
          <div class="actions">
            <button type="submit" class="btn">Crear tarea</button>
            <a class="btn btn-ghost" href="/FMSDIGITAL/Maquetacion/Profesor/tareas.php?id_clase=<?php echo $id_clase; ?>">Ver todas</a>
          </div>
        </form>
      </section>

      <!-- RECIENTES: ANUNCIOS -->
      <section class="card">
        <h2>Anuncios recientes</h2>
        <?php if (empty($anuncios)): ?>
          <div class="empty">Aún no publicaste anuncios para esta clase.</div>
        <?php else: ?>
          <div class="list">
            <?php foreach ($anuncios as $a): ?>
              <div class="list-item">
                <span><?php echo htmlspecialchars($a['contenido']); ?></span>
                <div class="actions">
                  <!-- Puedes crear AnuncioEditar.php si lo manejas aparte -->
                  <span class="pill">#<?php echo intval($a['id']); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- RECIENTES: TAREAS -->
      <section class="card">
        <h2>Tareas recientes</h2>
        <?php if (empty($tareas)): ?>
          <div class="empty">Aún no creaste tareas para esta clase.</div>
        <?php else: ?>
          <div class="task-list">
            <?php foreach ($tareas as $t): ?>
              <div class="task-item">
                <div class="info">
                  <div class="title"><?php echo htmlspecialchars($t['Titulo']); ?></div>
                  <div class="meta">
                    <?php if (!empty($t['Tema'])): ?><span class="pill"><?php echo htmlspecialchars($t['Tema']); ?></span><?php endif; ?>
                    <span class="pill">#<?php echo intval($t['id']); ?></span>
                  </div>
                  <?php if (!empty($t['Descripcion'])): ?>
                    <p><?php echo htmlspecialchars($t['Descripcion']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="actions">
                  <a class="btn btn-ghost" href="/FMSDIGITAL/Maquetacion/Profesor/EditarTarea.php?id_tarea=<?php echo intval($t['id']); ?>">Editar</a>
                  <a class="btn btn-ghost" href="/FMSDIGITAL/Maquetacion/Profesor/VerEntregas.php?id_tarea=<?php echo intval($t['id']); ?>">Calificar</a>
                  <a class="btn btn-danger" href="/FMSDIGITAL/Maquetacion/Profesor/EliminarTarea.php?id_tarea=<?php echo intval($t['id']); ?>" onclick="return confirm('¿Eliminar esta tarea?')">Eliminar</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- INFO CLASE -->
      <section class="card">
        <h2>Información de la clase</h2>
        <div class="class-info">
          <img src="/FMSDIGITAL/Maquetacion/imagenes/imagen fisica.png" alt="Imagen de la clase"/>
          <div>
            <p><strong>Nombre:</strong> <?php echo $clase ? htmlspecialchars($clase['nombreClase']) : "—"; ?></p>
            <p><strong>Código:</strong> <?php echo $clase ? htmlspecialchars($clase['codigoClase']) : "—"; ?></p>
            <p><strong>Profesor:</strong> <?php echo htmlspecialchars($nombreProfesor); ?></p>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Scroll suave a formularios
    document.querySelectorAll('a[href^="#form-"]').forEach(a => {
      a.addEventListener('click', e => {
        const id = a.getAttribute('href').slice(1);
        const el = document.getElementById(id);
        if (el) {
          e.preventDefault();
          window.scrollTo({ top: el.offsetTop - 80, behavior: 'smooth' });
        }
      });
    });

    // Validación rápida (opcional)
    $(function(){
      $("#FormAnuncio").validate({
        rules:{ contenido:{ required:true, maxlength:500, minlength:3 } },
        messages:{
          contenido:{ required:"Escribe el anuncio", maxlength:"Máx. 500 caracteres", minlength:"Mínimo 3" }
        },
        submitHandler:function(form){ form.submit(); }
      });
      $("#FormTarea").validate({
        rules:{
          titulo:{ required:true, maxlength:90, minlength:3 },
          tema:{ maxlength:90 },
          descripcion:{ required:true, maxlength:90, minlength:3 }
        },
        messages:{
          titulo:{ required:"Ingresa un título", maxlength:"Máx. 90", minlength:"Mínimo 3" },
          tema:{ maxlength:"Máx. 90" },
          descripcion:{ required:"Ingresa la descripción", maxlength:"Máx. 90", minlength:"Mínimo 3" }
        },
        submitHandler:function(form){ form.submit(); }
      });
    });
  </script>
</body>
</html>