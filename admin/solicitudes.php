<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$mensaje = '';
$tipoMensaje = '';

// Procesar actualización de estado de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];

    if ($action === 'actualizar_estado') {
        $idSolicitud = (int)($_POST['solicitud_id'] ?? 0);
        $nuevoEstado = trim((string)($_POST['nuevo_estado'] ?? ''));
        $estadosPermitidos = ['pendiente', 'en_revision', 'aceptada', 'rechazada'];

        if ($idSolicitud > 0 && in_array($nuevoEstado, $estadosPermitidos, true)) {
            try {
                $stmt = $pdo->prepare("UPDATE prematriculas SET estado_solicitud = :estado WHERE id = :id");
                $stmt->execute([':estado' => $nuevoEstado, ':id' => $idSolicitud]);
                $mensaje = "Estado de la solicitud #{$idSolicitud} actualizado a '{$nuevoEstado}'.";
                $tipoMensaje = "success";
            } catch (PDOException $e) {
                error_log("[Solicitudes Update Error] " . $e->getMessage());
                $mensaje = "Error al actualizar la solicitud.";
                $tipoMensaje = "danger";
            }
        }
    } elseif ($action === 'marcar_leido') {
        $idConsulta = (int)($_POST['consulta_id'] ?? 0);
        $leido = (int)($_POST['leido'] ?? 1);
        if ($idConsulta > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE consultas SET leido = :leido WHERE id = :id");
                $stmt->execute([':leido' => $leido, ':id' => $idConsulta]);
                $mensaje = "Mensaje #{$idConsulta} marcado como " . ($leido ? "leído" : "no leído") . ".";
                $tipoMensaje = "success";
            } catch (PDOException $e) {
                error_log("[Consultas Update Error] " . $e->getMessage());
                $mensaje = "Error al actualizar el mensaje.";
                $tipoMensaje = "danger";
            }
        }
    }
}

// Consultar todas las solicitudes y consultas
try {
    $solicitudes = $pdo->query("SELECT * FROM prematriculas ORDER BY fecha_registro DESC")->fetchAll();
    $consultas = $pdo->query("SELECT * FROM consultas ORDER BY fecha_envio DESC")->fetchAll();
} catch (PDOException $e) {
    error_log("[Solicitudes/Consultas Load Error] " . $e->getMessage());
    die("Error al consultar la información de solicitudes.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes y Consultas - CTP Rosario</title>
    <link rel="stylesheet" href="<?= url('assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>

    <header class="admin-navbar">
        <div class="container admin-nav-container">
            <div style="display: flex; align-items: center; gap: 15px;">
                <h2 style="font-size: 1.25rem; font-weight: bold; color: #ffd700;">
                    <i class="fa-solid fa-gauge-high"></i> CTP Rosario | Admin
                </h2>
                <ul class="admin-nav-links">
                    <li><a href="<?= url('admin/index.php'); ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="<?= url('admin/solicitudes.php'); ?>" class="active"><i class="fa-solid fa-file-signature"></i> Solicitudes & Consultas</a></li>
                    <li><a href="<?= url('admin/noticias.php'); ?>"><i class="fa-solid fa-newspaper"></i> Noticias</a></li>
                </ul>
            </div>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-circle"></i> <?= e($_SESSION['user_name'] ?? 'Administrador'); ?></span>
                <a href="<?= url('public/index.php'); ?>" target="_blank" rel="noopener noreferrer" style="color: #94a3b8; text-decoration: none;" title="Ver Portal Web">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Web
                </a>
                <a href="<?= url('admin/logout.php'); ?>" class="btn btn-sm btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 35px; margin-bottom: 60px;">

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= e($tipoMensaje); ?>">
                <i class="fa-solid fa-circle-info"></i>
                <div><?= e($mensaje); ?></div>
            </div>
        <?php endif; ?>

        <!-- Sección Solicitudes de Pre-Matrícula -->
        <h2 style="color: #003366; margin-bottom: 15px;"><i class="fa-solid fa-id-card"></i> Solicitudes de Pre-Matrícula (<?= count($solicitudes); ?>)</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Fecha Nac.</th>
                        <th>Especialidad</th>
                        <th>Encargado / Contacto</th>
                        <th>Observaciones</th>
                        <th>Estado Actual</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitudes)): ?>
                        <?php foreach ($solicitudes as $sol): ?>
                            <tr>
                                <td><strong>#<?= (int)$sol['id']; ?></strong></td>
                                <td><?= e($sol['cedula_estudiante'] ?? ''); ?></td>
                                <td>
                                    <strong><?= e(($sol['nombre_estudiante'] ?? '') . ' ' . ($sol['primer_apellido'] ?? '') . ' ' . ($sol['segundo_apellido'] ?? '')); ?></strong>
                                </td>
                                <td><?= e($sol['fecha_nacimiento'] ?? ''); ?></td>
                                <td><span style="color: #003366; font-weight: 600;"><?= e($sol['especialidad_interes'] ?? ''); ?></span></td>
                                <td>
                                    <small><strong>Tutor:</strong> <?= e($sol['nombre_encargado'] ?? ''); ?></small><br>
                                    <small><i class="fa-solid fa-phone"></i> <?= e($sol['telefono_contacto'] ?? ''); ?></small><br>
                                    <small><i class="fa-solid fa-envelope"></i> <?= e($sol['correo_contacto'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <small style="color: #64748b;"><?= !empty($sol['observaciones']) ? e($sol['observaciones']) : '<em>Sin notas</em>'; ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= e($sol['estado_solicitud'] ?? 'pendiente'); ?>">
                                        <?= e(str_replace('_', ' ', $sol['estado_solicitud'] ?? 'pendiente')); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('admin/solicitudes.php'); ?>" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="action" value="actualizar_estado">
                                        <input type="hidden" name="solicitud_id" value="<?= (int)$sol['id']; ?>">
                                        <select name="nuevo_estado" class="form-control" style="padding: 4px 6px; font-size: 0.82rem; width: auto;">
                                            <option value="pendiente" <?= ($sol['estado_solicitud'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="en_revision" <?= ($sol['estado_solicitud'] === 'en_revision') ? 'selected' : ''; ?>>En revisión</option>
                                            <option value="aceptada" <?= ($sol['estado_solicitud'] === 'aceptada') ? 'selected' : ''; ?>>Aceptada</option>
                                            <option value="rechazada" <?= ($sol['estado_solicitud'] === 'rechazada') ? 'selected' : ''; ?>>Rechazada</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" title="Guardar"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #94a3b8; padding: 25px;">No hay expedientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sección Mensajes y Consultas -->
        <h2 id="consultas" style="color: #003366; margin: 45px 0 15px 0;"><i class="fa-solid fa-envelope-open-text"></i> Consultas de Contacto (<?= count($consultas); ?>)</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Remitente</th>
                        <th>Contacto</th>
                        <th>Asunto</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consultas)): ?>
                        <?php foreach ($consultas as $c): ?>
                            <tr>
                                <td>#<?= (int)$c['id']; ?></td>
                                <td><strong><?= e($c['nombre_remitente'] ?? ''); ?></strong></td>
                                <td>
                                    <small><i class="fa-solid fa-envelope"></i> <?= e($c['correo_remitente'] ?? ''); ?></small><br>
                                    <?php if (!empty($c['telefono'])): ?>
                                        <small><i class="fa-solid fa-phone"></i> <?= e($c['telefono']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= e($c['asunto'] ?? ''); ?></strong></td>
                                <td>
                                    <div style="max-width: 320px; font-size: 0.88rem; color: #475569;">
                                        <?= nl2br(e($c['mensaje'] ?? '')); ?>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])); ?></td>
                                <td>
                                    <?php if (!empty($c['leido'])): ?>
                                        <span class="badge badge-aceptada"><i class="fa-solid fa-check-double"></i> Leído</span>
                                    <?php else: ?>
                                        <span class="badge badge-rechazada"><i class="fa-solid fa-circle"></i> Nuevo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('admin/solicitudes.php#consultas'); ?>">
                                        <input type="hidden" name="action" value="marcar_leido">
                                        <input type="hidden" name="consulta_id" value="<?= (int)$c['id']; ?>">
                                        <input type="hidden" name="leido" value="<?= !empty($c['leido']) ? '0' : '1'; ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary" style="color: #003366; border: 1px solid #cbd5e1;">
                                            <?= !empty($c['leido']) ? '<i class="fa-regular fa-envelope"></i> Marcar no leído' : '<i class="fa-solid fa-check"></i> Marcar leído'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 25px;">No hay mensajes recibidos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
