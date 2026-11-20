<?php
// Conexión usando variables de entorno (Railway) o valores locales (XAMPP)
$host     = getenv('MYSQLHOST')     ?: 'localhost';
$user     = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'sistema_residencia';
$port     = getenv('MYSQLPORT')     ?: 3306;

$conexion = new mysqli($host, $user, $password, $database, (int)$port);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');
?>
