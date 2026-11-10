<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Tanto 'admin' como 'editor' pueden gestionar especialidades
requerirRol('admin', 'editor');

// Catálogo de iconos Iconimate disponibles para especialidades
$iconosDisponibles = [
    'code'           => 'Desarrollo de Software / Programación',
    'calculator'     => 'Contabilidad y Finanzas',
    'microchip'      => 'Electrónica Industrial',
    'briefcase'      => 'Secretariado Ejecutivo / Oficina',
    'gears'          => 'Mecánica de Precisión / Industrial',
    'graduation-cap' => 'Académico General',
    'award'          => 'Calidad y Excelencia',
    'shield-check'   => 'Seguridad / Mantenimiento',
    'sliders'        => 'Automatización y Control'
];

// Función auxiliar para generar slug único
function generarSlugEspecialidad(string $texto, ?int $excluirId = null): string {
    global $pdo;
    $slug = preg_replace('~[^\pL\d]+~u', '-', $texto) ?? '';
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug) ?: $slug;
    $slug = preg_replace('~[^-\w]+~', '', $slug) ?? '';
    $slug = trim($slug, '-');
    $slug = strtolower($slug);
    if (empty($slug)) {
        $slug = 'esp-' . time();
    }

    $baseSlug = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM especialidades WHERE slug = :slug";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        $params = [':slug' => $slug];
        if ($excluirId !== null) {
            $params[':id'] = $excluirId;
        }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $baseSlug . '-' . $i++;
    }
    return $slug;
}

// Procesar acciones POST con Post-Redirect-Get (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        set_flash('danger', 'Token de seguridad inválido o expirado. Por favor, intente de nuevo.');
        header("Location: " . url('admin/especialidades.php'));
        exit;
    }

    // ACCIÓN: CREAR ESPECIALIDAD
    if ($action === 'crear') {
        $nombre      = trim((string)($_POST['nombre'] ?? ''));
        $descCorta   = trim((string)($_POST['descripcion_corta'] ?? ''));
        $perfilEgres = trim((string)($_POST['perfil_egresado'] ?? ''));
        $icono       = trim((string)($_POST['icono'] ?? 'graduation-cap'));
        $orden       = (int)($_POST['orden'] ?? 0);
        $activa      = isset($_POST['activa']) ? 1 : 0;

        if ($nombre === '' || $descCorta === '') {
            set_flash('danger', 'El nombre y la descripción corta son obligatorios.');
        } else {
            try {
                $slug = generarSlugEspecialidad($nombre);
                $stmt = $pdo->prepare(
                    "INSERT INTO especialidades (nombre, slug, descripcion_corta, perfil_egresado, icono, orden, activa, creado_en)
                     VALUES (:nombre, :slug, :desc, :perfil, :icono, :orden, :activa, NOW())"
                );
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':slug'   => $slug,
                    ':desc'   => $descCorta,
                    ':perfil' => $perfilEgres,
                    ':icono'  => array_key_exists($icono, $iconosDisponibles) ? $icono : 'graduation-cap',
                    ':orden'  => $orden,
                    ':activa' => $activa
                ]);
                set_flash('success', "Especialidad '{$nombre}' creada exitosamente.");
            } catch (PDOException $e) {
                error_log("[Especialidades Create Error] " . $e->getMessage());
                set_flash('danger', 'Error interno al registrar la especialidad.');
            }
        }
        header("Location: " . url('admin/especialidades.php'));
        exit;

    // ACCIÓN: EDITAR ESPECIALIDAD
    } elseif ($action === 'editar') {
        $id          = (int)($_POST['especialidad_id'] ?? 0);
        $nombre      = trim((string)($_POST['nombre'] ?? ''));
        $descCorta   = trim((string)($_POST['descripcion_corta'] ?? ''));
        $perfilEgres = trim((string)($_POST['perfil_egresado'] ?? ''));
        $icono       = trim((string)($_POST['icono'] ?? 'graduation-cap'));
        $orden       = (int)($_POST['orden'] ?? 0);
        $activa      = isset($_POST['activa']) ? 1 : 0;

        if ($id <= 0 || $nombre === '' || $descCorta === '') {
            set_flash('danger', 'Datos incompletos o identificador no válido.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE especialidades 
                     SET nombre = :nombre, descripcion_corta = :desc, perfil_egresado = :perfil, 
                         icono = :icono, orden = :orden, activa = :activa
                     WHERE id = :id"
                );
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':desc'   => $descCorta,
                    ':perfil' => $perfilEgres,
                    ':icono'  => array_key_exists($icono, $iconosDisponibles) ? $icono : 'graduation-cap',
                    ':orden'  => $orden,
                    ':activa' => $activa,
                    ':id'     => $id
                ]);
                set_flash('success', "Especialidad #{$id} actualizada correctamente.");
            } catch (PDOException $e) {
                error_log("[Especialidades Edit Error] " . $e->getMessage());
                set_flash('danger', 'Error al actualizar la especialidad.');
            }
        }
        header("Location: " . url('admin/especialidades.php'));
        exit;

    // ACCIÓN: TOGGLE ESTADO ACTIVA
    } elseif ($action === 'toggle_activa') {
        $id = (int)($_POST['especialidad_id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE especialidades SET activa = 1 - activa WHERE id = :id");
                $stmt->execute([':id' => $id]);
                set_flash('success', "Estado de la especialidad modificado.");
            } catch (PDOException $e) {
                error_log("[Especialidades Toggle Error] " . $e->getMessage());
                set_flash('danger', 'Error al cambiar estado de la especialidad.');
            }
        }
        header("Location: " . url('admin/especialidades.php'));
        exit;

    // ACCIÓN: ELIMINAR ESPECIALIDAD
    } elseif ($action === 'eliminar') {
        $id = (int)($_POST['especialidad_id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = :id");
                $stmt->execute([':id' => $id]);
                set_flash('success', "Especialidad eliminada correctamente.");
            } catch (PDOException $e) {
                error_log("[Especialidades Delete Error] " . $e->getMessage());
                set_flash('danger', 'No se pudo eliminar la especialidad.');
            }
        }
        header("Location: " . url('admin/especialidades.php'));
        exit;
    }
}

// Cargar todas las especialidades ordenadas
try {
    $especialidades = $pdo->query("SELECT * FROM especialidades ORDER BY orden ASC, nombre ASC")->fetchAll();
} catch (PDOException $e) {
    error_log("[Especialidades Load Error] " . $e->getMessage());
    $especialidades = [];
}

// Especialidad a editar si viene por GET
$editarEspecialidad = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    foreach ($especialidades as $esp) {
        if ((int)$esp['id'] === $editId) {
            $editarEspecialidad = $esp;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Especialidades Técnicas - CTP Rosario</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
</head>
<body>

    <!-- Navegación del Panel de Administración -->
    <header class="admin-navbar">
        <div class="container admin-nav-container">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <h2 style="font-size: 1.25rem; font-weight: bold; color: var(--color-gold); display: flex; align-items: center; gap: 8px;">
                    <?= icon('gauge-high', ['class' => 'icon-md']); ?> CTP Rosario | Admin
                </h2>
                <ul class="admin-nav-links">
                    <li><a href="<?= url('admin/index.php'); ?>"><?= icon('gauge-high', ['class' => 'icon-sm']); ?> Dashboard</a></li>
                    <li><a href="<?= url('admin/especialidades.php'); ?>" class="active"><?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades</a></li>
                    <?php if (tieneRol('admin')): ?>
                        <li><a href="<?= url('admin/configuracion.php'); ?>"><?= icon('sliders', ['class' => 'icon-sm']); ?> Configuración</a></li>
                        <li><a href="<?= url('admin/solicitudes.php'); ?>"><?= icon('file-signature', ['class' => 'icon-sm']); ?> Solicitudes & Consultas</a></li>
                        <li><a href="<?= url('admin/usuarios.php'); ?>"><?= icon('users-gear', ['class' => 'icon-sm']); ?> Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="<?= url('admin/noticias.php'); ?>"><?= icon('newspaper', ['class' => 'icon-sm']); ?> Noticias</a></li>
                </ul>
            </div>
            <div class="admin-user-info">
                <span>
                    <?= icon('user', ['class' => 'icon-sm']); ?> <?= e($_SESSION['user_name'] ?? 'Usuario'); ?>
                    <span class="badge badge-<?= e($_SESSION['user_role'] ?? 'user'); ?>" style="margin-left: 6px;">
                        <?= e($_SESSION['user_role'] ?? 'Usuario'); ?>
                    </span>
                </span>
                <a href="<?= url('public/oferta.php'); ?>" target="_blank" rel="noopener noreferrer" style="color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Ver Oferta en Web">
                    <?= icon('arrow-up-right-from-square', ['class' => 'icon-sm']); ?> Ver Web
                </a>
                <a href="<?= url('admin/logout.php'); ?>" class="btn btn-sm btn-danger" style="display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('right-from-bracket', ['class' => 'icon-sm']); ?> Salir
                </a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 35px; margin-bottom: 60px;">
        <!-- Mensajes Flash -->
        <?= render_flash(); ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.65rem; margin-bottom: 6px;">
                    Gestión de Especialidades Técnicas
                </h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Administra la oferta académica visible en el portal. Las especialidades activas se cargan automáticamente en la web y el formulario de matrícula.
                </p>
            </div>
            <?php if (!$editarEspecialidad): ?>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formEspecialidad').scrollIntoView({behavior: 'smooth'})">
                    <?= icon('plus-circle', ['class' => 'icon-sm']); ?> Nueva Especialidad
                </button>
            <?php endif; ?>
        </div>

        <!-- Tabla de Especialidades Registradas -->
        <div class="table-responsive" style="margin-bottom: 35px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Orden</th>
                        <th style="width: 50px;">Icono</th>
                        <th>Especialidad</th>
                        <th>Slug Canónico</th>
                        <th>Perfil Técnico</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="text-align: right; width: 170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($especialidades)): ?>
                        <?php foreach ($especialidades as $esp): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: var(--color-primary);">
                                    <?= (int)$esp['orden']; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="width: 38px; height: 38px; border-radius: var(--radius-md); background: var(--color-bg-muted); display: inline-flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                        <?= icon($esp['icono'] ?? 'graduation-cap', ['class' => 'icon-sm']); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= e($esp['nombre']); ?></strong>
                                    <div style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 3px;">
                                        <?= e(mb_substr($esp['descripcion_corta'], 0, 85, 'UTF-8')) . '...'; ?>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.82rem; background: var(--color-bg-muted); padding: 3px 7px; border-radius: 4px; color: var(--color-secondary);">
                                        <?= e($esp['slug']); ?>
                                    </code>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--color-text-muted);">
                                    <?= nl2br(e(mb_substr($esp['perfil_egresado'], 0, 90, 'UTF-8'))); ?>...
                                </td>
                                <td>
                                    <form action="<?= url('admin/especialidades.php'); ?>" method="POST" style="display: inline;">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="toggle_activa">
                                        <input type="hidden" name="especialidad_id" value="<?= (int)$esp['id']; ?>">
                                        <button type="submit" class="badge <?= !empty($esp['activa']) ? 'badge-activo' : 'badge-inactivo'; ?>" style="border: none; cursor: pointer;" title="Clic para alternar estado">
                                            <?= !empty($esp['activa']) ? icon('check-circle', ['class' => 'icon-sm']) . ' Activa' : icon('circle-exclamation', ['class' => 'icon-sm']) . ' Inactiva'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?= url('admin/especialidades.php?edit=' . (int)$esp['id']); ?>#formEspecialidad" class="btn btn-sm btn-outline" style="margin-right: 4px;" title="Editar especialidad">
                                        Editar
                                    </a>
                                    <form action="<?= url('admin/especialidades.php'); ?>" method="POST" style="display: inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta especialidad?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="especialidad_id" value="<?= (int)$esp['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar especialidad">
                                            <?= icon('trash', ['class' => 'icon-sm']); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--color-text-subtle); padding: 30px;">
                                No hay especialidades registradas en este momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario: Crear / Editar Especialidad -->
        <div class="form-card" id="formEspecialidad" style="scroll-margin-top: 100px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--color-border); padding-bottom: 12px;">
                <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <?= $editarEspecialidad ? icon('sliders', ['class' => 'icon-md']) . ' Editar Especialidad #' . (int)$editarEspecialidad['id'] : icon('plus-circle', ['class' => 'icon-md']) . ' Agregar Nueva Especialidad'; ?>
                </h3>
                <?php if ($editarEspecialidad): ?>
                    <a href="<?= url('admin/especialidades.php'); ?>" class="btn btn-sm btn-outline">Cancelar Edición</a>
                <?php endif; ?>
            </div>

            <form action="<?= url('admin/especialidades.php'); ?>" method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="<?= $editarEspecialidad ? 'editar' : 'crear'; ?>">
                <?php if ($editarEspecialidad): ?>
                    <input type="hidden" name="especialidad_id" value="<?= (int)$editarEspecialidad['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nombre">Nombre de la Especialidad *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required value="<?= e($editarEspecialidad['nombre'] ?? ''); ?>" placeholder="Ej: Informática en Desarrollo de Software">
                    </div>

                    <div class="form-group">
                        <label for="icono">Icono Representativo (Iconimate) *</label>
                        <select id="icono" name="icono" class="form-control" required>
                            <?php 
                            $iconoActual = $editarEspecialidad['icono'] ?? 'graduation-cap';
                            foreach ($iconosDisponibles as $key => $label): 
                            ?>
                                <option value="<?= e($key); ?>" <?= ($iconoActual === $key) ? 'selected' : ''; ?>>
                                    <?= e($label); ?> (<?= e($key); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="orden">Orden de Visualización *</label>
                        <input type="number" id="orden" name="orden" class="form-control" required value="<?= (int)($editarEspecialidad['orden'] ?? (count($especialidades) + 1)); ?>" min="1" max="99">
                    </div>

                    <div class="form-group full-width">
                        <label for="descripcion_corta">Descripción Formativa Corta *</label>
                        <textarea id="descripcion_corta" name="descripcion_corta" class="form-control" rows="3" required placeholder="Describe brevemente el alcance de la carrera técnica..."><?= e($editarEspecialidad['descripcion_corta'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="perfil_egresado">Competencias y Perfil de Salida (Una por línea)</label>
                        <textarea id="perfil_egresado" name="perfil_egresado" class="form-control" rows="4" placeholder="Ej:&#10;Programación web y móvil&#10;Bases de datos SQL&#10;Metodologías ágiles"><?= e($editarEspecialidad['perfil_egresado'] ?? ''); ?></textarea>
                        <small style="color: var(--color-text-muted); display: block; margin-top: 4px;">
                            Cada línea se presentará con un icono de verificación en la página de oferta académica.
                        </small>
                    </div>

                    <div class="form-group full-width" style="margin-top: 10px;">
                        <label class="switch-label">
                            <input type="checkbox" name="activa" value="1" class="switch-input" <?= (!isset($editarEspecialidad) || !empty($editarEspecialidad['activa'])) ? 'checked' : ''; ?>>
                            <span class="switch-slider"></span>
                            <span>Especialidad Activa (Visible en el portal público y formulario de matrícula)</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 25px; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                    <?php if ($editarEspecialidad): ?>
                        <a href="<?= url('admin/especialidades.php'); ?>" class="btn btn-outline">Cancelar</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= icon('check-circle', ['class' => 'icon-sm']); ?> <?= $editarEspecialidad ? 'Guardar Cambios' : 'Registrar Especialidad'; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Pie de página y controlador de animaciones -->
    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>

