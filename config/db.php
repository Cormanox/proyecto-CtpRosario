<?php
declare(strict_types=1);

/**
 * Conexión a la base de datos mediante PDO y configuración global
 * Proyecto: Portal Web Institucional CTP Rosario
 */

// 1. Detección automática y estandarizada de BASE_URL
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Remueve subdirectorios conocidos si el script se ejecuta dentro de ellos
    $basePath = preg_replace('#/(public|admin|includes|config)$#', '', $scriptDir);
    define('BASE_URL', rtrim((string)$basePath, '/'));
}

if (!function_exists('url')) {
    /**
     * Genera URLs canónicas dentro del proyecto
     *
     * @param string $path Ruta relativa al proyecto
     * @return string Ruta normalizada con BASE_URL
     */
    function url(string $path = ''): string {
        $cleanPath = ltrim($path, '/');
        return BASE_URL . ($cleanPath !== '' ? '/' . $cleanPath : '');
    }
}

if (!function_exists('e')) {
    /**
     * Escapa valores para salida HTML segura contra ataques XSS
     *
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 2. Parámetros de configuración del servidor local (AMPPS)
$host     = '127.0.0.1';
$db       = 'ctp_rosario';
$user     = 'root';
$pass     = 'mysql';     // Clave estándar en AMPPS
$charset  = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

// Opciones de configuración de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Registro interno seguro sin exponer credenciales en pantalla
    error_log("[DB Error] " . $e->getMessage());
    die("Error al conectar con la base de datos institucional. Por favor, verifique el servicio MySQL o contacte al administrador.");
}