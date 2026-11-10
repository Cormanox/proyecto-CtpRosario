<?php
declare(strict_types=1);

$pageTitle = "Oferta Académica y Especialidades Técnicas";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Consultar solo las especialidades activas ordenadas
try {
    $stmt = $pdo->query("SELECT * FROM especialidades WHERE activa = 1 ORDER BY orden ASC, nombre ASC");
    $especialidades = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Public Oferta Error] " . $e->getMessage());
    $especialidades = [];
}
?>

<div class="page-header">
    <div class="container">
        <h2>Oferta Educativa Técnica</h2>
        <p>Formación integral de alta calidad para responder a las demandas del sector productivo actual.</p>
    </div>
</div>

<main class="container" style="margin-top: 30px; margin-bottom: 60px;">

    <!-- Buscador / Filtro Rápido de Especialidades -->
    <div style="background: var(--color-bg-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 18px 22px; margin-bottom: 30px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 10px; width: 100%; max-width: 480px;">
            <span style="color: var(--color-text-muted);">
                <?= icon('magnifying-glass', ['class' => 'icon-md']); ?>
            </span>
            <input type="text" id="filtroEspecialidades" placeholder="Buscar especialidad por nombre, tecnología o competencia..." class="filter-control" style="width: 100%;" oninput="filtrarEspecialidades()">
        </div>
        <div style="color: var(--color-text-muted); font-size: 0.9rem;">
            Mostrando <strong id="contadorEspecialidades"><?= count($especialidades); ?></strong> especialidad(es) activa(s)
        </div>
    </div>

    <!-- Catálogo de Tarjetas Especialidad -->
    <div class="specialty-grid" id="gridEspecialidades">
        <?php if (!empty($especialidades)): ?>
            <?php foreach ($especialidades as $esp): ?>
                <?php 
                    $competencias = array_filter(array_map('trim', explode("\n", $esp['perfil_egresado'] ?? '')));
                    $iconoNombre = !empty($esp['icono']) ? $esp['icono'] : 'graduation-cap';
                ?>
                <article class="specialty-card" data-nombre="<?= e(mb_strtolower($esp['nombre'], 'UTF-8')); ?>" data-desc="<?= e(mb_strtolower($esp['descripcion_corta'], 'UTF-8')); ?>" data-perfil="<?= e(mb_strtolower($esp['perfil_egresado'], 'UTF-8')); ?>">
                    <div class="specialty-card-body" style="display: flex; flex-direction: column; height: 100%;">
                        <div class="specialty-icon" style="width: 58px; height: 58px; border-radius: var(--radius-lg); background: var(--color-bg-muted); display: flex; align-items: center; justify-content: center; color: var(--color-primary); margin-bottom: 18px;">
                            <?= icon($iconoNombre, ['class' => 'icon-lg']); ?>
                        </div>

                        <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem; margin-bottom: 12px; line-height: 1.3;">
                            <?= e($esp['nombre']); ?>
                        </h3>

                        <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px; flex-grow: 1;">
                            <?= e($esp['descripcion_corta']); ?>
                        </p>

                        <?php if (!empty($competencias)): ?>
                            <ul class="specialty-features" style="margin-bottom: 24px;">
                                <?php foreach ($competencias as $comp): ?>
                                    <li>
                                        <span style="color: #10b981; margin-right: 6px;"><?= icon('check-circle', ['class' => 'icon-sm']); ?></span>
                                        <?= e($comp); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div style="margin-top: auto;">
                            <a href="<?= url('public/prematricula.php?especialidad=' . urlencode($esp['slug'])); ?>" class="btn btn-primary" style="width: 100%; text-align: center; justify-content: center;">
                                <?= icon('file-signature', ['class' => 'icon-sm']); ?> Pre-matricularme en esta especialidad
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--color-bg-surface); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <p style="color: var(--color-text-muted); font-size: 1.1rem; margin: 0;">
                    No hay especialidades disponibles en este momento. Por favor comuníquese con la administración del colegio.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Beneficios Institucionales -->
    <section class="form-card" style="margin-top: 40px; border-left: 5px solid var(--color-gold);">
        <h3 style="color: var(--color-primary); margin-bottom: 15px; font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <?= icon('award', ['class' => 'icon-md', 'style' => 'color: var(--color-gold);']); ?> ¿Por qué estudiar en el CTP Rosario?
        </h3>
        <p style="color: var(--color-text-muted); margin-bottom: 20px; font-size: 0.98rem; line-height: 1.65;">
            Nuestro modelo educativo combina la formación académica de secundaria con pasantías y prácticas profesionales en empresas líderes de la región de Occidente, facilitando la inserción inmediata al mercado laboral y la continuidad de estudios universitarios en carreras STEM y de alta demanda.
        </p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="<?= url('public/prematricula.php'); ?>" class="btn btn-primary">
                <?= icon('file-signature', ['class' => 'icon-sm']); ?> Iniciar Proceso de Pre-Matrícula
            </a>
            <a href="<?= url('public/contacto.php'); ?>" class="btn btn-outline">
                <?= icon('envelope', ['class' => 'icon-sm']); ?> ¿Tienes preguntas? Escríbenos
            </a>
        </div>
    </section>
</main>

<script>
    function filtrarEspecialidades() {
        const query = document.getElementById('filtroEspecialidades').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.specialty-card');
        let visibles = 0;

        cards.forEach(card => {
            const nombre = card.getAttribute('data-nombre') || '';
            const desc = card.getAttribute('data-desc') || '';
            const perfil = card.getAttribute('data-perfil') || '';

            if (query === '' || nombre.includes(query) || desc.includes(query) || perfil.includes(query)) {
                card.style.display = '';
                visibles++;
            } else {
                card.style.display = 'none';
            }
        });

        const contador = document.getElementById('contadorEspecialidades');
        if (contador) {
            contador.innerText = visibles;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
