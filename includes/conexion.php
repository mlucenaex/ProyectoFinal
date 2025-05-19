<?php

$servername = "localhost";
$username   = "root";
$password   = "root";
$dbname     = "diario_ia";

// Crear conexión
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificar conexión
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
