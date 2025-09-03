<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>datosR.php</title>
</head>
<body>
    <?php
    // Iniciamos la sesión por si luego queremos usar variables de sesión
    session_start();

    // Conexión a la base de datos local
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

    // Si no se conecta, mostramos el error y detenemos todo
    if (!$conexion) {
        echo "Error en la conexion" . mysqli_error();
        die();
    }

    // Guardamos los datos que llegaron desde el formulario de registro
    $nom = $_POST['Nombres'];
    $apes = $_POST['Apellidos'];
    $Direc = $_POST['Direccion']; 
    $fNACI = $_POST['Nacimiento'];
    $tel = $_POST['Telefono'];
    $Cur = $_POST['Curso'];
    $CI = $_POST['CI'];
    $Rude = $_POST['Rude'];
    $clave = $_POST['Contraseña'];

    // Verificamos si tiene RUDE, entonces es estudiante, si no, es profesor
    if (!empty($Rude)) {
        $rol = 'Estudiante';
    } else {
        $rol = 'Profesor';
    }

    // Insertamos en la tabla Cuenta (donde se guarda el usuario y contraseña)
    $sqlCuenta = "INSERT INTO Cuenta (Usuario, Contraseña, Rol)
                  VALUES ('$CI', '$clave', '$rol')";

    // Si se guardó bien en Cuenta...
    if (mysqli_query($conexion, $sqlCuenta)) {
        // Guardamos el resto de la información en la tabla Informacion
        $sqlInfo = "INSERT INTO Informacion (Nombres, Apellidos, Direccion, Nacimiento, Telefono, Curso, CI, Rude, Cuenta_Usuario)
                    VALUES ('$nom', '$apes', '$Direc', '$fNACI', '$tel', '$Cur', '$CI', '$Rude', '$CI')";
        
        // Si también se guarda bien, lo mandamos al login
        if (mysqli_query($conexion, $sqlInfo)) {
            header('Location:/1r Sprint-FMSDigital/Maquetacion/CuentasDeUsuario/FormularioLogin.php');   
        } else {
            echo "Error al guardar los datos: " . mysqli_error($conexion);
        }

    } else {
        // Si no se pudo guardar en Cuenta, mostramos el error
        echo "Error al guardar los datos: " . mysqli_error($conexion);
    }
?>
</body>
</html>