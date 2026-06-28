<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/character_encoding.php';

use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Mappers\ProfesorMapper;
use SistemaAdmin\Services\ServicioProfesores;
use SistemaAdmin\Controllers\ProfesorController;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);
$profesorMapper = new ProfesorMapper($databaseAdapter);
$servicioProfesores = new ServicioProfesores($databaseAdapter, $profesorMapper);
$profesorController = new ProfesorController($databaseAdapter, $servicioProfesores);

$usuario = $servicioAutenticacion->verificarSesion();
if (!$usuario) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

$ajaxTipo = isset($_GET['ajax']) ? trim((string) $_GET['ajax']) : '';
if ($ajaxTipo === 'get_materias_curso') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $cursoRaw = $_GET['curso_id'] ?? '';
        $cursoV = filter_var(trim((string) $cursoRaw), FILTER_VALIDATE_INT);
        $cursoAjax = ($cursoV !== false && $cursoV > 0) ? $cursoV : 0;
        $resultadoAjax = $profesorController->obtenerMateriasCursoAjax([
            'curso_id' => $cursoAjax,
        ]);
        echo json_encode($resultadoAjax, JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Throwable $e) {
        error_log('Error en get_materias_curso: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener materias: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$pageTitle = 'Ficha del Profesor - Sistema Administrativo E.E.S.T N°2';

/**
 * Resuelve el ID del docente desde GET/POST. Usa $_GET además de filter_input porque en algunos
 * entornos (PHP-FPM/nginx) filter_input(INPUT_GET) puede no reflejar la query string.
 */
$profesor_id = 0;
$rawCandidates = [
    $_GET['id'] ?? null,
    $_GET['profesor_id'] ?? null,
];
foreach ($rawCandidates as $raw) {
    if ($raw === null || $raw === '') {
        continue;
    }
    $v = filter_var(trim((string) $raw), FILTER_VALIDATE_INT);

    if ($v !== false && $v > 0) {
        $profesor_id = $v;
        break;
    }
}
if ($profesor_id < 1) {
    $idPost = filter_input(INPUT_POST, 'profesor_id', FILTER_VALIDATE_INT);
    if ($idPost !== false && $idPost > 0) {
        $profesor_id = $idPost;
    }
}

$action = trim((string) (filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?? ''));
$success_message = '';
$error_message = '';

if ($profesor_id < 1) {
    header('Location: profesores.php');
    exit();
}

require_once __DIR__ . '/includes/auth_helpers.php';
require_once __DIR__ . '/includes/csrf_functions.php';

$puede_editar_ficha = hasRole('admin') || hasRole('directivo');
$csrfToken = getCSRFToken();

$esPost = strtoupper((string) (filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT) ?? '')) === 'POST';

if ($esPost) {
    $csrfPost = (string) (filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) ?? '');
    if (!verifyCSRFToken($csrfPost)) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
    } else {
        $usuarioRegistroId = (int) ($usuario['id'] ?? 0);
        if ($usuarioRegistroId < 1) {
            $usuarioRegistroId = 1;
        }
        $postOutcome = $profesorController->procesarPostFichaProfesor(
            $profesor_id,
            $_POST,
            $puede_editar_ficha,
            $usuarioRegistroId
        );
        if ($postOutcome['redirect'] !== null) {
            header('Location: ' . $postOutcome['redirect']);
            exit();
        }
        $error_message = $postOutcome['error'];
    }
}

if (!$esPost) {
    $ok = trim((string) (filter_input(INPUT_GET, 'success', FILTER_DEFAULT) ?? ''));
    switch ($ok) {
        case 'actualizado':
            $success_message = 'Información del profesor actualizada correctamente';
            break;
        case 'curso_asignado':
            $success_message = 'Curso asignado correctamente';
            break;
        case 'curso_desasignado':
            $success_message = 'Curso desasignado correctamente';
            break;
        case 'suplencia_creada':
            $success_message = 'Suplencia creada correctamente';
            break;
        case 'suplencia_finalizada':
            $success_message = 'Suplencia finalizada correctamente';
            break;
        case 'materia_asignada':
            $success_message = 'Materia asignada correctamente al curso especificado';
            break;
        case 'materia_desasignada':
            $success_message = 'Materia desasignada correctamente del curso especificado';
            break;
        case 'suplente_registrado':
            $success_message = 'Suplente registrado correctamente';
            break;
        default:
            break;
    }
}

$fichaResultado = $profesorController->obtenerFicha($profesor_id);
if (empty($fichaResultado['success'])) {
    $detalle = (string) ($fichaResultado['error'] ?? '');
    if ($detalle !== '') {
        error_log('profesor_ficha: no se pudo cargar id=' . $profesor_id . ' — ' . $detalle);
    }
    header('Location: profesores.php?error=ficha&pid=' . $profesor_id, true, 302);
    exit();
}

$fichaData = $profesorController->aplicarPresentacionFichaProfesor($fichaResultado['data']);
$profesor = $fichaData['profesor'];
$profesor['especialidad'] = $profesor['especialidad_nombre'] ?? null;
$profesor_inactivo_registro = empty($profesor['activo']);

$cursos_asignados = $fichaData['cursos_asignados'];
$cursos_disponibles = $fichaData['cursos_disponibles'];
$especialidades_catalogo = $fichaData['especialidades_catalogo'];
$suplencias_activas = $fichaData['suplencias_activas'];
$suplentes_disponibles = $fichaData['suplentes_disponibles'];
$materias_asignadas = $fichaData['materias_asignadas'];
$materias_profesor = $fichaData['materias_profesor'];
$total_cursos_actuales = $fichaData['total_cursos_actuales'];
$total_materias_actuales = $fichaData['total_materias_actuales'];
$anios_antiguedad = (int) ($fichaData['anios_antiguedad'] ?? 0);
$edad_anios = $fichaData['edad_anios'] ?? null;

$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/profesor_ficha.css">' . "\n";

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="profesor-ficha-section">
    <div class="section-header">
        <h2>Ficha del Profesor</h2>
        <div class="header-actions">
            <a href="profesores.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Profesores
            </a>
            <?php if ($puede_editar_ficha): ?>
            <button type="button" class="btn btn-primary" data-csp-show-modal="editModal" data-csp-modal-lock-body="1">
                <i class="fas fa-edit"></i> Editar Información
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($profesor_inactivo_registro)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Este registro de docente está <strong>inactivo</strong> en la base de datos. La ficha se muestra para consulta o reactivación.
        </div>
    <?php endif; ?>

    <!-- Información personal -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información Personal</h3>
        </div>
        <div class="card-body">
            <div class="student-profile">
                <div class="profile-photo">
                    <div class="default-photo">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($profesor['apellido'] . ', ' . $profesor['nombre']); ?></h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>DNI:</strong>
                            <span><?php echo htmlspecialchars($profesor['dni']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de Nacimiento:</strong>
                            <span>
                                <?php if ($profesor['fecha_nacimiento']): ?>
                                    <?php echo date('d/m/Y', strtotime($profesor['fecha_nacimiento'])); ?>
                                    (<?php echo $edad_anios !== null ? (int) $edad_anios : 0; ?> años)
                                <?php else: ?>
                                    No registrada
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Título:</strong>
                            <span><?php echo htmlspecialchars($profesor['titulo'] ?? '') ?: 'No registrado'; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Especialidad:</strong>
                            <span>
                                <?php if (!empty($profesor['especialidad'] ?? null)): ?>
                                    <span class="status status-success"><?php echo htmlspecialchars($profesor['especialidad']); ?></span>
                                <?php else: ?>
                                    <span class="status status-warning">No especificada</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de Ingreso:</strong>
                            <span>
                                <?php if ($profesor['fecha_ingreso']): ?>
                                    <?php echo date('d/m/Y', strtotime($profesor['fecha_ingreso'])); ?>
                                    (<?php echo $anios_antiguedad; ?> años de antigüedad)
                                <?php else: ?>
                                    No registrada
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-info">
                <h4>Información de Contacto</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Domicilio:</strong>
                        <span><?php echo htmlspecialchars($profesor['domicilio'] ?? '') ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono Fijo:</strong>
                        <span><?php echo htmlspecialchars($profesor['telefono_fijo'] ?? '') ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono Celular:</strong>
                        <span><?php echo htmlspecialchars($profesor['telefono_celular'] ?? '') ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($profesor['email'] ?? '') ?: 'No registrado'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-chalkboard"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_cursos_actuales; ?></h3>
                <p>Cursos Actuales</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon secondary">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_materias_actuales; ?></h3>
                <p>Materias Actuales</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $anios_antiguedad; ?></h3>
                <p>Años de Antigüedad</p>
            </div>
        </div>
    </div>

    <!-- Cursos asignados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chalkboard"></i>
                Cursos Asignados (<?php echo count($cursos_asignados); ?>)
            </h3>
            <?php if ($puede_editar_ficha): ?>
            <button type="button" class="btn btn-primary btn-sm profesor-ficha-card__action-spaced" data-csp-toggle-form="asignar-curso">
                <i class="fas fa-plus"></i> Asignar Curso
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Formulario para asignar curso -->
        <?php if ($puede_editar_ficha): ?>
        <div id="asignar-curso" class="form-section" style="display: none;">
            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="asignar_curso" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="curso_id">Curso:</label>
                        <select name="curso_id" id="curso_id" required>
                            <option value="">Seleccionar curso</option>
                            <?php foreach ($cursos_disponibles as $curso): ?>
                            <option value="<?php echo (int) $curso['id']; ?>">
                                <?php echo htmlspecialchars((string) ($curso['etiqueta_opcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Asignar Curso
                    </button>
                    <button type="button" class="btn btn-secondary" data-csp-toggle-form="asignar-curso">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="table-container">
            <?php if (!empty($cursos_asignados)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Especialidad</th>
                        <th>Turno</th>
                        <th>Fecha Asignación</th>
                        <?php if ($puede_editar_ficha): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos_asignados as $curso): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars((string) ($curso['etiqueta_corto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($curso['es_curso_inferior'])): ?>
                                <span class="text-muted">Sin especialidad</span>
                            <?php else: ?>
                                <?php echo htmlspecialchars((string) ($curso['especialidad'] ?? 'Sin especialidad'), ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($curso['turno']); ?>
                        </td>
                        <td>
                            <small><?php echo $curso['fecha_asignacion'] ? date('d/m/Y', strtotime($curso['fecha_asignacion'])) : 'N/A'; ?></small>
                        </td>
                        <?php if ($puede_editar_ficha): ?>
                        <td>
                            <form method="POST" class="profesor-ficha-inline-form js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Está seguro de que desea desasignar este curso?', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="desasignar_curso" value="1">
                                <input type="hidden" name="asignacion_id" value="<?php echo (int) $curso['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Desasignar curso">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="profesor-ficha-empty">
                <i class="fas fa-chalkboard profesor-ficha-empty__icon"></i>
                <br>No hay cursos asignados
                <br><small>Asigna cursos para comenzar</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Materias asignadas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-book"></i>
                Materias Asignadas (<?php echo count($materias_asignadas); ?>)
            </h3>
            <?php if ($puede_editar_ficha): ?>
            <button type="button" class="btn btn-primary btn-sm profesor-ficha-card__action-spaced" data-csp-toggle-form="asignar-materia">
                <i class="fas fa-plus"></i> Asignar Materia
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Formulario para asignar materia -->
        <?php if ($puede_editar_ficha): ?>
        <div id="asignar-materia" class="form-section" style="display: none;">
            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="asignar_materia" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="asignar_curso_id">Curso: *</label>
                        <select name="curso_id" id="asignar_curso_id" required>
                            <option value="">Seleccionar curso</option>
                            <?php foreach ($cursos_asignados as $curso): ?>
                            <option value="<?php echo (int) $curso['curso_id']; ?>" data-curso-id="<?php echo (int) $curso['curso_id']; ?>">
                                <?php echo htmlspecialchars((string) ($curso['etiqueta_opcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="asignar_materia_id">Materia: *</label>
                        <select name="materia_id" id="asignar_materia_id" required disabled>
                            <option value="">Primero selecciona un curso</option>
                        </select>
                    </div>

                    <div class="form-group" id="grupo_taller_container" style="display: none;">
                        <label for="grupo_taller">Grupo de Taller: *</label>
                        <select name="grupo_taller" id="grupo_taller">
                            <option value="">Seleccionar grupo</option>
                            <option value="A">Grupo A</option>
                            <option value="B">Grupo B</option>
                            <option value="C">Grupo C</option>
                            <option value="D">Grupo D</option>
                            <option value="E">Grupo E</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Asignar Materia
                    </button>
                    <button type="button" class="btn btn-secondary" data-csp-toggle-form="asignar-materia">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <div class="table-container">
            <?php if (!empty($materias_asignadas)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Curso</th>
                        <th>Fecha Asignación</th>
                        <?php if ($puede_editar_ficha): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias_asignadas as $materia): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($materia['nombre']); ?></strong>
                            <?php if (!empty($materia['grupo_taller'])): ?>
                                <span class="badge badge-info" style="background-color: #06b6d4; color: white; font-size: 0.65rem; padding: 0.1rem 0.25rem; border-radius: 4px; margin-left: 2px;">Grupo <?php echo htmlspecialchars($materia['grupo_taller']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($materia['curso'] === 'Sin curso específico'): ?>
                                <span class="status status-warning"><?php echo htmlspecialchars($materia['curso']); ?></span>
                                <br><small class="profesor-ficha-materia-hint">Registro anterior - requiere actualización</small>
                            <?php else: ?>
                                <span class="status status-success"><?php echo htmlspecialchars($materia['curso']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo $materia['fecha_asignacion'] ? date('d/m/Y', strtotime($materia['fecha_asignacion'])) : 'N/A'; ?></small>
                        </td>
                        <?php if ($puede_editar_ficha): ?>
                        <td>
                            <form method="POST" class="profesor-ficha-inline-form js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Está seguro de que desea desasignar esta materia del curso ' . $materia['curso'] . '?', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="desasignar_materia" value="1">
                                <input type="hidden" name="materia_curso_id" value="<?php echo (int) $materia['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Desasignar materia del curso">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="profesor-ficha-empty">
                <i class="fas fa-book profesor-ficha-empty__icon"></i>
                <br>No hay materias asignadas
                <br><small>Asigna materias para poder crear suplencias</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Suplencias activas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt"></i>
                Suplencias Activas (<?php echo count($suplencias_activas); ?>)
            </h3>
            <?php if ($puede_editar_ficha): ?>
            <button type="button" class="btn btn-primary btn-sm profesor-ficha-card__action-spaced" data-csp-toggle-form="nueva-suplencia">
                <i class="fas fa-plus"></i> Nueva Suplencia
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Formulario para nueva suplencia -->
        <?php if ($puede_editar_ficha): ?>
        <div id="nueva-suplencia" class="form-section" style="display: none;">
            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="crear_suplencia" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="suplencia_materia_id">Materia:</label>
                        <select name="materia_id" id="suplencia_materia_id" required>
                            <option value="">Seleccionar materia</option>
                            <?php foreach ($materias_profesor as $materia): ?>
                            <option value="<?php echo $materia['materia_id']; ?>">
                                <?php echo htmlspecialchars($materia['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="suplencia_fecha_inicio">Fecha de Inicio:</label>
                        <input type="date" name="fecha_inicio" id="suplencia_fecha_inicio" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="suplencia_fecha_fin">Fecha de Fin (opcional):</label>
                        <input type="date" name="fecha_fin" id="suplencia_fecha_fin">
                    </div>
                    
                    <div class="form-group">
                        <label for="suplencia_motivo">Motivo:</label>
                        <input type="text" name="motivo" id="suplencia_motivo" required 
                               placeholder="Ej: Licencia médica, Capacitación, etc.">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="suplente_id">Suplente:</label>
                        <select name="suplente_id" id="suplente_id">
                            <option value="">Seleccionar suplente</option>
                            <?php foreach ($suplentes_disponibles as $suplente): ?>
                            <option value="<?php echo $suplente['id']; ?>">
                                <?php echo htmlspecialchars($suplente['apellido'] . ', ' . $suplente['nombre']); ?>
                                <?php if ($suplente['especialidad']): ?>
                                    (<?php echo htmlspecialchars($suplente['especialidad']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-secondary profesor-ficha-suplente-extra" data-csp-toggle-form="nuevo-suplente">
                            <i class="fas fa-plus"></i> Crear Nuevo Suplente
                        </button>
                        <div class="checkbox-group" style="margin-top: 5px;">
                            <label class="checkbox-label" style="font-size: 11px; font-weight: normal;">
                                <input type="checkbox" id="mostrar_todos_suplentes">
                                <span class="checkmark"></span>
                                Mostrar todos los suplentes sin filtrar por especialidad
                            </label>
                        </div>
                        <div id="suplentes_warning_message" style="display: none; color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 5px 10px; border-radius: 4px; margin-top: 5px; font-size: 11.5px;">
                            <i class="fas fa-exclamation-triangle"></i> No hay suplentes con especialidad coincidente para esta materia. Considere activar "Mostrar todos los suplentes" o marcar "Fuera de servicio".
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="fuera_servicio" id="fuera_servicio">
                                <span class="checkmark"></span>
                                Fuera de servicio (sin suplente)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Suplencia
                    </button>
                    <button type="button" class="btn btn-secondary" data-csp-toggle-form="nueva-suplencia">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Formulario para nuevo suplente -->
        <div id="nuevo-suplente" class="form-section" style="display: none;">
            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="guardar_suplente" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="suplente_dni">DNI: *</label>
                        <input type="text" name="dni" id="suplente_dni" required maxlength="20" 
                               placeholder="Ej: 12345678">
                    </div>
                    
                    <div class="form-group">
                        <label for="suplente_apellido">Apellido: *</label>
                        <input type="text" name="apellido" id="suplente_apellido" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="suplente_nombre">Nombre: *</label>
                        <input type="text" name="nombre" id="suplente_nombre" required maxlength="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="suplente_telefono">Teléfono Celular:</label>
                        <input type="tel" name="telefono_celular" id="suplente_telefono" maxlength="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="suplente_email">Email:</label>
                        <input type="email" name="email" id="suplente_email" maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="suplente_especialidad">Especialidad:</label>
                        <input type="text" name="especialidad" id="suplente_especialidad" maxlength="200" 
                               placeholder="Ej: Matemática, Física, etc.">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Suplente
                    </button>
                    <button type="button" class="btn btn-secondary" data-csp-toggle-form="nuevo-suplente">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="table-container">
            <?php if (!empty($suplencias_activas)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Suplente</th>
                        <th>Período</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <?php if ($puede_editar_ficha): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suplencias_activas as $suplencia): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($suplencia['materia']); ?></strong>
                        </td>
                        <td>
                            <?php if ($suplencia['fuera_servicio']): ?>
                                <span class="status status-danger">Fuera de servicio</span>
                            <?php elseif ($suplencia['suplente_apellido']): ?>
                                <strong><?php echo htmlspecialchars($suplencia['suplente_apellido'] . ', ' . $suplencia['suplente_nombre']); ?></strong>
                            <?php else: ?>
                                <span class="status status-warning">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($suplencia['fecha_inicio'])); ?></strong>
                            <?php if ($suplencia['fecha_fin']): ?>
                                <br><small>hasta <?php echo date('d/m/Y', strtotime($suplencia['fecha_fin'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($suplencia['motivo']); ?></small>
                        </td>
                        <td>
                            <span class="status status-info">Activa</span>
                        </td>
                        <?php if ($puede_editar_ficha): ?>
                        <td>
                            <form method="POST" class="profesor-ficha-inline-form js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Está seguro de que desea finalizar esta suplencia?', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="finalizar_suplencia" value="1">
                                <input type="hidden" name="suplencia_id" value="<?php echo $suplencia['id']; ?>">
                                <button type="submit" name="finalizar_suplencia" value="1" class="btn btn-sm btn-success" title="Finalizar suplencia">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="profesor-ficha-empty">
                <i class="fas fa-exchange-alt profesor-ficha-empty__icon"></i>
                <br>No hay suplencias activas
                <br><small>Crea una suplencia cuando sea necesario</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($puede_editar_ficha): ?>
<!-- Modal de edición -->
<div id="editModal" class="profesor-ficha-modal" style="display: none;">
    <div class="profesor-ficha-modal__content">
        <div class="profesor-ficha-modal__header">
            <h3><i class="fas fa-edit"></i> Editar Información del Profesor</h3>
            <span class="profesor-ficha-modal__close" role="button" tabindex="0" data-csp-hide-modal="editModal" data-csp-modal-lock-body="1">&times;</span>
        </div>
        <div class="profesor-ficha-modal__body">
            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="actualizar_profesor" value="1">
                <input type="hidden" name="profesor_id" value="<?php echo $profesor_id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_telefono_fijo">Teléfono Fijo:</label>
                        <input type="tel" name="telefono_fijo" id="edit_telefono_fijo" 
                               value="<?php echo htmlspecialchars($profesor['telefono_fijo'] ?? ''); ?>" maxlength="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_telefono_celular">Teléfono Celular:</label>
                        <input type="tel" name="telefono_celular" id="edit_telefono_celular" 
                               value="<?php echo htmlspecialchars($profesor['telefono_celular'] ?? ''); ?>" maxlength="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email:</label>
                        <input type="email" name="email" id="edit_email" 
                               value="<?php echo htmlspecialchars($profesor['email'] ?? ''); ?>" maxlength="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_titulo">Título:</label>
                        <input type="text" name="titulo" id="edit_titulo" 
                               value="<?php echo htmlspecialchars($profesor['titulo'] ?? ''); ?>" maxlength="200">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_especialidad_id">Especialidad:</label>
                        <select name="especialidad_id" id="edit_especialidad_id">
                            <option value="">Sin especialidad</option>
                            <?php foreach ($especialidades_catalogo as $esp): ?>
                            <option value="<?php echo $esp['id']; ?>" 
                                    <?php echo (int)($profesor['especialidad_id'] ?? 0) === (int)$esp['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($esp['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" 
                               value="<?php echo htmlspecialchars($profesor['fecha_nacimiento'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_fecha_ingreso">Fecha de Ingreso:</label>
                        <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso" 
                               value="<?php echo htmlspecialchars($profesor['fecha_ingreso'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="edit_domicilio">Domicilio:</label>
                        <textarea name="domicilio" id="edit_domicilio" placeholder="Dirección completa"><?php echo htmlspecialchars($profesor['domicilio'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary" data-csp-hide-modal="editModal" data-csp-modal-lock-body="1">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$profesorFichaNonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8');
$profesorFichaBootstrap = [
    'profesorId' => $profesor_id,
    'materiasAsignadas' => $materias_asignadas,
    'abrirModalEditar' => $action === 'editar' && $puede_editar_ficha,
];
?>
<script type="application/json" id="profesor-ficha-bootstrap" nonce="<?php echo $profesorFichaNonce; ?>"><?php echo json_encode($profesorFichaBootstrap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
<script src="js/profesor_ficha.js" defer nonce="<?php echo $profesorFichaNonce; ?>"></script>

<?php include 'includes/footer.php'; ?>
