<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';

use SistemaAdmin\Services\ServicioAutenticacion;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);
$usuario = $servicioAutenticacion->verificarSesion();

if ($usuario === null) {
    header('Location: ' . app_base_path('/public/login.php'));
    exit();
}

if ((int) ($_SESSION['must_change_password'] ?? 0) !== 1) {
    header('Location: ' . app_base_path('/'));
    exit();
}

$error = '';
$success = '';
$csrfToken = getCSRFToken();
$esPost = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';

if ($esPost) {
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!verifyCSRFToken($token)) {
        $error = 'La solicitud no pudo validarse. Actualizá la página e intentá nuevamente.';
    } else {
        $passwordActual = (string) ($_POST['password_actual'] ?? '');
        $passwordNuevo = (string) ($_POST['password_nuevo'] ?? '');
        $passwordConfirmacion = (string) ($_POST['password_confirmacion'] ?? '');

        if ($passwordNuevo === '' || $passwordConfirmacion === '' || $passwordActual === '') {
            $error = 'Completá todos los campos.';
        } elseif ($passwordNuevo !== $passwordConfirmacion) {
            $error = 'La nueva contraseña y su confirmación no coinciden.';
        } else {
            $resultado = $servicioAutenticacion->cambiarPassword((int) $usuario['id'], $passwordActual, $passwordNuevo);
            if (!empty($resultado['success'])) {
                $_SESSION['must_change_password'] = 0;
                $success = 'Contraseña actualizada correctamente. Ya podés ingresar al sistema.';
                header('Location: ' . app_base_path('/?password_updated=1'));
                exit();
            }
            $error = (string) ($resultado['error'] ?? 'No se pudo actualizar la contraseña.');
        }
    }
}

sistema_admin_send_html_security_headers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio obligatorio de contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { width: 100%; max-width: 520px; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,.08); padding: 24px; }
        h1 { margin: 0 0 8px; font-size: 24px; color: #111827; }
        p { margin: 0 0 18px; color: #4b5563; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #374151; font-weight: 600; }
        input { width: 100%; padding: 10px 12px; margin-bottom: 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; padding: 11px 14px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; font-weight: 700; cursor: pointer; }
        .alert { margin-bottom: 14px; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Cambio obligatorio de contraseña</h1>
            <p>Por seguridad, debés cambiar la contraseña temporal antes de continuar.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="password_actual">Contraseña actual (temporal)</label>
                <input type="password" id="password_actual" name="password_actual" required autocomplete="current-password">

                <label for="password_nuevo">Nueva contraseña</label>
                <input type="password" id="password_nuevo" name="password_nuevo" required autocomplete="new-password">

                <label for="password_confirmacion">Confirmar nueva contraseña</label>
                <input type="password" id="password_confirmacion" name="password_confirmacion" required autocomplete="new-password">

                <button type="submit">Guardar nueva contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
