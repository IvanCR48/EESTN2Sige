<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/auth_helpers.php';

use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Mappers\AsistenciaMapper;
use SistemaAdmin\Services\ServicioAsistencia;

$databaseAdapter  = sistema_admin_db_adapter();
$servicioAuth     = new ServicioAutenticacion($databaseAdapter);
$usuario          = $servicioAuth->verificarSesion();

if (!$usuario) {
    header('Location: public/inicio.php');
    exit();
}

// Solo admin/directivo/preceptor
if (!hasRole(['admin', 'directivo', 'director', 'vicedirector', 'preceptor'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/includes/preceptor_scope.php';

$asistenciaMapper  = new AsistenciaMapper($databaseAdapter);
$servicioAsistencia = new ServicioAsistencia($databaseAdapter, $asistenciaMapper);

// Scope de cursos según rol
$preceptor_cids = preceptor_curso_ids();
$cursosIds = ($preceptor_cids !== []) ? $preceptor_cids : null;   // null = todos

$hoy     = date('Y-m-d');
$anio    = (int) date('Y');
$desde   = filter_input(INPUT_GET, 'desde', FILTER_DEFAULT) ?: ($anio . '-01-01');
$hasta   = filter_input(INPUT_GET, 'hasta', FILTER_DEFAULT) ?: $hoy;

// Datos del dashboard
$divisionesHoy = $servicioAsistencia->resumenDivisionesHoy($hoy, $cursosIds);
$enRiesgo      = $servicioAsistencia->alumnosEnRiesgoGlobal($cursosIds ?? [], $desde, $hasta);
$justificadosHoy = $servicioAsistencia->justificadosDelDia($hoy, $cursosIds);

// Verificación de Rol Directivo
$isDirectivo = hasRole(['admin', 'directivo', 'director', 'vicedirector']);

// Variables de filtros y métricas para Directivo
$cursosDisponibles = [];
$turnosDisponibles = [];
$especialidadesDisponibles = [];
$gradosDisponibles = [];
$divisionesDisponibles = [];
$metricasDirectivos = [];

$anioFiltro = 0;
$divisionFiltro = '';
$turnoFiltro = 0;
$especialidadFiltro = 0;

// Gráficos y Tendencias
$labelsTrend = [];
$dataTrend = [];
$labelsTurno = [];
$dataTurno = [];
$labelsSemanal = [];
$dataSemanal = [];
$labelsMensual = [];
$dataMensual = [];
$labelsAnual = [];
$dataAnual = [];

if ($isDirectivo) {
    $cursosDisponibles = $servicioAsistencia->cursosActivosParaAsistencia();
    $turnosDisponibles = $asistenciaMapper->obtenerTurnosActivos();
    $especialidadesDisponibles = $asistenciaMapper->obtenerEspecialidadesActivas();

    $gradosDisponibles = array_unique(array_column($cursosDisponibles, 'anio'));
    sort($gradosDisponibles);
    $divisionesDisponibles = array_unique(array_column($cursosDisponibles, 'division'));
    sort($divisionesDisponibles);

    $anioFiltro = (int) filter_input(INPUT_GET, 'anio_filtro', FILTER_VALIDATE_INT) ?: 0;
    $divisionFiltro = filter_input(INPUT_GET, 'division_filtro', FILTER_DEFAULT) ?: '';
    $turnoFiltro = (int) filter_input(INPUT_GET, 'turno_filtro', FILTER_VALIDATE_INT) ?: 0;
    $especialidadFiltro = (int) filter_input(INPUT_GET, 'especialidad_filtro', FILTER_VALIDATE_INT) ?: 0;

    $filtrosDirectivos = [
        'desde' => $desde,
        'hasta' => $hasta,
        'anio' => $anioFiltro,
        'division' => $divisionFiltro,
        'turno_id' => $turnoFiltro,
        'especialidad_id' => $especialidadFiltro
    ];

    $metricasDirectivos = $servicioAsistencia->obtenerMetricasDirectivos($filtrosDirectivos);

    // Preparar datos para los gráficos
    if (!empty($metricasDirectivos['serie_diaria'])) {
        foreach ($metricasDirectivos['serie_diaria'] as $dia) {
            $labelsTrend[] = date('d/m', strtotime($dia['fecha']));
            $tot = (int) $dia['total'];
            $pres = (int) $dia['presentes'];
            $dataTrend[] = $tot > 0 ? round((($tot - $pres) / $tot) * 100, 1) : 0.0;
        }
    }

    if (!empty($metricasDirectivos['asistencia_turno'])) {
        foreach ($metricasDirectivos['asistencia_turno'] as $t) {
            $labelsTurno[] = $t['turno'];
            $tot = (int) $t['total'];
            $pres = (int) $t['presentes'];
            $dataTurno[] = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0.0;
        }
    }

    if (!empty($metricasDirectivos['comparativa_semanal'])) {
        foreach ($metricasDirectivos['comparativa_semanal'] as $sem) {
            $labelsSemanal[] = 'Sem. ' . $sem['semana'] . ' (' . date('d/m', strtotime($sem['fecha_inicio'])) . ')';
            $tot = (int) $sem['total'];
            $pres = (int) $sem['presentes'];
            $dataSemanal[] = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0.0;
        }
    }

    $mesesNombres = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
    if (!empty($metricasDirectivos['comparativa_mensual'])) {
        foreach ($metricasDirectivos['comparativa_mensual'] as $mes) {
            $labelsMensual[] = $mesesNombres[(int)$mes['mes']] . ' ' . $mes['anio'];
            $tot = (int) $mes['total'];
            $pres = (int) $mes['presentes'];
            $dataMensual[] = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0.0;
        }
    }

    if (!empty($metricasDirectivos['comparativa_anual'])) {
        foreach ($metricasDirectivos['comparativa_anual'] as $an) {
            $labelsAnual[] = (string) $an['anio'];
            $tot = (int) $an['total'];
            $pres = (int) $an['presentes'];
            $dataAnual[] = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0.0;
        }
    }
}

$pageTitle        = 'Dashboard Asistencia - E.E.S.T N°2';
$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/asistencia_virtual.css">' . "\n";
sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>

<section class="av-section">
    <div class="section-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard de Asistencia</h2>
        <div class="header-actions">
            <a href="asistencia_virtual.php" class="btn btn-secondary">
                <i class="fas fa-clipboard-list"></i> Tomar asistencia
            </a>
        </div>
    </div>

    <!-- ====== UNIFIED TABS BAR ====== -->
    <div class="av-dashboard-tab-bar">
        <button type="button" class="av-dashboard-tab-btn active" data-tab="hoy">
            <i class="fas fa-calendar-day"></i>
            <span>Hoy</span>
        </button>
        <button type="button" class="av-dashboard-tab-btn" data-tab="justificados">
            <i class="fas fa-file-medical"></i>
            <span>Justificados</span>
        </button>
        <button type="button" class="av-dashboard-tab-btn" data-tab="riesgo">
            <i class="fas fa-exclamation-triangle"></i>
            <span>En Riesgo</span>
        </button>
        <?php if ($isDirectivo): ?>
        <button type="button" class="av-dashboard-tab-btn" data-tab="directivos">
            <i class="fas fa-chart-line"></i>
            <span>Directivos</span>
        </button>
        <?php endif; ?>
    </div>

    <!-- ── Resumen del día ── -->
    <div class="card av-dashboard-tab-content" data-tab="hoy">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-day"></i> Hoy — <?php echo date('d/m/Y'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($divisionesHoy)): ?>
                <p style="color:#94a3b8;text-align:center;padding:1rem">
                    <i class="fas fa-info-circle"></i> Sin datos de asistencia para el día de hoy.
                </p>
            <?php else: ?>
            <div class="av-dash-grid">
                <?php foreach ($divisionesHoy as $div):
                    $total    = max(1, (int) $div['total_alumnos']);
                    $pres     = (int) $div['presentes'] + (int) $div['tardanzas'];
                    $aus      = (int) $div['ausentes'];
                    $noReg    = $total - $pres - $aus;
                    $pctPres  = round($pres / $total * 100, 0);
                    $barCls   = $pctPres >= 85 ? 'ok' : ($pctPres >= 70 ? 'alerta' : 'riesgo');
                    $tieneReg = ($pres + $aus) > 0;
                ?>
                <a href="asistencia_virtual.php?curso_id=<?php echo (int)$div['curso_id']; ?>&fecha=<?php echo $hoy; ?>" class="av-dash-card" style="text-decoration:none">
                    <div class="av-dash-card__title">
                        <i class="fas fa-users"></i>
                        <?php echo htmlspecialchars($div['curso_label'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php if ($tieneReg): ?>
                    <div class="av-dash-bar-track">
                        <div class="av-dash-bar-fill av-dash-bar-fill--<?php echo $barCls; ?>"
                             style="width:<?php echo $pctPres; ?>%"></div>
                    </div>
                    <div class="av-dash-card__meta">
                        <span><?php echo $pres; ?> presentes (<?php echo $pctPres; ?>%)</span>
                        <span><?php echo $aus; ?> ausentes</span>
                    </div>
                    <?php else: ?>
                    <div style="color:#94a3b8;font-size:.78rem;font-style:italic">Sin asistencia registrada hoy</div>
                    <?php endif; ?>
                    <div class="av-dash-card__meta">
                        <span><i class="fas fa-user-graduate" style="opacity:.5"></i> <?php echo $total; ?> alumnos</span>
                        <?php if ($noReg > 0 && $tieneReg): ?>
                        <span style="color:#f59e0b"><?php echo $noReg; ?> sin marcar</span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Justificados del día ── -->
    <div class="card av-dashboard-tab-content" data-tab="justificados">
        <div class="card-header">
            <h3 class="card-title" style="color:var(--primary-color)"><i class="fas fa-file-medical"></i> Justificados hoy — <?php echo date('d/m/Y'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($justificadosHoy)): ?>
                <p style="color:#10b981;text-align:center;padding:1.5rem">
                    <i class="fas fa-check-circle"></i> No hay inasistencias justificadas cargadas en el día de hoy.
                </p>
            <?php else: ?>
            <div class="table-container av-desktop-only">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Materia</th>
                            <th>Observación</th>
                            <th>Justificativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($justificadosHoy as $just): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($just['apellido'] . ', ' . $just['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($just['curso_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($just['materia_nombre'] ?? 'Sin materia'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) ($just['observacion'] ?? ''), ENT_QUOTES, 'UTF-8') ?: '<span style="color:#94a3b8">—</span>'; ?></td>
                            <td style="text-align: center;">
                                <?php if (!empty($just['adjunto'])): ?>
                                <a href="uploads/<?php echo htmlspecialchars($just['adjunto'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" title="Ver justificativo" style="color:var(--primary-color);">
                                    <i class="fas fa-external-link-alt"></i> Ver
                                </a>
                                <?php else: ?>
                                <span style="color:#94a3b8; font-style:italic">Sin adjunto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="av-mobile-only">
                <?php foreach ($justificadosHoy as $just): ?>
                <div class="av-mobile-card">
                    <div class="av-mobile-card-row">
                        <div class="av-mobile-card-title"><?php echo htmlspecialchars($just['apellido'] . ', ' . $just['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="av-mobile-card-value">
                            <?php if (!empty($just['adjunto'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($just['adjunto'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="color:var(--primary-color);">
                                <i class="fas fa-external-link-alt"></i> Ver
                            </a>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.75rem; font-style:italic">Sin adjunto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="av-mobile-card-row" style="margin-top: 4px;">
                        <span class="av-mobile-card-label">Curso</span>
                        <span class="av-mobile-card-value"><?php echo htmlspecialchars($just['curso_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="av-mobile-card-row">
                        <span class="av-mobile-card-label">Materia</span>
                        <span class="av-mobile-card-value"><?php echo htmlspecialchars((string) ($just['materia_nombre'] ?? 'Sin materia'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <?php if (!empty($just['observacion'])): ?>
                    <div style="margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 6px;">
                        <span class="av-mobile-card-label" style="display:block; margin-bottom: 2px;">Observación</span>
                        <p style="margin: 0; font-size: 0.8rem; color: #475569; font-style: italic;"><?php echo htmlspecialchars((string)$just['observacion'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Alumnos en riesgo ── -->
    <div class="card av-dashboard-tab-content" data-tab="riesgo">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle" style="color:#ef4444"></i> Alumnos en riesgo de repitencia por inasistencias</h3>
            <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <input type="date" name="desde" value="<?php echo htmlspecialchars($desde); ?>" class="form-control" style="width:140px">
                <input type="date" name="hasta" value="<?php echo htmlspecialchars($hasta); ?>" class="form-control" style="width:140px">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
            </form>
        </div>
        <div class="card-body">
            <?php if (empty($enRiesgo)): ?>
                <p style="color:#10b981;text-align:center;padding:1.5rem">
                    <i class="fas fa-check-circle"></i> ¡Ningún alumno está en riesgo por inasistencias en el período seleccionado!
                </p>
            <?php else: ?>
            <div class="table-container av-desktop-only">
                <table class="table av-risk-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Presencias</th>
                            <th>Total clases</th>
                            <th>Asistencia</th>
                            <th>Riesgo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($enRiesgo as $al):
                        $pct    = (float) $al['porcentaje'];
                        $riesgo = $pct < 60 ? 'alto' : 'medio';
                        $rLbl   = $pct < 60 ? 'Alto' : 'Moderado';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($al['apellido'] . ', ' . $al['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($al['curso_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int)$al['presente'] + (int)$al['tardanza']; ?></td>
                        <td><?php echo (int)$al['total']; ?></td>
                        <td>
                            <span class="av-pct av-pct--riesgo" style="font-weight:700"><?php echo number_format($pct, 1); ?>%</span>
                        </td>
                        <td><span class="av-risk-badge av-risk-badge--<?php echo $riesgo; ?>"><?php echo $rLbl; ?></span></td>
                        <td>
                            <a href="estudiante_ficha.php?id=<?php echo (int)$al['id']; ?>" class="btn btn-sm btn-secondary" title="Ver ficha">
                                <i class="fas fa-user"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="av-mobile-only">
                <?php foreach ($enRiesgo as $al):
                    $pct    = (float) $al['porcentaje'];
                    $riesgo = $pct < 60 ? 'alto' : 'medio';
                    $rLbl   = $pct < 60 ? 'Alto' : 'Moderado';
                ?>
                <div class="av-mobile-card">
                    <div class="av-mobile-card-row">
                        <div class="av-mobile-card-title"><?php echo htmlspecialchars($al['apellido'] . ', ' . $al['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div>
                            <span class="av-risk-badge av-risk-badge--<?php echo $riesgo; ?>"><?php echo $rLbl; ?></span>
                        </div>
                    </div>
                    <div class="av-mobile-card-row" style="margin-top: 4px;">
                        <span class="av-mobile-card-label">Curso</span>
                        <span class="av-mobile-card-value"><?php echo htmlspecialchars($al['curso_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="av-mobile-card-row">
                        <span class="av-mobile-card-label">Clases (Presencias / Total)</span>
                        <span class="av-mobile-card-value"><?php echo ((int)$al['presente'] + (int)$al['tardanza']) . ' / ' . (int)$al['total']; ?></span>
                    </div>
                    <div class="av-mobile-card-row">
                        <span class="av-mobile-card-label">Asistencia</span>
                        <span class="av-pct av-pct--riesgo" style="font-weight:700"><?php echo number_format($pct, 1); ?>%</span>
                    </div>
                    <div style="margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 6px; display: flex; justify-content: flex-end;">
                        <a href="estudiante_ficha.php?id=<?php echo (int)$al['id']; ?>" class="btn btn-sm btn-secondary" style="font-size:0.75rem; padding: 4px 10px; display:inline-flex; align-items:center; gap: 4px;">
                            <i class="fas fa-user"></i> Ver Ficha
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:.75rem;font-size:.78rem;color:#64748b">
                <i class="fas fa-info-circle"></i>
                Umbral: alumnos con menos del <strong><?php echo ServicioAsistencia::UMBRAL_RIESGO; ?>%</strong> de asistencia en el período seleccionado.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Métricas Directivos ── -->
    <?php if ($isDirectivo): ?>
    <div class="card av-dashboard-tab-content" data-tab="directivos">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Panel de Métricas para Directivos</h3>
        </div>
        <div class="card-body">
            <!-- ====== FORMULARIO DE FILTROS ====== -->
            <form method="get" class="av-filter-card" style="margin-bottom: 2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.5rem;">
                <input type="hidden" name="tab" value="directivos">
                <div class="form-row" style="display:flex; gap:1.2rem; flex-wrap:wrap; align-items:flex-end;">
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label for="desde" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">Desde</label>
                        <input type="date" name="desde" id="desde" value="<?php echo htmlspecialchars($desde); ?>" class="form-control" style="width:100%;">
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label for="hasta" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">Hasta / Día Análisis</label>
                        <input type="date" name="hasta" id="hasta" value="<?php echo htmlspecialchars($hasta); ?>" class="form-control" style="width:100%;">
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label for="anio_filtro" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">Año (Grado)</label>
                        <select name="anio_filtro" id="anio_filtro" class="form-control" style="width:100%;">
                            <option value="">Todos</option>
                            <?php foreach ($gradosDisponibles as $g): ?>
                                <option value="<?php echo $g; ?>" <?php echo $anioFiltro === $g ? 'selected' : ''; ?>><?php echo $g; ?>° Año</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label for="division_filtro" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">División</label>
                        <select name="division_filtro" id="division_filtro" class="form-control" style="width:100%;">
                            <option value="">Todas</option>
                            <?php foreach ($divisionesDisponibles as $d): ?>
                                <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $divisionFiltro === $d ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label for="turno_filtro" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">Turno</label>
                        <select name="turno_filtro" id="turno_filtro" class="form-control" style="width:100%;">
                            <option value="">Todos</option>
                            <?php foreach ($turnosDisponibles as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $turnoFiltro === (int)$t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:180px;">
                        <label for="especialidad_filtro" style="font-weight:600; font-size:0.85rem; margin-bottom:4px; display:block;">Especialidad</label>
                        <select name="especialidad_filtro" id="especialidad_filtro" class="form-control" style="width:100%;">
                            <option value="">Todas</option>
                            <?php foreach ($especialidadesDisponibles as $esp): ?>
                                <option value="<?php echo $esp['id']; ?>" <?php echo $especialidadFiltro === (int)$esp['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($esp['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="min-width:120px;">
                        <button type="submit" class="btn btn-primary" style="width:100%; height:38px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            <!-- ====== INDICADORES CLAVE / RESUMEN ====== -->
            <div class="stats-grid av-stats" style="margin-bottom: 2rem;">
                <!-- Asistencia General -->
                <?php
                $diariaGeneral = $metricasDirectivos['diaria_general'] ?? ['presentes' => 0, 'total' => 0];
                $pctGeneral = $diariaGeneral['total'] > 0 ? ($diariaGeneral['presentes'] / $diariaGeneral['total']) * 100 : null;
                $generalTexto = $pctGeneral !== null ? number_format($pctGeneral, 1) . '%' : 'Sin datos';
                $generalSub = $pctGeneral !== null ? $diariaGeneral['presentes'] . ' de ' . $diariaGeneral['total'] . ' presentes' : 'No hay asistencia registrada para este día';
                ?>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#10b981;"><i class="fas fa-calendar-check" style="color:white;"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $generalTexto; ?></h3>
                        <p>Asistencia General (Día de Análisis)</p>
                        <small><?php echo $generalSub; ?></small>
                    </div>
                </div>

                <!-- Tendencia -->
                <?php
                $tend = $metricasDirectivos['tendencia'] ?? ['direccion' => 'estable', 'valor' => 0.0, 'texto' => 'Sin datos'];
                $tendColor = '#64748b';
                $tendIcon = 'exchange-alt';
                if ($tend['direccion'] === 'sube') {
                    $tendColor = '#ef4444'; // absenteeism rose is BAD
                    $tendIcon = 'long-arrow-alt-up';
                } elseif ($tend['direccion'] === 'baja') {
                    $tendColor = '#10b981'; // absenteeism fell is GOOD
                    $tendIcon = 'long-arrow-alt-down';
                }
                ?>
                <div class="stat-card" style="position:relative;">
                    <div class="stat-icon" style="background:<?php echo $tendColor; ?>;"><i class="fas fa-<?php echo $tendIcon; ?>" style="color:white;"></i></div>
                    <div class="stat-content">
                        <h3 style="font-size: 1.25rem; padding-top: 4px;"><?php echo htmlspecialchars($tend['texto']); ?></h3>
                        <p>Tendencia de Ausentismo</p>
                        <small>Comparación (últimos 7 días con datos vs periodo anterior)</small>
                    </div>
                </div>
            </div>

            <!-- ====== DESGLOSE DIARIO ====== -->
            <h4 style="margin: 1.5rem 0 1rem; color: #1e293b; font-weight:700;"><i class="fas fa-th-list"></i> Desglose de Asistencia Diaria (Día de Análisis: <?php echo date('d/m/Y', strtotime($metricasDirectivos['fecha_diaria'])); ?>)</h4>
            
            <div style="margin-bottom: 2.5rem; display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:1.25rem;">
                <!-- Agrupado por Grado -->
                <div class="card" style="border: 1px solid #e2e8f0; border-radius:12px; box-shadow:none; margin-bottom:0;">
                    <div class="card-header" style="padding: 10px 15px; background: #f8fafc; border-bottom:1px solid #e2e8f0;">
                        <h5 style="margin:0; font-size:0.9rem; font-weight:700;"><i class="fas fa-graduation-cap"></i> Por Grado / Año</h5>
                    </div>
                    <div class="card-body" style="padding: 15px; display:flex; flex-direction:column; gap:12px;">
                        <?php if (empty($metricasDirectivos['diaria_grado'])): ?>
                            <p style="color:#94a3b8; font-size:0.85rem; text-align:center; margin:0;">Sin registros</p>
                        <?php else: ?>
                            <?php foreach ($metricasDirectivos['diaria_grado'] as $g): 
                                $pct = $g['total'] > 0 ? ($g['presentes'] / $g['total']) * 100 : 0;
                                $barCls = $pct >= 85 ? 'ok' : ($pct >= 70 ? 'alerta' : 'riesgo');
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:4px;">
                                        <strong><?php echo $g['label']; ?>° Año</strong>
                                        <span><?php echo number_format($pct, 1); ?>%</span>
                                    </div>
                                    <div class="av-dash-bar-track" style="height: 6px; margin: 0;">
                                        <div class="av-dash-bar-fill av-dash-bar-fill--<?php echo $barCls; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Agrupado por División -->
                <div class="card" style="border: 1px solid #e2e8f0; border-radius:12px; box-shadow:none; margin-bottom:0;">
                    <div class="card-header" style="padding: 10px 15px; background: #f8fafc; border-bottom:1px solid #e2e8f0;">
                        <h5 style="margin:0; font-size:0.9rem; font-weight:700;"><i class="fas fa-columns"></i> Por División</h5>
                    </div>
                    <div class="card-body" style="padding: 15px; display:flex; flex-direction:column; gap:12px;">
                        <?php if (empty($metricasDirectivos['diaria_division'])): ?>
                            <p style="color:#94a3b8; font-size:0.85rem; text-align:center; margin:0;">Sin registros</p>
                        <?php else: ?>
                            <?php foreach ($metricasDirectivos['diaria_division'] as $d): 
                                $pct = $d['total'] > 0 ? ($d['presentes'] / $d['total']) * 100 : 0;
                                $barCls = $pct >= 85 ? 'ok' : ($pct >= 70 ? 'alerta' : 'riesgo');
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:4px;">
                                        <strong>División <?php echo htmlspecialchars($d['label']); ?></strong>
                                        <span><?php echo number_format($pct, 1); ?>%</span>
                                    </div>
                                    <div class="av-dash-bar-track" style="height: 6px; margin: 0;">
                                        <div class="av-dash-bar-fill av-dash-bar-fill--<?php echo $barCls; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Agrupado por Turno -->
                <div class="card" style="border: 1px solid #e2e8f0; border-radius:12px; box-shadow:none; margin-bottom:0;">
                    <div class="card-header" style="padding: 10px 15px; background: #f8fafc; border-bottom:1px solid #e2e8f0;">
                        <h5 style="margin:0; font-size:0.9rem; font-weight:700;"><i class="fas fa-clock"></i> Por Turno</h5>
                    </div>
                    <div class="card-body" style="padding: 15px; display:flex; flex-direction:column; gap:12px;">
                        <?php if (empty($metricasDirectivos['diaria_turno'])): ?>
                            <p style="color:#94a3b8; font-size:0.85rem; text-align:center; margin:0;">Sin registros</p>
                        <?php else: ?>
                            <?php foreach ($metricasDirectivos['diaria_turno'] as $t): 
                                $pct = $t['total'] > 0 ? ($t['presentes'] / $t['total']) * 100 : 0;
                                $barCls = $pct >= 85 ? 'ok' : ($pct >= 70 ? 'alerta' : 'riesgo');
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:4px;">
                                        <strong>Turno <?php echo htmlspecialchars($t['label']); ?></strong>
                                        <span><?php echo number_format($pct, 1); ?>%</span>
                                    </div>
                                    <div class="av-dash-bar-track" style="height: 6px; margin: 0;">
                                        <div class="av-dash-bar-fill av-dash-bar-fill--<?php echo $barCls; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ====== GRÁFICOS ====== -->
            <div class="charts-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap:1.5rem; margin-bottom: 2.5rem;">
                <!-- Evolución / Tendencia de Inasistencias -->
                <div class="chart-card card" style="padding: 1.5rem; border:1px solid #e2e8f0; border-radius:16px; margin-bottom:0;">
                    <h3 style="font-size:1.02rem; font-weight:700; margin-bottom:1rem; color:#1e293b; border:none; padding:0; background:none;"><i class="fas fa-chart-line"></i> Tendencia de Ausentismo (% Inasistencias Diarias)</h3>
                    <div style="position:relative; height:320px;">
                        <?php if (empty($metricasDirectivos['serie_diaria'])): ?>
                            <p style="color:#94a3b8; text-align:center; padding-top:100px;">Sin datos en el rango seleccionado</p>
                        <?php else: ?>
                            <canvas id="chartTendenciaInasistencias"></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Asistencia por Turno -->
                <div class="chart-card card" style="padding: 1.5rem; border:1px solid #e2e8f0; border-radius:16px; margin-bottom:0;">
                    <h3 style="font-size:1.02rem; font-weight:700; margin-bottom:1rem; color:#1e293b; border:none; padding:0; background:none;"><i class="fas fa-chart-pie"></i> Asistencia por Turno (Rango Acumulado)</h3>
                    <div style="position:relative; height:320px; display:flex; align-items:center; justify-content:center;">
                        <?php if (empty($metricasDirectivos['asistencia_turno'])): ?>
                            <p style="color:#94a3b8; text-align:center;">Sin datos en el rango seleccionado</p>
                        <?php else: ?>
                            <div style="width:100%; max-width:280px; height: 100%;"><canvas id="chartAsistenciaTurnos"></canvas></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ====== COMPARATIVAS ====== -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius:16px; margin-bottom:2.5rem;">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <h3 class="card-title" style="font-size:1.02rem; font-weight:700; color:#1e293b;"><i class="fas fa-history"></i> Comparativa de Asistencia (Semanal / Mensual / Anual)</h3>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <div style="position:relative; height:320px;">
                        <canvas id="chartComparativaAsistencia"></canvas>
                    </div>
                    <div style="display:flex; justify-content:center; gap:1.5rem; margin-top:1rem;">
                        <button type="button" class="btn btn-sm btn-secondary active" id="btnCompSemanal" style="font-size:0.8rem; font-weight:700; padding:6px 12px; border-radius:8px;">Semanal</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btnCompMensual" style="font-size:0.8rem; font-weight:700; padding:6px 12px; border-radius:8px;">Mensual</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btnCompAnual" style="font-size:0.8rem; font-weight:700; padding:6px 12px; border-radius:8px;">Anual</button>
                    </div>
                </div>
            </div>

            <!-- ====== RANKING CURSOS ====== -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius:16px; margin-bottom:0;">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <h3 class="card-title" style="font-size:1.02rem; font-weight:700; color:#1e293b;"><i class="fas fa-trophy" style="color:#eab308;"></i> Ranking de Cursos con Mayor Ausentismo</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($metricasDirectivos['ranking_cursos'])): ?>
                        <p style="color:#94a3b8; text-align:center; padding:2rem;">Sin datos en el rango seleccionado</p>
                    <?php else: ?>
                        <div class="table-container" style="border:none; margin:0;">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f1f5f9; text-align:left;">
                                        <th style="padding:12px 16px; font-weight:700; font-size:0.85rem; color:#475569; width:60px;">Puesto</th>
                                        <th style="padding:12px 16px; font-weight:700; font-size:0.85rem; color:#475569;">Curso</th>
                                        <th style="padding:12px 16px; font-weight:700; font-size:0.85rem; color:#475569;">Especialidad</th>
                                        <th style="padding:12px 16px; font-weight:700; font-size:0.85rem; color:#475569; width:150px;">% Ausentismo</th>
                                        <th style="padding:12px 16px; font-weight:700; font-size:0.85rem; color:#475569; width:220px;">Barra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $puesto = 1;
                                    foreach ($metricasDirectivos['ranking_cursos'] as $cur):
                                        $tot = (int)$cur['total'];
                                        $pres = (int)$cur['presentes'];
                                        $ausPct = $tot > 0 ? (($tot - $pres) / $tot) * 100 : 0;
                                    ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.15s;">
                                        <td style="padding:14px 16px; font-weight:bold; font-size:0.9rem; color:#475569;">#<?php echo $puesto++; ?></td>
                                        <td style="padding:14px 16px; font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($cur['curso_label']); ?></td>
                                        <td style="padding:14px 16px; font-size:0.85rem; color:#64748b;"><?php echo htmlspecialchars($cur['especialidad']); ?></td>
                                        <td style="padding:14px 16px; font-weight:700; color:#ef4444;"><?php echo number_format($ausPct, 1); ?>%</td>
                                        <td style="padding:14px 16px;">
                                            <div class="av-dash-bar-track" style="height:8px; background:#fee2e2; margin:0;">
                                                <div class="av-dash-bar-fill" style="width:<?php echo $ausPct; ?>%; background:linear-gradient(90deg, #f87171, #ef4444);"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>

<?php $dashNonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<script nonce="<?php echo $dashNonce; ?>">
document.addEventListener('DOMContentLoaded', function () {
    var dashTabBtns = document.querySelectorAll('.av-dashboard-tab-btn');
    var dashTabContents = document.querySelectorAll('.av-dashboard-tab-content');

    function switchDashTab(tabId) {
        dashTabBtns.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
        });
        dashTabContents.forEach(function (content) {
            content.classList.toggle('active-tab', content.getAttribute('data-tab') === tabId);
        });
    }

    if (dashTabBtns.length > 0) {
        dashTabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchDashTab(this.getAttribute('data-tab'));
                sessionStorage.setItem('av_dashboard_active_tab', this.getAttribute('data-tab'));
            });
        });

        var urlParams = new URLSearchParams(window.location.search);
        var activeTab = urlParams.get('tab') || sessionStorage.getItem('av_dashboard_active_tab') || 'hoy';
        
        var tabExists = Array.from(dashTabBtns).some(function(btn) {
            return btn.getAttribute('data-tab') === activeTab;
        });
        if (!tabExists) {
            activeTab = 'hoy';
        }
        
        switchDashTab(activeTab);
    }

    // Inicializar gráficos si corresponde
    <?php if ($isDirectivo && !empty($metricasDirectivos)): ?>
    var trendLabels = <?php echo json_encode($labelsTrend); ?>;
    var trendData = <?php echo json_encode($dataTrend); ?>;

    var turnosLabels = <?php echo json_encode($labelsTurno); ?>;
    var turnosData = <?php echo json_encode($dataTurno); ?>;

    var compSemanalLabels = <?php echo json_encode($labelsSemanal); ?>;
    var compSemanalData = <?php echo json_encode($dataSemanal); ?>;

    var compMensualLabels = <?php echo json_encode($labelsMensual); ?>;
    var compMensualData = <?php echo json_encode($dataMensual); ?>;

    var compAnualLabels = <?php echo json_encode($labelsAnual); ?>;
    var compAnualData = <?php echo json_encode($dataAnual); ?>;

    if (typeof Chart !== 'undefined' && document.getElementById('chartTendenciaInasistencias')) {
        // Gráfico de Tendencia
        var ctxTrend = document.getElementById('chartTendenciaInasistencias').getContext('2d');
        var trendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '% Inasistencias',
                    data: trendData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function(value) { return value + '%'; } }
                    }
                }
            }
        });

        // Gráfico de Turnos
        var ctxTurnos = document.getElementById('chartAsistenciaTurnos').getContext('2d');
        var turnosChart = new Chart(ctxTurnos, {
            type: 'doughnut',
            data: {
                labels: turnosLabels,
                datasets: [{
                    data: turnosData,
                    backgroundColor: ['#6366f1', '#f59e0b', '#3b82f6'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + '% Asistencia';
                            }
                        }
                    }
                }
            }
        });

        // Gráfico Comparativo
        var ctxComp = document.getElementById('chartComparativaAsistencia').getContext('2d');
        var compChart = new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: compSemanalLabels,
                datasets: [{
                    label: '% Asistencia',
                    data: compSemanalData,
                    backgroundColor: 'rgba(14, 165, 163, 0.85)',
                    hoverBackgroundColor: 'rgba(14, 165, 163, 1)',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function(value) { return value + '%'; } }
                    }
                }
            }
        });

        // Eventos para cambiar comparativas
        document.getElementById('btnCompSemanal').addEventListener('click', function() {
            toggleCompButton(this);
            compChart.data.labels = compSemanalLabels;
            compChart.data.datasets[0].data = compSemanalData;
            compChart.update();
        });
        document.getElementById('btnCompMensual').addEventListener('click', function() {
            toggleCompButton(this);
            compChart.data.labels = compMensualLabels;
            compChart.data.datasets[0].data = compMensualData;
            compChart.update();
        });
        document.getElementById('btnCompAnual').addEventListener('click', function() {
            toggleCompButton(this);
            compChart.data.labels = compAnualLabels;
            compChart.data.datasets[0].data = compAnualData;
            compChart.update();
        });

        function toggleCompButton(activeBtn) {
            ['btnCompSemanal', 'btnCompMensual', 'btnCompAnual'].forEach(function(id) {
                document.getElementById(id).classList.remove('active');
            });
            activeBtn.classList.add('active');
        }
    }
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
