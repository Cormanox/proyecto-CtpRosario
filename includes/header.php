<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga de configuración global, conexión y funciones auxiliares
require_once __DIR__ . '/../config/db.php';

// Normalización de la URI actual para determinar enlace activo
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . " - CTP Rosario" : "CTP Rosario - Portal Web Institucional"; ?></title>
    <!-- Estilos CSS centralizados -->
    <link rel="stylesheet" href="<?= url('assets/css/styles.css'); ?>">
    <!-- FontAwesome para iconografía -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>

    <!-- Header Principal -->
    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="<?= url('public/index.php'); ?>">
                    <img src="<?= url('assets/img/escudoctp.png'); ?>" alt="Escudo CTP Rosario" class="logo-img">
                    <div class="logo-text">
                        <h1>CTP Rosario</h1>
                        <span>Colegio Técnico Profesional</span>
                    </div>
                </a>
            </div>
            <nav class="main-nav" aria-label="Navegación institucional">
                <ul>
                    <li>
                        <a href="<?= url('public/index.php'); ?>" class="<?= (str_ends_with($currentUri, 'public/index.php') || $currentUri === url('') || $currentUri === url('/') || str_ends_with($currentUri, '/proyecto-CtpRosario/')) ? 'active' : ''; ?>">
                            Inicio
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/oferta.php'); ?>" class="<?= str_contains($currentUri, 'oferta.php') ? 'active' : ''; ?>">
                            Especialidades
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/prematricula.php'); ?>" class="btn-highlight <?= str_contains($currentUri, 'prematricula.php') ? 'active' : ''; ?>">
                            Pre-Matrícula
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('public/contacto.php'); ?>" class="<?= str_contains($currentUri, 'contacto.php') ? 'active' : ''; ?>">
                            Contacto
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="<?= url('admin/index.php'); ?>" class="btn-login">
                                <i class="fa-solid fa-gauge-high"></i> Panel Admin
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= url('login.php'); ?>" class="btn-login">
                                <i class="fa-solid fa-user-lock"></i> Acceso Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>