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
    <link rel="stylesheet" href="<?= url('assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body style="background-color: #f1f5f9;">

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <a href="<?= url('public/index.php'); ?>" style="text-decoration: none; display: inline-block; margin-bottom: 12px;">
                    <img src="<?= url('assets/img/escudoctp.png'); ?>" alt="Escudo CTP Rosario" style="height: 65px; object-fit: contain;">
                </a>
                <h2>Panel Administrativo</h2>
                <p>Colegio Técnico Profesional Rosario</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><?= e($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('login.php'); ?>" method="POST">
                <div class="form-group">
                    <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= e($email ?: 'admin@ctprosario.ed.cr'); ?>" placeholder="admin@ctprosario.ed.cr" autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password"><i class="fa-solid fa-lock"></i> Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; cursor: pointer;">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión
                </button>
            </form>

            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 0.85rem; color: #64748b;">
                <p style="margin-bottom: 10px;">
                    <i class="fa-solid fa-circle-info" style="color: #003366;"></i> <strong>Credenciales configuradas:</strong><br>
                    Usuario: <code>admin@ctprosario.ed.cr</code><br>
                    Clave: <code>admin123</code>
                </p>
                <a href="<?= url('public/index.php'); ?>" style="color: #003366; text-decoration: none; font-weight: 600;">
                    &larr; Volver al Portal Web
                </a>
            </div>
        </div>
    </div>

</body>
</html>
