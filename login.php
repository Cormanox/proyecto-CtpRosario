<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

// Si ya tiene sesión activa, redirigir al panel de administración
if (isset($_SESSION['user_id'])) {
    header("Location: " . url('admin/index.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        $error = 'Token de seguridad inválido o expirado. Por favor intente nuevamente.';
    } else {
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));

        if (empty($email) || empty($password)) {
            $error = 'Por favor, ingresa tu correo y contraseña.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, nombre, email, password_hash, rol, estado 
                     FROM usuarios 
                     WHERE email = :email 
                     LIMIT 1"
                );
                $stmt->execute([':email' => $email]);
                $usuario = $stmt->fetch();

                if ($usuario) {
                    if ((int)$usuario['estado'] !== 1) {
                        $error = 'Tu cuenta está inactiva o suspendida. Contacta a la dirección.';
                    } elseif (password_verify($password, $usuario['password_hash'])) {
                        // Prevenir Session Fixation
                        session_regenerate_id(true);

                        // Inicializar sesión segura
                        $_SESSION['user_id']    = (int)$usuario['id'];
                        $_SESSION['user_name']  = (string)$usuario['nombre'];
                        $_SESSION['user_email'] = (string)$usuario['email'];
                        $_SESSION['user_role']  = (string)$usuario['rol'];

                        set_flash('success', "¡Bienvenido al panel, " . htmlspecialchars($usuario['nombre']) . "!");
                        header("Location: " . url('admin/index.php'));
                        exit;
                    } else {
                        $error = 'Contraseña incorrecta. Por favor, verifica tus credenciales.';
                    }
                } else {
                    $error = 'No existe una cuenta registrada con este correo electrónico.';
                }
            } catch (PDOException $e) {
                error_log("[Login Error] " . $e->getMessage());
                $error = 'Ocurrió un error al procesar el inicio de sesión. Por favor, intente nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - CTP Rosario</title>
    <!-- Estilos CSS Centralizados -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
</head>
<body class="login-body">

    <main class="login-container">
        <div class="login-card">
            <header class="login-header">
                <a href="<?= url('public/index.php'); ?>" title="Volver al Portal Web">
                    <img src="<?= url('assets/img/escudoctp.png'); ?>" alt="Escudo CTP Rosario" class="logo-img" style="height: 68px; margin: 0 auto; display: block;" width="68" height="68">
                </a>
                <h2 style="font-size: clamp(1.4rem, 3vw, 1.8rem); margin-top: 14px;">Acceso Administrativo</h2>
                <p>Colegio Técnico Profesional Rosario</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= icon('circle-exclamation', ['class' => 'icon-md']); ?>
                    <div><?= e($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('login.php'); ?>" method="POST" autocomplete="on">
                <?= csrf_field(); ?>

                <div class="form-group">
                    <label for="email" style="display: flex; align-items: center; gap: 6px;">
                        <?= icon('envelope', ['class' => 'icon-sm']); ?> Correo Institucional
                    </label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= e($email ?: 'admin@ctprosario.ed.cr'); ?>" placeholder="admin@ctprosario.ed.cr" autofocus autocomplete="username">
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="password" style="display: flex; align-items: center; gap: 6px;">
                        <?= icon('lock', ['class' => 'icon-sm']); ?> Contraseña de Acceso
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" autocomplete="current-password" style="padding-right: 44px;">
                        <button type="button" onclick="toggleLoginPassword()" id="btnTogglePass" aria-label="Mostrar u ocultar contraseña" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94A3B8; cursor: pointer; padding: 6px; min-height: 36px; display: inline-flex; align-items: center;">
                            <span id="toggleIconWrap"><?= icon('eye', ['class' => 'icon-sm']); ?></span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-size: 1rem; border-radius: var(--radius-md); justify-content: center;">
                    <?= icon('arrow-right-to-bracket', ['class' => 'icon-sm']); ?> Iniciar Sesión Segura
                </button>
            </form>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--color-border); text-align: center; font-size: 0.85rem; color: var(--color-text-muted);">
                <div style="background: var(--color-bg-page); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 16px; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 8px; color: var(--color-primary); font-weight: 700; margin-bottom: 6px;">
                        <?= icon('shield-check', ['class' => 'icon-sm', 'style' => 'color: var(--color-secondary);']); ?>
                        <span>Credenciales del Sistema:</span>
                    </div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; color: var(--color-text-main); display: flex; flex-direction: column; gap: 3px;">
                        <div>Usuario: <strong>admin@ctprosario.ed.cr</strong></div>
                        <div>Clave: <strong>admin123</strong></div>
                    </div>
                </div>

                <a href="<?= url('public/index.php'); ?>" style="color: var(--color-secondary); font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('arrow-left', ['class' => 'icon-sm']); ?> Volver al Portal Institucional
                </a>
            </div>
        </div>
    </main>

    <script>
        function toggleLoginPassword() {
            const passInput = document.getElementById('password');
            const wrap = document.getElementById('toggleIconWrap');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                wrap.innerHTML = '<?= addslashes(icon('eye-slash', ['class' => 'icon-sm'])); ?>';
            } else {
                passInput.type = 'password';
                wrap.innerHTML = '<?= addslashes(icon('eye', ['class' => 'icon-sm'])); ?>';
            }
        }
    </script>

    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>
