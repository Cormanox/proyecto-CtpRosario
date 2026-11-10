<?php
declare(strict_types=1);

/**
 * Conexión a la base de datos mediante PDO y configuración global segura
 * Proyecto: Portal Web Institucional y CMS CTP Rosario
 */

// 1. Configuración de sesiones seguras
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 2. Cabeceras HTTP de seguridad
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// 3. Detección automática y estandarizada de BASE_URL
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/(public|admin|includes|config)$#', '', $scriptDir);
    define('BASE_URL', rtrim((string)$basePath, '/'));
}

if (!function_exists('url')) {
    /**
     * Genera URLs canónicas dentro del proyecto
     */
    function url(string $path = ''): string {
        $cleanPath = ltrim($path, '/');
        return BASE_URL . ($cleanPath !== '' ? '/' . $cleanPath : '');
    }
}

if (!function_exists('e')) {
    /**
     * Escapa valores para salida HTML segura contra ataques XSS
     */
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 4. Carga de variables desde .env si existe (con fallback seguro)
$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Quitar comillas envolventes si existen
            $val = trim($val, "\"'");
            $env[$key] = $val;
        }
    }
}

$host    = $env['DB_HOST'] ?? '127.0.0.1';
$port    = $env['DB_PORT'] ?? '3306';
$db      = $env['DB_NAME'] ?? 'ctp_rosario';
$user    = $env['DB_USER'] ?? 'root';
$pass    = $env['DB_PASS'] ?? 'mysql';     // Clave estándar en AMPPS
$charset = $env['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("[DB Error] " . $e->getMessage());
    die("Error al conectar con la base de datos institucional. Por favor, verifique el servicio MySQL o contacte al administrador.");
}

// 5. Inclusión de helpers globales
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../includes/csrf.php';

// 6. Helpers de Configuración Institucional Dinámica (Clave-Valor)
$GLOBALS['__SITE_CONFIG_CACHE'] = null;

if (!function_exists('obtener_todas_configuraciones')) {
    /**
     * Recupera todos los parámetros de configuración institucional en caché de memoria
     */
    function obtener_todas_configuraciones(): array {
        global $pdo;
        if ($GLOBALS['__SITE_CONFIG_CACHE'] !== null) {
            return $GLOBALS['__SITE_CONFIG_CACHE'];
        }

        try {
            $stmt = $pdo->query("SELECT clave, valor FROM configuracion_sitio");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $GLOBALS['__SITE_CONFIG_CACHE'] = $rows ?: [];
        } catch (Throwable $e) {
            error_log("[Config Load Error] " . $e->getMessage());
            $GLOBALS['__SITE_CONFIG_CACHE'] = [];
        }

        return $GLOBALS['__SITE_CONFIG_CACHE'];
    }
}

if (!function_exists('obtener_config')) {
    /**
     * Obtiene el valor de una clave de configuración con valor por defecto seguro
     */
    function obtener_config(string $clave, ?string $default = ''): string {
        $configs = obtener_todas_configuraciones();
        return array_key_exists($clave, $configs) ? (string)$configs[$clave] : (string)$default;
    }
}

if (!function_exists('guardar_config')) {
    /**
     * Guarda o actualiza un parámetro de configuración institucional
     */
    function guardar_config(string $clave, string $valor, string $categoria = 'general', ?string $descripcion = null): bool {
        global $pdo;
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO configuracion_sitio (clave, valor, categoria, descripcion, actualizado_en) 
                 VALUES (:clave, :valor, :categoria, :descripcion, NOW())
                 ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor), 
                    categoria = VALUES(categoria),
                    descripcion = COALESCE(VALUES(descripcion), descripcion),
                    actualizado_en = NOW()"
            );
            $stmt->execute([
                ':clave'       => $clave,
                ':valor'       => $valor,
                ':categoria'   => $categoria,
                ':descripcion' => $descripcion
            ]);
            // Invalidar caché
            $GLOBALS['__SITE_CONFIG_CACHE'] = null;
            return true;
        } catch (PDOException $e) {
            error_log("[Config Save Error] " . $e->getMessage());
            return false;
        }
    }
}
