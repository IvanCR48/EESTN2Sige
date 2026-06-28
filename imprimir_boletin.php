<?php

declare(strict_types=1);

/**
 * Impresión del boletín: sesión, CSRF en query, datos vía ServicioBoletinNotas (sin SQL en vista).
 */
require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';

use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioBoletinNotas;
use SistemaAdmin\Services\ServicioAsistencia;
use SistemaAdmin\Services\ServicioEstudiantes;
use SistemaAdmin\Mappers\AsistenciaMapper;
use SistemaAdmin\Mappers\EstudianteMapper;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);
$servicioBoletinNotas = new ServicioBoletinNotas($databaseAdapter);
$asistenciaMapper = new AsistenciaMapper($databaseAdapter);
$servicioAsistencia = new ServicioAsistencia($databaseAdapter, $asistenciaMapper);

$usuario = $servicioAutenticacion->verificarSesion();

require_once __DIR__ . '/includes/auth_helpers.php';
require_once __DIR__ . '/includes/preceptor_scope.php';
require_once __DIR__ . '/includes/profesor_scope.php';

if (!$usuario) {
    header('Location: ' . app_base_path('/public/login.php'));
    exit();
}

$servicioEstudiantesPrint = new ServicioEstudiantes($databaseAdapter, new EstudianteMapper($databaseAdapter));
$preceptorCidsBoletin = preceptor_curso_ids();
$esProfesorBoletin = es_profesor();
$profesorCidsBoletin = $esProfesorBoletin ? profesor_curso_ids() : [];

if ($esProfesorBoletin) {
    if ($profesorCidsBoletin === []) {
        header('Location: ' . app_base_path('estudiantes.php'));
        exit();
    }
} elseif ((($_SESSION['rol'] ?? '') === 'preceptor') && $preceptorCidsBoletin === []) {
    header('Location: ' . app_base_path('estudiantes.php'));
    exit();
}

$idGet = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$estudianteId = ($idGet !== false && $idGet > 0) ? $idGet : 0;

if ($estudianteId < 1) {
    header('Location: ' . app_base_path('estudiantes.php'));
    exit();
}

if ($esProfesorBoletin) {
    $cursoAlumno = $servicioEstudiantesPrint->obtenerCursoIdEstudianteActivo($estudianteId);
    if ($cursoAlumno === null || !in_array($cursoAlumno, $profesorCidsBoletin, true)) {
        header('Location: ' . app_base_path('estudiantes.php'));
        exit();
    }
} elseif ($preceptorCidsBoletin !== []) {
    $cursoAlumno = $servicioEstudiantesPrint->obtenerCursoIdEstudianteActivo($estudianteId);
    if ($cursoAlumno === null || !in_array($cursoAlumno, $preceptorCidsBoletin, true)) {
        header('Location: ' . app_base_path('estudiantes.php'));
        exit();
    }
} elseif (!(hasRole('admin') || hasRole('directivo') || hasRole('director') || hasRole('vicedirector') || hasRole('secretario'))) {
    header('Location: ' . app_base_path('index.php?error=unauthorized'));
    exit();
}

$csrfGet = trim((string) (filter_input(INPUT_GET, 'csrf_token', FILTER_DEFAULT) ?? ''));

if ($estudianteId < 1 || !verifyCSRFToken($csrfGet)) {
    header('Location: ' . app_base_path('estudiantes.php'));
    exit();
}

$payload = $servicioBoletinNotas->obtenerBoletinParaImpresion($estudianteId);
if (!$payload['encontrado'] || $payload['estudiante'] === null) {
    header('Location: ' . app_base_path('estudiantes.php'));
    exit();
}

$estudiante = $payload['estudiante'];
$notas_estudiante = $payload['notas_por_materia'];
$materias_previas = $payload['materias_previas'] ?? [];
$estadisticas = $payload['estadisticas'];

$fecha_actual = date('d/m/Y');
$anioLectivo = (int) date('Y');

$rangoCuatrimestres = [
    'c1' => [$anioLectivo . '-03-01', $anioLectivo . '-07-31'],
    'c2' => [$anioLectivo . '-08-01', $anioLectivo . '-12-31'],
    'total' => [$anioLectivo . '-03-01', $anioLectivo . '-12-31'],
];

$asistenciaBoletin = [];
foreach ($rangoCuatrimestres as $clave => [$desde, $hasta]) {
    $res = $servicioAsistencia->datosAsistenciaFicha($estudianteId, $desde, $hasta, 5)['resumen'];
    $totalReg = (int) ($res['total'] ?? 0);
    $inasistencias = ((int) ($res['tardanza'] ?? 0) * 0.25) + ((int) ($res['media_falta'] ?? 0) * 0.5) + (int) ($res['ausente_justificado'] ?? 0) + (int) ($res['ausente'] ?? 0);
    $asistio = (float) ($res['presente'] ?? 0) + ((float) ($res['tardanza'] ?? 0) * 0.75) + ((float) ($res['media_falta'] ?? 0) * 0.5);
    $porcentaje = $totalReg > 0
        ? round(($asistio / $totalReg) * 100, 1)
        : -1.0;
    $asistenciaBoletin[$clave] = [
        'inasistencias' => $inasistencias,
        'total' => $totalReg,
        'porcentaje' => $porcentaje,
    ];
}
$c2Iniciado = date('Y-m-d') >= $rangoCuatrimestres['c2'][0];

sistema_admin_send_html_security_headers();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletín de Notas - <?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_base_path('css/imprimir_boletin.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
    <div class="watermark">EEST N°2</div>
    
    <div class="boletin-container">
        <div class="header">
            <div class="school-logo">EEST</div>
            <div class="school-info">
                <h1>E.E.S.T. N°2 "Educación y Trabajo"</h1>
                <h2>Escuela de Educación Secundaria Técnica</h2>
            </div>
            <div class="boletin-title">Boletín de Calificaciones</div>
        </div>
        
        <div class="student-info-section" style="display: flex; gap: 20px; align-items: center;">
            <div class="student-info-grid" style="flex: 1; margin-bottom: 0;">
                <div class="info-group">
                    <div class="info-label">Estudiante:</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">DNI:</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['dni']); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Curso:</div>
                    <div class="info-value"><?php echo htmlspecialchars(
                        ($estudiante['anio'] ?? '') . '° ' . ($estudiante['division'] ?? '')
                        . (isset($estudiante['especialidad']) && $estudiante['especialidad'] !== ''
                            ? ' - ' . $estudiante['especialidad']
                            : '')
                    ); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value"><?php echo $fecha_actual; ?></div>
                </div>
            </div>

            <?php
            $fotoPath = '';
            if (!empty($estudiante['foto'])) {
                $fotoPath = (string) $estudiante['foto'];
                if ($fotoPath !== '' && !str_starts_with($fotoPath, 'http://')
                    && !str_starts_with($fotoPath, 'https://')
                    && !str_starts_with($fotoPath, '//')
                    && !str_starts_with($fotoPath, '/')) {
                    $fotoPath = app_base_path(ltrim($fotoPath, '/'));
                }
            }
            ?>
            <div class="student-photo-container" style="width: 80px; height: 100px; border: 2px solid #1a4b84; display: flex; align-items: center; justify-content: center; background: #e9ecef; border-radius: 4px; overflow: hidden; flex-shrink: 0;">
                <?php if ($fotoPath !== ''): ?>
                    <img src="<?php echo htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="font-size: 8px; color: #6c757d; text-align: center; text-transform: uppercase; font-weight: bold; font-family: sans-serif; padding: 4px;">Sin Foto</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="academic-year">
            Año Lectivo <?php echo date('Y'); ?>
        </div>
        
        <div class="grades-section">
            <?php if (!empty($notas_estudiante)): ?>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="cuatrimestre-cell">Cuatrimestre</th>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <th class="materia-header">
                            <strong><?php
                            $palabrasMateria = explode(' ', (string) $datos['materia']['nombre']);
                            $nombreCortoMateria = implode(' ', array_slice($palabrasMateria, 0, 4));
                            if (count($palabrasMateria) > 4) {
                                $nombreCortoMateria .= '...';
                            }
                            echo htmlspecialchars($nombreCortoMateria, ENT_QUOTES, 'UTF-8');
                            ?></strong>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr class="avance-row">
                        <td class="cuatrimestre-cell">
                            <strong>Avance 1</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td class="avance-cell">
                            <span class="avance-value"><?php
                                $a1 = $datos['avances']['avance1'] ?? null;
                                echo $a1 !== null && $a1 !== '' ? htmlspecialchars((string) $a1, ENT_QUOTES, 'UTF-8') : '-';
                            ?></span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td class="cuatrimestre-cell">
                            <strong>1° Cuatrimestre</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td>
                            <span class="nota-value"><?php
                                $n1 = $datos['cuatrimestres'][1] ?? null;
                                echo $n1 !== null && $n1 !== '' ? htmlspecialchars((string) $n1, ENT_QUOTES, 'UTF-8') : '-';
                            ?></span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td class="cuatrimestre-cell">
                            <strong>2° Cuatrimestre</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td>
                            <span class="nota-value"><?php
                                $n2 = $datos['cuatrimestres'][2] ?? null;
                                echo $n2 !== null && $n2 !== '' ? htmlspecialchars((string) $n2, ENT_QUOTES, 'UTF-8') : '-';
                            ?></span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr class="avance-row">
                        <td class="cuatrimestre-cell">
                            <strong>Avance 2</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td class="avance-cell">
                            <span class="avance-value"><?php
                                $a2 = $datos['avances']['avance2'] ?? null;
                                echo $a2 !== null && $a2 !== '' ? htmlspecialchars((string) $a2, ENT_QUOTES, 'UTF-8') : '-';
                            ?></span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr class="promedio-row">
                        <td class="cuatrimestre-cell">
                            <strong>Promedio</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td>
                            <?php if ($datos['promedio'] !== null): ?>
                                <span class="promedio-value"><?php echo htmlspecialchars((string) $datos['promedio'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if (!$datos['promedio_completo']): ?>
                                    <div class="promedio-parcial">Parcial</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="promedio-pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr class="estado-row">
                        <td class="cuatrimestre-cell">
                            <strong>Estado</strong>
                        </td>
                        <?php foreach ($notas_estudiante as $materia_id => $datos): ?>
                        <td>
                            <?php if ($datos['promedio_completo']): ?>
                                <?php if ($datos['promedio'] >= 7): ?>
                                    <span class="estado aprobado">Aprobado</span>
                                <?php else: ?>
                                    <span class="estado reprobado">Reprobado</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="estado pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                </tbody>
            </table>
            <?php else: ?>
            <div class="boletin-grades-empty">
                <p>No hay notas registradas para este estudiante</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($materias_previas)): ?>
        <div class="summary-section">
            <div class="attendance-summary">
                <h3>Materias previas</h3>
                <div class="table-container">
                    <table class="grades-table grades-table--previas">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Año previo</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias_previas as $previa): ?>
                            <?php
                            $estadoPrevia = strtolower((string) ($previa['estado'] ?? 'pendiente'));
                            $estadoLabel = match ($estadoPrevia) {
                                'aprobada' => 'Aprobada',
                                'regularizada' => 'Regularizada',
                                default => 'Pendiente',
                            };
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($previa['materia_nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) ($previa['anio_previo'] ?? 0); ?>°</td>
                                <td><?php echo htmlspecialchars($estadoLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($previa['observaciones'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($notas_estudiante)): ?>
        <div class="summary-section">
            <div class="attendance-summary">
                <h3>Asistencia del estudiante</h3>
                <div class="attendance-summary-grid">
                    <div class="attendance-summary-item">
                        <div class="attendance-summary-item__label">Inasistencias 1° Cuatr.</div>
                        <div class="attendance-summary-item__value"><?php echo (float) $asistenciaBoletin['c1']['inasistencias']; ?></div>
                        <div class="attendance-summary-item__meta"><?php echo $asistenciaBoletin['c1']['porcentaje'] >= 0 ? number_format((float) $asistenciaBoletin['c1']['porcentaje'], 1) . '% asistencia' : 'S/D'; ?></div>
                    </div>
                    <div class="attendance-summary-item">
                        <div class="attendance-summary-item__label">Inasistencias 2° Cuatr.</div>
                        <div class="attendance-summary-item__value"><?php echo (float) $asistenciaBoletin['c2']['inasistencias']; ?></div>
                        <div class="attendance-summary-item__meta">
                            <?php
                            if (!$c2Iniciado) {
                                echo 'En curso (aún no inicia)';
                            } else {
                                echo $asistenciaBoletin['c2']['porcentaje'] >= 0 ? number_format((float) $asistenciaBoletin['c2']['porcentaje'], 1) . '% asistencia' : 'S/D';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="attendance-summary-item attendance-summary-item--total">
                        <div class="attendance-summary-item__label">Total Inasistencias</div>
                        <div class="attendance-summary-item__value"><?php echo (float) $asistenciaBoletin['total']['inasistencias']; ?></div>
                        <div class="attendance-summary-item__meta"><?php echo $asistenciaBoletin['total']['porcentaje'] >= 0 ? number_format((float) $asistenciaBoletin['total']['porcentaje'], 1) . '% asistencia' : 'S/D'; ?></div>
                    </div>
                </div>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Materias Aprobadas</div>
                    <div class="summary-value"><?php echo (int) $estadisticas['materias_aprobadas']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Materias Reprobadas</div>
                    <div class="summary-value"><?php echo (int) $estadisticas['materias_reprobadas']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Materias Pendientes</div>
                    <div class="summary-value"><?php echo (int) $estadisticas['materias_pendientes']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Materias</div>
                    <div class="summary-value"><?php echo (int) $estadisticas['total_materias']; ?></div>
                </div>
            </div>
            <?php if ($estadisticas['promedio_general'] !== null): ?>
            <div class="boletin-promedio-general">
                <div class="boletin-promedio-general__label">PROMEDIO GENERAL</div>
                <div class="boletin-promedio-general__value"><?php echo htmlspecialchars((string) $estadisticas['promedio_general'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Preceptor</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Director</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Sello Institucional</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Fecha</div>
            </div>
        </div>
        
        <div class="boletin-footer">
            <p><strong>E.E.S.T. N°2 "Educación y Trabajo"</strong> - Av. Principal 123, Ciudad - Tel: (123) 456-7890</p>
            <p style="margin: 5px 0 0; font-size: 10px; opacity: 0.85; font-style: italic;">Validez del documento: Este informe de calificaciones impreso vía web es una copia de carácter exclusivamente informativo. No reemplaza ni sustituye al boletín oficial firmado y sellado físicamente por las autoridades de la institución.</p>
            <p style="margin: 5px 0 0;">Este documento fue generado automáticamente por el Sistema Administrativo - <?php echo $fecha_actual; ?></p>
        </div>
    </div>
    
    <script nonce="<?php echo htmlspecialchars($GLOBALS['csp_nonce'] ?? ''); ?>">
        // Imprimir automáticamente cuando se carga la página
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
