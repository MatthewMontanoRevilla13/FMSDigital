<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }
mysqli_set_charset($conexion, "utf8");

$clases = mysqli_query($conexion, "SELECT id_clase, nombreClase, codigoClase FROM clase ORDER BY id_clase DESC");

$clase_seleccionada = isset($_GET['clase']) ? (int)$_GET['clase'] : 0;
$mensajes = null;
if ($clase_seleccionada > 0) {
  $mensajes = mysqli_query($conexion, "SELECT id, contenido, fechaEdi, Cuenta_Usuario, archivo
    FROM comentario
    WHERE Clase_id_clase = $clase_seleccionada
    ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mensajería (Admin)</title>
  <!-- CSS global si lo tienes -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/admin.css">
  <!-- CSS propio y responsivo de esta vista -->
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/Mensajeria.css">
</head>
<body>

<?php include '../header.php'; ?>

<!-- Sub-barra específica de esta página -->
<div class="sub-barra">
  <a href="/FMSDIGITAL/Maquetacion/Administrador/admin.php">← Volver al panel</a>
</div>

<div class="contenedor">
  <div class="tarjeta">
    <form action="" method="get" class="formulario">
      <label for="clase">Seleccionar clase</label>
      <select id="clase" name="clase">
        <option value="0">-- Elegir --</option>
        <?php
        if ($clases) {
          while ($c = mysqli_fetch_assoc($clases)) {
            $sel = ($clase_seleccionada == (int)$c['id_clase']) ? "selected" : "";
            echo "<option value='".(int)$c['id_clase']."' ".$sel.">".htmlspecialchars($c['nombreClase'])." (".htmlspecialchars($c['codigoClase']).")</option>";
          }
        }
        ?>
      </select>
      <button type="submit" class="boton">Ver mensajes</button>
    </form>
  </div>

  <?php if ($clase_seleccionada > 0) { ?>
    <div class="tarjeta">
      <h3>Nuevo mensaje para la clase #<?php echo (int)$clase_seleccionada; ?></h3>
      <form class="formulario" action="MensajeGuardar.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_clase" value="<?php echo (int)$clase_seleccionada; ?>">

        <label for="texto">Texto</label>
        <textarea id="texto" name="texto" rows="4" required></textarea>

        <label for="archivo">Archivo (opcional)</label>
        <input type="file" id="archivo" name="archivo">

        <button type="submit" class="boton">Publicar mensaje</button>
      </form>
    </div>

    <div class="tarjeta">
      <h3>Mensajes existentes</h3>
      <div class="tabla-caja">
        <table class="tabla">
          <thead>
            <tr>
              <th>Texto</th>
              <th>Autor (Usuario)</th>
              <th>Fecha</th>
              <th>Archivo</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($mensajes) {
              while ($m = mysqli_fetch_assoc($mensajes)) {
                $texto   = htmlspecialchars($m['contenido']);
                $autor   = htmlspecialchars($m['Cuenta_Usuario']);
                $fecha   = htmlspecialchars($m['fechaEdi']);
                $archivo = htmlspecialchars($m['archivo'] ?? '');

                echo "<tr>";
                echo "<td>{$texto}</td>";
                echo "<td>{$autor}</td>";
                echo "<td>{$fecha}</td>";
                if ($archivo !== "") {
                  echo "<td><a class='boton boton-pequeno' href='media/clases/{$archivo}' target='_blank'>Ver archivo</a></td>";
                } else {
                  echo "<td>—</td>";
                }
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='4'>No hay mensajes.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php } ?>
</div>
</body>
</html>
