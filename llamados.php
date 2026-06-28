<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';

use SistemaAdmin\Controllers\LlamadoController;
use SistemaAdmin\Mappers\EstudianteMapper;
use SistemaAdmin\Mappers\LlamadoMapper;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioEstudiantes;
use SistemaAdmin\Services\ServicioLlamados;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);

$usuario = $servicioAutenticacion->verificarSesion();
if (!$usuario) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

require_once __DIR__ . '/includes/preceptor_scope.php';
$preceptor_cids = preceptor_curso_ids();

$pageTitle = 'Llamados de Atención - Sistema Administrativo E.E.S.T N°2';

$llamadoMapper = new LlamadoMapper($databaseAdapter);
$estudianteMapper = new EstudianteMapper($databaseAdapter);
$servicioEstudiantes = new ServicioEstudiantes($databaseAdapter, $estudianteMapper);
$servicioLlamados = new ServicioLlamados($databaseAdapter, $llamadoMapper, $estudianteMapper);
$llamadoController = new LlamadoController($databaseAdapter, $servicioLlamados, $servicioEstudiantes);

$csrfToken = getCSRFToken();

$action = trim((string) (filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?? ''));
$success_message = '';
$error_message = '';
$llamado_form_values = null;

$queryGet = [];
foreach (['fecha_desde', 'fecha_hasta', 'curso', 'estudiante', 'motivo'] as $key) {
    $v = filter_input(INPUT_GET, $key, FILTER_DEFAULT);
    if ($v !== null && trim((string) $v) !== '') {
        $queryGet[$key] = trim((string) $v);
    }
}

$esPostLlamados = $_SERVER['REQUEST_METHOD'] === 'POST'
    && (
        filter_input(INPUT_POST, 'registrar_llamado', FILTER_DEFAULT) !== null
        || filter_input(INPUT_POST, 'eliminar_llamado', FILTER_DEFAULT) !== null
    );

if ($esPostLlamados) {
    if (!verifyCSRFToken((string) (filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) ?? ''))) {
        $error_message = 'La solicitud no pudo validarse. Actualice la página e intente nuevamente.';
    } else {
        $usuarioRegistroId = (int) ($usuario['id'] ?? 0);
        $postOutcome = $llamadoController->procesarPostLlamados($_POST, $preceptor_cids, $usuarioRegistroId, $queryGet);
        if ($postOutcome['redirect'] !== null) {
            header('Location: ' . $postOutcome['redirect']);
            exit();
        }
        $error_message = (string) $postOutcome['error'];
        if ($postOutcome['action'] !== null) {
            $action = (string) $postOutcome['action'];
        }
        if ($postOutcome['form_values'] !== null) {
            $llamado_form_values = $postOutcome['form_values'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $successKey = filter_input(INPUT_GET, 'success', FILTER_DEFAULT);
    switch ((string) $successKey) {
        case 'registrado':
            $success_message = 'Llamado de atención registrado correctamente';
            break;
        case 'eliminado':
            $success_message = 'Llamado de atención eliminado correctamente.';
            break;
        default:
            break;
    }
}

$pageListado = max(1, (int) (filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1));
$perPageListado = 20;

$vista = $llamadoController->datosVistaPagina($preceptor_cids, $queryGet, $llamado_form_values, $pageListado, $perPageListado);
$exportQueryString = $queryGet !== [] ? http_build_query($queryGet) : '';
$postBackQuery = $queryGet !== [] ? ('?' . http_build_query($queryGet)) : '';

$cursos = $vista['cursos'];
$estudiantes_filtro = $vista['estudiantes_filtro'];
$estudiantes = $vista['estudiantes'];
$llamados = $vista['llamados'];
$fecha_desde = (string) $vista['fecha_desde'];
$fecha_hasta = (string) $vista['fecha_hasta'];
$motivo_filter = (string) $vista['motivo_filter'];
$curso_filter = (string) $vista['curso_filter'];
$estudiante_filter = (string) $vista['estudiante_filter'];
$estudiante_id = (string) $vista['estudiante_id'];
$curso_preseleccionado = (string) $vista['curso_preseleccionado'];
$form_prefill_llamado = $vista['form_prefill_llamado'];
$motivos_opciones = $vista['motivos_opciones'];
$sanciones_opciones = $vista['sanciones_opciones'];
$total_listado_filtrado = $vista['total_filtrado'] ?? 0;
$pagination = $vista['pagination'] ?? null;

$llamadosUrlParams = $queryGet;
$total_llamados = (int) $vista['total_llamados'];
$llamados_hoy = (int) $vista['llamados_hoy'];
$llamados_con_sancion = (int) $vista['llamados_con_sancion'];
$llamados_sin_sancion = (int) $vista['llamados_sin_sancion'];
$motivos_frecuentes = $vista['motivos_frecuentes'];
$sanciones_frecuentes = $vista['sanciones_frecuentes'];
$curso_form_nuevo = $curso_preseleccionado;

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="llamados-section">
    <div class="section-header">
        <h2>Gestión de Llamados de Atención</h2>
        <a href="llamados.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Llamado
        </a>
    </div>

    <?php if ($success_message !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($error_message !== ''): ?>
        <div class="alert alert-error"><?php echo nl2br(htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8')); ?></div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_llamados, 0, ',', '.'); ?></h3>
                <p>Total Llamados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $llamados_hoy; ?></h3>
                <p>Llamados Hoy</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($llamados_con_sancion, 0, ',', '.'); ?></h3>
                <p>Con Sanción</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($llamados_sin_sancion, 0, ',', '.'); ?></h3>
                <p>Sin Sanción</p>
            </div>
        </div>
    </div>

    <!-- Formulario de nuevo llamado -->
    <?php if ($action === 'nuevo' || $estudiante_id): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nuevo Llamado de Atención</h3>
        </div>
        <form method="POST" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso_form">Curso:</label>
                    <select id="curso_form">
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos as $c): ?>
                        <?php $espLabel = $c['especialidad'] ?: ($c['anio'] < 4 ? 'Ciclo básico' : 'Sin especialidad'); ?>
                        <option value="<?php echo (int) $c['id']; ?>" <?php echo $curso_form_nuevo === (string) (int) $c['id'] ? 'selected' : ''; ?>>
                            <?php echo $c['anio'] . '° ' . $c['division'] . ' - ' . htmlspecialchars($espLabel); ?>
                        </option>
                        <?php endforeach; ?>
                        <option value="sin_curso" <?php echo $curso_form_nuevo === 'sin_curso' ? 'selected' : ''; ?>>Sin curso asignado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estudiante_id">Estudiante:</label>
                    <select name="estudiante_id" id="estudiante_id" required>
                        <option value="">Seleccionar estudiante</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <?php $estCursoId = (string) (($est['curso_id'] ?? '') ?: 'sin_curso'); ?>
                        <option value="<?php echo $est['id']; ?>" data-curso-id="<?php echo htmlspecialchars($estCursoId, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo (string) $estudiante_id === (string) $est['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?php echo htmlspecialchars($form_prefill_llamado['fecha'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo:</label>
                    <select name="motivo" id="motivo" required>
                        <option value="">Seleccionar motivo</option>
                        <?php foreach ($motivos_opciones as $motOpt): ?>
                        <option value="<?php echo htmlspecialchars($motOpt, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $form_prefill_llamado['motivo'] === $motOpt ? ' selected' : ''; ?>><?php echo htmlspecialchars($motOpt, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="descripcion">Descripción del Hecho:</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Describir detalladamente lo ocurrido" required><?php echo htmlspecialchars($form_prefill_llamado['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sancion">Sanción Aplicada:</label>
                    <select name="sancion" id="sancion">
                        <option value=""<?php echo $form_prefill_llamado['sancion'] === '' ? ' selected' : ''; ?>>Sin sanción</option>
                        <?php foreach ($sanciones_opciones as $sanOpt): ?>
                        <option value="<?php echo htmlspecialchars($sanOpt, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $form_prefill_llamado['sancion'] === $sanOpt ? ' selected' : ''; ?>><?php echo htmlspecialchars($sanOpt, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="registrar_llamado" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Llamado
                </button>
                <a href="llamados.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros para el listado -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="form-container" id="llamados-filtros-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo (int) $curso['id']; ?>" <?php echo (string) $curso_filter === (string) ($curso['id'] ?? '') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) (($curso['anio'] ?? '') . '° ' . ($curso['division'] ?? '') . ' - ' . ($curso['especialidad'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante">
                        <option value="">Todos los estudiantes</option>
                        <?php foreach ($estudiantes_filtro as $est): ?>
                        <option value="<?php echo (int) $est['id']; ?>" data-curso-id="<?php echo htmlspecialchars((string) ($est['curso_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $estudiante_filter === (string) ($est['id'] ?? '') ? 'selected' : ''; ?>>
                            <?php
                            $estEtiqueta = htmlspecialchars((string) (($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')), ENT_QUOTES, 'UTF-8');
                            if (!empty($est['anio'])) {
                                $estEtiqueta .= htmlspecialchars(' - ' . $est['anio'] . '° ' . ($est['division'] ?? ''), ENT_QUOTES, 'UTF-8');
                            }
                            echo $estEtiqueta;
                            ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" 
                           value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" 
                           value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                </div>
                
                <div class="form-group">
                    <label for="motivo_filter">Motivo:</label>
                    <input type="text" name="motivo" id="motivo_filter" 
                           value="<?php echo htmlspecialchars($motivo_filter); ?>" 
                           placeholder="Buscar por motivo">
                </div>
            </div>
            
            <div class="form-actions" style="flex-wrap: wrap; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar Reporte
                </button>
                <a href="llamados.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar Filtros
                </a>
                <a href="export_llamados.php<?php echo $exportQueryString !== '' ? '?' . htmlspecialchars($exportQueryString, ENT_QUOTES, 'UTF-8') : ''; ?>" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Exportar Excel (ahora)
                </a>
                <button type="button" class="btn btn-success" id="btn-exportar-llamados-async" style="opacity: 0.95;" title="Genera el archivo en el servidor; recibirá un enlace de descarga al terminar (requiere cron de colas)">
                    <i class="fas fa-cloud-download-alt"></i> Exportar Excel en segundo plano
                </button>
            </div>
            <div id="export-async-status" class="card-body" style="display: none; margin-top: 0.75rem; padding: 0.75rem 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 0.9rem;" role="status" aria-live="polite"></div>
        </form>
    </div>

    <!-- Análisis por motivos -->
    <?php if (!empty($motivos_frecuentes)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Motivos Más Frecuentes</h3>
        </div>
        <div class="card-body">
            <?php foreach ($motivos_frecuentes as $motivo): ?>
            <div class="motivo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                <span><?php echo htmlspecialchars((string) ($motivo['motivo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 100px; height: 8px; background: var(--light-gray); border-radius: 4px; overflow: hidden;">
                        <div style="width: <?php echo htmlspecialchars((string) (float) ($motivo['pct_barra'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>%; height: 100%; background: var(--warning-color);"></div>
                    </div>
                    <span class="status status-warning"><?php echo number_format((int) ($motivo['cantidad'] ?? 0), 0, ',', '.'); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Análisis por sanciones -->
    <?php if (!empty($sanciones_frecuentes)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sanciones Más Aplicadas</h3>
        </div>
        <div class="card-body">
            <?php foreach ($sanciones_frecuentes as $sancion): ?>
            <div class="sancion-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                <span><?php echo htmlspecialchars((string) ($sancion['sancion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 100px; height: 8px; background: var(--light-gray); border-radius: 4px; overflow: hidden;">
                        <div style="width: <?php echo htmlspecialchars((string) (float) ($sancion['pct_barra'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>%; height: 100%; background: var(--danger-color);"></div>
                    </div>
                    <span class="status status-danger"><?php echo number_format((int) ($sancion['cantidad'] ?? 0), 0, ',', '.'); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de llamados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalle de Llamados de Atención (<?php echo number_format($total_llamados, 0, ',', '.'); ?>)</h3>
        </div>
        
        <?php if (!empty($llamados)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th>Motivo</th>
                        <th>Sanción</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($llamados as $llamado): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars((string) ($llamado['vista_fecha_corta'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br><small><?php echo htmlspecialchars((string) ($llamado['vista_dia_semana'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars((string) (($llamado['apellido'] ?? '') . ', ' . ($llamado['nombre'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br><small>DNI: <?php echo htmlspecialchars((string) ($llamado['dni'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars((string) (($llamado['anio'] ?? '') . '° ' . ($llamado['division'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                            <br><small><?php echo htmlspecialchars((string) ($llamado['especialidad'] ?? 'Sin especialidad'), ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <td>
                            <span class="status status-warning">
                                <?php echo htmlspecialchars((string) ($llamado['motivo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if (!empty($llamado['descripcion'])): ?>
                                <br><small style="color: var(--secondary-color);"
                                          title="<?php echo htmlspecialchars((string) $llamado['descripcion'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) ($llamado['vista_descripcion_resumen'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($llamado['sancion'])): ?>
                                <span class="status status-danger"><?php echo htmlspecialchars((string) $llamado['sancion'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php else: ?>
                                <span class="status status-success">Sin sanción</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars((string) (($llamado['usuario_apellido'] ?? '') . ', ' . ($llamado['usuario_nombre'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                                <br><?php
                                $fr = strtotime((string) ($llamado['fecha_registro'] ?? ''));
                                echo $fr ? htmlspecialchars(date('d/m/Y H:i', $fr), ENT_QUOTES, 'UTF-8') : '';
                                ?>
                            </small>
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="estudiante_ficha.php?id=<?php echo (int) $llamado['estudiante_id']; ?>"
                               class="btn btn-sm btn-primary" title="Ver ficha del estudiante">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="post" action="llamados.php<?php echo htmlspecialchars($postBackQuery, ENT_QUOTES, 'UTF-8'); ?>" style="display:inline-block; margin-left:0.35rem;" class="js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Eliminar este llamado de atención? Esta acción no se puede deshacer.', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="llamado_id" value="<?php echo (int) $llamado['id']; ?>">
                                <?php /* Oculto: form.submit() tras confirm (CSP) no envía el botón; sin esto no llega eliminar_llamado al POST */ ?>
                                <input type="hidden" name="eliminar_llamado" value="1">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar llamado">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_listado_filtrado > 0 && $pagination !== null && (int) $pagination['total_pages'] > 1): ?>
        <nav class="pagination-nav" aria-label="Paginación de llamados" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.35rem; padding: 1rem 1rem 1.25rem; border-top: 1px solid var(--medium-gray, #e2e8f0);">
            <?php
            $pn = (int) $pagination['current_page'];
            $tp = (int) $pagination['total_pages'];
            $linkBase = $llamadosUrlParams;
            $mk = static function (array $base, int $p): string {
                $base['page'] = $p;
                return 'llamados.php?' . http_build_query($base);
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
        
        <?php else: ?>
        <div class="card-body text-center" style="padding: 3rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--secondary-color); opacity: 0.3; margin-bottom: 1rem;"></i>
            <h3 style="color: var(--secondary-color); margin-bottom: 0.5rem;">No hay llamados de atención registrados</h3>
            <p style="color: var(--secondary-color); margin-bottom: 2rem;">
                <?php if ($fecha_desde || $motivo_filter): ?>
                    No se encontraron llamados con los criterios especificados
                <?php else: ?>
                    Aún no se han registrado llamados de atención
                <?php endif; ?>
            </p>
            <a href="llamados.php?action=nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Registrar Primer Llamado
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
$llamadosNonce = htmlspecialchars($GLOBALS['csp_nonce'] ?? '', ENT_QUOTES, 'UTF-8');
$filtrosDependientesPath = __DIR__ . '/js/filtros_dependientes.js';
$filtrosDependientesVersion = is_file($filtrosDependientesPath) ? (string) filemtime($filtrosDependientesPath) : '1';
$llamadosJsPath = __DIR__ . '/js/llamados.js';
$llamadosJsVersion = is_file($llamadosJsPath) ? (string) filemtime($llamadosJsPath) : '1';
?>
<script src="js/filtros_dependientes.js?v=<?php echo htmlspecialchars($filtrosDependientesVersion, ENT_QUOTES, 'UTF-8'); ?>" defer nonce="<?php echo $llamadosNonce; ?>"></script>
<script src="js/llamados.js?v=<?php echo htmlspecialchars($llamadosJsVersion, ENT_QUOTES, 'UTF-8'); ?>" defer nonce="<?php echo $llamadosNonce; ?>"></script>
<script nonce="<?php echo $llamadosNonce; ?>">
(function () {
    var btn = document.getElementById('btn-exportar-llamados-async');
    var box = document.getElementById('export-async-status');
    var form = document.getElementById('llamados-filtros-form');
    if (!btn || !box || !form) return;

    var csrf = <?php echo json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function setStatus(html, show) {
        box.innerHTML = html;
        box.style.display = show ? 'block' : 'none';
    }

    function parseJsonResponse(r, text) {
        try {
            return JSON.parse(text);
        } catch (e) {
            return { success: false, error: text || 'Respuesta no válida' };
        }
    }

    btn.addEventListener('click', function () {
        var fd = new FormData(form);
        var body = {
            csrf_token: csrf,
            fecha_desde: fd.get('fecha_desde') || '',
            fecha_hasta: fd.get('fecha_hasta') || '',
            curso: fd.get('curso') || '',
            estudiante: fd.get('estudiante') || '',
            motivo: fd.get('motivo') || ''
        };

        btn.disabled = true;
        setStatus('<strong>En cola.</strong> La exportación se procesará en el servidor. Puede seguir usando el sistema; el aviso se actualizará aquí.', true);

        fetch('api/export_llamados_queue.php?action=enqueue', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
            credentials: 'same-origin'
        })
            .then(function (r) {
                return r.text().then(function (text) {
                    return { ok: r.ok, j: parseJsonResponse(r, text) };
                });
            })
            .then(function (res) {
                if (!res.ok || !res.j.success) {
                    var err = (res.j && res.j.error) ? res.j.error : 'No se pudo encolar la exportación.';
                    setStatus('<strong>Error.</strong> ' + err, true);
                    btn.disabled = false;
                    return;
                }
                var exportId = res.j.export_id;
                if (!exportId) {
                    setStatus('<strong>Error.</strong> Respuesta incompleta del servidor.', true);
                    btn.disabled = false;
                    return;
                }
                setStatus('<strong>Procesando en segundo plano.</strong> Cuando el archivo esté listo aparecerá el enlace de descarga. Asegúrese de que el cron de colas esté activo (Herramientas admin → Monitoreo).', true);

                var intentos = 0;
                var maxIntentos = 90;
                var t = setInterval(function () {
                    intentos++;
                    if (intentos > maxIntentos) {
                        clearInterval(t);
                        setStatus('<strong>Aún en proceso o sin worker.</strong> Revise el cron de colas o use «Exportar Excel (ahora)».', true);
                        btn.disabled = false;
                        return;
                    }
                    fetch('api/export_llamados_queue.php?action=status&export_id=' + encodeURIComponent(String(exportId)), { credentials: 'same-origin' })
                        .then(function (r) { return r.text().then(function (text) { return parseJsonResponse(r, text); }); })
                        .then(function (st) {
                            if (!st.success) return;
                            if (st.estado === 'failed') {
                                clearInterval(t);
                                var msg = st.error ? st.error : 'Falló la generación.';
                                setStatus('<strong>Error en la exportación.</strong> ' + msg, true);
                                btn.disabled = false;
                            } else if (st.listo_para_descargar) {
                                clearInterval(t);
                                var url = 'export_llamados.php?download_export=1&export_id=' + encodeURIComponent(String(exportId));
                                setStatus('<strong>Listo.</strong> <a href="' + url + '" class="btn btn-success" style="display:inline-block;margin-top:0.5rem;">Descargar Excel</a>', true);
                                btn.disabled = false;
                            }
                        })
                        .catch(function () {});
                }, 2000);
            })
            .catch(function () {
                setStatus('<strong>Error de red.</strong> Intente nuevamente.', true);
                btn.disabled = false;
            });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
