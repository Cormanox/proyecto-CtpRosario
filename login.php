<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

// Si ya tiene sesión activa, redirigir al panel de administración
if (isset($_SESSION['user_id'])) {
    header("Location: " . url('admin/index.php'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Por favor, ingresa tu correo y contraseña.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol, estado 
                                   FROM usuarios 
                                   WHERE email = :email 
                                   LIMIT 1");
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                // Verificar estado de la cuenta
                if ((int)$usuario['estado'] !== 1) {
                    $error = 'Tu cuenta está inactiva. Contacta al administrador del sistema.';
                } elseif (password_verify($password, $usuario['password_hash'])) {
                    // Prevenir Session Fixation
                    session_regenerate_id(true);

                    // Inicializar sesión segura
                    $_SESSION['user_id']    = (int)$usuario['id'];
                    $_SESSION['user_name']  = (string)$usuario['nombre'];
                    $_SESSION['user_email'] = (string)$usuario['email'];
                    $_SESSION['user_role']  = (string)$usuario['rol'];

                    header("Location: " . url('admin/index.php'));
                    exit;
                } else {
                    $error = 'Contraseña incorrecta. Por favor, verifica tus datos.';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - CTP Rosario</title>
    <!-- Estilos CSS Centralizados -->
    <link rel="stylesheet" href="<?= url('assets/css/styles.css'); ?>">
    <!-- FontAwesome 6 para iconografía institucional -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="login-body">

    <main class="login-container">
        <div class="login-card">
            <header class="login-header">
                <a href="<?= url('public/index.php'); ?>" title="Volver al Portal Web">
                    <img src="<?= url('assets/img/escudoctp.png'); ?>" alt="Escudo CTP Rosario" class="logo-img" style="height: 68px; margin: 0 auto; display: block;">
                </a>
                <h2>Acceso Administrativo</h2>
                <p>Colegio Técnico Profesional Rosario</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 1.15rem; margin-top: 2px;"></i>
                    <div><?= e($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('login.php'); ?>" method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico Institucional</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= e($email ?: 'admin@ctprosario.ed.cr'); ?>" placeholder="admin@ctprosario.ed.cr" autofocus autocomplete="username">
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="password"><i class="fa-solid fa-lock"></i> Contraseña de Acceso</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" autocomplete="current-password" style="padding-right: 42px;">
                        <button type="button" onclick="toggleLoginPassword()" id="btnTogglePass" aria-label="Mostrar u ocultar contraseña" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; font-size: 0.95rem;">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-size: 1rem; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión Segura
                </button>
            </form>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--color-border); text-align: center; font-size: 0.85rem; color: var(--color-text-muted);">
                <div style="background: var(--color-bg-page); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 16px; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 8px; color: var(--color-primary); font-weight: 700; margin-bottom: 6px;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--color-secondary);"></i>
                        <span>Credenciales del Sistema:</span>
                    </div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; color: var(--color-text-main); display: flex; flex-direction: column; gap: 3px;">
                        <div>Usuario: <strong>admin@ctprosario.ed.cr</strong></div>
                        <div>Clave: <strong>admin123</strong></div>
                    </div>
                </div>

                <a href="<?= url('public/index.php'); ?>" style="color: var(--color-secondary); font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Portal Institucional
                </a>
            </div>
        </div>
    </main>

    <script>
        function toggleLoginPassword() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
