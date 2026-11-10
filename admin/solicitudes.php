<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Solo el rol 'admin' puede gestionar solicitudes y consultas
requerirRol('admin');

// 1. Exportación a CSV compatible con Excel (UTF-8 con BOM)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filtroEsp    = trim((string)($_GET['especialidad'] ?? ''));
    $filtroEstado = trim((string)($_GET['estado'] ?? ''));
    $fechaDesde   = trim((string)($_GET['desde'] ?? ''));
    $fechaHasta   = trim((string)($_GET['hasta'] ?? ''));
    $busqueda     = trim((string)($_GET['q'] ?? ''));

    $sql = "SELECT * FROM prematriculas WHERE 1=1";
    $params = [];

    if ($filtroEsp !== '') {
        $sql .= " AND especialidad_interes = :esp";
        $params[':esp'] = $filtroEsp;
    }
    if ($filtroEstado !== '') {
        $sql .= " AND estado_solicitud = :estado";
        $params[':estado'] = $filtroEstado;
    }
    if ($fechaDesde !== '') {
        $sql .= " AND DATE(fecha_registro) >= :desde";
        $params[':desde'] = $fechaDesde;
    }
    if ($fechaHasta !== '') {
        $sql .= " AND DATE(fecha_registro) <= :hasta";
        $params[':hasta'] = $fechaHasta;
    }
    if ($busqueda !== '') {
        $sql .= " AND (cedula_estudiante LIKE :q OR nombre_estudiante LIKE :q OR primer_apellido LIKE :q OR segundo_apellido LIKE :q OR nombre_encargado LIKE :q)";
        $params[':q'] = "%{$busqueda}%";
    }

    $sql .= " ORDER BY fecha_registro DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $solicitudesExport = $stmt->fetchAll();

        $filename = "prematriculas_ctp_rosario_" . date('Y-m-d_His') . ".csv";
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Escribir BOM de UTF-8 para apertura correcta en Microsoft Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeceras de columnas
        fputcsv($output, [
            'ID Expediente',
            'Cédula',
            'Nombre Estudiante',
            'Primer Apellido',
            'Segundo Apellido',
            'Fecha Nacimiento',
            'Especialidad de Interés',
            'Encargado Legal',
            'Teléfono Contacto',
            'Correo Contacto',
            'Estado Solicitud',
            'Observaciones',
            'Fecha de Registro'
        ]);

        foreach ($solicitudesExport as $row) {
            fputcsv($output, [
                $row['id'],
                $row['cedula_estudiante'],
                $row['nombre_estudiante'],
                $row['primer_apellido'],
                $row['segundo_apellido'],
                $row['fecha_nacimiento'],
                $row['especialidad_interes'],
                $row['nombre_encargado'],
                $row['telefono_contacto'],
                $row['correo_contacto'],
                ucfirst(str_replace('_', ' ', $row['estado_solicitud'])),
                $row['observaciones'],
                $row['fecha_registro']
            ]);
        }

        fclose($output);
        exit;
    } catch (PDOException $e) {
        error_log("[CSV Export Error] " . $e->getMessage());
        die("Error al generar el archivo de exportación.");
    }
}

// 2. Procesar acciones POST con Post-Redirect-Get (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        set_flash('danger', 'Token de seguridad inválido o expirado.');
        header("Location: " . url('admin/solicitudes.php'));
        exit;
    }

    if ($action === 'actualizar_estado') {
        $idSolicitud = (int)($_POST['solicitud_id'] ?? 0);
        $nuevoEstado = trim((string)($_POST['nuevo_estado'] ?? ''));
        $estadosPermitidos = ['pendiente', 'en_revision', 'aceptada', 'rechazada'];

        if ($idSolicitud > 0 && in_array($nuevoEstado, $estadosPermitidos, true)) {
            try {
                $stmt = $pdo->prepare("UPDATE prematriculas SET estado_solicitud = :estado WHERE id = :id");
                $stmt->execute([':estado' => $nuevoEstado, ':id' => $idSolicitud]);
                set_flash('success', "Estado de la solicitud #{$idSolicitud} actualizado a '{$nuevoEstado}'.");
            } catch (PDOException $e) {
                error_log("[Solicitudes Update Error] " . $e->getMessage());
                set_flash('danger', 'Error al actualizar el estado de la solicitud.');
            }
        }
    } elseif ($action === 'marcar_leido') {
        $idConsulta = (int)($_POST['consulta_id'] ?? 0);
        $leido = (int)($_POST['leido'] ?? 1);
        if ($idConsulta > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE consultas SET leido = :leido WHERE id = :id");
                $stmt->execute([':leido' => $leido, ':id' => $idConsulta]);
                set_flash('success', "Mensaje #{$idConsulta} marcado como " . ($leido ? 'leído' : 'no leído') . ".");
            } catch (PDOException $e) {
                error_log("[Consultas Update Error] " . $e->getMessage());
                set_flash('danger', 'Error al actualizar el mensaje.');
            }
        }
    }

    header("Location: " . url('admin/solicitudes.php'));
    exit;
}

// 3. Obtener especialidades registradas para el dropdown de filtros
try {
    $especialidadesOpts = $pdo->query("SELECT DISTINCT nombre FROM especialidades ORDER BY nombre ASC")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($especialidadesOpts)) {
        $especialidadesOpts = $pdo->query("SELECT DISTINCT especialidad_interes FROM prematriculas ORDER BY especialidad_interes ASC")->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    $especialidadesOpts = [];
}

// 4. Parámetros de filtrado
$filtroEsp    = trim((string)($_GET['especialidad'] ?? ''));
$filtroEstado = trim((string)($_GET['estado'] ?? ''));
$fechaDesde   = trim((string)($_GET['desde'] ?? ''));
$fechaHasta   = trim((string)($_GET['hasta'] ?? ''));
$busqueda     = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT * FROM prematriculas WHERE 1=1";
$params = [];

if ($filtroEsp !== '') {
    $sql .= " AND especialidad_interes = :esp";
    $params[':esp'] = $filtroEsp;
}
if ($filtroEstado !== '') {
    $sql .= " AND estado_solicitud = :estado";
    $params[':estado'] = $filtroEstado;
}
if ($fechaDesde !== '') {
    $sql .= " AND DATE(fecha_registro) >= :desde";
    $params[':desde'] = $fechaDesde;
}
if ($fechaHasta !== '') {
    $sql .= " AND DATE(fecha_registro) <= :hasta";
    $params[':hasta'] = $fechaHasta;
}
if ($busqueda !== '') {
    $sql .= " AND (cedula_estudiante LIKE :q OR nombre_estudiante LIKE :q OR primer_apellido LIKE :q OR segundo_apellido LIKE :q OR nombre_encargado LIKE :q)";
    $params[':q'] = "%{$busqueda}%";
}

$sql .= " ORDER BY fecha_registro DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll();

    $consultas = $pdo->query("SELECT * FROM consultas ORDER BY fecha_envio DESC")->fetchAll();
} catch (PDOException $e) {
    error_log("[Solicitudes/Consultas Load Error] " . $e->getMessage());
    $solicitudes = [];
    $consultas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes y Consultas - CTP Rosario</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
</head>
<body>

    <header class="admin-navbar">
        <div class="container admin-nav-container">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <h2 style="font-size: 1.25rem; font-weight: bold; color: var(--color-gold); display: flex; align-items: center; gap: 8px;">
                    <?= icon('gauge-high', ['class' => 'icon-md']); ?> CTP Rosario | Admin
                </h2>
                <ul class="admin-nav-links">
                    <li><a href="<?= url('admin/index.php'); ?>"><?= icon('gauge-high', ['class' => 'icon-sm']); ?> Dashboard</a></li>
                    <li><a href="<?= url('admin/especialidades.php'); ?>"><?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades</a></li>
                    <li><a href="<?= url('admin/configuracion.php'); ?>"><?= icon('sliders', ['class' => 'icon-sm']); ?> Configuración</a></li>
                    <li><a href="<?= url('admin/solicitudes.php'); ?>" class="active"><?= icon('file-signature', ['class' => 'icon-sm']); ?> Solicitudes & Consultas</a></li>
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

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <div>
                <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.65rem; margin-bottom: 4px;">
                    Expedientes de Pre-Matrícula
                </h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Total encontrados: <strong><?= count($solicitudes); ?></strong> solicitud(es)
                </p>
            </div>
            <div>
                <?php
                $exportQuery = http_build_query([
                    'export'       => 'csv',
                    'especialidad' => $filtroEsp,
                    'estado'       => $filtroEstado,
                    'desde'        => $fechaDesde,
                    'hasta'        => $fechaHasta,
                    'q'            => $busqueda
                ]);
                ?>
                <a href="<?= url('admin/solicitudes.php?' . $exportQuery); ?>" class="btn-export">
                    <?= icon('download-simple', ['class' => 'icon-sm']); ?> Exportar a CSV (Excel)
                </a>
            </div>
        </div>

        <!-- BARRA DE FILTROS COMBINABLES -->
        <form method="GET" action="<?= url('admin/solicitudes.php'); ?>" class="filter-toolbar">
            <div class="filter-group">
                <!-- Buscador Instantáneo -->
                <input type="text" name="q" class="filter-control" placeholder="Buscar cédula, estudiante, encargado..." value="<?= e($busqueda); ?>" style="min-width: 260px;" id="searchInput">

                <!-- Filtro por Especialidad -->
                <select name="especialidad" class="filter-control">
                    <option value="">Todas las Especialidades</option>
                    <?php foreach ($especialidadesOpts as $opt): ?>
                        <option value="<?= e($opt); ?>" <?= ($filtroEsp === $opt) ? 'selected' : ''; ?>>
                            <?= e($opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Filtro por Estado -->
                <select name="estado" class="filter-control">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente" <?= ($filtroEstado === 'pendiente') ? 'selected' : ''; ?>>Pendientes</option>
                    <option value="en_revision" <?= ($filtroEstado === 'en_revision') ? 'selected' : ''; ?>>En Revisión</option>
                    <option value="aceptada" <?= ($filtroEstado === 'aceptada') ? 'selected' : ''; ?>>Aceptadas</option>
                    <option value="rechazada" <?= ($filtroEstado === 'rechazada') ? 'selected' : ''; ?>>Rechazadas</option>
                </select>

                <!-- Rango de Fechas -->
                <input type="date" name="desde" class="filter-control" title="Fecha Desde" value="<?= e($fechaDesde); ?>">
                <input type="date" name="hasta" class="filter-control" title="Fecha Hasta" value="<?= e($fechaHasta); ?>">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                    <?= icon('funnel', ['class' => 'icon-sm']); ?> Filtrar
                </button>
                <?php if ($filtroEsp !== '' || $filtroEstado !== '' || $fechaDesde !== '' || $fechaHasta !== '' || $busqueda !== ''): ?>
                    <a href="<?= url('admin/solicitudes.php'); ?>" class="btn btn-outline" style="padding: 8px 14px;" title="Limpiar filtros">
                        <?= icon('arrows-clockwise', ['class' => 'icon-sm']); ?> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Tabla de Pre-Matrículas -->
        <div class="table-responsive" style="margin-bottom: 50px;">
            <table class="data-table" id="prematriculasTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Especialidad</th>
                        <th>Encargado / Contacto</th>
                        <th>Estado Actual</th>
                        <th style="width: 140px;">Fecha Registro</th>
                        <th style="text-align: right; width: 170px;">Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitudes)): ?>
                        <?php foreach ($solicitudes as $sol): ?>
                            <tr>
                                <td>#<?= (int)$sol['id']; ?></td>
                                <td><strong><?= e($sol['cedula_estudiante'] ?? ''); ?></strong></td>
                                <td>
                                    <strong><?= e(($sol['nombre_estudiante'] ?? '') . ' ' . ($sol['primer_apellido'] ?? '') . ' ' . ($sol['segundo_apellido'] ?? '')); ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                        Nacimiento: <?= date('d/m/Y', strtotime($sol['fecha_nacimiento'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--color-primary);">
                                        <?= e($sol['especialidad_interes'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= e($sol['nombre_encargado'] ?? ''); ?></div>
                                    <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                                        <a href="tel:<?= e($sol['telefono_contacto'] ?? ''); ?>"><?= e($sol['telefono_contacto'] ?? ''); ?></a> | 
                                        <a href="mailto:<?= e($sol['correo_contacto'] ?? ''); ?>"><?= e($sol['correo_contacto'] ?? ''); ?></a>
                                    </div>
                                    <?php if (!empty($sol['observaciones'])): ?>
                                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px; font-style: italic;">
                                            Obs: <?= e(mb_substr($sol['observaciones'], 0, 70, 'UTF-8')); ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= e($sol['estado_solicitud'] ?? 'pendiente'); ?>">
                                        <?= e(str_replace('_', ' ', $sol['estado_solicitud'] ?? 'pendiente')); ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--color-text-muted);">
                                    <?= date('d/m/Y H:i', strtotime($sol['fecha_registro'])); ?>
                                </td>
                                <td style="text-align: right;">
                                    <form action="<?= url('admin/solicitudes.php'); ?>" method="POST" style="display: flex; gap: 5px; justify-content: flex-end;">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="actualizar_estado">
                                        <input type="hidden" name="solicitud_id" value="<?= (int)$sol['id']; ?>">
                                        <select name="nuevo_estado" class="filter-control" style="font-size: 0.8rem; padding: 4px 6px; min-height: 36px;" onchange="this.form.submit()">
                                            <option value="pendiente" <?= ($sol['estado_solicitud'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="en_revision" <?= ($sol['estado_solicitud'] === 'en_revision') ? 'selected' : ''; ?>>En Revisión</option>
                                            <option value="aceptada" <?= ($sol['estado_solicitud'] === 'aceptada') ? 'selected' : ''; ?>>Aceptar</option>
                                            <option value="rechazada" <?= ($sol['estado_solicitud'] === 'rechazada') ? 'selected' : ''; ?>>Rechazar</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--color-text-subtle); padding: 30px;">
                                No se encontraron solicitudes que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SECCIÓN DE CONSULTAS GENERALES -->
        <div style="margin-top: 40px; margin-bottom: 20px;" id="consultas">
            <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.5rem; margin-bottom: 4px;">
                Bandeja de Consultas y Contacto
            </h2>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                Mensajes enviados por padres de familia y postulantes mediante el formulario de contacto institucional.
            </p>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Remitente</th>
                        <th>Contacto</th>
                        <th>Asunto & Mensaje</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th style="text-align: right; width: 140px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consultas)): ?>
                        <?php foreach ($consultas as $c): ?>
                            <tr>
                                <td>#<?= (int)$c['id']; ?></td>
                                <td><strong><?= e($c['nombre_remitente'] ?? ''); ?></strong></td>
                                <td>
                                    <div><?= e($c['correo_remitente'] ?? ''); ?></div>
                                    <?php if (!empty($c['telefono'])): ?>
                                        <div style="font-size: 0.82rem; color: var(--color-text-muted);"><?= e($c['telefono']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($c['asunto'] ?? ''); ?></strong>
                                    <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px;">
                                        <?= nl2br(e($c['mensaje'] ?? '')); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($c['leido'])): ?>
                                        <span class="badge badge-activo"><?= icon('check-circle', ['class' => 'icon-sm']); ?> Leído</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo"><?= icon('bell-ringing', ['class' => 'icon-sm']); ?> No Leído</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--color-text-muted);">
                                    <?= date('d/m/Y H:i', strtotime($c['fecha_envio'])); ?>
                                </td>
                                <td style="text-align: right;">
                                    <form action="<?= url('admin/solicitudes.php'); ?>" method="POST" style="display: inline;">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="marcar_leido">
                                        <input type="hidden" name="consulta_id" value="<?= (int)$c['id']; ?>">
                                        <input type="hidden" name="leido" value="<?= !empty($c['leido']) ? '0' : '1'; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline">
                                            <?= !empty($c['leido']) ? 'Marcar No Leído' : 'Marcar Leído'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--color-text-subtle); padding: 30px;">
                                No hay mensajes recibidos en la bandeja de contacto.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>
