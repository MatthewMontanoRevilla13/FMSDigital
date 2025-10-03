<?php

// Iniciamos la sesión para poder leer variables guardadas (rol, usuario, etc.)
session_start();

// Si no existe rol en sesión o el rol NO es "Profesor", lo enviamos al login.
// Esto evita que un alumno o un invitado acceda a este proceso.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit; // Importante para detener la ejecución
}

// CONEXIÓN A BASE DE DATOS
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");

// Si falla la conexión, mostramos el error y cortamos el script.
if (!$conexion) { 
  echo "Error en la conexion" . mysqli_error($conexion); 
  die(); 
}

// Forzamos el conjunto de caracteres a UTF-8 para evitar problemas con tildes/ñ.
mysqli_set_charset($conexion, "utf8");

// Este script solo debe ejecutarse con datos enviados por POST (desde un formulario).
// Si viene por GET u otro método, lo redirigimos al formulario de creación.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: FormularioCrearClase.php");
  exit;
}

// Obtenemos los campos enviados por el formulario y los limpiamos con trim().
// Si no existen, asignamos cadenas vacías para evitar "undefined index".
$nombreCompleto = isset($_POST['nombreCompleto']) ? trim($_POST['nombreCompleto']) : '';
$nombreClase    = isset($_POST['nombreClase'])    ? trim($_POST['nombreClase'])    : '';
$codigoClase    = isset($_POST['codigoClase'])    ? trim($_POST['codigoClase'])    : '';

// También recuperamos el ID de cuenta del profesor desde la sesión.
// Si no existe (algo raro), ponemos 0 para forzar error.
$cuentaUser     = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : 0;

// VALIDACIÓN MÍNIMA DEL LADO SERVIDOR 
// Creamos un arreglo para ir guardando mensajes de error.
$errores = [];

// Regla 1: nombre completo con al menos 5 caracteres
if ($nombreCompleto === '' || strlen($nombreCompleto) < 5) { 
  $errores[] = "Nombre completo inválido."; 
}

// Regla 2: nombre de clase con al menos 3 caracteres
if ($nombreClase === '' || strlen($nombreClase) < 3) {         
  $errores[] = "Nombre de clase inválido."; 
}

// Regla 3: debe existir una sesión válida con ID de usuario > 0
if ($cuentaUser <= 0) {                                        
  $errores[] = "Sesión inválida."; 
}

// Si hay errores, los concatenamos y redirigimos de vuelta al formulario con el mensaje.
if (!empty($errores)) {
  $msg = urlencode(implode(' ', $errores)); // urlencode para que no rompa la URL
  header("Location: FormularioCrearClase.php?error=$msg");
  exit;
}

// GENERAR CÓDIGO DE CLASE SI NO LLEGÓ O ES MUY CORTO
// Función que crea un código aleatorio de longitud $len usando un set de caracteres.
function generarCodigo($len = 6) {
  $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // Sin I, L, O, 1, 0 para evitar confusiones
  $s = '';
  for ($i = 0; $i < $len; $i++) { 
    $s .= $chars[random_int(0, strlen($chars) - 1)]; 
  }
  return $s;
}

// Si el usuario no envió código o envió uno demasiado corto, generamos uno de 6 caracteres.
if ($codigoClase === '' || strlen($codigoClase) < 4) {
  $codigoClase = generarCodigo(6);
}

//ASEGURAR QUE EL CÓDIGO SEA ÚNICO EN LA TABLA
// Hacemos una verificación en bucle: si el código ya existe, generamos otro y volvemos a checar.
// Salimos del bucle cuando encontramos un código que no está en uso.
while (true) {
  $res = mysqli_query($conexion, "SELECT 1 FROM clase WHERE codigoClase='$codigoClase' LIMIT 1");
  if ($res && mysqli_num_rows($res) > 0) {
    // Ya existe: generamos uno nuevo y repetimos
    $codigoClase = generarCodigo(6);
  } else {
    // No existe: podemos usarlo
    break;
  }
}

// PREPARAR VALORES PARA INSERTAR
// (En este código se insertan directamente; en producción se recomienda usar consultas preparadas)
$nombreClase_sql = $nombreClase;
$nomProfe_sql    = $nombreCompleto;

// Armamos la consulta de inserción para la tabla "clase".
// Columnas: nombreClase, codigoClase, nomProfe, Cuenta_Usuario
$sql = "INSERT INTO clase (nombreClase, codigoClase, nomProfe, Cuenta_Usuario)
        VALUES ('$nombreClase_sql', '$codigoClase', '$nomProfe_sql', $cuentaUser)";

// Ejecutamos el INSERT. Si falla, redirigimos con el mensaje de error de MySQL.
if (!mysqli_query($conexion, $sql)) {
  $msg = urlencode("No se pudo crear la clase: " . mysqli_error($conexion));
  header("Location: FormularioCrearClase.php?error=$msg");
  exit;
}

// SI TODO SALIÓ BIEN
// Redirigimos al panel principal del profesor con un indicador ok=1 para mostrar mensaje de éxito.
header("Location: PanelPrincipalDeProfesor.php?ok=1");
exit;
