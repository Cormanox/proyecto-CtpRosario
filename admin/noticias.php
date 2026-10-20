<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Tanto admin como editor tienen acceso a noticias
requerirRol('admin', 'editor');


$mensaje = '';
$tipoMensaje = '';

// Función auxiliar para generar slug
function crearSlug(string $texto): string {
    $texto = preg_replace('~[^\pL\d]+~u', '-', $texto) ?? '';
    $texto = iconv('utf-8', 'us-ascii//TRANSLIT', $texto) ?: $texto;
    $texto = preg_replace('~[^-\w]+~', '', $texto) ?? '';
    $texto = trim($texto, '-');
    $texto = preg_replace('~-+~', '-', $texto) ?? '';
    $texto = strtolower($texto);
    return empty($texto) ? 'n-' . time() : $texto . '-' . substr(uniqid('', true), 0, 4);
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];

    if ($action === 'crear_noticia') {
        $titulo    = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $imagenUrl = trim($_POST['imagen_url'] ?? '');
        $estado    = in_array($_POST['estado'] ?? '', ['publicado', 'borrador'], true) ? $_POST['estado'] : 'publicado';

        if (empty($titulo) || empty($contenido)) {
            $mensaje = "El título y el contenido son obligatorios.";
            $tipoMensaje = "danger";
        } else {
            try {
                $slug = crearSlug($titulo);
                $userId = (int)($_SESSION['user_id'] ?? 1);

                $stmt = $pdo->prepare("INSERT INTO noticias (titulo, slug, contenido, imagen_url, estado, usuario_id, fecha_publicacion) 
                                       VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$titulo, $slug, $contenido, $imagenUrl, $estado, $userId]);

                $mensaje = "¡Noticia publicada con éxito!";
                $tipoMensaje = "success";
            } catch (PDOException $e) {
                error_log("[Noticias Insert Error] " . $e->getMessage());
                $mensaje = "Error interno al guardar la noticia. Por favor intente más tarde.";
                $tipoMensaje = "danger";
            }
        }
    } elseif ($action === 'eliminar_noticia') {
        $id = (int)($_POST['noticia_id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje = "Noticia eliminada correctamente.";
                $tipoMensaje = "success";
            } catch (PDOException $e) {
                error_log("[Noticias Delete Error] " . $e->getMessage());
                $mensaje = "Error al eliminar la noticia.";
                $tipoMensaje = "danger";
            }
        }
    } elseif ($action === 'cambiar_estado') {
        $id = (int)($_POST['noticia_id'] ?? 0);
        $nuevoEstado = in_array($_POST['nuevo_estado'] ?? '', ['publicado', 'borrador'], true) ? $_POST['nuevo_estado'] : 'publicado';
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE noticias SET estado = ? WHERE id = ?");
                $stmt->execute([$nuevoEstado, $id]);
                $mensaje = "Estado de la noticia actualizado a '{$nuevoEstado}'.";
                $tipoMensaje = "success";
            } catch (PDOException $e) {
                error_log("[Noticias Update Error] " . $e->getMessage());
                $mensaje = "Error al cambiar el estado.";
                $tipoMensaje = "danger";
            }
        }
    }
}

// Cargar todas las noticias
try {
    $noticias = $pdo->query("SELECT n.*, u.nombre AS autor 
                             FROM noticias n 
                             LEFT JOIN usuarios u ON n.usuario_id = u.id 
                             ORDER BY n.fecha_publicacion DESC")->fetchAll();
} catch (PDOException $e) {
    error_log("[Noticias Load Error] " . $e->getMessage());
    die("Error al cargar el módulo de noticias.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias y Comunicados - CTP Rosario</title>
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
                    <?php if (tieneRol('admin')): ?>
                        <li><a href="<?= url('admin/solicitudes.php'); ?>"><i class="fa-solid fa-file-signature"></i> Solicitudes & Consultas</a></li>
                        <li><a href="<?= url('admin/usuarios.php'); ?>"><i class="fa-solid fa-users-gear"></i> Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="<?= url('admin/noticias.php'); ?>" class="active"><i class="fa-solid fa-newspaper"></i> Noticias</a></li>
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

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= e($tipoMensaje); ?>">
                <i class="fa-solid fa-circle-info"></i>
                <div><?= e($mensaje); ?></div>
            </div>
        <?php endif; ?>

        <!-- Formulario Crear Noticia -->
        <div class="form-card">
            <h3 style="color: #003366; margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fa-solid fa-pen-to-square"></i> Publicar Nueva Noticia o Comunicado
            </h3>

            <form action="<?= url('admin/noticias.php'); ?>" method="POST">
                <input type="hidden" name="action" value="crear_noticia">

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="titulo">Título de la Noticia *</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ej: Inauguración del Ciclo Lectivo 2026">
                    </div>

                    <div class="form-group">
                        <label for="imagen_url">URL de Imagen (Opcional)</label>
                        <input type="url" id="imagen_url" name="imagen_url" class="form-control" placeholder="https://ejemplo.com/foto.jpg">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado de Publicación *</label>
                        <select id="estado" name="estado" class="form-control">
                            <option value="publicado">Publicado (Visible en el sitio web)</option>
                            <option value="borrador">Borrador (Oculto)</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="contenido">Contenido del Comunicado *</label>
                        <textarea id="contenido" name="contenido" class="form-control" required placeholder="Escribe el texto de la noticia aquí..." style="min-height: 140px;"></textarea>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 15px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 25px; cursor: pointer;">
                        <i class="fa-solid fa-plus-circle"></i> Guardar y Publicar
                    </button>
                </div>
            </form>
        </div>

        <!-- Listado de Noticias -->
        <h3 style="color: #003366; margin: 40px 0 15px 0; font-size: 1.3rem;">
            <i class="fa-solid fa-list-check"></i> Noticias Registradas (<?= count($noticias); ?>)
        </h3>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($noticias)): ?>
                        <?php foreach ($noticias as $n): ?>
                            <tr>
                                <td>#<?= (int)$n['id']; ?></td>
                                <td style="width: 70px;">
                                    <img src="<?= !empty($n['imagen_url']) ? e($n['imagen_url']) : 'https://via.placeholder.com/80x50?text=CTP'; ?>" alt="<?= e($n['titulo']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://via.placeholder.com/80x50?text=CTP';">
                                </td>
                                <td>
                                    <strong><?= e($n['titulo']); ?></strong><br>
                                    <small style="color: #64748b;"><?= e(mb_substr($n['contenido'] ?? '', 0, 90, 'UTF-8')) . '...'; ?></small>
                                </td>
                                <td><?= e($n['autor'] ?? 'Admin'); ?></td>
                                <td><?= date('d/m/Y', strtotime($n['fecha_publicacion'])); ?></td>
                                <td>
                                    <span class="badge badge-<?= e($n['estado']); ?>">
                                        <?= e($n['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <!-- Botón cambiar estado -->
                                        <form method="POST" action="<?= url('admin/noticias.php'); ?>" style="display: inline;">
                                            <input type="hidden" name="action" value="cambiar_estado">
                                            <input type="hidden" name="noticia_id" value="<?= (int)$n['id']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?= ($n['estado'] === 'publicado') ? 'borrador' : 'publicado'; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary" style="border: 1px solid #cbd5e1;" title="Alternar estado">
                                                <i class="fa-solid <?= ($n['estado'] === 'publicado') ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Botón eliminar -->
                                        <form method="POST" action="<?= url('admin/noticias.php'); ?>" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta noticia?');">
                                            <input type="hidden" name="action" value="eliminar_noticia">
                                            <input type="hidden" name="noticia_id" value="<?= (int)$n['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 25px;">No hay noticias registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
