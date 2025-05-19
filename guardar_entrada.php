<?php

session_start();
include 'includes/conexion.php';

$userId = $_SESSION['user_id'];

// Recibir datos del formulario
$titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
$contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
$videoLink = mysqli_real_escape_string($conn, $_POST['video']);

// 1) Insertar en tabla 'entrada'
$sql = "INSERT INTO entrada (titulo, contenido, fecha, usuario_id) VALUES (?, ?, NOW(), ?)";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ssi", $titulo, $contenido, $userId);
    mysqli_stmt_execute($stmt);
    $entrada_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
} else {
    die("Error al preparar la consulta: " . mysqli_error($conn));
}

// 2) Procesar imágenes subidas
if (!empty($_FILES['imagenes']['name'][0])) {
    $targetDir = "uploads/images/";
    
    $allowedTypes = ['jpg','jpeg','png','gif'];
    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['imagenes']['name'][$key]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($tmp_name, $targetFilePath)) {  
                // Guardar ruta en BD
                $stmtImg = mysqli_prepare($conn, "INSERT INTO imagen (entrada_id, ruta) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmtImg, "is", $entrada_id, $targetFilePath);
                mysqli_stmt_execute($stmtImg);
                mysqli_stmt_close($stmtImg);
            }
        }
    }
}

// 3) Procesar enlace de video
if (!empty($videoLink)) {
    $stmtVid = mysqli_prepare($conn, "INSERT INTO video (entrada_id, enlace) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmtVid, "is", $entrada_id, $videoLink);
    mysqli_stmt_execute($stmtVid);
    mysqli_stmt_close($stmtVid);
}

// Vuelta al menú principal
header("Location: index.php");
exit;
?>
