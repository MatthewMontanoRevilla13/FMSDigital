<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Document</title>
  <link rel="stylesheet" href="tarea.css" />
</head>
<body>
  <header>tarea</header>

  <!-- Input oculto para subir archivo -->
  <input type="file" id="fileUpload" style="display: none;">

  <textarea name="titulo" id="titulo">titulo</textarea><br>
  obligatorio <br>
  <textarea name="instrucciones" id="instrucciones">(opcional)</textarea>

  <!-- Botones con sus funciones -->
  <button class="uno" onclick="window.open('https://www.youtube.com', '_blank')" title="Ir a YouTube"></button>

  <button class="dos" onclick="document.getElementById('fileUpload').click()" title="Subir archivo"></button>

  <button class="tres" onclick="window.open('https://drive.google.com', '_blank')" title="Abrir Google Drive"></button>

  <button class="cuatro" onclick="window.open('https://www.dropbox.com', '_blank')" title="Abrir Dropbox"></button>

  <button class="cinco" onclick="window.open('https://onedrive.live.com', '_blank')" title="Abrir OneDrive"></button>


<section class="formulario">
  <label for="asignar">Asignar a</label>
  <select id="asignar">
    <option>Todos los alumnos</option>
  </select>
  
  <label for="puntos">Puntos</label>
  <select id="puntos">
    <option>100</option>
    <option>50</option>
    <option>10</option>
  </select>

  <label for="fecha">Fecha de entrega</label>
  <input type="date" id="fecha" />

  <label for="tema">Tema</label>
  <select id="tema">
    <option>Sin tema</option>
    <option>Tema 1</option>
    <option>Tema 2</option>
  </select>

  <label for="rubrica">Rúbrica</label>
  <button id="rubrica">+ Rúbrica</button>
</section>

</body>
</html>
