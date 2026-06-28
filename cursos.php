<?php 
// Iniciar sesión al principio
require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';

use SistemaAdmin\Controllers\CursoController;
use SistemaAdmin\Mappers\CursoMapper;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioCursos;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);

// Verificar si hay sesión activa
$usuario = $servicioAutenticacion->verificarSesion();
if (!$usuario) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

require_once __DIR__ . '/includes/preceptor_scope.php';
require_once __DIR__ . '/includes/profesor_scope.php';
require_once __DIR__ . '/includes/auth_helpers.php';
require_once __DIR__ . '/includes/csrf_functions.php';
require_once __DIR__ . '/includes/cursos/helpers.php';
$preceptor_cids = preceptor_curso_ids();
$cursos_alcance_vista = null;
if (es_profesor()) {
    $cursos_alcance_vista = profesor_curso_ids();
} elseif (($usuario['rol'] ?? '') === 'preceptor') {
    $cursos_alcance_vista = $preceptor_cids;
}

$cursoMapper = new CursoMapper($databaseAdapter);
$servicioCursos = new ServicioCursos($databaseAdapter, $cursoMapper);
$estudianteMapper = new \SistemaAdmin\Mappers\EstudianteMapper($databaseAdapter);
$servicioEstudiantes = new \SistemaAdmin\Services\ServicioEstudiantes($databaseAdapter, $estudianteMapper);
$cursoController = new CursoController($databaseAdapter, $servicioCursos, $servicioEstudiantes);

$pageTitle = 'Cursos - Sistema Administrativo E.E.S.T N°2';

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';
$form_curso = [
    'anio' => '',
    'division' => '',
    'turno_id' => '',
    'especialidad_id' => '',
];

$csrfToken = getCSRFToken();

// Solo admin y directivo pueden crear cursos
if ($action === 'nuevo' && !(hasRole('admin') || hasRole('directivo'))) {
    header('Location: cursos.php?error=unauthorized');
    exit();
}

// Procesar formulario de nuevo curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_curso'])) {
    if (!verifyCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
        $action = 'nuevo';
        $form_curso = cursos_form_curso_desde_post($_POST);
    } else {
        try {
            $resultadoGuardar = $cursoController->procesarGuardarCursoDesdePost($_POST);
            if ($resultadoGuardar['success']) {
                $success_message = 'Curso creado correctamente';
                $action = '';
            } else {
                $error_message = $resultadoGuardar['error'];
                $action = 'nuevo';
                $form_curso = cursos_form_curso_desde_post($_POST);
            }
        } catch (\Throwable $e) {
            $error_message = 'Error al crear curso: ' . $e->getMessage();
            $action = 'nuevo';
            $form_curso = cursos_form_curso_desde_post($_POST);
        }
    }
}

// Procesar eliminación de curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_curso'])) {
    if (!verifyCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
    } else {
        $puedeEliminar = hasRole('admin') || hasRole('directivo');
        $resultadoEliminar = $cursoController->procesarEliminarCursoDesdePost($_POST, $puedeEliminar);
        if ($resultadoEliminar['success']) {
            $success_message = $resultadoEliminar['message'];
        } else {
            $error_message = $resultadoEliminar['error'];
        }
    }
}

$anio_filter = (string) ($_GET['anio'] ?? '');
$division_filter = (string) ($_GET['division'] ?? '');
$especialidad_filter = (string) ($_GET['especialidad'] ?? '');

$vista = $cursoController->datosVistaGestion($cursos_alcance_vista, $anio_filter, $division_filter, $especialidad_filter);
$cursos = $vista['cursos'];
$cursos_por_grupo = $vista['cursos_por_grupo'];
$anios = $vista['anios'];
$divisiones = $vista['divisiones'];
$especialidades = $vista['especialidades'];
$turnos = $vista['turnos'];
$anio_filter = $vista['anio_filter'];
$division_filter = $vista['division_filter'];
$especialidad_filter = $vista['especialidad_filter'];
$total_cursos = $vista['total_cursos'];
$total_estudiantes = $vista['total_estudiantes'];
$cursos_sin_estudiantes = $vista['cursos_sin_estudiantes'];

$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/cursos_gestion.css">' . "\n";

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="cursos-section">
    <?php if (es_profesor()): ?>
        <?php
        $anioLectivoActual = \SistemaAdmin\Services\NotasSubjectGradesPayloadBuilder::inferSchoolYearCicloMarzoArgentina(new \DateTimeImmutable());
        $correctionPeriodInfo = check_grade_correction_period($databaseAdapter, (int) $anioLectivoActual);
        $modificacionHabilitadaDocente = $correctionPeriodInfo['is_open'];
        ?>
        <div class="grade-correction-status-alert <?php echo $modificacionHabilitadaDocente ? 'grade-correction-status-alert--open' : 'grade-correction-status-alert--closed'; ?>" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); <?php echo $modificacionHabilitadaDocente ? 'background-color: #ecfdf5; border-color: #a7f3d0;' : 'background-color: #fffbeb; border-color: #fde68a;'; ?>">
            <div class="status-alert-icon" style="font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                <?php if ($modificacionHabilitadaDocente): ?>
                    <i class="fas fa-unlock-alt" style="color: #059669;"></i>
                <?php else: ?>
                    <i class="fas fa-lock" style="color: #d97706;"></i>
                <?php endif; ?>
            </div>
            <div class="status-alert-content" style="flex: 1;">
                <h4 style="margin: 0 0 0.25rem; font-weight: 700; font-size: 1rem; color: <?php echo $modificacionHabilitadaDocente ? '#065f46' : '#92400e'; ?>;">
                    <?php echo $modificacionHabilitadaDocente ? 'Período de modificaciones habilitado' : 'Período de modificaciones cerrado'; ?>
                </h4>
                <p style="margin: 0; font-size: 0.875rem; line-height: 1.4; color: <?php echo $modificacionHabilitadaDocente ? '#047857' : '#b45309'; ?>;">
                    <?php
                    if ($modificacionHabilitadaDocente) {
                        if ($correctionPeriodInfo['status'] === 'open_manual') {
                            echo 'Las modificaciones de notas están habilitadas temporalmente por la dirección o preceptoría.';
                        } else {
                            $endDateFormatted = $correctionPeriodInfo['end_date'] ? date('d/m/Y', strtotime($correctionPeriodInfo['end_date'])) : '';
                            echo 'Las modificaciones de notas están habilitadas hasta el <strong>' . htmlspecialchars($endDateFormatted, ENT_QUOTES, 'UTF-8') . '</strong> inclusive.';
                        }
                    } else {
                        if ($correctionPeriodInfo['start_date']) {
                            $startDateFormatted = date('d/m/Y', strtotime($correctionPeriodInfo['start_date']));
                            echo 'Las modificaciones de notas están deshabilitadas. Próxima apertura programada: <strong>' . htmlspecialchars($startDateFormatted, ENT_QUOTES, 'UTF-8') . '</strong>.';
                        } else {
                            echo 'Actualmente no se permiten modificaciones de notas por parte de los docentes.';
                        }
                    }
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($preceptor_cids !== []): ?>
    <div class="alert alert-info" style="margin-bottom: 1rem;">
        <i class="fas fa-info-circle"></i> Como preceptor solo visualiza sus cursos asignados
        (<strong><?php echo htmlspecialchars(preceptor_curso_etiqueta()); ?></strong>).
    </div>
    <?php endif; ?>
    <div class="section-header">
        <h2>Gestión de Cursos</h2>
        <?php if (hasRole('admin') || hasRole('directivo')): ?>
        <a href="cursos.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Curso
        </a>
        <?php endif; ?>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            No tienes permisos para crear cursos.
        </div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_cursos, 0, ',', '.'); ?></h3>
                <p>Total Cursos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_estudiantes, 0, ',', '.'); ?></h3>
                <p>Total Estudiantes</p>
            </div>
        </div>
        
        <?php if ($cursos_sin_estudiantes > 0): ?>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($cursos_sin_estudiantes, 0, ',', '.'); ?></h3>
                <p>Cursos Sin Estudiantes</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario nuevo curso -->
    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Crear Nuevo Curso</h3>
        </div>
        <form method="POST" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="anio">Año: *</label>
                    <select name="anio" id="anio" required>
                        <option value="">Seleccionar año</option>
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $form_curso['anio'] !== '' && (string) (int) $form_curso['anio'] === (string) $i ? 'selected' : ''; ?>><?php echo $i; ?>°</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="division">División: *</label>
                    <select name="division" id="division" required>
                        <option value="">Seleccionar división</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $form_curso['division'] !== '' && (string) $form_curso['division'] === (string) $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="turno_id">Turno: *</label>
                    <select name="turno_id" id="turno_id" required>
                        <option value="">Seleccionar turno</option>
                        <?php foreach ($turnos as $turno): ?>
                        <option value="<?php echo $turno['id']; ?>" <?php echo $form_curso['turno_id'] !== '' && (string) $form_curso['turno_id'] === (string) $turno['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turno['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="especialidad_id">Especialidad:</label>
                    <select name="especialidad_id" id="especialidad_id">
                        <option value="">Sin especialidad (ciclo básico)</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?php echo $especialidad['id']; ?>" <?php echo $form_curso['especialidad_id'] !== '' && (string) $form_curso['especialidad_id'] === (string) $especialidad['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_curso" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Curso
                </button>
                <a href="cursos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="anio_filter">Año:</label>
                    <select name="anio" id="anio_filter">
                        <option value="">Todos los años</option>
                        <?php foreach ($anios as $a): ?>
                        <option value="<?php echo $a; ?>"
                                <?php echo $anio_filter !== '' && $anio_filter === (string) $a ? 'selected' : ''; ?>>
                            <?php echo $a; ?>°
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="division_filter">División:</label>
                    <select name="division" id="division_filter">
                        <option value="">Todas las divisiones</option>
                        <?php foreach ($divisiones as $d): ?>
                        <option value="<?php echo htmlspecialchars($d); ?>"
                                <?php echo $division_filter !== '' && $division_filter === (string) $d ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="especialidad">Especialidad:</label>
                    <select name="especialidad" id="especialidad">
                        <option value="">Todas las especialidades</option>
                        <option value="sin_especialidad" <?php echo $especialidad_filter === 'sin_especialidad' ? 'selected' : ''; ?>>Sin especialidad</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?php echo (int) $especialidad['id']; ?>"
                                <?php echo $especialidad_filter !== '' && $especialidad_filter === (string) $especialidad['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="cursos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de cursos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cursos Registrados (<?php echo number_format($total_cursos, 0, ',', '.'); ?>)</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Especialidad</th>
                        <th>Turno</th>
                        <th>Grado</th>
                        <th>Estudiantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cursos)): ?>
                        <?php foreach ($cursos as $curso): ?>
                        <tr>
                            <td>
                                <strong><?php echo $curso['anio'] . '° ' . $curso['division']; ?></strong>
                            </td>
                            <td>
                                <?php echo $curso['especialidad'] ? htmlspecialchars($curso['especialidad']) : 'Sin especialidad'; ?>
                            </td>
                            <td>
                                <i class="fas fa-clock"></i> 
                                <?php echo htmlspecialchars($curso['turno']); ?>
                            </td>
                            <td>
                                <span class="status <?php echo $curso['grado'] === 'inferior' ? 'status-success' : 'status-warning'; ?>">
                                    <?php echo ucfirst($curso['grado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($curso['cantidad_estudiantes'] > 0): ?>
                                    <a href="estudiantes.php?curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-users"></i> <?php echo $curso['cantidad_estudiantes']; ?> estudiante<?php echo $curso['cantidad_estudiantes'] != 1 ? 's' : ''; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="status status-warning">Sin estudiantes</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="horarios.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-success" title="Ver horarios">
                                    <i class="fas fa-clock"></i>
                                </a>
                                <a href="estudiantes.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="Ver estudiantes">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="profesores.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-purple" title="Ver profesores">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </a>
                                <?php if (hasRole('admin') || hasRole('directivo')): ?>
                                <a href="notas.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-secondary" title="Ver notas del curso">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                
                                <?php include __DIR__ . '/includes/cursos/partials/form_eliminar_curso.php'; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 2rem; color: var(--secondary-color);">
                                <i class="fas fa-graduation-cap" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <br>No se encontraron cursos con los criterios especificados
                                <br><small>Prueba modificando los filtros de búsqueda</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vista por grupos -->
    <?php if ($anio_filter !== '' && $division_filter !== ''): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vista por Grupos (<?php echo htmlspecialchars($anio_filter); ?>° <?php echo htmlspecialchars($division_filter); ?>)</h3>
        </div>
        <div class="card-body">
            <div class="grupos-grid">
                <?php if (!empty($cursos_por_grupo)): ?>
                    <?php foreach ($cursos_por_grupo as $grupo_data): ?>
                    <div class="grupo-section">
                        <h4 class="grupo-title" style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
                            <span class="grupo-letter" style="font-weight: bold; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-users"></i> Grupo <?php echo htmlspecialchars($grupo_data['grupo']); ?>
                            </span>
                            <span class="status status-primary" style="font-size: 0.75rem;">
                                <?php echo $grupo_data['cantidad']; ?> estudiante<?php echo $grupo_data['cantidad'] != 1 ? 's' : ''; ?>
                            </span>
                        </h4>
                        
                        <div class="grupo-students-list">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($grupo_data['estudiantes'] as $est_nombre): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px dashed var(--border-color); display: flex; align-items: center; gap: 0.5rem; color: var(--text-color);">
                                    <i class="fas fa-user-graduate" style="color: var(--primary-color); opacity: 0.7;"></i>
                                    <?php echo htmlspecialchars($est_nombre); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" style="padding: 2rem; color: var(--secondary-color); width: 100%;">
                        <i class="fas fa-users-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <br>No hay grupos activos para este curso
                        <br><small>Asigna un grupo taller (A-E) a algún estudiante desde su ficha</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php
$nonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<script src="js/cursos_gestion.js" defer nonce="<?php echo $nonce; ?>"></script>
<?php include 'includes/footer.php'; ?>
