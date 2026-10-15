<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
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
                <p><i class="fa-solid fa-location-dot"></i> Rosario, Costa Rica</p>
                <p><i class="fa-solid fa-phone"></i> +506 2000-0000</p>
                <p><i class="fa-solid fa-envelope"></i> info@ctprosario.ed.cr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y'); ?> CTP Rosario. Todos los derechos reservados. Proyecto Académico de Desarrollo Web.</p>
            </div>
        </div>
    </footer>

</body>
</html>