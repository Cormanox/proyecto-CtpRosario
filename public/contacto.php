<?php
declare(strict_types=1);

$pageTitle = "Contacto y Consultas";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Cargar información de contacto oficial desde la configuración institucional
$telOficial    = obtener_config('telefono_oficial', '+506 2451-0000');
$correoOficial = obtener_config('correo_oficial', 'info@ctprosario.ed.cr');
$dirOficial    = obtener_config('direccion_oficial', 'Rosario de Naranjo, Alajuela, Costa Rica');
$horario       = obtener_config('horario_atencion', 'Lunes a Viernes: 7:00 AM - 4:30 PM');
$colegioNombre = obtener_config('colegio_nombre_largo', 'Colegio Técnico Profesional Rosario');
$fbUrl         = obtener_config('red_facebook', '');
$igUrl         = obtener_config('red_instagram', '');
$ytUrl         = obtener_config('red_youtube', '');

$mensaje = '';
$tipoMensaje = '';

$nombre = $correo = $telefono = $asunto = $texto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        $mensaje = 'Token de seguridad inválido o expirado. Por favor recargue el formulario e intente nuevamente.';
        $tipoMensaje = 'danger';
    } else {
        $nombre   = trim((string)($_POST['nombre_remitente'] ?? ''));
        $correo   = trim((string)($_POST['correo_remitente'] ?? ''));
        $telefono = trim((string)($_POST['telefono'] ?? ''));
        $asunto   = trim((string)($_POST['asunto'] ?? ''));
        $texto    = trim((string)($_POST['mensaje'] ?? ''));

        if (empty($nombre) || empty($correo) || empty($asunto) || empty($texto)) {
            $mensaje = 'Por favor, completa los campos requeridos marcados con (*).';
            $tipoMensaje = 'danger';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Por favor, ingresa una dirección de correo electrónico válida.';
            $tipoMensaje = 'danger';
        } else {
            try {
                $sql = "INSERT INTO consultas (nombre_remitente, correo_remitente, telefono, asunto, mensaje, leido, fecha_envio)
                        VALUES (:nombre, :correo, :telefono, :asunto, :mensaje, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre'   => $nombre,
                    ':correo'   => $correo,
                    ':telefono' => $telefono,
                    ':asunto'   => $asunto,
                    ':mensaje'  => $texto
                ]);

                $mensaje = '¡Tu consulta ha sido enviada con éxito! Nuestro personal administrativo te responderá a la brevedad posible.';
                $tipoMensaje = 'success';
                $nombre = $correo = $telefono = $asunto = $texto = '';
            } catch (PDOException $e) {
                error_log("[Contacto Insert Error] " . $e->getMessage());
                $mensaje = 'Ocurrió un error al enviar tu consulta. Por favor intente nuevamente o utilice nuestras líneas telefónicas.';
                $tipoMensaje = 'danger';
            }
        }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h2>Contacto Institucional</h2>
        <p>Estamos a tu entera disposición para atender tus consultas, trámites y orientarte en tu proceso educativo.</p>
    </div>
</div>

<main class="container" style="margin-top: 35px; margin-bottom: 60px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
        
        <!-- Tarjeta de Información de Contacto -->
        <div class="form-card" style="margin-bottom: 0;">
            <h3 style="color: var(--color-primary); margin-bottom: 20px; font-size: 1.35rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <?= icon('house', ['class' => 'icon-md']); ?> Datos del Colegio
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 20px; color: var(--color-text-muted);">
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: var(--color-primary); font-size: 1.3rem; margin-top: 2px;">
                        <?= icon('location-dot', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <strong style="color: var(--color-text-main); display: block;">Dirección Oficial:</strong>
                        <?= e($dirOficial); ?>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: var(--color-primary); font-size: 1.3rem; margin-top: 2px;">
                        <?= icon('phone', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <strong style="color: var(--color-text-main); display: block;">Teléfono Central:</strong>
                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $telOficial)); ?>" style="color: inherit; font-weight: 600;"><?= e($telOficial); ?></a>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: var(--color-primary); font-size: 1.3rem; margin-top: 2px;">
                        <?= icon('envelope', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <strong style="color: var(--color-text-main); display: block;">Correo Oficial:</strong>
                        <a href="mailto:<?= e($correoOficial); ?>" style="color: inherit; font-weight: 600;"><?= e($correoOficial); ?></a>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: var(--color-primary); font-size: 1.3rem; margin-top: 2px;">
                        <?= icon('calendar', ['class' => 'icon-md']); ?>
                    </div>
                    <div>
                        <strong style="color: var(--color-text-main); display: block;">Horario de Atención:</strong>
                        <?= e($horario); ?>
                    </div>
                </div>

                <?php if (!empty($fbUrl) || !empty($igUrl) || !empty($ytUrl)): ?>
                    <div style="margin-top: 10px; padding-top: 15px; border-top: 1px solid var(--color-border);">
                        <strong style="color: var(--color-text-main); display: block; margin-bottom: 10px;">Canales y Redes Sociales:</strong>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <?php if (!empty($fbUrl)): ?>
                                <a href="<?= e($fbUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                                    Facebook
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($igUrl)): ?>
                                <a href="<?= e($igUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                                    Instagram
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($ytUrl)): ?>
                                <a href="<?= e($ytUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                                    YouTube
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario de Envío de Consultas -->
        <div class="form-card" style="margin-bottom: 0;">
            <h3 style="color: var(--color-primary); margin-bottom: 20px; font-size: 1.35rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <?= icon('envelope', ['class' => 'icon-md']); ?> Envíanos un Mensaje
            </h3>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= e($tipoMensaje); ?>" role="alert">
                    <?= ($tipoMensaje === 'success') ? icon('check-circle', ['class' => 'icon-md']) : icon('circle-exclamation', ['class' => 'icon-md']); ?>
                    <div><?= e($mensaje); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('public/contacto.php'); ?>" method="POST">
                <?= csrf_field(); ?>

                <div class="form-group">
                    <label for="nombre_remitente">Nombre Completo *</label>
                    <input type="text" id="nombre_remitente" name="nombre_remitente" class="form-control" required value="<?= e($nombre); ?>" placeholder="Tu nombre y apellidos">
                </div>

                <div class="form-group">
                    <label for="correo_remitente">Correo Electrónico *</label>
                    <input type="email" id="correo_remitente" name="correo_remitente" class="form-control" required value="<?= e($correo); ?>" placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono de Contacto (Opcional)</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" value="<?= e($telefono); ?>" placeholder="Ej: 8888-8888">
                </div>

                <div class="form-group">
                    <label for="asunto">Asunto de la Consulta *</label>
                    <input type="text" id="asunto" name="asunto" class="form-control" required value="<?= e($asunto); ?>" placeholder="Ej: Consulta sobre admisión o especialidad">
                </div>

                <div class="form-group">
                    <label for="mensaje">Mensaje o Detalle *</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" rows="4" required placeholder="Escribe aquí tu consulta detallada..."><?= e($texto); ?></textarea>
                </div>

                <div style="margin-top: 25px; text-align: right;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                        <?= icon('envelope', ['class' => 'icon-sm']); ?> Enviar Consulta
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
