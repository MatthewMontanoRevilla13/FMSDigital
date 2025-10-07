<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>datosL.php</title>
</head>
<body>
<?php
// ------------------------- ENTRADA -------------------------
session_start();

// Validación básica de entrada
$usu   = isset($_POST['Usuario']) ? trim($_POST['Usuario']) : '';
$clave = isset($_POST['Contraseña']) ? trim($_POST['Contraseña']) : '';

if ($usu === '' || $clave === '') {
    // Campos obligatorios
    header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php');
    exit;
}

// ------------------------- CONEXIÓN -------------------------
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) {
    // Error de conexión
    // (En producción evita imprimir detalles del error)
    echo "Error en la conexión a la base de datos.";
    exit;
}
mysqli_set_charset($conexion, "utf8");

// ------------------------- CONSULTA PREPARADA -------------------------
// Usamos consulta preparada para evitar inyección SQL
$sqlJ = "
    SELECT c.Usuario, c.Contraseña, c.Rol, c.Bloqueado,
           i.Nombres, i.Apellidos
    FROM Cuenta c
    JOIN Informacion i ON c.Usuario = i.Cuenta_Usuario
    WHERE c.Usuario = ? AND c.Contraseña = ?
";

$stmt = mysqli_prepare($conexion, $sqlJ);
if (!$stmt) {
    // Falla al preparar (por ejemplo, error de sintaxis)
    echo "Error al preparar la consulta.";
    exit;
}

// Vinculamos parámetros (ss = string, string)
mysqli_stmt_bind_param($stmt, "ss", $usu, $clave);

// Ejecutamos
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    // Falla al ejecutar
    echo "Error al ejecutar la consulta.";
    exit;
}

// Obtenemos resultados
mysqli_stmt_store_result($stmt);  // Necesario para num_rows
if (mysqli_stmt_num_rows($stmt) > 0) {
    // Vincular columnas de salida
    mysqli_stmt_bind_result($stmt, $dbUsuario, $dbClave, $dbRol, $dbBloqueado, $dbNombres, $dbApellidos);
    mysqli_stmt_fetch($stmt);

    // ------------------------- VALIDACIÓN DE ESTADO -------------------------
    // Si está bloqueado
    if (!empty($dbBloqueado)) {
        // Usamos redirección con mensaje (podrás leer ?msg=bloqueado en el login si quieres mostrar alerta)
        header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?msg=bloqueado');
        exit;
    }

    // ------------------------- SESIÓN -------------------------
    $_SESSION['usu']  = $dbUsuario;
    $_SESSION['rol']  = $dbRol;
    $_SESSION['nom']  = $dbNombres;
    $_SESSION['apes'] = $dbApellidos;

    // ------------------------- RUTEO POR ROL -------------------------
    if ($dbRol === 'Administrador') {
        header('Location:/FMSDIGITAL/Maquetacion/Administrador/admin.php');
        exit;
    } elseif ($dbRol === 'Estudiante') {
        header('Location:/FMSDIGITAL/Maquetacion/Estudiante/PanelDeEstudiante.php');
        exit;
    } elseif ($dbRol === 'Profesor') {
        header('Location:/FMSDIGITAL/Maquetacion/Profesor/PanelPrincipalDeProfesor.php');
        exit;
    } else {
        // Rol desconocido
        header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?msg=rol_desconocido');
        exit;
    }

} else {
    // ------------------------- SIN COINCIDENCIAS -------------------------
    header('Location:/FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php?msg=credenciales');
    exit;
}

// Limpieza
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
</body>
</html>

