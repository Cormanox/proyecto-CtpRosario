<?php
declare(strict_types=1);

/**
 * Motor de Iconos Inline SVG basado en el estándar Iconimate (https://iconimate.app/)
 * Cuadrícula: viewBox="0 0 256 256" (optimizado a 24px)
 * Proyecto: CTP Rosario de Naranjo
 */

if (!function_exists('icon')) {
    /**
     * Genera un elemento SVG inline optimizado y animable con Iconimate
     *
     * @param string $name Nombre del icono
     * @param array $attrs Atributos HTML adicionales (class, style, aria-label, etc.)
     * @return string Código SVG inline completo
     */
    function icon(string $name, array $attrs = []): string {
        $icons = [
            // Campana / Notificación
            'bell-ringing' => '<path d="M48,104a80,80,0,0,1,160,0c0,56,16,72,24,80H24c8-8,24-24,24-80Z"/><path d="M104,224a24,24,0,0,0,48,0"/><line x1="128" y1="24" x2="128" y2="40"/><line x1="32" y1="48" x2="48" y2="64"/><line x1="224" y1="48" x2="208" y2="64"/>',

            // Archivo / Caja
            'archive' => '<rect x="32" y="48" width="192" height="48" rx="8"/><path d="M48,96V192a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V96"/><line x1="104" y1="136" x2="152" y2="136"/>',

            // Papelera
            'trash' => '<line x1="216" y1="56" x2="40" y2="56"/><line x1="104" y1="104" x2="104" y2="168"/><line x1="152" y1="104" x2="152" y2="168"/><path d="M200,56V208a8,8,0,0,1-8,8H64a8,8,0,0,1-8-8V56"/><path d="M168,56V40a16,16,0,0,0-16-16H104A16,16,0,0,0,88,40V56"/>',

            // Flechas en sentido horario / Recargar
            'arrows-clockwise' => '<polyline points="176.01 40 224.01 40 224.01 88"/><polyline points="79.99 216 31.99 216 31.99 168"/><path d="M190.2,89.8a88,88,0,1,0,1.9,80.4"/>',

            // Círculo de verificación / Éxito
            'check-circle' => '<polyline points="88 136 112 160 168 104"/><circle cx="128" cy="128" r="96"/>',

            // Usuario
            'user' => '<circle cx="128" cy="96" r="40"/><path d="M32,216c0-44.18,42.98-80,96-80s96,35.82,96,80"/>',

            // Birrete de graduación / Educación
            'graduation-cap' => '<polygon points="224 96 128 48 32 96 128 144 224 96"/><path d="M64,112v56c0,28,28.65,48,64,48s64-20,64-48V112"/><line x1="224" y1="96" x2="224" y2="160"/>',

            // Sobre / Correo
            'envelope' => '<rect x="32" y="56" width="192" height="144" rx="8"/><polyline points="224 56 128 136 32 56"/>',

            // Periódico / Noticias
            'newspaper' => '<path d="M24,200a16,16,0,0,0,16,16H200a24,24,0,0,0,24-24V48a8,8,0,0,0-8-8H56A16,16,0,0,0,40,56V192a8,8,0,0,1-16,8Z"/><line x1="80" y1="88" x2="176" y2="88"/><line x1="80" y1="120" x2="176" y2="120"/><line x1="80" y1="152" x2="136" y2="152"/>',

            // Escudo de seguridad / Verificado
            'shield-check' => '<path d="M208,48H48A8,8,0,0,0,40,56v64c0,64,88,104,88,104s88-40,88-104V56A8,8,0,0,0,208,48Z"/><polyline points="88 120 112 144 168 88"/>',

            // Deslizadores / Filtro o Configuración
            'sliders' => '<line x1="40" y1="72" x2="112" y2="72"/><line x1="144" y1="72" x2="216" y2="72"/><circle cx="128" cy="72" r="16"/><line x1="40" y1="128" x2="160" y2="128"/><line x1="192" y1="128" x2="216" y2="128"/><circle cx="176" cy="128" r="16"/><line x1="40" y1="184" x2="64" y2="184"/><line x1="96" y1="184" x2="216" y2="184"/><circle cx="80" cy="184" r="16"/>',

            // Embudo / Filtro
            'funnel' => '<polygon points="32 48 224 48 152 136 152 208 104 224 104 136 32 48"/>',

            // Descarga de archivos
            'download-simple' => '<polyline points="88 136 128 176 168 136"/><line x1="128" y1="40" x2="128" y2="176"/><path d="M216,168v40a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V168"/>',

            // Más / Agregar
            'plus-circle' => '<circle cx="128" cy="128" r="96"/><line x1="88" y1="128" x2="168" y2="128"/><line x1="128" y1="88" x2="128" y2="168"/>',

            // Código / Desarrollo de Software
            'code' => '<polyline points="64 88 16 128 64 168"/><polyline points="192 88 240 128 192 168"/><line x1="160" y1="40" x2="96" y2="216"/>',

            // Calculadora / Contabilidad
            'calculator' => '<rect x="40" y="32" width="176" height="192" rx="16"/><line x1="40" y1="96" x2="216" y2="96"/><line x1="80" y1="64" x2="176" y2="64"/><circle cx="88" cy="136" r="10"/><circle cx="128" cy="136" r="10"/><circle cx="168" cy="136" r="10"/><circle cx="88" cy="176" r="10"/><circle cx="128" cy="176" r="10"/><circle cx="168" cy="176" r="10"/>',

            // Microchip / Electrónica Industrial
            'microchip' => '<rect x="64" y="64" width="128" height="128" rx="8"/><rect x="96" y="96" width="64" height="64"/><line x1="96" y1="32" x2="96" y2="64"/><line x1="128" y1="32" x2="128" y2="64"/><line x1="160" y1="32" x2="160" y2="64"/><line x1="96" y1="192" x2="96" y2="224"/><line x1="128" y1="192" x2="128" y2="224"/><line x1="160" y1="192" x2="160" y2="224"/><line x1="32" y1="96" x2="64" y2="96"/><line x1="32" y1="128" x2="64" y2="128"/><line x1="32" y1="160" x2="64" y2="160"/><line x1="192" y1="96" x2="224" y2="96"/><line x1="192" y1="128" x2="224" y2="128"/><line x1="192" y1="160" x2="224" y2="160"/>',

            // Maletín / Secretariado Ejecutivo
            'briefcase' => '<rect x="32" y="80" width="192" height="128" rx="8"/><path d="M168,80V56a16,16,0,0,0-16-16H104A16,16,0,0,0,88,56V80"/><line x1="32" y1="136" x2="224" y2="136"/><polyline points="112 136 112 152 144 152 144 136"/>',

            // Engranajes / Mecánica de Precisión
            'gears' => '<circle cx="96" cy="104" r="28"/><circle cx="168" cy="168" r="24"/><path d="M96,48V60m0,88v12M48,104H60m88,0h12m-80.49-33.94l8.49,8.49m56.57,56.57,8.48,8.49m0-73.55-8.48,8.49m-56.57,56.57-8.49,8.48"/><path d="M168,124v10m0,68v10M124,168h10m68,0h10m-63.64-26.87,7.07,7.07m45.26,45.26,7.07,7.07m0-59.4-7.07,7.07m-45.26,45.26-7.07,7.07"/>',

            // Teléfono
            'phone' => '<path d="M92.48,79.52l17.75-17.75a16,16,0,0,1,22.63,0l19.51,19.51a16,16,0,0,1,0,22.63L138.83,117.45a72,72,0,0,0,39.72,39.72l13.54-13.54a16,16,0,0,1,22.63,0l19.51,19.51a16,16,0,0,1,0,22.63l-17.75,17.75c-24.16,24.16-72.3,10.63-113.82-30.89S-4.41,89.87,19.75,65.71l17.75-17.75a16,16,0,0,1,22.63,0L79.64,67.47a16,16,0,0,1,0,22.63Z"/>',

            // Ubicación / Mapa
            'location-dot' => '<path d="M128,24A72,72,0,0,0,56,96c0,56,72,136,72,136s72-80,72-136A72,72,0,0,0,128,24Z"/><circle cx="128" cy="96" r="24"/>',

            // Ojo / Mostrar contraseña
            'eye' => '<path d="M128,56C48,56,16,128,16,128s32,72,112,72,112-72,112-72S208,56,128,56Z"/><circle cx="128" cy="128" r="32"/>',

            // Ojo tachado / Ocultar
            'eye-slash' => '<line x1="48" y1="40" x2="208" y2="216"/><path d="M154.91,157.6A32,32,0,0,1,101.4,104.09"/><path d="M128,56C48,56,16,128,16,128a192.51,192.51,0,0,0,45.16,49.27"/><path d="M96.44,60.85A129.83,129.83,0,0,1,128,56c80,0,112,72,112,72a192.35,192.35,0,0,1-30.7,39.63"/>',

            // Flecha derecha
            'arrow-right' => '<line x1="40" y1="128" x2="216" y2="128"/><polyline points="144 56 216 128 144 200"/>',

            // Flecha izquierda
            'arrow-left' => '<line x1="216" y1="128" x2="40" y2="128"/><polyline points="112 56 40 128 112 200"/>',

            // Barras menú móvil (Hamburguesa)
            'bars' => '<line x1="40" y1="64" x2="216" y2="64"/><line x1="40" y1="128" x2="216" y2="128"/><line x1="40" y1="192" x2="216" y2="192"/>',

            // Cerrar menú / X
            'xmark' => '<line x1="200" y1="56" x2="56" y2="200"/><line x1="200" y1="200" x2="56" y2="56"/>',

            // Lupa / Búsqueda
            'magnifying-glass' => '<circle cx="112" cy="112" r="72"/><line x1="162.91" y1="162.91" x2="216" y2="216"/>',

            // Casa / Inicio
            'house' => '<path d="M216,115.34V208a8,8,0,0,1-8,8H152V152H104v64H48a8,8,0,0,1-8-8V115.34a8,8,0,0,1,2.83-6.14l72-60a8,8,0,0,1,10.34,0l72,60A8,8,0,0,1,216,115.34Z"/>',

            // Velocímetro / Dashboard
            'gauge-high' => '<path d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,168a72,72,0,1,1,72-72A72.08,72.08,0,0,1,128,208Z"/><circle cx="128" cy="128" r="16"/><line x1="139.31" y1="116.69" x2="168" y2="88"/>',

            // Tarjeta de identidad
            'id-card' => '<rect x="32" y="48" width="192" height="160" rx="8"/><circle cx="96" cy="120" r="24"/><path d="M64,176a32,32,0,0,1,64,0"/><line x1="144" y1="104" x2="184" y2="104"/><line x1="144" y1="136" x2="184" y2="136"/>',

            // Usuarios / Ajustes de usuarios
            'users-gear' => '<circle cx="88" cy="104" r="32"/><path d="M24,208c0-35.35,28.65-64,64-64s64,28.65,64,64"/><circle cx="184" cy="96" r="24"/><path d="M152,148.6a56,56,0,0,1,56,0"/><circle cx="208" cy="192" r="14"/><path d="M208,166v6m0,40v6m-26-26h6m40,0h6m-32.87-18.49l4.24,4.25m28.29,28.28l4.24,4.24m0-36.76l-4.24,4.24m-28.29,28.29l-4.24,4.24"/>',

            // Mensajes / Comentarios
            'comments' => '<path d="M80,160V192a8,8,0,0,0,13.15,6.24L128,170.67H192a16,16,0,0,0,16-16V64a16,16,0,0,0-16-16H48A16,16,0,0,0,32,64V144a16,16,0,0,0,16,16H80Z"/><path d="M176,170.67h16a16,16,0,0,0,16-16V96"/>',

            // Calendario
            'calendar' => '<rect x="40" y="48" width="176" height="160" rx="8"/><line x1="176" y1="24" x2="176" y2="48"/><line x1="80" y1="24" x2="80" y2="48"/><line x1="40" y1="88" x2="216" y2="88"/>',

            // Candado
            'lock' => '<rect x="40" y="88" width="176" height="128" rx="8"/><path d="M88,88V56a40,40,0,0,1,80,0V88"/>',

            // Iniciar sesión
            'arrow-right-to-bracket' => '<line x1="32" y1="128" x2="152" y2="128"/><polyline points="104 80 152 128 104 176"/><path d="M120,48h72a16,16,0,0,1,16,16V192a16,16,0,0,1-16,16H120"/>',

            // Cerrar sesión
            'right-from-bracket' => '<line x1="176" y1="128" x2="56" y2="128"/><polyline points="104 80 56 128 104 176"/><path d="M136,48h72a16,16,0,0,1,16,16V192a16,16,0,0,1-16,16H136"/>',

            // Enlace externo
            'arrow-up-right-from-square' => '<polyline points="152 40 216 40 216 104"/><line x1="104" y1="152" x2="216" y2="40"/><path d="M184,136v64a16,16,0,0,1-16,16H56a16,16,0,0,1-16-16V88A16,16,0,0,1,56,72h64"/>',

            // Exclamación circular / Alerta
            'circle-exclamation' => '<circle cx="128" cy="128" r="96"/><line x1="128" y1="80" x2="128" y2="136"/><circle cx="128" cy="172" r="8"/>',

            // Firma de documento / Matrícula
            'file-signature' => '<path d="M168,224H56a16,16,0,0,1-16-16V48A16,16,0,0,1,56,32h96l64,64V128"/><polyline points="152 32 152 96 216 96"/><path d="M96,184c24-16,40-8,64-16s24-24,32-24"/><line x1="88" y1="144" x2="144" y2="144"/>',

            // Premio / Galardón / Excelencia
            'award' => '<circle cx="128" cy="96" r="56"/><polyline points="88 136 64 224 128 192 192 224 168 136"/>'
        ];

        $svgBody = $icons[$name] ?? $icons['graduation-cap'];

        // Atributos predeterminados con estándar Iconimate
        $defaultAttrs = [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 256 256',
            'fill' => 'none',
            'stroke' => 'currentColor',
            'stroke-width' => '16',
            'stroke-linecap' => 'round',
            'stroke-linejoin' => 'round',
            'data-iconimate' => $name,
            'aria-hidden' => isset($attrs['aria-label']) ? 'false' : 'true',
            'role' => isset($attrs['aria-label']) ? 'img' : 'presentation',
            'class' => 'iconimate-icon'
        ];

        // Mezclar clases
        if (isset($attrs['class'])) {
            $defaultAttrs['class'] .= ' ' . trim((string)$attrs['class']);
            unset($attrs['class']);
        }

        $merged = array_merge($defaultAttrs, $attrs);

        $attrString = '';
        foreach ($merged as $k => $v) {
            $attrString .= ' ' . htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<span class="iconimate">' . "<svg{$attrString}>{$svgBody}</svg>" . '</span>';
    }
}

