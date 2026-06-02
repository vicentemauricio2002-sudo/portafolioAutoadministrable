<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "portafolio_db";

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_errno) {
    $msg = match($conn->connect_errno) {
        1045 => "Usuario/contraseña incorrectos.",
        1049 => "Base de datos 'portafolio_db' no encontrada. Ejecuta db.sql.",
        2002 => "MySQL no está corriendo. Inicia MySQL desde XAMPP.",
        default => "Error de conexión: " . $conn->connect_error
    };
    die("<div class='alert alert-danger'>⚠️ " . $msg . "</div>");
}

// Configurar charset
$conn->set_charset("utf8mb4");
?>
