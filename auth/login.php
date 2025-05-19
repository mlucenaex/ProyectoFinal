<?php
session_start();
require __DIR__.'/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

  
    $sql = "SELECT usuario_id, `contraseña`, email_confirmado 
            FROM Usuario 
            WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
           
            $stmt->bind_result($usuario_id, $hash, $email_confirmado);
            $stmt->fetch();

            // Si no han confirmado el email, bloqueamos el acceso
            if ($email_confirmado == 0) {
                header("Location: login.html?error=confirm");
                exit();
            }

            // Verificar contraseña (
            if (password_verify($pass, $hash)) {
                
                $_SESSION['usuario_id'] = $usuario_id;
                if (isset($_POST['remember'])) {
                    setcookie("user_remember", $email, time() + 86400 * 30, "/"); 
                }
                header("Location: principal.php");
                exit();
            } else {
                // Contraseña incorrecta
                header("Location: login.html?error=1");
                exit();
            }
        } else {
            // Usuario no encontrado
            header("Location: login.html?error=1");
            exit();
        }
    } else {
        echo "Error en la consulta.";
    }
}
?>
