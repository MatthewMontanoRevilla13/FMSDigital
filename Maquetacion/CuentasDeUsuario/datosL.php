<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>datosL.php</title>
</head>
<body>
    <?php
    // Guardamos lo que se escribió en el formulario
    $usu = $_POST['Usuario'];
    $clave = $_POST['Contraseña'];

    // Iniciamos sesión para guardar datos del usuario si todo sale bien
    session_start();

    // Conectamos con la base de datos local
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

    // Si no se conecta, mostramos error y paramos todo
    if (!$conexion) {
        echo "Error en la conexion" . mysqli_error();
        die();
    }

    // Consulta para ver si el usuario y contraseña existen y obtener sus datos
    $sqlJ = "SELECT c.Usuario, c.Contraseña, c.Rol, c.Bloqueado, 
                    i.Nombres, i.Apellidos
             FROM Cuenta c 
             JOIN Informacion i ON c.Usuario = i.Cuenta_Usuario
             WHERE c.Usuario = '$usu' AND c.Contraseña = '$clave'";

    // Ejecutamos la consulta
    $resultado = mysqli_query($conexion, $sqlJ);

    // Si encontró resultados y hay por lo menos un usuario
    if (!empty($resultado) && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado); // Guardamos los datos del usuario

        // Si el usuario está bloqueado, se lo avisa y se lo manda de vuelta al login
        if (!empty($fila['Bloqueado'])) {
            echo "<script>alert('Tu cuenta está bloqueada. Contacta con el administrador.');</script>";
            echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php';</script>";
            exit;
        }

        // Guardamos los datos del usuario en variables de sesión
        $_SESSION['usu'] = $fila['Usuario'];
        $_SESSION['rol'] = $fila['Rol'];
        $_SESSION['nom'] = $fila['Nombres'];
        $_SESSION['apes'] = $fila['Apellidos'];

        // Según su rol, lo mandamos a su panel correspondiente
        if($fila['Rol'] === 'Administrador'){
            header('Location:/FMSDIGITAL/Maquetacion/Administrador/admin.php');
        }else{
            if ($fila['Rol'] === 'Estudiante') {
            header('Location:/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.php');
            } elseif ($fila['Rol'] === 'Profesor') {
            header('Location:/FMSDIGITAL/Maquetacion/Profesor/PanelPrincipalDeProfesor.php');
          } else {
             // Si por algún motivo el rol no es válido
             echo "<script>alert('Rol desconocido. Contacta al administrador.');</script>";
             echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/cuentasDeUsuario/FormularioLogin.php';</script>";
          }
        exit;
    }
    } else {
        // Si no se encontró el usuario, lo mandamos de nuevo al login
        header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php');
    }
?>

</body>
</html>