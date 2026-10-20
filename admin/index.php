<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Estadísticas de la base de datos
try {
    $totalPrematriculas = (int)$pdo->query("SELECT COUNT(*) FROM prematriculas")->fetchColumn();
    $pendientesPrematriculas = (int)$pdo->query("SELECT COUNT(*) FROM prematriculas WHERE estado_solicitud = 'pendiente'")->fetchColumn();
    $totalConsultas = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
    $consultasNoLeidas = (int)$pdo->query("SELECT COUNT(*) FROM consultas WHERE leido = 0")->fetchColumn();
    $totalNoticias = (int)$pdo->query("SELECT COUNT(*) FROM noticias WHERE estado = 'publicado'")->fetchColumn();
    $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalUsuariosActivos = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 1")->fetchColumn();

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
    <link rel="stylesheet" href="<?= url('assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>

    <!-- Navegación del Panel de Administración -->
    <header class="admin-navbar">
        <div class="container admin-nav-container">
            <div style="display: flex; align-items: center; gap: 15px;">
                <h2 style="font-size: 1.25rem; font-weight: bold; color: #ffd700;">
                    <i class="fa-solid fa-gauge-high"></i> CTP Rosario | Admin
                </h2>
                <ul class="admin-nav-links">
                    <li><a href="<?= url('admin/index.php'); ?>" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <?php if (tieneRol('admin')): ?>
                        <li><a href="<?= url('admin/solicitudes.php'); ?>"><i class="fa-solid fa-file-signature"></i> Solicitudes & Consultas</a></li>
                        <li><a href="<?= url('admin/usuarios.php'); ?>"><i class="fa-solid fa-users-gear"></i> Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="<?= url('admin/noticias.php'); ?>"><i class="fa-solid fa-newspaper"></i> Noticias</a></li>
                </ul>
            </div>
            <div class="admin-user-info">
                <span>
                    <i class="fa-solid fa-user-circle"></i> <?= e($_SESSION['user_name'] ?? 'Usuario'); ?>
                    <span style="background: <?= (($_SESSION['user_role'] ?? '') === 'admin') ? '#ef4444' : '#3b82f6'; ?>; color: #fff; font-size: 0.72rem; padding: 2px 7px; border-radius: 12px; margin-left: 5px; text-transform: uppercase; font-weight: bold;">
                        <?= e($_SESSION['user_role'] ?? 'Usuario'); ?>
                    </span>
                </span>
                <a href="<?= url('public/index.php'); ?>" target="_blank" rel="noopener noreferrer" style="color: #94a3b8; text-decoration: none;" title="Ver Portal Web">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Web
                </a>
                <a href="<?= url('admin/logout.php'); ?>" class="btn btn-sm btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 35px; margin-bottom: 60px;">
        <h2 style="color: var(--color-primary); font-weight: 800; margin-bottom: 25px;">
            Resumen General del Sistema
        </h2>

        <!-- Tarjetas de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="stat-content">
                    <h4>Pre-Matrículas</h4>
                    <div class="stat-number"><?= $totalPrematriculas; ?></div>
                    <small style="color: #f59e0b;"><?= $pendientesPrematriculas; ?> pendientes</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <div class="stat-content">
                    <h4>Noticias Activas</h4>
                    <div class="stat-number"><?= $totalNoticias; ?></div>
                    <small style="color: #10b981;">Publicadas en portada</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <div class="stat-content">
                    <h4>Consultas Recibidas</h4>
                    <div class="stat-number"><?= $totalConsultas; ?></div>
                    <small style="color: #ef4444;"><?= $consultasNoLeidas; ?> sin leer</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <div class="stat-icon" style="background: #f5f3ff; color: #8b5cf6;">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div class="stat-content">
                    <h4>Servidor PHP & BD</h4>
                    <div class="stat-number" style="font-size: 1.2rem; color: #10b981;">Conectado</div>
                    <small style="color: #64748b;">MySQL (ctp_rosario)</small>
                </div>
            </div>

            <?php if (tieneRol('admin')): ?>
                <div class="stat-card" style="border-left-color: #871a2f;">
                    <div class="stat-icon" style="background: #fdf2f4; color: #871a2f;">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Usuarios & Roles</h4>
                        <div class="stat-number"><?= $totalUsuarios; ?></div>
                        <small style="color: #10b981;"><?= $totalUsuariosActivos; ?> activos / <a href="<?= url('admin/usuarios.php'); ?>" style="color: #871a2f; font-weight: 600; text-decoration: underline;">Gestionar</a></small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabla: Últimas Pre-matrículas -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 35px 0 15px 0;">
            <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem;"><i class="fa-solid fa-user-clock"></i> Últimas Solicitudes de Pre-Matrícula</h3>
            <a href="<?= url('admin/solicitudes.php'); ?>" style="color: var(--color-secondary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">Ver todas las solicitudes &rarr;</a>
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
                                <td><?= e($sol['especialidad_interes'] ?? ''); ?></td>
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
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 25px;">No hay solicitudes registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla: Últimas Consultas -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 35px 0 15px 0;">
            <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem;"><i class="fa-solid fa-comments"></i> Mensajes Recientes de Contacto</h3>
            <a href="<?= url('admin/solicitudes.php#consultas'); ?>" style="color: var(--color-secondary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">Ver todas las consultas &rarr;</a>
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
                                        <span class="badge badge-aceptada">Leído</span>
                                    <?php else: ?>
                                        <span class="badge badge-rechazada">No leído</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 25px;">No hay mensajes recibidos aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
