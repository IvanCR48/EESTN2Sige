<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';

use SistemaAdmin\Controllers\MateriaPreviaController;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioMateriasPrevias;

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

$servicioMateriasPrevias = new ServicioMateriasPrevias($databaseAdapter);
$materiaPreviaController = new MateriaPreviaController($databaseAdapter, $servicioMateriasPrevias);

$pageTitle = 'Materias Previas - Sistema Administrativo E.E.S.T N°2';

$csrfToken = getCSRFToken();

$cursoGet = filter_input(INPUT_GET, 'curso', FILTER_DEFAULT);
$estudianteGet = filter_input(INPUT_GET, 'estudiante', FILTER_DEFAULT);
$curso_filter = $cursoGet !== null && trim((string) $cursoGet) !== '' ? trim((string) $cursoGet) : '';
$estudiante_filter = $estudianteGet !== null && trim((string) $estudianteGet) !== '' ? trim((string) $estudianteGet) : '';

$action = trim((string) (filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?? ''));
$success_message = '';
$error_message = '';

$esPostPrevias = $_SERVER['REQUEST_METHOD'] === 'POST'
    && (
        filter_input(INPUT_POST, 'guardar_previa', FILTER_DEFAULT) !== null
        || filter_input(INPUT_POST, 'eliminar_previa', FILTER_DEFAULT) !== null
        || filter_input(INPUT_POST, 'aprobar_previa', FILTER_DEFAULT) !== null
    );

if ($esPostPrevias) {
    if (!verifyCSRFToken((string) (filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) ?? ''))) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
        if (filter_input(INPUT_POST, 'guardar_previa', FILTER_DEFAULT) !== null) {
            $action = 'nueva';
        }
    } else {
        $postOutcome = $materiaPreviaController->procesarPostMateriasPrevias($_POST, $curso_filter, $estudiante_filter);
        if ($postOutcome['redirect'] !== null) {
            header('Location: ' . $postOutcome['redirect']);
            exit();
        }
        $error_message = (string) $postOutcome['error'];
        if ($postOutcome['action'] !== null) {
            $action = (string) $postOutcome['action'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $successKey = filter_input(INPUT_GET, 'success', FILTER_DEFAULT);
    switch ((string) $successKey) {
        case 'guardada':
            $success_message = 'Materia previa registrada';
            break;
        case 'eliminada':
            $success_message = 'Registro eliminado';
            break;
        case 'aprobada':
            $success_message = 'Materia previa aprobada exitosamente.';
            break;
        default:
            break;
    }
}

$pageListado = max(1, (int) (filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1));
$perPageListado = 20;

$vista = $materiaPreviaController->datosVista($curso_filter, $estudiante_filter, $pageListado, $perPageListado);
$cursos = $vista['cursos'];
$estudiantes = $vista['estudiantes'];
$estudiantes_filtro_lista = $vista['estudiantes_filtro_lista'];
$materias = $vista['materias'];
$previas = $vista['previas'];
$total_filtrado = $vista['total_filtrado'];
$pagination = $vista['pagination'];

$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/materias_previas.css">' . "\n";
sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="materias-previas-section">
    <div class="section-header">
        <h2>Materias Previas</h2>
        <a href="materias_previas.php?action=nueva" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Previa</a>
    </div>

    <?php if ($success_message !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($error_message !== ''): ?><div class="alert alert-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>


    <?php if ($action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Registrar Materia Previa</h3></div>
        <form method="POST" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="previa_curso_id">Curso *</label>
                    <select id="previa_curso_id" name="curso_id" required>
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo (int) $curso['id']; ?>">
                            <?php echo htmlspecialchars(MateriaPreviaController::etiquetaCursoAltaPrevia($curso), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; display: block; margin-top: 0.25rem;">Luego podrá elegir solo alumnos de este curso.</small>
                </div>
                <div class="form-group">
                    <label for="previa_estudiante_id">Estudiante *</label>
                    <select id="previa_estudiante_id" name="estudiante_id" required>
                        <option value="">Seleccione un curso primero</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <?php $cursoData = ($est['curso_id'] ?? null) !== null ? (string) $est['curso_id'] : ''; ?>
                        <option value="<?php echo (int) ($est['id'] ?? 0); ?>" data-curso-id="<?php echo htmlspecialchars($cursoData, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars((string) (($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="materia_id">Materia *</label>
                    <select id="materia_id" name="materia_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($materias as $m): ?>
                        <option value="<?php echo (int) $m['id']; ?>"><?php echo htmlspecialchars((string) $m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="anio_previo">Año</label>
                    <input type="number" min="1" max="7" id="anio_previo" name="anio_previo" value="1" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="pendiente"><?php echo htmlspecialchars('Pendiente', ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="regularizada"><?php echo htmlspecialchars('Regularizada', ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="aprobada"><?php echo htmlspecialchars('Aprobada', ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <input type="text" id="observaciones" name="observaciones" placeholder="Opcional">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_previa" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="materias_previas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo (int) $curso['id']; ?>" <?php echo $curso_filter === (string) $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(MateriaPreviaController::etiquetaCursoListado($curso), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante">
                        <option value="">Todos los estudiantes</option>
                        <?php foreach ($estudiantes_filtro_lista as $est): ?>
                        <option value="<?php echo (int) $est['id']; ?>"
                                data-curso-id="<?php echo htmlspecialchars((string) ($est['curso_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $estudiante_filter === (string) $est['id'] ? 'selected' : ''; ?>>
                            <?php
                            $labEst = htmlspecialchars((string) (($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')), ENT_QUOTES, 'UTF-8');
                            if (!empty($est['anio'])) {
                                $labEst .= htmlspecialchars(' — ' . $est['anio'] . '° ' . ($est['division'] ?? ''), ENT_QUOTES, 'UTF-8');
                            }
                            echo $labEst;
                            ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; display: block; margin-top: 0.25rem;">Al elegir un curso, solo se listan alumnos de ese curso.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="materias_previas.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    <div class="card card-listado-previas">
        <div class="card-header">
            <h3 class="card-title">Listado (<?php echo number_format($total_filtrado, 0, ',', '.'); ?> registros)</h3>
            <?php if ($total_filtrado > 0 && (int) $pagination['total_pages'] > 1): ?>
            <p class="pagination-summary" style="margin: 0.35rem 0 0; font-size: 0.9rem; color: var(--secondary-color, #64748b);">
                Página <?php echo (int) $pagination['current_page']; ?> de <?php echo (int) $pagination['total_pages']; ?>
                · <?php echo (int) $pagination['start_item']; ?>–<?php echo (int) $pagination['end_item']; ?> de <?php echo number_format($total_filtrado, 0, ',', '.'); ?>
            </p>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Curso Actual</th>
                        <th>Materia</th>
                        <th>Año Previo</th>
                        <th>Estado</th>
                        <th>Obs.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previas as $p): ?>
                    <tr>
                        <td><span class="estudiante-nombre"><?php echo htmlspecialchars((string) (($p['apellido'] ?? '') . ', ' . ($p['nombre'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td>
                            <?php if (empty($p['vista_sin_curso'])): ?>
                                <?php echo htmlspecialchars((string) ($p['vista_curso_principal'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($p['vista_curso_especialidad'])): ?>
                                    <br><small><?php echo htmlspecialchars((string) $p['vista_curso_especialidad'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                Sin curso
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars((string) ($p['materia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int) ($p['anio_previo'] ?? 0); ?>°</td>
                        <td>
                            <span class="<?php echo htmlspecialchars((string) ($p['vista_estado_class'] ?? 'status'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($p['vista_estado_text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars((string) ($p['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <?php if (($p['estado'] ?? '') !== 'aprobada'): ?>
                                <button type="button" class="btn btn-success btn-sm btn-open-aprobar btn-aprobar-previa" data-previa-id="<?php echo (int) $p['id']; ?>">
                                    <i class="fas fa-check"></i> Aprobar
                                </button>
                                <?php endif; ?>
                                <form method="POST" class="js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Eliminar registro?', ENT_QUOTES, 'UTF-8'); ?>" style="margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="previa_id" value="<?php echo (int) $p['id']; ?>">
                                    <button type="submit" name="eliminar_previa" class="btn btn-danger btn-sm btn-eliminar-previa">
                                        <i class="fas fa-trash"></i> Eliminar
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
        <nav class="pagination-nav" aria-label="Paginación de materias previas" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.35rem; padding: 1rem 1rem 1.25rem; border-top: 1px solid var(--medium-gray, #e2e8f0);">
            <?php
            $pn = (int) $pagination['current_page'];
            $tp = (int) $pagination['total_pages'];
            $linkBase = [];
            if ($curso_filter !== '') {
                $linkBase['curso'] = $curso_filter;
            }
            if ($estudiante_filter !== '') {
                $linkBase['estudiante'] = $estudiante_filter;
            }
            $mk = static function (array $base, int $p): string {
                $base['page'] = $p;
                $rel = 'materias_previas.php?' . http_build_query($base);

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
$nonce = htmlspecialchars($GLOBALS['csp_nonce'] ?? '', ENT_QUOTES, 'UTF-8');
$filtrosJsPath = __DIR__ . '/js/filtros_dependientes.js';
$filtrosJsVersion = is_file($filtrosJsPath) ? (string) filemtime($filtrosJsPath) : '1';
?>
<script src="js/filtros_dependientes.js?v=<?php echo htmlspecialchars($filtrosJsVersion, ENT_QUOTES, 'UTF-8'); ?>" defer nonce="<?php echo $nonce; ?>"></script>
<script nonce="<?php echo $nonce; ?>">
document.addEventListener('DOMContentLoaded', function () {
    if (window.FiltrosDependientes) {
        window.FiltrosDependientes.init({
            sourceSelectId: 'curso',
            targets: [
                { selectId: 'estudiante', emptyValue: '', dataAttr: 'data-curso-id', allowNoSourceShowAll: true }
            ]
        });
        window.FiltrosDependientes.init({
            sourceSelectId: 'previa_curso_id',
            targets: [
                { selectId: 'previa_estudiante_id', emptyValue: '', dataAttr: 'data-curso-id', allowNoSourceShowAll: false }
            ]
        });
    }

    // Modal de aprobación
    const modalAprobar = document.getElementById('modal-aprobar-previa');
    const inputPreviaId = document.getElementById('modal_previa_id');
    const btnsOpenAprobar = document.querySelectorAll('.btn-open-aprobar');
    const btnsCloseModal = document.querySelectorAll('.modal-close[data-close="modal-aprobar-previa"]');

    function openModalAprobar(id) {
        inputPreviaId.value = id;
        modalAprobar.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModalAprobar() {
        modalAprobar.classList.remove('active');
        document.body.style.overflow = '';
        inputPreviaId.value = '';
    }

    btnsOpenAprobar.forEach(btn => {
        btn.addEventListener('click', () => openModalAprobar(btn.dataset.previaId));
    });

    btnsCloseModal.forEach(btn => {
        btn.addEventListener('click', closeModalAprobar);
    });

    modalAprobar.addEventListener('click', (e) => {
        if (e.target === modalAprobar) closeModalAprobar();
    });
});
</script>

<div class="modal-overlay" id="modal-aprobar-previa" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width: 400px;">
        <button type="button" class="modal-close" data-close="modal-aprobar-previa" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h3>Aprobar Materia Previa</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem;">Ingrese los datos de la mesa examinadora.</p>
        </div>
        <form method="POST" id="form-aprobar-previa" style="margin-top: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="previa_id" id="modal_previa_id" value="">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Mes de Regularización</label>
                <select name="mes_aprobacion" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit;">
                    <option value="Diciembre">Diciembre</option>
                    <option value="Febrero">Febrero</option>
                    <option value="Marzo">Marzo</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Año</label>
                <input type="number" name="anio_aprobacion" value="<?php echo date('Y'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit;">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nota</label>
                <input type="number" name="nota" min="1" max="10" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit;">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">La nota mínima y recomendada es 4 para aprobar.</small>
            </div>
            <div class="form-actions" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="submit" name="aprobar_previa" class="btn btn-success" style="width: 100%;"><i class="fas fa-check"></i> Guardar Aprobación</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
