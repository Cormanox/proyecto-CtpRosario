<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga de configuración global, conexión y funciones auxiliares
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/icons.php';

// Normalización de la URI actual para determinar enlace activo
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$colegioNombre = function_exists('obtener_config') ? obtener_config('colegio_nombre', 'CTP Rosario') : 'CTP Rosario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . " - " . e($colegioNombre) : e($colegioNombre) . " - Portal Web Institucional"; ?></title>
    <!-- Estilos CSS centralizados (Sistema de Diseño UI/UX Pro Max) -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
</head>
<body>

    <!-- Header Principal con Efecto Glassmorphism y Desenfoque Suave -->
    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="<?= url('public/index.php'); ?>">
                    <img src="<?= url('assets/img/escudoctp.png'); ?>" alt="Escudo <?= e($colegioNombre); ?>" class="logo-img" width="52" height="52">
                    <div class="logo-text">
                        <h1><?= e($colegioNombre); ?></h1>
                        <span>Colegio Técnico Profesional</span>
                    </div>
                </a>
            </div>

            <!-- Botón Hamburguesa Accesible para Dispositivos Móviles -->
            <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Abrir menú de navegación" aria-expanded="false" onclick="toggleMobileNav()">
                <span id="menuToggleIconWrap"><?= icon('bars', ['class' => 'icon-md']); ?></span>
            </button>

            <!-- Navegación Institucional -->
            <nav class="main-nav" id="mainNav" aria-label="Navegación institucional">
                <ul>
                    <li>
                        <a href="<?= url('public/index.php'); ?>" class="<?= (str_ends_with($currentUri, 'public/index.php') || $currentUri === url('') || $currentUri === url('/') || str_ends_with($currentUri, '/proyecto-CtpRosario/')) ? 'active' : ''; ?>">
                            <?= icon('house', ['class' => 'icon-sm']); ?> Inicio
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/oferta.php'); ?>" class="<?= str_contains($currentUri, 'oferta.php') ? 'active' : ''; ?>">
                            <?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/prematricula.php'); ?>" class="btn-highlight <?= str_contains($currentUri, 'prematricula.php') ? 'active' : ''; ?>">
                            <?= icon('file-signature', ['class' => 'icon-sm']); ?> Pre-Matrícula
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/contacto.php'); ?>" class="<?= str_contains($currentUri, 'contacto.php') ? 'active' : ''; ?>">
                            <?= icon('envelope', ['class' => 'icon-sm']); ?> Contacto
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="<?= url('admin/index.php'); ?>" class="btn-login">
                                <?= icon('gauge-high', ['class' => 'icon-sm']); ?> Panel Admin
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= url('login.php'); ?>" class="btn-login">
                                <?= icon('lock', ['class' => 'icon-sm']); ?> Acceso Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        function toggleMobileNav() {
            const nav = document.getElementById('mainNav');
            const wrap = document.getElementById('menuToggleIconWrap');
            const btn = document.getElementById('mobileMenuBtn');
            const isOpen = nav.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                wrap.innerHTML = '<?= addslashes(icon('xmark', ['class' => 'icon-md'])); ?>';
            } else {
                wrap.innerHTML = '<?= addslashes(icon('bars', ['class' => 'icon-md'])); ?>';
            }
        }
    </script>
