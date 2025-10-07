<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>datosL.php</title>
</head>
<body>
<?php
// Iniciar sesión
session_start();

// 1) Validación de llegada de datos y que no estén vacíos
if (!isset($_POST['Usuario'], $_POST['Contraseña'])) {
    // Si no vinieron los campos esperados
    header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?e=faltan_campos');
    exit;
}

$usu   = trim($_POST['Usuario']);
$clave = trim($_POST['Contraseña']);

if ($usu === '' || $clave === '') {
    // Si llegaron vacíos
    header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?e=vacíos');
    exit;
}

// 2) Conectar con la base de datos
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    // No mostramos detalles del error al usuario final (seguridad)
    // Puedes loguearlo si quieres en un archivo.
    echo "<script>alert('Error de conexión con la base de datos.');</script>";
    echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php';</script>";
    exit;
}

// Usar el set de caracteres correcto ANTES de escapar
mysqli_set_charset($conexion, "utf8mb4");

// 3) Escapar valores para evitar inyección SQL (sin prepared statements)
$usu_safe   = mysqli_real_escape_string($conexion, $usu);
$clave_safe = mysqli_real_escape_string($conexion, $clave);

// 4) Construir la consulta (misma lógica, ahora con variables escapadas)
$sqlJ = "
    SELECT c.Usuario, c.Contraseña, c.Rol, c.Bloqueado,
           i.Nombres, i.Apellidos
    FROM Cuenta c
    JOIN Informacion i ON c.Usuario = i.Cuenta_Usuario
    WHERE c.Usuario = '$usu_safe' AND c.Contraseña = '$clave_safe'
";

// 5) Ejecutar y validar que la consulta se ejecute correctamente
$resultado = mysqli_query($conexion, $sqlJ);

if ($resultado === false) {
    // Error al ejecutar la consulta
    // (opcional) Puedes loguear mysqli_error($conexion) en un archivo .log
    echo "<script>alert('Ocurrió un problema al procesar la solicitud.');</script>";
    echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php';</script>";
    mysqli_close($conexion);
    exit;
}

// 6) Validar si hay filas
if (mysqli_num_rows($resultado) > 0) {
    $fila = mysqli_fetch_assoc($resultado);

    // 7) Chequear bloqueo (si tu campo es 1/0, esto lo cubre)
    if (!empty($fila['Bloqueado'])) {
        echo "<script>alert('Tu cuenta está bloqueada. Contacta con el administrador.');</script>";
        echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php';</script>";
        mysqli_free_result($resultado);
        mysqli_close($conexion);
        exit;
    }

    // 8) Guardar datos en sesión
    $_SESSION['usu']  = $fila['Usuario'];
    $_SESSION['rol']  = $fila['Rol'];
    $_SESSION['nom']  = $fila['Nombres'];
    $_SESSION['apes'] = $fila['Apellidos'];

    // 9) Redirigir según rol
    if ($fila['Rol'] === 'Administrador') {
        mysqli_free_result($resultado);
        mysqli_close($conexion);
        header('Location:/FMSDIGITAL/Maquetacion/Administrador/admin.php');
        exit;
    } else {
        if ($fila['Rol'] === 'Estudiante') {
            mysqli_free_result($resultado);
            mysqli_close($conexion);
            header('Location:/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.php');
            exit;
        } elseif ($fila['Rol'] === 'Profesor') {
            mysqli_free_result($resultado);
            mysqli_close($conexion);
            header('Location:/FMSDIGITAL/Maquetacion/Profesor/PanelPrincipalDeProfesor.php');
            exit;
        } else {
            // Rol inesperado
            echo "<script>alert('Rol desconocido. Contacta al administrador.');</script>";
            echo "<script>window.location.href = '/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php';</script>";
            mysqli_free_result($resultado);
            mysqli_close($conexion);
            exit;
        }
    }
} else {
    // 10) Credenciales inválidas
    mysqli_free_result($resultado);
    mysqli_close($conexion);
    // Puedes pasar un flag por querystring para mostrar mensaje en el login
    header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?e=credenciales');
    exit;
}
?>
</body>
</html>
