<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "RegistroP6");
if (!$conexion) { die("Error de conexión: " . mysqli_connect_error()); }

$id_clase   = $_POST['id_clase'];
$usuario    = $_SESSION['usu'];
$contenido  = trim($_POST['contenido'] ?? "");

// Carpeta donde guardar archivos del tablón
$targetDir = __DIR__ . "/../media/comentarios/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$archivoNombre = null;
if (!empty($_FILES['fileUpload']['name'])) {
    $ext = strtolower(pathinfo($_FILES["fileUpload"]["name"], PATHINFO_EXTENSION));
    $nuevoNombre = "C{$id_clase}_U{$usuario}_" . time() . "." . $ext;
    $targetFile = $targetDir . $nuevoNombre;

    if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $targetFile)) {
        $archivoNombre = $nuevoNombre;
    } else {
        die("Error al subir el archivo.");
    }
}

$sql = "INSERT INTO Comentario (contenido, fechaPub, Clase_id_clase, Cuenta_Usuario, archivo)
        VALUES (?, NOW(), ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "siis", $contenido, $id_clase, $usuario, $archivoNombre);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ClaseDeAlumno.php?id_clase=".$id_clase);
    exit;
} else {
    echo "Error al guardar comentario: " . mysqli_error($conexion);
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>