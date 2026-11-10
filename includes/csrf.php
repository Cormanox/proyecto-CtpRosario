<?php
declare(strict_types=1);

/**
 * Módulo de Protección CSRF y Mensajes Flash en Sesión
 * Proyecto: CTP Rosario de Naranjo
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

if (!function_exists('csrf_token')) {
    /**
     * Genera o recupera el token CSRF para la sesión actual
     */
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera el input hidden con el token CSRF para formularios HTML
     */
    function csrf_field(): string {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('validar_csrf')) {
    /**
     * Valida de manera segura (tiempo constante) el token CSRF
     */
    function validar_csrf(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
        if ($sessionToken === '' || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, (string)$token);
    }
}

if (!function_exists('set_flash')) {
    /**
     * Almacena un mensaje flash para el siguiente ciclo Post-Redirect-Get
     */
    function set_flash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['_flash_message'] = [
            'type'    => in_array($type, ['success', 'danger', 'warning', 'info'], true) ? $type : 'info',
            'message' => $message
        ];
    }
}

if (!function_exists('get_flash')) {
    /**
     * Obtiene y elimina el mensaje flash almacenado
     */
    function get_flash(): ?array {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (isset($_SESSION['_flash_message'])) {
            $flash = $_SESSION['_flash_message'];
            unset($_SESSION['_flash_message']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('render_flash')) {
    /**
     * Renderiza el componente visual del mensaje flash si existe
     */
    function render_flash(): string {
        $flash = get_flash();
        if (!$flash) {
            return '';
        }

        $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
        $msg  = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        $icon = ($type === 'success') ? 'check-circle' : (($type === 'danger') ? 'circle-exclamation' : 'bell-ringing');
        $iconHtml = function_exists('icon') ? icon($icon, ['class' => 'icon-md']) : '';

        return <<<HTML
        <div class="alert alert-{$type}" role="alert" tabindex="-1">
            {$iconHtml}
            <div>{$msg}</div>
        </div>
HTML;
    }
}

