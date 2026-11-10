<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/icons.php';

$telOficial   = function_exists('obtener_config') ? obtener_config('telefono_oficial', '+506 2451-0000') : '+506 2451-0000';
$correoOficial = function_exists('obtener_config') ? obtener_config('correo_oficial', 'info@ctprosario.ed.cr') : 'info@ctprosario.ed.cr';
$dirOficial   = function_exists('obtener_config') ? obtener_config('direccion_oficial', 'Rosario de Naranjo, Alajuela, Costa Rica') : 'Rosario de Naranjo, Alajuela, Costa Rica';
$colegioNombre = function_exists('obtener_config') ? obtener_config('colegio_nombre', 'Colegio Técnico Profesional Rosario') : 'Colegio Técnico Profesional Rosario';
?>
    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-info">
                <h3>CTP Rosario</h3>
                <p>Formando profesionales técnicos con excelencia, valores y visión de futuro para la comunidad.</p>
            </div>
            <div class="footer-links">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="<?= url('public/index.php'); ?>">Inicio</a></li>
                    <li><a href="<?= url('public/oferta.php'); ?>">Oferta Académica</a></li>
                    <li><a href="<?= url('public/prematricula.php'); ?>">Proceso de Pre-Matrícula</a></li>
                    <li><a href="<?= url('public/contacto.php'); ?>">Consultas</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacto Institucional</h4>
                <p><?= icon('location-dot', ['class' => 'icon-sm']); ?> <?= e($dirOficial); ?></p>
                <p><?= icon('phone', ['class' => 'icon-sm']); ?> <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $telOficial)); ?>" style="color: inherit;"><?= e($telOficial); ?></a></p>
                <p><?= icon('envelope', ['class' => 'icon-sm']); ?> <a href="mailto:<?= e($correoOficial); ?>" style="color: inherit;"><?= e($correoOficial); ?></a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y'); ?> <?= e($colegioNombre); ?>. Todos los derechos reservados. Proyecto Académico de Desarrollo Web.</p>
            </div>
        </div>
    </footer>

    <!-- Controlador de Animaciones Anime.js v4 & Microinteracciones Iconimate -->
    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>
