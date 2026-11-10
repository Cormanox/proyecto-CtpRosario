<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Estadísticas de la base de datos
try {
    $totalPrematriculas     = (int)$pdo->query("SELECT COUNT(*) FROM prematriculas")->fetchColumn();
    $pendientesPrematriculas = (int)$pdo->query("SELECT COUNT(*) FROM prematriculas WHERE estado_solicitud = 'pendiente'")->fetchColumn();
    $totalConsultas         = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
    $consultasNoLeidas      = (int)$pdo->query("SELECT COUNT(*) FROM consultas WHERE leido = 0")->fetchColumn();
    $totalNoticias          = (int)$pdo->query("SELECT COUNT(*) FROM noticias WHERE estado = 'publicado'")->fetchColumn();
    $totalUsuarios          = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalUsuariosActivos   = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 1")->fetchColumn();
    $totalEspecialidades    = (int)$pdo->query("SELECT COUNT(*) FROM especialidades WHERE activa = 1")->fetchColumn();
    $prematriculaAbierta    = obtener_config('prematricula_abierta', '1') === '1';

    // Últimas solicitudes de pre-matrícula
    $stmtPre = $pdo->query("SELECT * FROM prematriculas ORDER BY fecha_registro DESC LIMIT 5");
    $ultimasPrematriculas = $stmtPre->fetchAll();

    // Últimas consultas
    $stmtCon = $pdo->query("SELECT * FROM consultas ORDER BY fecha_envio DESC LIMIT 5");
    $ultimasConsultas = $stmtCon->fetchAll();

} catch (PDOException $e) {
    error_log("[Admin Dashboard Error] " . $e->getMessage());
    die("Error al cargar los datos del dashboard. Por favor verifique el registro del sistema.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - CTP Rosario</title>
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
                    <li><a href="<?= url('admin/index.php'); ?>" class="active"><?= icon('gauge-high', ['class' => 'icon-sm']); ?> Dashboard</a></li>
                    <li><a href="<?= url('admin/especialidades.php'); ?>"><?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades</a></li>
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

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.65rem; margin-bottom: 4px;">
                    Resumen General del Sistema
                </h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Bienvenido, <strong><?= e($_SESSION['user_name'] ?? 'Usuario'); ?></strong>. Panel de control institucional del CTP Rosario de Naranjo.
                </p>
            </div>
            <div>
                <span class="badge <?= $prematriculaAbierta ? 'badge-activo' : 'badge-inactivo'; ?>" style="font-size: 0.9rem; padding: 6px 14px;">
                    <?= $prematriculaAbierta ? icon('check-circle', ['class' => 'icon-sm']) . ' Convocatoria Pre-Matrícula Abierta' : icon('circle-exclamation', ['class' => 'icon-sm']) . ' Convocatoria Pre-Matrícula Cerrada'; ?>
                </span>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas KPI con Contadores Animados -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                    <?= icon('file-signature', ['class' => 'icon-md']); ?>
                </div>
                <div class="stat-content">
                    <h4>Pre-Matrículas</h4>
                    <div class="stat-number"><?= $totalPrematriculas; ?></div>
                    <small style="color: #f59e0b;"><?= $pendientesPrematriculas; ?> pendientes</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">
                    <?= icon('newspaper', ['class' => 'icon-md']); ?>
                </div>
                <div class="stat-content">
                    <h4>Noticias Activas</h4>
                    <div class="stat-number"><?= $totalNoticias; ?></div>
                    <small style="color: #10b981;">Publicadas en portada</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #871a2f;">
                <div class="stat-icon" style="background: #fdf2f4; color: #871a2f;">
                    <?= icon('graduation-cap', ['class' => 'icon-md']); ?>
                </div>
                <div class="stat-content">
                    <h4>Especialidades Activas</h4>
                    <div class="stat-number"><?= $totalEspecialidades; ?></div>
                    <small style="color: var(--color-primary);"><a href="<?= url('admin/especialidades.php'); ?>" style="color: inherit; text-decoration: underline;">Gestionar oferta</a></small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">
                    <?= icon('envelope', ['class' => 'icon-md']); ?>
                </div>
                <div class="stat-content">
                    <h4>Consultas Recibidas</h4>
                    <div class="stat-number"><?= $totalConsultas; ?></div>
                    <small style="color: #ef4444;"><?= $consultasNoLeidas; ?> sin leer</small>
                </div>
            </div>

            <?php if (tieneRol('admin')): ?>
                <div class="stat-card" style="border-left-color: #9c1932;">
                    <div class="stat-icon" style="background: #fdf2f4; color: #9c1932;">
                        <?= icon('users-gear', ['class' => 'icon-md']); ?>
                    </div>
                    <div class="stat-content">
                        <h4>Usuarios & Roles</h4>
                        <div class="stat-number"><?= $totalUsuarios; ?></div>
                        <small style="color: #10b981;"><?= $totalUsuariosActivos; ?> activos / <a href="<?= url('admin/usuarios.php'); ?>" style="color: var(--color-secondary); font-weight: 600; text-decoration: underline;">Gestionar</a></small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabla: Últimas Pre-matrículas -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 35px 0 15px 0;">
            <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem; display: flex; align-items: center; gap: 8px;">
                <?= icon('file-signature', ['class' => 'icon-md']); ?> Últimas Solicitudes de Pre-Matrícula
            </h3>
            <a href="<?= url('admin/solicitudes.php'); ?>" style="color: var(--color-secondary); font-weight: 700; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px;">
                Ver todas las solicitudes <?= icon('arrow-right', ['class' => 'icon-sm']); ?>
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Especialidad</th>
                        <th>Encargado</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ultimasPrematriculas)): ?>
                        <?php foreach ($ultimasPrematriculas as $sol): ?>
                            <tr>
                                <td>#<?= (int)$sol['id']; ?></td>
                                <td><strong><?= e($sol['cedula_estudiante'] ?? ''); ?></strong></td>
                                <td><?= e(($sol['nombre_estudiante'] ?? '') . ' ' . ($sol['primer_apellido'] ?? '')); ?></td>
                                <td><span style="font-weight: 600; color: var(--color-primary);"><?= e($sol['especialidad_interes'] ?? ''); ?></span></td>
                                <td><?= e($sol['nombre_encargado'] ?? ''); ?></td>
                                <td><?= e($sol['telefono_contacto'] ?? ''); ?></td>
                                <td>
                                    <span class="badge badge-<?= e($sol['estado_solicitud'] ?? 'pendiente'); ?>">
                                        <?= e(str_replace('_', ' ', $sol['estado_solicitud'] ?? 'pendiente')); ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($sol['fecha_registro'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--color-text-subtle); padding: 25px;">No hay solicitudes registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla: Últimas Consultas -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 35px 0 15px 0;">
            <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem; display: flex; align-items: center; gap: 8px;">
                <?= icon('comments', ['class' => 'icon-md']); ?> Mensajes Recientes de Contacto
            </h3>
            <a href="<?= url('admin/solicitudes.php#consultas'); ?>" style="color: var(--color-secondary); font-weight: 700; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px;">
                Ver todas las consultas <?= icon('arrow-right', ['class' => 'icon-sm']); ?>
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Remitente</th>
                        <th>Correo</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ultimasConsultas)): ?>
                        <?php foreach ($ultimasConsultas as $c): ?>
                            <tr>
                                <td>#<?= (int)$c['id']; ?></td>
                                <td><strong><?= e($c['nombre_remitente'] ?? ''); ?></strong></td>
                                <td><?= e($c['correo_remitente'] ?? ''); ?></td>
                                <td><?= e($c['asunto'] ?? ''); ?></td>
                                <td>
                                    <?php if (!empty($c['leido'])): ?>
                                        <span class="badge badge-activo"><?= icon('check-circle', ['class' => 'icon-sm']); ?> Leído</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo"><?= icon('bell-ringing', ['class' => 'icon-sm']); ?> No leído</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-subtle); padding: 25px;">No hay mensajes recibidos aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>
