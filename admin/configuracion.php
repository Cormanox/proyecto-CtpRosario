<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Solo el rol 'admin' puede modificar configuraciones globales del portal
requerirRol('admin');

// Procesar actualización de configuraciones POST (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar_configuracion') {
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        set_flash('danger', 'Token de seguridad inválido o expirado. Por favor recargue e intente nuevamente.');
        header("Location: " . url('admin/configuracion.php'));
        exit;
    }

    $camposPermitidos = [
        'colegio_nombre'             => 'general',
        'colegio_nombre_largo'       => 'general',
        'hero_titulo'                => 'hero',
        'hero_subtitulo'             => 'hero',
        'hero_cta_matricula'         => 'hero',
        'hero_cta_oferta'            => 'hero',
        'mision'                     => 'institucional',
        'vision'                     => 'institucional',
        'resena_historica'           => 'institucional',
        'prematricula_abierta'       => 'matricula',
        'prematricula_mensaje_cierre'=> 'matricula',
        'periodo_lectivo_actual'     => 'matricula',
        'telefono_oficial'           => 'contacto',
        'correo_oficial'             => 'contacto',
        'direccion_oficial'          => 'contacto',
        'horario_atencion'           => 'contacto',
        'red_facebook'               => 'redes',
        'red_instagram'              => 'redes',
        'red_youtube'                => 'redes',
    ];

    try {
        $pdo->beginTransaction();

        foreach ($camposPermitidos as $campo => $categoria) {
            // Tratamiento especial para interruptores checkbox
            if ($campo === 'prematricula_abierta') {
                $valor = isset($_POST['prematricula_abierta']) ? '1' : '0';
            } else {
                $valor = trim((string)($_POST[$campo] ?? ''));
            }

            guardar_config($campo, $valor, $categoria);
        }

        $pdo->commit();
        set_flash('success', '¡Configuraciones institucionales actualizadas exitosamente!');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("[Config Update Error] " . $e->getMessage());
        set_flash('danger', 'Ocurrió un error al guardar la configuración del portal.');
    }

    header("Location: " . url('admin/configuracion.php'));
    exit;
}

// Cargar todas las configuraciones actuales
$configs = obtener_todas_configuraciones();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Institucional - CTP Rosario</title>
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
                    <li><a href="<?= url('admin/especialidades.php'); ?>"><?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades</a></li>
                    <li><a href="<?= url('admin/configuracion.php'); ?>" class="active"><?= icon('sliders', ['class' => 'icon-sm']); ?> Configuración</a></li>
                    <li><a href="<?= url('admin/solicitudes.php'); ?>"><?= icon('file-signature', ['class' => 'icon-sm']); ?> Solicitudes & Consultas</a></li>
                    <li><a href="<?= url('admin/usuarios.php'); ?>"><?= icon('users-gear', ['class' => 'icon-sm']); ?> Usuarios</a></li>
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
                <a href="<?= url('public/index.php'); ?>" target="_blank" rel="noopener noreferrer" style="color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Ver Portal Web">
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

        <div style="margin-bottom: 25px;">
            <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.65rem; margin-bottom: 6px;">
                Configuración del Portal Web Institucional
            </h2>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                Administra los textos, banners de portada, estado de la convocatoria de pre-matrícula, filosofía y datos de contacto oficiales del centro educativo.
            </p>
        </div>

        <form action="<?= url('admin/configuracion.php'); ?>" method="POST">
            <?= csrf_field(); ?>
            <input type="hidden" name="action" value="guardar_configuracion">

            <!-- 1. BLOQUE CRÍTICO: CONVOCATORIA DE PRE-MATRÍCULA -->
            <div class="form-card" style="margin-bottom: 30px; border-top: 4px solid var(--color-secondary);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                    <div style="color: var(--color-secondary); font-size: 1.3rem;">
                        <?= icon('file-signature', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.25rem; margin: 0;">
                            Control de Convocatoria de Pre-Matrícula
                        </h3>
                        <small style="color: var(--color-text-muted);">
                            Abre o cierra la recepción digital de solicitudes en línea con un solo clic.
                        </small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width" style="background: var(--color-bg-page); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                        <label class="switch-label" style="font-size: 1.05rem;">
                            <input type="checkbox" name="prematricula_abierta" value="1" class="switch-input" <?= (isset($configs['prematricula_abierta']) && $configs['prematricula_abierta'] === '1') ? 'checked' : ''; ?>>
                            <span class="switch-slider"></span>
                            <span style="display: flex; flex-direction: column;">
                                <strong>Período de Pre-Matrícula Abierto</strong>
                                <span style="font-size: 0.85rem; font-weight: 400; color: var(--color-text-muted);">
                                    Si está activo, los postulantes podrán completar el formulario. Si está cerrado, verán el mensaje oficial de espera.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="periodo_lectivo_actual">Período Lectivo Activo *</label>
                        <input type="text" id="periodo_lectivo_actual" name="periodo_lectivo_actual" class="form-control" required value="<?= e($configs['periodo_lectivo_actual'] ?? '2027'); ?>" placeholder="Ej: 2027">
                    </div>

                    <div class="form-group full-width">
                        <label for="prematricula_mensaje_cierre">Mensaje Oficial cuando la Pre-Matrícula esté Cerrada *</label>
                        <textarea id="prematricula_mensaje_cierre" name="prematricula_mensaje_cierre" class="form-control" rows="3" required placeholder="Indica a los usuarios cuándo se reanudará o cómo contactar a secretaría..."><?= e($configs['prematricula_mensaje_cierre'] ?? 'El período de pre-matrícula se encuentra cerrado.'); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 2. BLOQUE HERO & IDENTIDAD -->
            <div class="form-card" style="margin-bottom: 30px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                    <div style="color: var(--color-primary); font-size: 1.3rem;">
                        <?= icon('house', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.25rem; margin: 0;">
                            Portada Principal (Hero Banner)
                        </h3>
                        <small style="color: var(--color-text-muted);">Textos de bienvenida visibles en la página de inicio pública.</small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="colegio_nombre">Nombre Abreviado del Colegio *</label>
                        <input type="text" id="colegio_nombre" name="colegio_nombre" class="form-control" required value="<?= e($configs['colegio_nombre'] ?? 'CTP Rosario'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="colegio_nombre_largo">Nombre Oficial Completo *</label>
                        <input type="text" id="colegio_nombre_largo" name="colegio_nombre_largo" class="form-control" required value="<?= e($configs['colegio_nombre_largo'] ?? 'Colegio Técnico Profesional Rosario'); ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="hero_titulo">Título Principal del Hero *</label>
                        <input type="text" id="hero_titulo" name="hero_titulo" class="form-control" required value="<?= e($configs['hero_titulo'] ?? 'Bienvenidos al Colegio Técnico Profesional Rosario'); ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="hero_subtitulo">Subtítulo Descriptivo del Hero *</label>
                        <textarea id="hero_subtitulo" name="hero_subtitulo" class="form-control" rows="2" required><?= e($configs['hero_subtitulo'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="hero_cta_matricula">Texto del Botón Principal (Pre-Matrícula) *</label>
                        <input type="text" id="hero_cta_matricula" name="hero_cta_matricula" class="form-control" required value="<?= e($configs['hero_cta_matricula'] ?? 'Solicitar Pre-Matrícula'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="hero_cta_oferta">Texto del Botón Secundario (Especialidades) *</label>
                        <input type="text" id="hero_cta_oferta" name="hero_cta_oferta" class="form-control" required value="<?= e($configs['hero_cta_oferta'] ?? 'Conocer Especialidades'); ?>">
                    </div>
                </div>
            </div>

            <!-- 3. BLOQUE FILOSOFÍA INSTITUCIONAL -->
            <div class="form-card" style="margin-bottom: 30px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                    <div style="color: var(--color-primary); font-size: 1.3rem;">
                        <?= icon('award', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.25rem; margin: 0;">
                            Filosofía e Historia Institucional
                        </h3>
                        <small style="color: var(--color-text-muted);">Misión, visión y reseña histórica presentadas a la comunidad.</small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="mision">Misión Institucional *</label>
                        <textarea id="mision" name="mision" class="form-control" rows="3" required><?= e($configs['mision'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="vision">Visión Institucional *</label>
                        <textarea id="vision" name="vision" class="form-control" rows="3" required><?= e($configs['vision'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="resena_historica">Reseña Histórica del Colegio</label>
                        <textarea id="resena_historica" name="resena_historica" class="form-control" rows="4"><?= e($configs['resena_historica'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 4. BLOQUE CONTACTO & REDES SOCIALES -->
            <div class="form-card" style="margin-bottom: 35px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                    <div style="color: var(--color-primary); font-size: 1.3rem;">
                        <?= icon('envelope', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.25rem; margin: 0;">
                            Información Oficial de Contacto y Redes
                        </h3>
                        <small style="color: var(--color-text-muted);">Datos mostrados en el encabezado, pie de página y formulario de contacto.</small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="telefono_oficial">Teléfono Principal de Contacto *</label>
                        <input type="text" id="telefono_oficial" name="telefono_oficial" class="form-control" required value="<?= e($configs['telefono_oficial'] ?? '+506 2451-0000'); ?>" placeholder="Ej: +506 2451-0000">
                    </div>

                    <div class="form-group">
                        <label for="correo_oficial">Correo Electrónico Oficial *</label>
                        <input type="email" id="correo_oficial" name="correo_oficial" class="form-control" required value="<?= e($configs['correo_oficial'] ?? 'info@ctprosario.ed.cr'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="direccion_oficial">Dirección Física del Centro Educativo *</label>
                        <input type="text" id="direccion_oficial" name="direccion_oficial" class="form-control" required value="<?= e($configs['direccion_oficial'] ?? 'Rosario de Naranjo, Alajuela, Costa Rica'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="horario_atencion">Horario de Atención al Público *</label>
                        <input type="text" id="horario_atencion" name="horario_atencion" class="form-control" required value="<?= e($configs['horario_atencion'] ?? 'Lunes a Viernes: 7:00 AM - 4:30 PM'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="red_facebook">Página Oficial de Facebook (URL)</label>
                        <input type="url" id="red_facebook" name="red_facebook" class="form-control" value="<?= e($configs['red_facebook'] ?? ''); ?>" placeholder="https://facebook.com/ctprosario">
                    </div>

                    <div class="form-group">
                        <label for="red_instagram">Perfil Oficial de Instagram (URL)</label>
                        <input type="url" id="red_instagram" name="red_instagram" class="form-control" value="<?= e($configs['red_instagram'] ?? ''); ?>" placeholder="https://instagram.com/ctprosario">
                    </div>

                    <div class="form-group full-width">
                        <label for="red_youtube">Canal Oficial de YouTube (URL)</label>
                        <input type="url" id="red_youtube" name="red_youtube" class="form-control" value="<?= e($configs['red_youtube'] ?? ''); ?>" placeholder="https://youtube.com/@ctprosario">
                    </div>
                </div>
            </div>

            <!-- Botón de Guardar Flotante / Destacado -->
            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="padding: 14px 32px; font-size: 1.05rem;">
                    <?= icon('check-circle', ['class' => 'icon-sm']); ?> Guardar Toda la Configuración
                </button>
            </div>
        </form>
    </main>

    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>

