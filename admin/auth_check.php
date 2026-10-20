<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: " . url('login.php'));
    exit;
}

if (!function_exists('tieneRol')) {
    /**
     * Verifica si el usuario actual posee alguno de los roles indicados.
     */
    function tieneRol(string ...$rolesPermitidos): bool {
        $rolActual = (string)($_SESSION['user_role'] ?? '');
        return in_array($rolActual, $rolesPermitidos, true);
    }
}

if (!function_exists('requerirRol')) {
    /**
     * Exige que el usuario cuente con al menos uno de los roles autorizados; de lo contrario aborta con 403.
     */
    function requerirRol(string ...$rolesPermitidos): void {
        if (!tieneRol(...$rolesPermitidos)) {
            http_response_code(403);
            echo "<!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>403 - Acceso Denegado</title>
                <link rel='stylesheet' href='" . url('assets/css/styles.css') . "'>
            </head>
            <body style='display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f1f5f9; font-family:sans-serif;'>
                <div style='background:#fff; padding:30px; border-radius:8px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); text-align:center; max-width:450px;'>
                    <h2 style='color:#dc2626; margin-top:0;'>Acceso Denegado (403)</h2>
                    <p style='color:#475569;'>No cuentas con los permisos necesarios (" . implode(', ', $rolesPermitidos) . ") para acceder a esta sección.</p>
                    <a href='" . url('admin/index.php') . "' class='btn btn-primary' style='display:inline-block; padding:10px 20px; background:#003366; color:#fff; text-decoration:none; border-radius:5px;'>Volver al Panel</a>
                </div>
            </body>
            </html>";
            exit;
        }
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Genera o retorna el token CSRF de la sesión actual.
     */
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Retorna el campo oculto HTML con el token CSRF.
     */
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('validar_csrf')) {
    /**
     * Valida el token CSRF contra el almacenado en la sesión de forma segura.
     */
    function validar_csrf(?string $token): bool {
        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
        if ($sessionToken === '' || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, (string)$token);
    }
}
