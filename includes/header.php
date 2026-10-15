<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - CTP Rosario" : "CTP Rosario - Portal Web Institucional"; ?></title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="/ctp_rosario/assets/css/styles.css">
    <!-- FontAwesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header Principal -->
    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="/ctp_rosario/public/index.php">
                    <img src="/ctp_rosario/assets/img/logo.png" alt="Logo CTP Rosario" class="logo-img" onerror="this.src='https://via.placeholder.com/60x60?text=CTP';">
                    <div class="logo-text">
                        <h1>CTP Rosario</h1>
                        <span>Colegio Técnico Profesional</span>
                    </div>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/ctp_rosario/public/index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Inicio</a></li>
                    <li><a href="/ctp_rosario/public/oferta.php" class="<?php echo ($currentPage == 'oferta.php') ? 'active' : ''; ?>">Especialidades</a></li>
                    <li><a href="/ctp_rosario/public/prematricula.php" class="btn-highlight <?php echo ($currentPage == 'prematricula.php') ? 'active' : ''; ?>">Pre-Matrícula</a></li>
                    <li><a href="/ctp_rosario/public/contacto.php" class="<?php echo ($currentPage == 'contacto.php') ? 'active' : ''; ?>">Contacto</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/ctp_rosario/admin/index.php" class="btn-login"><i class="fa-solid fa-gauge-high"></i> Panel Admin</a></li>
                    <?php else: ?>
                        <li><a href="/ctp_rosario/login.php" class="btn-login"><i class="fa-solid fa-user-lock"></i> Acceso Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>