<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';

use SistemaAdmin\Controllers\MateriaController;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioMaterias;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);

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

$servicioMaterias = new ServicioMaterias($databaseAdapter);
$materiaController = new MateriaController($databaseAdapter, $servicioMaterias);

$pageTitle = 'Materias - Sistema Administrativo E.E.S.T N°2';

$action = trim((string) (filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?? ''));
$success_message = '';
$error_message = '';
$csrfToken = getCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_materia'])) {
    if (!verifyCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
        $action = 'nueva';
    } else {
        $resultado = $materiaController->procesarGuardarDesdePost($_POST);
        if ($resultado['success']) {
            $success_message = 'Materia creada y asignada a cursos exitosamente';
            $action = '';
        } else {
            $error_message = $resultado['error'];
            $action = 'nueva';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desactivar_materia'])) {
    if (!verifyCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error_message = $error_message !== ''
            ? $error_message
            : 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
    } else {
        $resultado = $materiaController->procesarDesactivarDesdePost($_POST);
        if ($resultado['success']) {
            $success_message = 'Materia desactivada';
        } else {
            $error_message = $resultado['error'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gestionar_cursos_materia'])) {
    if (!verifyCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error_message = $error_message !== ''
            ? $error_message
            : 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
    } else {
        $resultado = $materiaController->procesarGestionarCursosDesdePost($_POST);
        if ($resultado['success']) {
            $success_message = 'Cursos de la materia actualizados correctamente';
            $action = '';
        } else {
            $error_message = $resultado['error'];
        }
    }
}

$materia_gestionar = null;
if ($action === 'gestionar_cursos') {
    $idGet = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($idGet === false || $idGet < 1) {
        if ($error_message === '') {
            $error_message = 'Debe indicar una materia válida para gestionar cursos.';
        }
        $action = '';
    } else {
        $materia_gestionar = $servicioMaterias->obtenerMateriaParaGestion($idGet);
        if ($materia_gestionar === null) {
            $error_message = 'Materia no encontrada';
            $action = '';
        }
    }
}

$rawFiltroEsp = filter_input(INPUT_GET, 'filtro_especialidad', FILTER_DEFAULT);
$rawFiltroEsp = $rawFiltroEsp !== null ? trim((string) $rawFiltroEsp) : '';
if ($rawFiltroEsp === 'sin_especialidad') {
    $filtro_especialidad = 'sin_especialidad';
} elseif ($rawFiltroEsp !== '' && ctype_digit($rawFiltroEsp)) {
    $filtro_especialidad = $rawFiltroEsp;
} else {
    $filtro_especialidad = '';
}

$pageListado = max(1, (int) (filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1));
$perPageListado = 20;

$vista = $materiaController->datosVista($filtro_especialidad, $materia_gestionar, $pageListado, $perPageListado);
extract($vista, EXTR_SKIP);

$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/materias.css">' . "\n";

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="materias-section">
    <div class="section-header">
        <h2>Materias</h2>
        <a href="materias.php?action=nueva" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Materia</a>
    </div>

    <?php if ($success_message !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($error_message !== ''): ?><div class="alert alert-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

    <?php if ($action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Crear Materia</h3></div>
        <form method="POST" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Matemática, Lengua, etc.">
                </div>
                <div class="form-group">
                    <label for="es_taller">Tipo de Materia</label>
                    <select id="es_taller" name="es_taller">
                        <option value="">Materia Regular</option>
                        <option value="1">Taller</option>
                    </select>
                    <small class="form-hint">
                        Selecciona "Taller" si es una materia práctica o taller
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="especialidad_id">Especialidad <small>(opcional, dejar vacío para materias de ciclo básico o generales)</small></label>
                    <select id="especialidad_id" name="especialidad_id">
                        <option value="">Sin especialidad (General)</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?php echo (int) $esp['id']; ?>"><?php echo htmlspecialchars((string) ($esp['nombre'] ?? '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">
                        Si eliges una especialidad, solo podrás asignar cursos de esa especialidad de 4° en adelante (la especialidad se cursa desde 4°).
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-group--full">
                    <label>Asignar a Cursos * <small>(selecciona uno o varios)</small></label>
                    <div
                        id="cursos-asignar-container"
                        class="cursos-asignar-container"
                        data-anio-min-especialidad="<?php echo (int) $anio_min_especialidad; ?>"
                    >
                        <?php foreach ($cursos as $curso): ?>
                        <label class="curso-asignar-item"
                               data-curso-especialidad-id="<?php echo $curso['especialidad_id'] !== null ? (int) $curso['especialidad_id'] : ''; ?>"
                               data-anio="<?php echo (int) $curso['anio']; ?>">
                            <input type="checkbox" name="cursos[]" value="<?php echo (int) $curso['id']; ?>">
                            <span>
                                <?php
                                echo (int) $curso['anio'] . '° ' . htmlspecialchars((string) ($curso['division'] ?? ''));
                                if (!empty($curso['especialidad'])) {
                                    echo ' - ' . htmlspecialchars((string) $curso['especialidad']);
                                }
                                if (!empty($curso['turno'])) {
                                    echo ' (' . htmlspecialchars((string) $curso['turno']) . ')';
                                }
                                ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="guardar_materia" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="materias.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($action === 'gestionar_cursos' && $materia_gestionar): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestionar Cursos - <?php echo htmlspecialchars((string) $materia_gestionar['nombre']); ?></h3>
        </div>
        <div class="card-body">
            <div class="materia-info-panel">
                <h4>Información de la Materia</h4>
                <div class="info-grid">
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars((string) $materia_gestionar['nombre']); ?></div>
                    <div><strong>Especialidad:</strong> <?php echo htmlspecialchars((string) ($materia_gestionar['especialidad'] ?? 'Sin especialidad')); ?></div>
                </div>
            </div>

            <form method="POST" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="materia_id" value="<?php echo (int) $materia_gestionar['id']; ?>">

                <h4 class="gestionar-cursos-intro">Seleccionar Cursos</h4>
                <p class="gestionar-cursos-help">
                    Marca los cursos que deben tener esta materia asignada.
                    <?php if (!empty($materia_gestionar['especialidad_id'])): ?>
                        Se muestran los cursos activos de la especialidad <strong><?php echo htmlspecialchars((string) ($materia_gestionar['especialidad'] ?? '')); ?></strong>
                        de <strong><?php echo (int) $anio_min_especialidad; ?>°</strong> en adelante.
                        Los cursos que ya tenían la materia asignada aparecen siempre (aunque no cumplan el filtro), para que puedas corregir la asignación.
                    <?php else: ?>
                        Se muestran todos los cursos activos (incluye ciclo básico sin especialidad).
                    <?php endif; ?>
                </p>

                <div class="cursos-grid">
                    <?php foreach ($cursos_disponibles as $curso): ?>
                    <div class="curso-checkbox">
                        <label>
                            <input type="checkbox" name="cursos[]" value="<?php echo (int) $curso['id']; ?>"
                                   <?php echo in_array((int) $curso['id'], $cursos_asignados_ids, true) ? 'checked' : ''; ?>>
                            <div>
                                <strong><?php echo htmlspecialchars((string) $curso['anio'] . '° ' . (string) $curso['division']); ?></strong>
                                <br><small><?php echo htmlspecialchars((string) ($curso['especialidad'] ?? '')); ?> (<?php echo htmlspecialchars((string) ($curso['turno'] ?? '')); ?>)</small>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($cursos_disponibles)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay cursos disponibles para esta materia.
                    <?php if ($materia_gestionar['especialidad_id']): ?>
                        No existen cursos activos en la especialidad: <strong><?php echo htmlspecialchars((string) ($materia_gestionar['especialidad'] ?? '')); ?></strong>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" name="gestionar_cursos_materia" class="btn btn-primary" <?php echo empty($cursos_disponibles) ? 'disabled' : ''; ?>>
                        <i class="fas fa-save"></i> Guardar Asignaciones
                    </button>
                    <a href="materias.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="filtro_especialidad">Especialidad:</label>
                    <select id="filtro_especialidad" name="filtro_especialidad">
                        <option value="">Todas las especialidades</option>
                        <option value="sin_especialidad" <?php echo $filtro_especialidad === 'sin_especialidad' ? 'selected' : ''; ?>>Sin especialidad</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?php echo (int) $esp['id']; ?>" <?php echo $filtro_especialidad !== '' && $filtro_especialidad === (string) $esp['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) ($esp['nombre'] ?? '')); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="materias.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Materias Registradas (<?php echo number_format($total_filtrado, 0, ',', '.'); ?>)</h3>
            <?php if ($total_filtrado > 0 && (int) $pagination['total_pages'] > 1): ?>
            <p class="pagination-summary" style="margin: 0.35rem 0 0; font-size: 0.9rem; color: var(--secondary-color, #64748b);">
                Página <?php echo (int) $pagination['current_page']; ?> de <?php echo (int) $pagination['total_pages']; ?>
                · <?php echo (int) $pagination['start_item']; ?>–<?php echo (int) $pagination['end_item']; ?> de <?php echo number_format($total_filtrado, 0, ',', '.'); ?>
            </p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <?php if ($filtro_especialidad !== ''): ?>
            <div class="filtro-activo-banner">
                <i class="fas fa-filter"></i>
                Mostrando <strong><?php echo $total_filtrado; ?></strong> materia<?php echo $total_filtrado !== 1 ? 's' : ''; ?>
                <?php if ($filtro_especialidad === 'sin_especialidad'): ?>
                    sin especialidad
                <?php else: ?>
                    de la especialidad:
                    <strong><?php echo htmlspecialchars($filtro_especialidad_label !== '' ? $filtro_especialidad_label : $filtro_especialidad); ?></strong>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Especialidad</th>
                        <th>Cursos Asignados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($m['nombre'] ?? '')); ?></td>
                        <td>
                            <span class="status <?php echo (int)($m['es_taller'] ?? 0) === 1 ? 'status-warning' : 'status-success'; ?>">
                                <?php echo (int)($m['es_taller'] ?? 0) === 1 ? 'Taller' : 'Regular'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars((string) ($m['especialidad'] ?? '-')); ?></td>
                        <td>
                            <?php if ((int) ($m['total_cursos'] ?? 0) > 0): ?>
                                <?php
                                $detalleCursos = (string) ($m['cursos_asignados'] ?? '');
                                $cursosList = explode(',', $detalleCursos);
                                ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; max-width: 300px;">
                                <?php foreach ($cursosList as $c): ?>
                                    <?php if (trim($c) !== ''): ?>
                                        <span class="status status-primary" style="font-size: 0.75rem; padding: 0.15rem 0.35rem; margin: 0; display: inline-block; white-space: nowrap;"><?php echo htmlspecialchars(trim($c), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="status status-warning status-sin-asignar">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones-materia">
                                <a href="materias.php?action=gestionar_cursos&amp;id=<?php echo (int) $m['id']; ?>"
                                   class="btn btn-warning btn-sm" title="Gestionar cursos">
                                    <i class="fas fa-users"></i> Cursos
                                </a>
                                <form method="POST" class="js-confirm-submit materias-inline-form" data-confirm-message="<?php echo htmlspecialchars('¿Desactivar materia?', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="materia_id" value="<?php echo (int) $m['id']; ?>">
                                    <button type="submit" name="desactivar_materia" class="btn btn-danger btn-sm" title="Desactivar materia">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_filtrado > 0 && (int) $pagination['total_pages'] > 1): ?>
        <nav class="pagination-nav" aria-label="Paginación de materias" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.35rem; padding: 1rem 1rem 1.25rem; border-top: 1px solid var(--medium-gray, #e2e8f0);">
            <?php
            $pn = (int) $pagination['current_page'];
            $tp = (int) $pagination['total_pages'];
            $linkBase = [];
            if ($filtro_especialidad !== '') {
                $linkBase['filtro_especialidad'] = $filtro_especialidad;
            }
            $mk = static function (array $base, int $p): string {
                $base['page'] = $p;
                $rel = 'materias.php?' . http_build_query($base);

                return function_exists('app_base_path') ? app_base_path($rel) : $rel;
            };
            ?>
            <?php if (!empty($pagination['has_previous'])): ?>
            <a class="btn btn-sm btn-secondary" href="<?php echo htmlspecialchars($mk($linkBase, $pn - 1), ENT_QUOTES, 'UTF-8'); ?>" rel="prev">« Anterior</a>
            <?php else: ?>
            <span class="btn btn-sm btn-secondary" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">« Anterior</span>
            <?php endif; ?>

            <?php foreach ($pagination['page_numbers'] as $num): ?>
                <?php $num = (int) $num; ?>
                <?php if ($num === $pn): ?>
            <span class="btn btn-sm btn-primary" aria-current="page"><?php echo $num; ?></span>
                <?php else: ?>
            <a class="btn btn-sm btn-secondary" href="<?php echo htmlspecialchars($mk($linkBase, $num), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $num; ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty($pagination['has_next'])): ?>
            <a class="btn btn-sm btn-secondary" href="<?php echo htmlspecialchars($mk($linkBase, $pn + 1), ENT_QUOTES, 'UTF-8'); ?>" rel="next">Siguiente »</a>
            <?php else: ?>
            <span class="btn btn-sm btn-secondary" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Siguiente »</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>

<?php
$nonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<script src="js/materias_page.js" defer nonce="<?php echo $nonce; ?>"></script>
<?php include 'includes/footer.php'; ?>
