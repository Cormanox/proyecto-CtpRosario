<?php
declare(strict_types=1);

// Título dinámico para la pestaña
$pageTitle = "Inicio";

// Inclusión de la conexión a la base de datos y la cabecera
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Cargar configuraciones del sitio
$heroTitulo    = obtener_config('hero_titulo', 'Bienvenidos al Colegio Técnico Profesional Rosario');
$heroSubtitulo = obtener_config('hero_subtitulo', 'Innovación, educación técnica de calidad y oportunidades para construir tu futuro profesional.');
$heroCtaMatricula = obtener_config('hero_cta_matricula', 'Solicitar Pre-Matrícula');
$heroCtaOferta    = obtener_config('hero_cta_oferta', 'Conocer Especialidades');
$prematriculaAbierta = obtener_config('prematricula_abierta', '1') === '1';
$periodoLectivo   = obtener_config('periodo_lectivo_actual', '2027');
$mision           = obtener_config('mision', '');
$vision           = obtener_config('vision', '');

// Consultar las últimas 3 noticias publicadas
$noticias = [];
try {
    $stmt = $pdo->prepare("SELECT id, titulo, contenido, imagen_url, fecha_publicacion 
                           FROM noticias 
                           WHERE estado = :estado 
                           ORDER BY fecha_publicacion DESC 
                           LIMIT 3");
    $stmt->execute([':estado' => 'publicado']);
    $noticias = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Public Index Error] " . $e->getMessage());
    $noticias = [];
}
?>

<!-- Sección Hero / Bienvenida -->
<section class="hero" style="background-image: linear-gradient(135deg, rgba(135, 26, 47, 0.88) 0%, rgba(92, 17, 32, 0.92) 100%), url('<?= url('assets/img/heroctp.webp'); ?>');">
    <div class="container hero-content">
        <h2><?= e($heroTitulo); ?></h2>
        <p><?= e($heroSubtitulo); ?></p>
        <div class="hero-buttons">
            <a href="<?= url('public/prematricula.php'); ?>" class="btn btn-primary">
                <?= icon('file-signature', ['class' => 'icon-sm']); ?> <?= e($heroCtaMatricula); ?>
            </a>
            <a href="<?= url('public/oferta.php'); ?>" class="btn btn-secondary">
                <?= icon('graduation-cap', ['class' => 'icon-sm']); ?> <?= e($heroCtaOferta); ?>
            </a>
        </div>
        <div style="margin-top: 20px;">
            <span class="badge <?= $prematriculaAbierta ? 'badge-activo' : 'badge-editor'; ?>" style="font-size: 0.88rem; padding: 6px 14px; background: rgba(255, 255, 255, 0.15); color: #ffffff; border-color: rgba(255, 255, 255, 0.3);">
                <?= $prematriculaAbierta ? icon('check-circle', ['class' => 'icon-sm']) . " Convocatoria de Pre-Matrícula {$periodoLectivo} Abierta" : icon('archive', ['class' => 'icon-sm']) . " Convocatoria {$periodoLectivo} Cerrada Temporalmente"; ?>
            </span>
        </div>
    </div>
</section>

<!-- Sección Misión y Visión Institucionales -->
<?php if (!empty($mision) || !empty($vision)): ?>
<section style="background: var(--color-bg-surface); padding: 45px 0; border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <?php if (!empty($mision)): ?>
                <div style="background: var(--color-bg-page); border: 1px solid var(--color-border); border-left: 4px solid var(--color-primary); border-radius: var(--radius-md); padding: 24px 28px;">
                    <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.25rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <?= icon('shield-check', ['class' => 'icon-md']); ?> Misión Institucional
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        <?= e($mision); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($vision)): ?>
                <div style="background: var(--color-bg-page); border: 1px solid var(--color-border); border-left: 4px solid var(--color-secondary); border-radius: var(--radius-md); padding: 24px 28px;">
                    <h3 style="color: var(--color-secondary); font-weight: 800; font-size: 1.25rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <?= icon('award', ['class' => 'icon-md']); ?> Visión Institucional
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        <?= e($vision); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Sección de Noticias / Comunicados Institucionales -->
<section class="news-section">
    <div class="container">
        <h2 class="section-title">Noticias y Comunicados Oficiales</h2>
        <div class="news-grid">
            <?php if (!empty($noticias)): ?>
                <?php foreach ($noticias as $noticia): ?>
                    <?php 
                        $imgUrl = !empty($noticia['imagen_url']) ? $noticia['imagen_url'] : 'https://via.placeholder.com/400x200?text=CTP+Rosario';
                        $fechaFormateada = date('d/m/Y', strtotime($noticia['fecha_publicacion']));
                        $resumen = mb_substr($noticia['contenido'] ?? '', 0, 130, 'UTF-8') . '...';
                    ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?= e($imgUrl); ?>" alt="<?= e($noticia['titulo'] ?? 'Noticia'); ?>" loading="lazy">
                        </div>
                        <div class="news-body">
                            <span class="news-date">
                                <?= icon('calendar', ['class' => 'icon-sm']); ?> <?= e($fechaFormateada); ?>
                            </span>
                            <h3><?= e($noticia['titulo'] ?? ''); ?></h3>
                            <p><?= e($resumen); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-news">
                    <p>No hay noticias publicadas en este momento. Vuelve a consultar pronto.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Sección de Accesos Rápidos / Servicios -->
<section class="services-section">
    <div class="container">
        <h2 class="section-title">Servicios Digitales del Colegio</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <?= icon('file-signature', ['class' => 'icon-xl']); ?>
                </div>
                <h3>Pre-Matrícula Digital</h3>
                <p>Realiza tu solicitud de admisión en línea de forma rápida y sencilla para el próximo periodo lectivo <?= e($periodoLectivo); ?>.</p>
                <a href="<?= url('public/prematricula.php'); ?>" class="service-link">
                    Ir a Pre-Matrícula <?= icon('arrow-right', ['class' => 'icon-sm']); ?>
                </a>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <?= icon('graduation-cap', ['class' => 'icon-xl']); ?>
                </div>
                <h3>Oferta de Especialidades</h3>
                <p>Explora nuestras carreras técnicas y descubre las habilidades de alta demanda laboral que desarrollarás.</p>
                <a href="<?= url('public/oferta.php'); ?>" class="service-link">
                    Ver Especialidades <?= icon('arrow-right', ['class' => 'icon-sm']); ?>
                </a>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <?= icon('comments', ['class' => 'icon-xl']); ?>
                </div>
                <h3>Consultas y Contacto</h3>
                <p>Envía tus dudas o comentarios a la administración del colegio mediante nuestro formulario de atención directa.</p>
                <a href="<?= url('public/contacto.php'); ?>" class="service-link">
                    Enviar Consulta <?= icon('arrow-right', ['class' => 'icon-sm']); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php 
// Inclusión del pie de página institucional
require_once __DIR__ . '/../includes/footer.php'; 
?>
