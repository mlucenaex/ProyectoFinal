<?php
session_start();


header('Content-Type: application/json; charset=utf-8');

// 2) Autoload de Composer (PHPMailer + Dotenv)
require __DIR__.'/../vendor/autoload.php';

// 3) Conexión a la base de datos
require __DIR__.'/../conexion.php';

// 4) Carga de configuración SMTP y creación de PHPMailer
try {
    $mail = require __DIR__.'/../Mailer.php';
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'error'   => 9,
        'msg'     => 'Error al iniciar el sistema de correo.'
    ]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 1,
        'msg'     => 'Método no permitido.'
    ]);
    exit;
}

// 6) Recoger datos
$nombre = trim($_POST['nombre']  ?? '');
$email  = trim($_POST['email']   ?? '');
$pass   = $_POST['password']     ?? '';
$conf   = $_POST['confirm']      ?? '';

// 7) Validaciones básicas
if ($nombre === '' || $email === '' || $pass === '' || $conf === '') {
    echo json_encode(['success'=>false,'error'=>2,'msg'=>'Rellena todos los campos.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'error'=>3,'msg'=>'Email no válido.']);
    exit;
}
if ($pass !== $conf) {
    echo json_encode(['success'=>false,'error'=>4,'msg'=>'Las contraseñas no coinciden.']);
    exit;
}

// 8) Comprobar dominio de correo
$domain = substr(strrchr($email, "@"), 1);
$hasMX = false;
if (function_exists('getmxrr') && @getmxrr($domain, $mxHosts)) {
    $hasMX = true;
} elseif (function_exists('checkdnsrr') && checkdnsrr($domain, "A")) {
    $hasMX = true;
} elseif (gethostbyname($domain) !== $domain) {
    $hasMX = true;
}
if (! $hasMX) {
    echo json_encode(['success'=>false,'error'=>7,'msg'=>'El dominio del correo no existe.']);
    exit;
}

// 9) Comprobar email duplicado
$stmt = $conn->prepare("SELECT usuario_id FROM Usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success'=>false,'error'=>5,'msg'=>'Ese correo ya está registrado.']);
    exit;
}
$stmt->close();

// 10) Insertar usuario con token
$hash  = password_hash($pass, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));
$now   = date('Y-m-d H:i:s');

$stmt = $conn->prepare("
  INSERT INTO Usuario
    (nombre, email, contraseña, fecha_registro, email_confirmado, token_confirmacion)
  VALUES (?, ?, ?, ?, 0, ?)
");
$stmt->bind_param("sssss", $nombre, $email, $hash, $now, $token);
if (! $stmt->execute()) {
    echo json_encode(['success'=>false,'error'=>6,'msg'=>'Error al registrar.']);
    exit;
}
$stmt->close();

// 11) Enviar correo de confirmación
try {
    $mail->addAddress($email, $nombre);
    $mail->Subject = 'Confirma tu cuenta en Diario IA';

    // Generar enlace de confirmación
    $linkConfirm = $_ENV['BASE_URL'] . '/confirmar.php?token=' . $token;
    $mail->Body    = "<p>¡Hola <strong>{$nombre}</strong>!</p>
                      <p>Para activar tu cuenta haz clic <a href=\"{$linkConfirm}\">aquí</a>.</p>";
    $mail->AltBody = "Visita: {$linkConfirm}";

    $mail->send();

    echo json_encode(['success'=>true,'redirect'=>'login.html?registered=1']);
    exit;

} catch (\Exception $e) {
    error_log("PHPMailer error: " . $e->getMessage());
    echo json_encode([
      'success'=>false,
      'error'=>8,
      'msg'=>'Error al enviar el correo de confirmación.'
    ]);
    exit;
}
