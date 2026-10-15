<?php
// Título dinámico para la pestaña
$pageTitle = "Inicio";

// Incluimos la conexión a la base de datos y el encabezado
require_once '../config/db.php';
require_once '../includes/header.php';


// Consultar las últimas 3 noticias publicadas
$noticias = [];
try {
    $stmt = $pdo->query("SELECT * FROM noticias WHERE estado = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 3");
    $noticias = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si hay error en la consulta se maneja de forma silenciosa para el visitante
    $noticias = [];
}
?>
<link rel="stylesheet" href="../assets/css/styles.css">
<!-- Sección Hero / Bienvenda -->
<section class="hero">
    <div class="container hero-content">
        <h2>Bienvenidos al Colegio Técnico Profesional Rosario</h2>
        <p>Innovación, educación técnica de calidad y oportunidades para construir tu futuro profesional.</p>
        <div class="hero-buttons">
            <a href="prematricula.php" class="btn btn-primary">Solicitar Pre-Matrícula</a>
            <a href="oferta.php" class="btn btn-secondary">Conocer Especialidades</a>
        </div>
    </div>
</section>

<!-- Sección de Noticas / Comunicados Institucionales -->
<section class="news-section">
    <div class="container">
        <h2 class="section-title">Noticias y Comunicados</h2>
        <div class="news-grid">
            <?php if (!empty($noticias)): ?>
                <?php foreach ($noticias as $noticia): ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?php echo !empty($noticia['imagen_url']) ? htmlspecialchars($noticia['imagen_url']) : 'https://via.placeholder.com/400x200?text=CTP+Rosario'; ?>" alt="Noticia">
                        </div>
                        <div class="news-body">
                            <span class="news-date"><i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?></span>
                            <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($noticia['contenido'], 0, 120)) . '...'; ?></p>
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
        <h2 class="section-title">Servicios Digitales</h2>
        <div class="services-grid">
            <div class="service-card">
                <i class="fa-solid fa-file-signature service-icon"></i>
                <h3>Pre-Matrícula Digital</h3>
                <p>Realiza tu solicitud de admisión en línea de forma rápida y sencilla para el próximo periodo lectivo.</p>
                <a href="prematricula.php" class="service-link">Ir a Pre-Matrícula &rarr;</a>
            </div>
            <div class="service-card">
                <i class="fa-solid fa-graduation-cap service-icon"></i>
                <h3>Oferta Especialidades</h3>
                <p>Explora nuestras especialidades técnicas y elige la orientación laboral que más te apasiona.</p>
                <a href="oferta.php" class="service-link">Ver Especialidades &rarr;</a>
            </div>
            <div class="service-card">
                <i class="fa-solid fa-comments service-icon"></i>
                <h3>Consultas y Contacto</h3>
                <p>Envía tus dudas o comentarios a la administración del colegio mediante nuestro formulario de atención.</p>
                <a href="contacto.php" class="service-link">Enviar Consulta &rarr;</a>
            </div>
        </div>
    </div>
</section>

<?php 
// Incluimos el pie de página
require_once '../includes/footer.php'; 
?>