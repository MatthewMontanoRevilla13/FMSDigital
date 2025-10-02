<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error en la conexion"); }

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
  <title>Mensajería (Admin)</title>
  <link rel="stylesheet" href="/FMSDIGITAL/Maquetacion/Administrador/Mensajeria.css">
  <style>
    /* Estilos mínimos solo para la sub-barra bajo el header global */
    .sub-barra {
      background: #fbeaec;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      border-bottom: 1px solid #efd0d5;
    }
    .sub-barra span { font-weight: bold; color: #6b0014; }
    .sub-barra a { text-decoration: none; color: #6b0014; font-weight: bold; }
    .sub-barra a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<?php include '../header.php'; ?>

<!-- Sub-barra específica de esta página (NO se modifica header.php) -->
<div class="sub-barra">
  <a href="/FMSDIGITAL/Maquetacion/Administrador/admin.php">← Volver al panel</a>
</div>

<div class="contenedor">
  <div class="tarjeta">
    <form action="" method="get" class="form">
      <label for="clase">Seleccionar clase</label>
      <select id="clase" name="clase">
        <option value="0">-- Elegir --</option>
        <?php
        while ($c = mysqli_fetch_assoc($clases)) {
          $sel = ($clase_seleccionada == $c['id_clase']) ? "selected" : "";
          echo "<option value='".$c['id_clase']."' ".$sel.">".$c['nombreClase']." (".$c['codigoClase'].")</option>";
        }
        ?>
      </select>
      <input type="submit" value="Ver mensajes">
    </form>
  </div>

  <?php if ($clase_seleccionada > 0) { ?>
    <div class="tarjeta">
      <h3>Nuevo mensaje para la clase #<?php echo $clase_seleccionada; ?></h3>
      <form class="form" action="MensajeGuardar.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_clase" value="<?php echo $clase_seleccionada; ?>">
        <label for="texto">Texto</label>
        <textarea id="texto" name="texto" rows="3" required></textarea>

        <label for="archivo">Archivo (opcional)</label>
        <input type="file" id="archivo" name="archivo">

        <input type="submit" value="Publicar mensaje">
      </form>
    </div>

    <div class="tarjeta">
      <h3>Mensajes existentes</h3>
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
              echo "<tr>";
              echo "<td>".$m['contenido']."</td>";
              echo "<td>".$m['Cuenta_Usuario']."</td>";
              echo "<td>".$m['fechaEdi']."</td>";
              if ($m['archivo'] != "") {
                echo "<td><a class='boton' href='media/clases/".$m['archivo']."' target='_blank'>Ver archivo</a></td>";
              } else {
                echo "<td>—</td>";
              }
              echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
</div>
</body>
</html>
