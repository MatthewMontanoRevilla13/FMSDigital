<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cerrarL.php</title>
</head>
<body>
    
    <?php
    // Inicia la sesión
    session_start();
    // Cierra o elimina la sesión actual
    session_destroy();
    // Redirige a la página principal después de cerrar sesión
    header("Location:/1r Sprint-FMSDigital/Maquetacion/PaginaWeb/PaginaPrincipal.php");
    ?>
</body>
</html>