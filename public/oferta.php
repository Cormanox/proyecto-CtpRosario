<?php
declare(strict_types=1);

$pageTitle = "Oferta Académica y Especialidades";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h2>Oferta Educativa Técnica</h2>
        <p>Formación integral de alta calidad para responder a las demandas del sector productivo actual.</p>
    </div>
</div>

<main class="container">
    <div class="specialty-grid">

        <!-- Desarrollo de Software -->
        <article class="specialty-card">
            <div class="specialty-card-body">
                <div class="specialty-icon">
                    <i class="fa-solid fa-code"></i>
                </div>
                <h3>Informática en Desarrollo de Software</h3>
                <p>Aprende diseño, programación y mantenimiento de aplicaciones web, móviles y bases de datos con los estándares y lenguajes modernos de la industria.</p>
                <ul class="specialty-features">
                    <li><i class="fa-solid fa-check"></i> Programación web (HTML, CSS, JS, PHP, Python)</li>
                    <li><i class="fa-solid fa-check"></i> Gestión de Bases de Datos relacionales (MySQL, SQL)</li>
                    <li><i class="fa-solid fa-check"></i> Metodologías ágiles y proyectos colaborativos</li>
                </ul>
                <a href="<?= url('public/prematricula.php') . '?' . http_build_query(['especialidad' => 'Desarrollo de Software']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Elegir Especialidad</a>
            </div>
        </article>

        <!-- Contabilidad y Finanzas -->
        <article class="specialty-card">
            <div class="specialty-card-body">
                <div class="specialty-icon">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <h3>Contabilidad y Finanzas</h3>
                <p>Domina los principios contables, gestión tributaria, auditoría básica, nómina y uso de software contable especializado para empresas públicas y privadas.</p>
                <ul class="specialty-features">
                    <li><i class="fa-solid fa-check"></i> Ciclo contable completo y estados financieros</li>
                    <li><i class="fa-solid fa-check"></i> Legislación tributaria y laboral vigente</li>
                    <li><i class="fa-solid fa-check"></i> Sistemas automatizados de gestión contable</li>
                </ul>
                <a href="<?= url('public/prematricula.php') . '?' . http_build_query(['especialidad' => 'Contabilidad y Finanzas']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Elegir Especialidad</a>
            </div>
        </article>

        <!-- Electrónica Industrial -->
        <article class="specialty-card">
            <div class="specialty-card-body">
                <div class="specialty-icon">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <h3>Electrónica Industrial</h3>
                <p>Capacitación en automatización, control de motores, sistemas embebidos, robótica, sensores y mantenimiento preventivo de líneas industriales.</p>
                <ul class="specialty-features">
                    <li><i class="fa-solid fa-check"></i> Controladores lógicos programables (PLC)</li>
                    <li><i class="fa-solid fa-check"></i> Circuitos digitales y electrónica de potencia</li>
                    <li><i class="fa-solid fa-check"></i> Diagnóstico y reparación de equipo electrónico</li>
                </ul>
                <a href="<?= url('public/prematricula.php') . '?' . http_build_query(['especialidad' => 'Electronica Industrial']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Elegir Especialidad</a>
            </div>
        </article>

        <!-- Secretariado Ejecutivo -->
        <article class="specialty-card">
            <div class="specialty-card-body">
                <div class="specialty-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h3>Secretariado Ejecutivo</h3>
                <p>Desarrolla habilidades en administración de oficinas modernas, comunicación corporativa, servicio al cliente bilingüe y redacción comercial ejecutiva.</p>
                <ul class="specialty-features">
                    <li><i class="fa-solid fa-check"></i> Gestión documental y ofimática avanzada</li>
                    <li><i class="fa-solid fa-check"></i> Protocolo empresarial y relaciones públicas</li>
                    <li><i class="fa-solid fa-check"></i> Manejo de agendas y organización de eventos</li>
                </ul>
                <a href="<?= url('public/prematricula.php') . '?' . http_build_query(['especialidad' => 'Secretariado Ejecutivo']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Elegir Especialidad</a>
            </div>
        </article>

        <!-- Mecánica de Precisión -->
        <article class="specialty-card">
            <div class="specialty-card-body">
                <div class="specialty-icon">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <h3>Mecánica de Precisión</h3>
                <p>Formación en mecanizado con tornos, fresadoras, diseño asistido por computadora (CAD/CAM) y control numérico computarizado (CNC).</p>
                <ul class="specialty-features">
                    <li><i class="fa-solid fa-check"></i> Mecanizado convencional y CNC</li>
                    <li><i class="fa-solid fa-check"></i> Metrología y control de calidad dimensional</li>
                    <li><i class="fa-solid fa-check"></i> Interpretación y elaboración de planos técnicos</li>
                </ul>
                <a href="<?= url('public/prematricula.php') . '?' . http_build_query(['especialidad' => 'Mecanica de Precision']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Elegir Especialidad</a>
            </div>
        </article>

    </div>

    <!-- Beneficios Institucionales -->
    <section class="form-card" style="margin-top: 30px;">
        <h3 style="color: #003366; margin-bottom: 15px; font-size: 1.4rem;"><i class="fa-solid fa-award"></i> ¿Por qué estudiar en el CTP Rosario?</h3>
        <p style="color: #475569; margin-bottom: 20px;">Nuestro modelo educativo combina la formación académica de secundaria con pasantías y prácticas profesionales en empresas líderes de la región, facilitando la inserción inmediata al mercado laboral y la continuidad de estudios universitarios.</p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="<?= url('public/prematricula.php'); ?>" class="btn btn-primary"><i class="fa-solid fa-file-signature"></i> Iniciar Proceso de Pre-Matrícula</a>
            <a href="<?= url('public/contacto.php'); ?>" class="btn btn-secondary" style="color: #003366; border-color: #003366;"><i class="fa-solid fa-envelope"></i> ¿Tienes preguntas? Escríbenos</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
