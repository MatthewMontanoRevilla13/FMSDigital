<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
     <?php
    // Inicia la sesión para usar datos del usuario que está logueado
    session_start();

    // Conectamos con la base de datos
    $conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

    // Si falla la conexión, se muestra el error y se detiene todo
    if (!$conexion) {
        echo "Error en la conexion" . mysqli_error();
        die();
    }

    // Guardamos el código de clase que el estudiante escribió en el formulario
    $codigo = $_POST['Codigo'];

    // Obtenemos el usuario actual desde la sesión
    $usuario = $_SESSION['usu'];

    // Buscamos el ID de la clase con ese código
    $sql = "SELECT id_clase FROM Clase WHERE codigoClase = '$codigo'";
    $resultado = mysqli_query($conexion, $sql);

    // Si encontró una clase con ese código
    if (mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $idC = $fila['id_clase'];

        // Insertamos al usuario en la tabla que une cuenta con clase (se une a la clase)
        $unirrr = "INSERT INTO Cuenta_has_Clase (Cuenta_Usuario, Clase_id_clase)
                   VALUES ('$usuario', '$idC')";

        // Si todo sale bien, lo mandamos a la clase
        if (mysqli_query($conexion, $unirrr)) {
            header('Location:/FMSDIGITAL/Maquetacion/Estudiante/ClaseDeAlumno.php');
        } else {
            echo "Error al unirse a la clase: " . mysqli_error($conexion);
        }
    } else {
        // Si no encontró el código, le avisa y lo regresa al panel
        echo "<script>alert('El código ingresado no existe'); window.location.href = '/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.php';</script>";
    }
?>
</body>
</html>