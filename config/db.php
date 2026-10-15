<?php
/**
 * Conexión a la base de datos mediante PDO
 * Proyecto: Portal Web CTP Rosario
 */

// Parámetros de configuración del servidor local (AMPPS)
$host     = '127.0.0.1'; // o 'localhost'
$db       = 'ctp_rosario';
$user     = 'root';
$pass     = 'mysql';     // En AMPPS la clave por defecto de root suele ser 'mysql' o estar vacía ''
$charset  = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de configuración de PDO
$options = [
    // Lanza excepciones en caso de errores SQL (súper útil para desarrollo)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Retorna los resultados como arreglos asociativos por defecto
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Desactiva la emulación de sentencias preparadas para mayor seguridad real en MySQL
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Instancia de la conexión PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En entorno de desarrollo mostramos el mensaje de error.
    // En producción es mejor guardar el log y mostrar un mensaje genérico al usuario.
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>