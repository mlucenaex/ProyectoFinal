<?php
// confirmar.php
header('Content-Type: text/html; charset=utf-8');

require __DIR__.'/../conexion.php';

if (empty($_GET['token'])) {
    exit('<h1>Token no válido.</h1>');
}

$token = $_GET['token'];

// 1) Buscar y validar el token
$stmt = $conn->prepare("
    SELECT usuario_id
    FROM Usuario
    WHERE token_confirmacion = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    $stmt->close();
    exit('<h1>Token inválido o ya usado.</h1>');
}

$stmt->bind_result($usuario_id);
$stmt->fetch();
$stmt->close();

// 2) Marcar email como confirmado
$stmt = $conn->prepare("
    UPDATE Usuario
    SET email_confirmado = 1,
        token_confirmacion = NULL
    WHERE usuario_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->close();

// 3) Cerrar conexión y mostrar mensaje
$conn->close();

echo '<h1>¡Tu cuenta ha sido confirmada!</h1>';
echo '<p><a href="login.html">Haz clic aquí para iniciar sesión</a></p>';
