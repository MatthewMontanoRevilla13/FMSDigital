<?php
// --- SOLO PROFESOR ---
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
  header("Location: /FMSDIGITAL/Maquetacion/CuentasDeUsuario/FormularioLogin.php");
  exit;
}

// Conexión (simple)
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { echo "Error en la conexion".mysqli_error($conexion); die(); }
mysqli_set_charset($conexion, "utf8");

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: FormularioCrearClase.php");
  exit;
}

// ====== Captura ======
$nombreCompleto = isset($_POST['nombreCompleto']) ? trim($_POST['nombreCompleto']) : '';
$nombreClase    = isset($_POST['nombreClase'])    ? trim($_POST['nombreClase'])    : '';
$codigoClase    = isset($_POST['codigoClase'])    ? trim($_POST['codigoClase'])    : '';
$cuentaUser     = isset($_SESSION['usu']) ? intval($_SESSION['usu']) : 0;

// ====== Validación mínima (server) ======
$errores = [];
if ($nombreCompleto === '' || strlen($nombreCompleto) < 5) { $errores[] = "Nombre completo inválido."; }
if ($nombreClase === '' || strlen($nombreClase) < 3)         { $errores[] = "Nombre de clase inválido."; }
if ($cuentaUser <= 0)                                        { $errores[] = "Sesión inválida."; }

if (!empty($errores)) {
  $msg = urlencode(implode(' ', $errores));
  header("Location: FormularioCrearClase.php?error=$msg");
  exit;
}

// ====== Generar código si no llega o es muy corto ======
function generarCodigo($len = 6) {
  $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; 
  $s = '';
  for ($i = 0; $i < $len; $i++) { $s .= $chars[random_int(0, strlen($chars) - 1)]; }
  return $s;
}
if ($codigoClase === '' || strlen($codigoClase) < 4) {
  $codigoClase = generarCodigo(6);
}

// ====== Asegurar UNICO ======
while (true) {
  $res = mysqli_query($conexion, "SELECT 1 FROM clase WHERE codigoClase='$codigoClase' LIMIT 1");
  if ($res && mysqli_num_rows($res) > 0) {
    $codigoClase = generarCodigo(6);
  } else {
    break;
  }
}


$nombreClase_sql = $nombreClase;
$nomProfe_sql    = $nombreCompleto;

$sql = "INSERT INTO clase (nombreClase, codigoClase, nomProfe, Cuenta_Usuario)
        VALUES ('$nombreClase_sql', '$codigoClase', '$nomProfe_sql', $cuentaUser)";

if (!mysqli_query($conexion, $sql)) {
  $msg = urlencode("No se pudo crear la clase: ".mysqli_error($conexion));
  header("Location: FormularioCrearClase.php?error=$msg");
  exit;
}

// OK
header("Location: PanelPrincipalDeProfesor.php?ok=1");
exit;
