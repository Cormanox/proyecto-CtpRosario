<?php
$pageTitle = "Contacto y Consultas";
require_once '../config/db.php';
require_once '../includes/header.php';

$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre_remitente'] ?? '');
    $correo   = trim($_POST['correo_remitente'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $asunto   = trim($_POST['asunto'] ?? '');
    $texto    = trim($_POST['mensaje'] ?? '');

    if (empty($nombre) || empty($correo) || empty($asunto) || empty($texto)) {
        $mensaje = 'Por favor, completa los campos requeridos marcados con (*).';
        $tipoMensaje = 'danger';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor, ingresa un correo electrónico válido.';
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

            $mensaje = '¡Tu consulta ha sido enviada con éxito! Nuestro equipo administrativo te responderá a la brevedad.';
            $tipoMensaje = 'success';
            $nombre = $correo = $telefono = $asunto = $texto = '';
        } catch (PDOException $e) {
            $mensaje = 'Ocurrió un error al enviar tu consulta: ' . htmlspecialchars($e->getMessage());
            $tipoMensaje = 'danger';
        }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h2>Contacto Institucional</h2>
        <p>Estamos a tu disposición para atender tus consultas, trámites y orientarte en tu proceso educativo.</p>
    </div>
</div>

<main class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 50px;">
        
        <!-- Tarjeta de Información de Contacto -->
        <div class="form-card" style="margin-bottom: 0;">
            <h3 style="color: #003366; margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fa-solid fa-school"></i> Datos del Colegio
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 20px; color: #475569;">
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: #003366; font-size: 1.3rem; margin-top: 2px;"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <strong style="color: #1e293b; display: block;">Dirección:</strong>
                        Rosario, Costa Rica. Del parque central 400m norte.
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: #003366; font-size: 1.3rem; margin-top: 2px;"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <strong style="color: #1e293b; display: block;">Teléfono Central:</strong>
                        +506 2000-0000 / +506 2000-0001
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: #003366; font-size: 1.3rem; margin-top: 2px;"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <strong style="color: #1e293b; display: block;">Correo Institucional:</strong>
                        info@ctprosario.ed.cr / admisiones@ctprosario.ed.cr
                    </div>
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="color: #003366; font-size: 1.3rem; margin-top: 2px;"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <strong style="color: #1e293b; display: block;">Horario de Atención:</strong>
                        Lunes a Viernes de 7:00 a.m. a 4:30 p.m.
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #ffd700;">
                <strong style="color: #003366; display: block; margin-bottom: 5px;">¿Buscas matrícula?</strong>
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px;">Puedes registrar tu solicitud en línea sin necesidad de hacer filas en secretaría.</p>
                <a href="prematricula.php" class="service-link" style="margin-top: 0;">Completar Pre-Matrícula &rarr;</a>
            </div>
        </div>

        <!-- Formulario de Consulta -->
        <div class="form-card" style="margin-bottom: 0;">
            <h3 style="color: #003366; margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fa-solid fa-paper-plane"></i> Envíanos un Mensaje
            </h3>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipoMensaje; ?>">
                    <i class="fa-solid <?php echo ($tipoMensaje === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <div><?php echo $mensaje; ?></div>
                </div>
            <?php endif; ?>

            <form action="contacto.php" method="POST">
                <div class="form-group">
                    <label for="nombre_remitente">Nombre Completo *</label>
                    <input type="text" id="nombre_remitente" name="nombre_remitente" class="form-control" required value="<?php echo htmlspecialchars($nombre ?? ''); ?>" placeholder="Tu nombre y apellidos">
                </div>

                <div class="form-group">
                    <label for="correo_remitente">Correo Electrónico *</label>
                    <input type="email" id="correo_remitente" name="correo_remitente" class="form-control" required value="<?php echo htmlspecialchars($correo ?? ''); ?>" placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($telefono ?? ''); ?>" placeholder="Ej: 8888-8888">
                </div>

                <div class="form-group">
                    <label for="asunto">Asunto de la Consulta *</label>
                    <input type="text" id="asunto" name="asunto" class="form-control" required value="<?php echo htmlspecialchars($asunto ?? ''); ?>" placeholder="Ej: Información sobre uniformes y horarios">
                </div>

                <div class="form-group">
                    <label for="mensaje">Mensaje o Detalle *</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" required placeholder="Escribe tu mensaje aquí..."><?php echo htmlspecialchars($texto ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; cursor: pointer;">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Mensaje
                </button>
            </form>
        </div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
