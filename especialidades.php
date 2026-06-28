<?php 
// Iniciar sesión al principio
require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';

use SistemaAdmin\Controllers\EspecialidadController;
use SistemaAdmin\Mappers\EspecialidadMapper;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioEspecialidades;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);

// Verificar si hay sesión activa
$usuario = $servicioAutenticacion->verificarSesion();
if (!$usuario) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

require_once __DIR__ . '/includes/auth_helpers.php';
if (!(hasRole('admin') || hasRole('directivo'))) {
    header('Location: index.php?error=unauthorized');
    exit();
}

$especialidadMapper = new EspecialidadMapper($databaseAdapter);
$servicioEspecialidades = new ServicioEspecialidades($databaseAdapter, $especialidadMapper);
$especialidadController = new EspecialidadController($databaseAdapter, $servicioEspecialidades);

$pageTitle = 'Especialidades - Sistema Administrativo E.E.S.T N°2';

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';
$form_especialidad = [
    'nombre' => '',
    'descripcion' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_especialidad'])) {
    try {
        $resultado = $especialidadController->procesarGuardarDesdePost($_POST);
        if ($resultado['success']) {
            $success_message = 'Especialidad creada';
            $action = '';
        } else {
            $error_message = $resultado['error'];
            $action = 'nueva';
            $form_especialidad = [
                'nombre' => (string) ($_POST['nombre'] ?? ''),
                'descripcion' => (string) ($_POST['descripcion'] ?? ''),
            ];
        }
    } catch (\Throwable $e) {
        $error_message = 'Error al crear la especialidad: ' . $e->getMessage();
        $action = 'nueva';
        $form_especialidad = [
            'nombre' => (string) ($_POST['nombre'] ?? ''),
            'descripcion' => (string) ($_POST['descripcion'] ?? ''),
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desactivar_especialidad'])) {
    try {
        $resultado = $especialidadController->procesarDesactivarDesdePost($_POST);
        if ($resultado['success']) {
            $success_message = 'Especialidad desactivada';
        } else {
            $error_message = $resultado['error'];
        }
    } catch (\Throwable $e) {
        $error_message = 'Error al desactivar: ' . $e->getMessage();
    }
}

$vista = $especialidadController->datosVista();
$especialidades = $vista['especialidades'];

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="especialidades-section">
    <div class="section-header">
        <h2>Especialidades</h2>
        <a href="especialidades.php?action=nueva" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Especialidad</a>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

    <?php if ($action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Crear Especialidad</h3></div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($form_especialidad['nombre']); ?>">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($form_especialidad['descripcion']); ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_especialidad" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="especialidades.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Listado</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($especialidades as $e): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($e['descripcion'] ?? ''); ?></td>
                        <td>
                            <form method="POST" class="js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Desactivar esta especialidad?', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="especialidad_id" value="<?php echo (int) $e['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" name="desactivar_especialidad" value="1"><i class="fas fa-trash"></i> Desactivar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
