<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Control estricto de acceso: Solo administradores pueden gestionar usuarios
requerirRol('admin');

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$mensaje = '';
$tipoMensaje = '';

// Procesar acciones POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $submittedToken = (string)($_POST['csrf_token'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || !empty($_POST['is_ajax']);

    // 1. Verificación obligatoria de CSRF
    if (!validar_csrf($submittedToken)) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido o expirado. Recargue la página.']);
            exit;
        }
        $mensaje = 'Token de seguridad (CSRF) inválido o expirado. Por favor recargue la página e intente de nuevo.';
        $tipoMensaje = 'danger';
    } else {
        // --- ACCIÓN: CREAR USUARIO ---
        if ($action === 'crear_usuario') {
            $nombre   = trim((string)($_POST['nombre'] ?? ''));
            $email    = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $rol      = trim((string)($_POST['rol'] ?? 'user'));
            $estado   = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

            $rolesValidos = ['admin', 'editor', 'user'];

            if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
                $mensaje = 'El nombre completo debe tener entre 3 y 100 caracteres.';
                $tipoMensaje = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
                $mensaje = 'Ingrese una dirección de correo electrónico válida.';
                $tipoMensaje = 'danger';
            } elseif (strlen($password) < 8) {
                $mensaje = 'La contraseña debe tener un mínimo de 8 caracteres.';
                $tipoMensaje = 'danger';
            } elseif (!in_array($rol, $rolesValidos, true)) {
                $mensaje = 'El rol seleccionado no es válido.';
                $tipoMensaje = 'danger';
            } elseif (!in_array($estado, [0, 1], true)) {
                $mensaje = 'El estado seleccionado no es válido.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    // Verificar si el correo ya existe
                    $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
                    $checkStmt->execute([':email' => $email]);
                    if ($checkStmt->fetch()) {
                        $mensaje = 'Ya existe un usuario registrado con el correo: ' . e($email);
                        $tipoMensaje = 'danger';
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $insertStmt = $pdo->prepare(
                            "INSERT INTO usuarios (nombre, email, password_hash, rol, estado, creado_en) 
                             VALUES (:nombre, :email, :password_hash, :rol, :estado, NOW())"
                        );
                        $insertStmt->execute([
                            ':nombre'        => $nombre,
                            ':email'         => $email,
                            ':password_hash' => $hash,
                            ':rol'           => $rol,
                            ':estado'        => $estado,
                        ]);

                        $mensaje = "Usuario '{$nombre}' registrado exitosamente con rol '{$rol}'.";
                        $tipoMensaje = 'success';
                    }
                } catch (PDOException $e) {
                    error_log("[User Create Error] " . $e->getMessage());
                    $mensaje = 'Error interno al registrar el usuario.';
                    $tipoMensaje = 'danger';
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => ($tipoMensaje === 'success'),
                    'message' => $mensaje,
                    'csrf_token' => csrf_token()
                ]);
                exit;
            }

        // --- ACCIÓN: EDITAR USUARIO ---
        } elseif ($action === 'editar_usuario') {
            $id       = (int)($_POST['usuario_id'] ?? 0);
            $nombre   = trim((string)($_POST['nombre'] ?? ''));
            $email    = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $rol      = trim((string)($_POST['rol'] ?? 'user'));
            $estado   = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

            $rolesValidos = ['admin', 'editor', 'user'];

            if ($id <= 0) {
                $mensaje = 'Identificador de usuario inválido.';
                $tipoMensaje = 'danger';
            } elseif (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
                $mensaje = 'El nombre completo debe tener entre 3 y 100 caracteres.';
                $tipoMensaje = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensaje = 'El correo electrónico ingresado no es válido.';
                $tipoMensaje = 'danger';
            } elseif (!in_array($rol, $rolesValidos, true)) {
                $mensaje = 'El rol seleccionado no es válido.';
                $tipoMensaje = 'danger';
            } elseif (!in_array($estado, [0, 1], true)) {
                $mensaje = 'El estado seleccionado no es válido.';
                $tipoMensaje = 'danger';
            } elseif ($password !== '' && strlen($password) < 8) {
                $mensaje = 'Si modifica la contraseña, debe tener al menos 8 caracteres.';
                $tipoMensaje = 'danger';
            // Prevención de auto-bloqueo al editar la propia cuenta
            } elseif ($id === $currentUserId && $rol !== 'admin') {
                $mensaje = 'Seguridad: No puedes revocar tus propios privilegios de administrador.';
                $tipoMensaje = 'danger';
            } elseif ($id === $currentUserId && $estado !== 1) {
                $mensaje = 'Seguridad: No puedes desactivar o suspender tu propia cuenta activa.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    // Verificar si el correo pertenece a otro usuario
                    $checkEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
                    $checkEmail->execute([':email' => $email, ':id' => $id]);
                    if ($checkEmail->fetch()) {
                        $mensaje = 'El correo electrónico ya está registrado por otro usuario.';
                        $tipoMensaje = 'danger';
                    } else {
                        if ($password !== '') {
                            $hash = password_hash($password, PASSWORD_BCRYPT);
                            $updateStmt = $pdo->prepare(
                                "UPDATE usuarios 
                                 SET nombre = :nombre, email = :email, password_hash = :hash, rol = :rol, estado = :estado 
                                 WHERE id = :id"
                            );
                            $updateStmt->execute([
                                ':nombre' => $nombre,
                                ':email'  => $email,
                                ':hash'   => $hash,
                                ':rol'    => $rol,
                                ':estado' => $estado,
                                ':id'     => $id
                            ]);
                        } else {
                            $updateStmt = $pdo->prepare(
                                "UPDATE usuarios 
                                 SET nombre = :nombre, email = :email, rol = :rol, estado = :estado 
                                 WHERE id = :id"
                            );
                            $updateStmt->execute([
                                ':nombre' => $nombre,
                                ':email'  => $email,
                                ':rol'    => $rol,
                                ':estado' => $estado,
                                ':id'     => $id
                            ]);
                        }

                        // Actualizar variables de sesión si se editó el propio usuario
                        if ($id === $currentUserId) {
                            $_SESSION['user_name']  = $nombre;
                            $_SESSION['user_email'] = $email;
                        }

                        $mensaje = "Usuario #{$id} ({$nombre}) actualizado correctamente.";
                        $tipoMensaje = 'success';
                    }
                } catch (PDOException $e) {
                    error_log("[User Edit Error] " . $e->getMessage());
                    $mensaje = 'Error interno al actualizar el usuario.';
                    $tipoMensaje = 'danger';
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => ($tipoMensaje === 'success'),
                    'message' => $mensaje,
                    'csrf_token' => csrf_token()
                ]);
                exit;
            }

        // --- ACCIÓN: CAMBIAR ROL DINÁMICAMENTE ---
        } elseif ($action === 'cambiar_rol') {
            $id = (int)($_POST['usuario_id'] ?? 0);
            $nuevoRol = trim((string)($_POST['nuevo_rol'] ?? ''));
            $rolesValidos = ['admin', 'editor', 'user'];

            if ($id <= 0 || !in_array($nuevoRol, $rolesValidos, true)) {
                $mensaje = 'Datos de solicitud de rol no válidos.';
                $tipoMensaje = 'danger';
            // Prevención de auto-bloqueo
            } elseif ($id === $currentUserId && $nuevoRol !== 'admin') {
                $mensaje = 'Seguridad: No puedes revocar tus propios privilegios de administrador.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE usuarios SET rol = :rol WHERE id = :id");
                    $stmt->execute([':rol' => $nuevoRol, ':id' => $id]);
                    $mensaje = "Rol del usuario #{$id} actualizado a '{$nuevoRol}'.";
                    $tipoMensaje = 'success';
                } catch (PDOException $e) {
                    error_log("[User Role Update Error] " . $e->getMessage());
                    $mensaje = 'Error al actualizar el rol del usuario.';
                    $tipoMensaje = 'danger';
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => ($tipoMensaje === 'success'),
                    'message' => $mensaje,
                    'csrf_token' => csrf_token()
                ]);
                exit;
            }

        // --- ACCIÓN: CAMBIAR ESTADO (ACTIVO/SUSPENDIDO) ---
        } elseif ($action === 'cambiar_estado') {
            $id = (int)($_POST['usuario_id'] ?? 0);
            $nuevoEstado = isset($_POST['nuevo_estado']) ? (int)$_POST['nuevo_estado'] : null;

            if ($id <= 0 || !in_array($nuevoEstado, [0, 1], true)) {
                $mensaje = 'Datos de estado no válidos.';
                $tipoMensaje = 'danger';
            // Prevención de auto-bloqueo
            } elseif ($id === $currentUserId) {
                $mensaje = 'Seguridad: No puedes suspender o desactivar tu propia cuenta activa.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
                    $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
                    $estadoTexto = ($nuevoEstado === 1) ? 'activada' : 'suspendida/inactiva';
                    $mensaje = "La cuenta del usuario #{$id} ha sido {$estadoTexto}.";
                    $tipoMensaje = 'success';
                } catch (PDOException $e) {
                    error_log("[User Status Update Error] " . $e->getMessage());
                    $mensaje = 'Error al actualizar el estado del usuario.';
                    $tipoMensaje = 'danger';
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => ($tipoMensaje === 'success'),
                    'message' => $mensaje,
                    'csrf_token' => csrf_token()
                ]);
                exit;
            }

        // --- ACCIÓN: ELIMINAR USUARIO ---
        } elseif ($action === 'eliminar_usuario') {
            $id = (int)($_POST['usuario_id'] ?? 0);

            if ($id <= 0) {
                $mensaje = 'Identificador de usuario inválido para eliminación.';
                $tipoMensaje = 'danger';
            // Prevención de auto-bloqueo
            } elseif ($id === $currentUserId) {
                $mensaje = 'Seguridad: No puedes eliminar tu propia cuenta de administrador con sesión activa.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    // Obtener nombre antes de eliminar para el mensaje
                    $nameStmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = :id LIMIT 1");
                    $nameStmt->execute([':id' => $id]);
                    $userToDelete = $nameStmt->fetch();

                    if (!$userToDelete) {
                        $mensaje = 'El usuario que intenta eliminar no existe o ya fue removido.';
                        $tipoMensaje = 'danger';
                    } else {
                        $deleteStmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
                        $deleteStmt->execute([':id' => $id]);
                        $mensaje = "Usuario #{$id} (" . e($userToDelete['nombre']) . ") eliminado definitivamente.";
                        $tipoMensaje = 'success';
                    }
                } catch (PDOException $e) {
                    error_log("[User Delete Error] " . $e->getMessage());
                    $mensaje = 'Error al eliminar el usuario del sistema.';
                    $tipoMensaje = 'danger';
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => ($tipoMensaje === 'success'),
                    'message' => $mensaje,
                    'csrf_token' => csrf_token()
                ]);
                exit;
            }
        }
    }
}

// Búsqueda y Filtrado
$busqueda     = trim((string)($_GET['q'] ?? ''));
$filtroRol    = trim((string)($_GET['rol'] ?? ''));
$filtroEstado = (isset($_GET['estado']) && $_GET['estado'] !== '') ? (int)$_GET['estado'] : null;

try {
    $whereConditions = [];
    $queryParams = [];

    if ($busqueda !== '') {
        $whereConditions[] = "(nombre LIKE :q_nombre OR email LIKE :q_email)";
        $queryParams[':q_nombre'] = '%' . $busqueda . '%';
        $queryParams[':q_email']  = '%' . $busqueda . '%';
    }

    if ($filtroRol !== '' && in_array($filtroRol, ['admin', 'editor', 'user'], true)) {
        $whereConditions[] = "rol = :rol";
        $queryParams[':rol'] = $filtroRol;
    }

    if ($filtroEstado !== null && in_array($filtroEstado, [0, 1], true)) {
        $whereConditions[] = "estado = :estado";
        $queryParams[':estado'] = $filtroEstado;
    }

    $sql = "SELECT id, nombre, email, rol, estado, creado_en FROM usuarios";
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(' AND ', $whereConditions);
    }
    $sql .= " ORDER BY creado_en DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($queryParams);
    $usuarios = $stmt->fetchAll();

    // Contadores generales
    $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalAdmins   = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
    $totalEditores = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'editor'")->fetchColumn();
    $totalActivos  = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 1")->fetchColumn();

} catch (PDOException $e) {
    error_log("[Usuarios Load Error] " . $e->getMessage());
    die("Error al cargar la información de usuarios.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y Roles - CTP Rosario</title>
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
                    <li><a href="<?= url('admin/index.php'); ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <?php if (tieneRol('admin')): ?>
                        <li><a href="<?= url('admin/solicitudes.php'); ?>"><i class="fa-solid fa-file-signature"></i> Solicitudes & Consultas</a></li>
                        <li><a href="<?= url('admin/usuarios.php'); ?>" class="active"><i class="fa-solid fa-users-gear"></i> Usuarios</a></li>
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

        <!-- Encabezado de la página -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="color: #003366; margin-bottom: 5px;">
                    <i class="fa-solid fa-users-gear" style="color: #871a2f;"></i> Gestión de Usuarios y Roles
                </h2>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Administra las cuentas de acceso al panel, asigna permisos institucionales y audita el estado del personal.
                </p>
            </div>
            <button type="button" class="btn btn-primary" onclick="abrirModalCrear()" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-plus"></i> Crear Nuevo Usuario
            </button>
        </div>

        <!-- Alertas de estado -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= e($tipoMensaje); ?>" id="alertNotification">
                <i class="fa-solid <?= ($tipoMensaje === 'success') ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <div><?= e($mensaje); ?></div>
            </div>
        <?php endif; ?>

        <!-- Resumen de Contadores Rápidos -->
        <div class="stats-grid" style="margin-bottom: 25px;">
            <div class="stat-card" style="border-left-color: #871a2f;">
                <div class="stat-icon" style="background: #fdf2f4; color: #871a2f;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-content">
                    <h4>Total Usuarios</h4>
                    <div class="stat-number"><?= $totalUsuarios; ?></div>
                    <small style="color: #10b981;"><?= $totalActivos; ?> activos en sistema</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #ef4444;">
                <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <h4>Administradores</h4>
                    <div class="stat-number"><?= $totalAdmins; ?></div>
                    <small style="color: #ef4444;">Acceso completo</small>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div class="stat-content">
                    <h4>Editores</h4>
                    <div class="stat-number"><?= $totalEditores; ?></div>
                    <small style="color: #3b82f6;">Gestión de noticias</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Búsqueda y Filtros -->
        <div class="filter-card">
            <form action="<?= url('admin/usuarios.php'); ?>" method="GET" class="filter-controls" id="filterForm">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o correo..." value="<?= e($busqueda); ?>" autocomplete="off">
                </div>

                <select name="rol" class="filter-select" onchange="document.getElementById('filterForm').submit();">
                    <option value="">-- Todos los Roles --</option>
                    <option value="admin" <?= ($filtroRol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="editor" <?= ($filtroRol === 'editor') ? 'selected' : ''; ?>>Editor</option>
                    <option value="user" <?= ($filtroRol === 'user') ? 'selected' : ''; ?>>Usuario</option>
                </select>

                <select name="estado" class="filter-select" onchange="document.getElementById('filterForm').submit();">
                    <option value="">-- Todos los Estados --</option>
                    <option value="1" <?= ($filtroEstado === 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?= ($filtroEstado === 0) ? 'selected' : ''; ?>>Inactivo / Suspendido</option>
                </select>

                <button type="submit" class="btn btn-sm btn-primary" style="margin: 0; padding: 8px 16px;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>

                <?php if ($busqueda !== '' || $filtroRol !== '' || $filtroEstado !== null): ?>
                    <a href="<?= url('admin/usuarios.php'); ?>" class="btn btn-sm btn-outline" style="margin: 0; padding: 8px 14px;" title="Limpiar Filtros">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>

            <div style="font-size: 0.88rem; color: #64748b; font-weight: 500;">
                Mostrando: <strong><?= count($usuarios); ?></strong> de <?= $totalUsuarios; ?> usuarios
            </div>
        </div>

        <!-- Tabla Responsiva de Usuarios -->
        <div class="table-responsive">
            <table class="data-table" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nombre y Apellidos</th>
                        <th>Correo Electrónico</th>
                        <th style="width: 170px;">Rol de Acceso</th>
                        <th style="width: 120px;">Estado</th>
                        <th style="width: 140px;">Fecha Registro</th>
                        <th style="width: 160px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php 
                                $esPropioUsuario = ((int)$u['id'] === $currentUserId); 
                                $esActivo = ((int)$u['estado'] === 1);
                            ?>
                            <tr id="user-row-<?= (int)$u['id']; ?>" style="<?= $esPropioUsuario ? 'background: #fdfaf6;' : ''; ?>">
                                <td>
                                    <strong>#<?= (int)$u['id']; ?></strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                        <?= e($u['nombre']); ?>
                                        <?php if ($esPropioUsuario): ?>
                                            <span style="font-size: 0.7rem; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: bold;" title="Tu sesión activa">
                                                <i class="fa-solid fa-user-check"></i> Tú
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: #475569; font-size: 0.9rem;">
                                        <i class="fa-regular fa-envelope" style="color: #94a3b8; margin-right: 4px;"></i>
                                        <?= e($u['email']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Selector dinámico de Rol con prevención de auto-degradación -->
                                    <form action="<?= url('admin/usuarios.php'); ?>" method="POST" style="margin: 0;" class="role-change-form">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="cambiar_rol">
                                        <input type="hidden" name="usuario_id" value="<?= (int)$u['id']; ?>">
                                        <select name="nuevo_rol" 
                                                class="filter-select" 
                                                style="padding: 4px 8px; font-size: 0.82rem; font-weight: 600; width: 100%; <?= $esPropioUsuario ? 'background:#f8fafc; cursor:not-allowed;' : ''; ?>"
                                                <?= $esPropioUsuario ? 'disabled title="Seguridad: No puedes revocar tus propios privilegios de administrador"' : 'onchange="confirmarCambioRol(this, ' . (int)$u['id'] . ')"'; ?>>
                                            <option value="admin" <?= ($u['rol'] === 'admin') ? 'selected' : ''; ?>>🛡️ Administrador</option>
                                            <option value="editor" <?= ($u['rol'] === 'editor') ? 'selected' : ''; ?>>✍️ Editor</option>
                                            <option value="user" <?= ($u['rol'] === 'user') ? 'selected' : ''; ?>>👤 Usuario</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <!-- Badge de Estado con alternancia interactiva -->
                                    <?php if ($esActivo): ?>
                                        <span class="badge badge-activo" title="Cuenta operativa">
                                            <i class="fa-solid fa-circle-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo" title="Acceso bloqueado">
                                            <i class="fa-solid fa-circle-xmark"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: #64748b;">
                                    <?= date('d/m/Y H:i', strtotime($u['creado_en'])); ?>
                                </td>
                                <td>
                                    <div class="actions-cell" style="justify-content: center;">
                                        <!-- Botón Editar -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline" 
                                                style="margin: 0; padding: 6px 10px;" 
                                                title="Editar detalles del usuario"
                                                onclick='abrirModalEditar(<?= json_encode([
                                                    "id"     => (int)$u['id'],
                                                    "nombre" => $u['nombre'],
                                                    "email"  => $u['email'],
                                                    "rol"    => $u['rol'],
                                                    "estado" => (int)$u['estado'],
                                                    "es_yo"  => $esPropioUsuario
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Botón Alternar Estado (Activar/Suspender) -->
                                        <?php if ($esPropioUsuario): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline" 
                                                    style="margin: 0; padding: 6px 10px; opacity: 0.5; cursor: not-allowed;" 
                                                    disabled 
                                                    title="Seguridad: No puedes suspender tu propia cuenta">
                                                <i class="fa-solid fa-toggle-on" style="color: #10b981;"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= url('admin/usuarios.php'); ?>" method="POST" style="display: inline; margin: 0;">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="action" value="cambiar_estado">
                                                <input type="hidden" name="usuario_id" value="<?= (int)$u['id']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?= $esActivo ? 0 : 1; ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline" 
                                                        style="margin: 0; padding: 6px 10px;" 
                                                        title="<?= $esActivo ? 'Suspender cuenta de usuario' : 'Activar cuenta de usuario'; ?>"
                                                        onclick="return confirm('¿Confirma que desea <?= $esActivo ? 'suspender/desactivar' : 'activar'; ?> el acceso para este usuario?');">
                                                    <i class="fa-solid <?= $esActivo ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" style="color: <?= $esActivo ? '#10b981' : '#94a3b8'; ?>;"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Botón Eliminar -->
                                        <?php if ($esPropioUsuario): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline" 
                                                    style="margin: 0; padding: 6px 10px; opacity: 0.5; cursor: not-allowed;" 
                                                    disabled 
                                                    title="Seguridad: No puedes eliminar tu propia cuenta">
                                                <i class="fa-solid fa-trash-can" style="color: #cbd5e1;"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    style="margin: 0; padding: 6px 10px;" 
                                                    title="Eliminar usuario definitivamente"
                                                    onclick="abrirModalEliminar(<?= (int)$u['id']; ?>, '<?= e($u['nombre']); ?>', '<?= e($u['email']); ?>')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 40px 20px;">
                                <i class="fa-solid fa-user-slash" style="font-size: 2.2rem; display: block; margin-bottom: 12px; color: #cbd5e1;"></i>
                                No se encontraron usuarios con los criterios de búsqueda especificados.
                                <?php if ($busqueda !== '' || $filtroRol !== '' || $filtroEstado !== null): ?>
                                    <div style="margin-top: 10px;">
                                        <a href="<?= url('admin/usuarios.php'); ?>" style="color: #871a2f; font-weight: bold; text-decoration: underline;">
                                            Restablecer filtros de búsqueda
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: CREAR NUEVO USUARIO                                   -->
    <!-- ============================================================== -->
    <div class="modal" id="modalCrearUsuario" role="dialog" aria-modal="true" aria-labelledby="modalCrearTitle">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="modalCrearTitle">
                    <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Usuario
                </h3>
                <button type="button" class="modal-close" onclick="cerrarModal('modalCrearUsuario')" aria-label="Cerrar modal">&times;</button>
            </div>
            <form action="<?= url('admin/usuarios.php'); ?>" method="POST" id="formCrearUsuario">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action" value="crear_usuario">

                    <div class="form-group">
                        <label for="crear_nombre"><i class="fa-solid fa-id-badge"></i> Nombre Completo *</label>
                        <input type="text" id="crear_nombre" name="nombre" class="form-control" required minlength="3" maxlength="100" placeholder="Ej. Juan Pérez González">
                    </div>

                    <div class="form-group">
                        <label for="crear_email"><i class="fa-solid fa-envelope"></i> Correo Electrónico Institucional *</label>
                        <input type="email" id="crear_email" name="email" class="form-control" required maxlength="120" placeholder="nombre@ctprosario.ed.cr">
                    </div>

                    <div class="form-group">
                        <label for="crear_password"><i class="fa-solid fa-key"></i> Contraseña de Acceso *</label>
                        <div style="position: relative;">
                            <input type="password" id="crear_password" name="password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres">
                            <button type="button" onclick="togglePasswordVisibility('crear_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px;">Se encriptará con BCRYPT en la base de datos.</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="crear_rol"><i class="fa-solid fa-shield-halved"></i> Rol en Sistema *</label>
                            <select id="crear_rol" name="rol" class="form-control" required>
                                <option value="user">Usuario (Básico)</option>
                                <option value="editor">Editor (Noticias)</option>
                                <option value="admin">Administrador (Total)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="crear_estado"><i class="fa-solid fa-power-off"></i> Estado Inicial *</label>
                            <select id="crear_estado" name="estado" class="form-control" required>
                                <option value="1">Activo (Habilitado)</option>
                                <option value="0">Inactivo (Suspendido)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalCrearUsuario')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: EDITAR USUARIO                                        -->
    <!-- ============================================================== -->
    <div class="modal" id="modalEditarUsuario" role="dialog" aria-modal="true" aria-labelledby="modalEditarTitle">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="modalEditarTitle">
                    <i class="fa-solid fa-user-pen"></i> Editar Usuario #<span id="editar_id_badge"></span>
                </h3>
                <button type="button" class="modal-close" onclick="cerrarModal('modalEditarUsuario')" aria-label="Cerrar modal">&times;</button>
            </div>
            <form action="<?= url('admin/usuarios.php'); ?>" method="POST" id="formEditarUsuario">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action" value="editar_usuario">
                    <input type="hidden" id="editar_usuario_id" name="usuario_id" value="">

                    <div id="propio_aviso" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 10px 14px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-exclamation"></i> <strong>Aviso:</strong> Estás editando tu propia cuenta. Por seguridad, no puedes suspenderla ni cambiar tu rol de administrador.
                    </div>

                    <div class="form-group">
                        <label for="editar_nombre"><i class="fa-solid fa-id-badge"></i> Nombre Completo *</label>
                        <input type="text" id="editar_nombre" name="nombre" class="form-control" required minlength="3" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="editar_email"><i class="fa-solid fa-envelope"></i> Correo Electrónico *</label>
                        <input type="email" id="editar_email" name="email" class="form-control" required maxlength="120">
                    </div>

                    <div class="form-group">
                        <label for="editar_password"><i class="fa-solid fa-key"></i> Nueva Contraseña (Opcional)</label>
                        <div style="position: relative;">
                            <input type="password" id="editar_password" name="password" class="form-control" minlength="8" placeholder="Dejar en blanco para conservar la actual">
                            <button type="button" onclick="togglePasswordVisibility('editar_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px;">Mínimo 8 caracteres si se especifica.</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="editar_rol"><i class="fa-solid fa-shield-halved"></i> Rol *</label>
                            <select id="editar_rol" name="rol" class="form-control" required>
                                <option value="user">Usuario (Básico)</option>
                                <option value="editor">Editor (Noticias)</option>
                                <option value="admin">Administrador (Total)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editar_estado"><i class="fa-solid fa-power-off"></i> Estado *</label>
                            <select id="editar_estado" name="estado" class="form-control" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo / Suspendido</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEditarUsuario')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: CONFIRMAR ELIMINACIÓN                                 -->
    <!-- ============================================================== -->
    <div class="modal" id="modalEliminarUsuario" role="dialog" aria-modal="true" aria-labelledby="modalEliminarTitle">
        <div class="modal-dialog" style="max-width: 460px;">
            <div class="modal-header" style="background: #dc2626;">
                <h3 id="modalEliminarTitle" style="color: #fff;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Confirmar Eliminación
                </h3>
                <button type="button" class="modal-close" onclick="cerrarModal('modalEliminarUsuario')" aria-label="Cerrar modal">&times;</button>
            </div>
            <form action="<?= url('admin/usuarios.php'); ?>" method="POST" id="formEliminarUsuario">
                <div class="modal-body" style="text-align: center; padding: 30px 24px;">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action" value="eliminar_usuario">
                    <input type="hidden" id="eliminar_usuario_id" name="usuario_id" value="">

                    <div style="width: 60px; height: 60px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 18px auto;">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>

                    <h4 style="margin: 0 0 10px 0; color: #0f172a; font-size: 1.15rem;">
                        ¿Eliminar este usuario de forma permanente?
                    </h4>
                    <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 15px 0;">
                        Se borrará la cuenta de <strong id="eliminar_nombre" style="color: #1e293b;"></strong> (<span id="eliminar_email"></span>).
                    </p>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-size: 0.82rem; color: #b91c1c; text-align: left;">
                        <i class="fa-solid fa-circle-info"></i> <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminarán sus accesos inmediatamente.
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEliminarUsuario')">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash-can"></i> Sí, Eliminar Definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- SCRIPTS JAVASCRIPT VANILLA PARA MODALES Y ACCIONES             -->
    <!-- ============================================================== -->
    <script>
        function abrirModalCrear() {
            document.getElementById('formCrearUsuario').reset();
            const modal = document.getElementById('modalCrearUsuario');
            modal.classList.add('active');
            setTimeout(() => document.getElementById('crear_nombre').focus(), 100);
        }

        function abrirModalEditar(userData) {
            document.getElementById('formEditarUsuario').reset();
            document.getElementById('editar_usuario_id').value = userData.id;
            document.getElementById('editar_id_badge').textContent = userData.id;
            document.getElementById('editar_nombre').value = userData.nombre;
            document.getElementById('editar_email').value = userData.email;
            document.getElementById('editar_password').value = '';
            document.getElementById('editar_rol').value = userData.rol;
            document.getElementById('editar_estado').value = userData.estado;

            const avisoPropio = document.getElementById('propio_aviso');
            const rolSelect = document.getElementById('editar_rol');
            const estadoSelect = document.getElementById('editar_estado');

            if (userData.es_yo) {
                avisoPropio.style.display = 'block';
                rolSelect.disabled = true;
                estadoSelect.disabled = true;
            } else {
                avisoPropio.style.display = 'none';
                rolSelect.disabled = false;
                estadoSelect.disabled = false;
            }

            document.getElementById('modalEditarUsuario').classList.add('active');
            setTimeout(() => document.getElementById('editar_nombre').focus(), 100);
        }

        function abrirModalEliminar(id, nombre, email) {
            document.getElementById('eliminar_usuario_id').value = id;
            document.getElementById('eliminar_nombre').textContent = nombre;
            document.getElementById('eliminar_email').textContent = email;
            document.getElementById('modalEliminarUsuario').classList.add('active');
        }

        function cerrarModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Cerrar modal al hacer clic en el backdrop
        window.addEventListener('click', function(e) {
            ['modalCrearUsuario', 'modalEditarUsuario', 'modalEliminarUsuario'].forEach(function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal && e.target === modal) {
                    cerrarModal(modalId);
                }
            });
        });

        // Cerrar modal con tecla Escape
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['modalCrearUsuario', 'modalEditarUsuario', 'modalEliminarUsuario'].forEach(cerrarModal);
            }
        });

        // Alternar visibilidad de contraseñas
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Confirmar cambio dinámico de rol
        function confirmarCambioRol(selectElem, userId) {
            const nuevoRol = selectElem.value;
            const rolNombre = selectElem.options[selectElem.selectedIndex].text;
            if (confirm('¿Desea cambiar el rol del usuario #' + userId + ' a ' + rolNombre + '?')) {
                selectElem.form.submit();
            } else {
                // Revertir selección al recargar o restaurar
                window.location.reload();
            }
        }

        // Auto-cerrar alertas temporales
        const alertBox = document.getElementById('alertNotification');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = 'opacity 0.5s ease';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 6000);
        }
    </script>

</body>
</html>
