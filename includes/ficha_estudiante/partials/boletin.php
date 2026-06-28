<?php

use SistemaAdmin\Services\ServicioBoletinNotas;

/**
 * Boletín de notas (tabla o estado vacío). Estructura HTML corregida (un solo .card).
 *
 * Variables: $estudiante, $notas_boletin, $estudiante_id_int
 * Opcional: $puede_cambiar_curso_ficha (bool) — si true, muestra asignar/cambiar curso (staff, no familias ni docentes).
 * Opcional: $intensificaciones_ficha, $school_year_intensif_ficha — calificaciones de intensificación/recuperación (ciclo actual).
 * Opcional: $puede_aprobar_materia_previa_ficha (bool) — staff puede confirmar aprobación de previa sin mesa de examen.
 */
$puedeBoletinCambioCurso = !empty($puede_cambiar_curso_ficha);
$puedeAprobarPreviaStaff = !empty($puede_aprobar_materia_previa_ficha);
$tituloCurso = '';
if (!empty($estudiante['anio']) && !empty($estudiante['division'])) {
    $tituloCurso = (int) $estudiante['anio'] . '° ' . htmlspecialchars((string) $estudiante['division'], ENT_QUOTES, 'UTF-8')
        . ' - ' . htmlspecialchars((string) ($estudiante['especialidad'] ?? ''), ENT_QUOTES, 'UTF-8');
} else {
    $tituloCurso = 'Sin curso asignado';
}
$hayNotas = $notas_boletin !== [];
$mostrarImprimirBoletin = empty($GLOBALS['familia_portal_vista']);
$materiasPreviasFicha = $materias_previas_ficha ?? [];
$intensificacionesFicha = $intensificaciones_ficha ?? [];
$schoolYearIntensifFicha = isset($school_year_intensif_ficha) ? (int) $school_year_intensif_ficha : null;
?>
    <div class="card ficha-boletin-card">
        <div class="card-header ficha-boletin-card__header">
            <div class="ficha-boletin-card__header-inner">
                <h3 class="card-title">📊 Boletín de Notas — <?php echo $tituloCurso; ?></h3>
                <div class="ficha-boletin-card__actions">
                <?php if ($hayNotas && $mostrarImprimirBoletin): ?>
                <button type="button" class="btn btn-success ficha-boletin-card__print" data-csp-open-boletin="<?php echo htmlspecialchars(app_base_path('imprimir_boletin.php?' . http_build_query(['id' => $estudiante_id_int, 'csrf_token' => $csrfToken ?? ''])), ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fas fa-print"></i> Imprimir Boletín
                </button>
                <?php endif; ?>
                <?php if ($puedeBoletinCambioCurso && !empty($estudiante['curso_id'])): ?>
                <button type="button" class="btn btn-warning" data-csp-show-modal="modalCambioCurso">
                    <i class="fas fa-exchange-alt"></i> Cambiar de curso
                </button>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($hayNotas): ?>
        <div class="boletin-estudiante-container">
            <table class="boletin-estudiante-table">
                <thead>
                    <tr>
                        <th class="materia-header">Materia</th>
                        <th class="cuatrimestre-header">Avance 1</th>
                        <th class="cuatrimestre-header">1° Cuatrimestre</th>
                        <th class="cuatrimestre-header">Avance 2</th>
                        <th class="cuatrimestre-header">2° Cuatrimestre</th>
                        <th class="promedio-header">Promedio</th>
                        <th class="estado-header">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas_boletin as $datos): ?>
                    <?php
                    $avance1 = $datos['avances']['avance1'];
                    $avance2 = $datos['avances']['avance2'];
                    $v1 = is_array($avance1) && !empty($avance1['valor']) ? (string) $avance1['valor'] : '';
                    $v2 = is_array($avance2) && !empty($avance2['valor']) ? (string) $avance2['valor'] : '';
                    $registroEstado = $datos['registro_academico_estado'] ?? null;

                    // Las calificaciones ya vienen efectivas desde ServicioEstudiantes
                    // (los cuatrimestres son sobrescritos con effectiveSemester1/2 cuando aplica).
                    $t1Raw = $datos['cuatrimestres'][1] ?? null;
                    $t2Raw = $datos['cuatrimestres'][2] ?? null;
                    $t1 = $t1Raw !== null ? $t1Raw : '-';
                    $t2 = $t2Raw !== null ? $t2Raw : '-';
                    $promOk = !empty($datos['promedio_calculado']);
                    $prom = $datos['promedio'];
                    ?>
                    <tr>
                        <td class="materia-cell">
                            <strong><?php echo htmlspecialchars((string) $datos['materia']['nombre']); ?></strong>
                        </td>
                        <td class="nota-cell">
                            <span class="nota-value"><?php echo $v1 !== '' ? htmlspecialchars($v1) : '-'; ?></span>
                        </td>
                        <td class="nota-cell">
                            <span class="nota-value"><?php echo htmlspecialchars((string) $t1); ?></span>
                        </td>
                        <td class="nota-cell">
                            <span class="nota-value"><?php echo $v2 !== '' ? htmlspecialchars($v2) : '-'; ?></span>
                        </td>
                        <td class="nota-cell">
                            <span class="nota-value"><?php echo htmlspecialchars((string) $t2); ?></span>
                        </td>
                        <td class="promedio-cell">
                            <?php if ($promOk): ?>
                                <span class="promedio-value"><?php echo htmlspecialchars((string) $prom); ?></span>
                            <?php else: ?>
                                <span class="promedio-pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="estado-cell">
                            <?php
                            if ($registroEstado instanceof \SistemaAdmin\DTO\SubjectStatusResult):
                                $st = $registroEstado->status->value;
                                $s1 = $registroEstado->effectiveSemester1;
                                $s2 = $registroEstado->effectiveSemester2;
                                $title = sprintf(
                                    'Sem1: %s | Sem2: %s',
                                    $s1 !== null ? (string) $s1 : '—',
                                    $s2 !== null ? (string) $s2 : '—'
                                );
                                if ($st === 'Passed'): ?>
                                    <span class="estado aprobado" title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">Curso aprobado</span>
                                <?php elseif ($st === 'Intensification'): ?>
                                    <span class="estado pendiente" title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">En intensificación</span>
                                <?php else: ?>
                                    <span class="estado reprobado" title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">Materia previa</span>
                                <?php endif;
                            elseif ($promOk): ?>
                                <?php if ($prom >= 7): ?>
                                    <span class="estado aprobado">Aprobado</span>
                                <?php else: ?>
                                    <span class="estado reprobado">Reprobado</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="estado pendiente">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card-body">
            <div class="ficha-boletin-empty">
                <i class="fas fa-clipboard-list ficha-boletin-empty__icon" aria-hidden="true"></i>
                <h4 class="ficha-boletin-empty__title">No hay notas registradas</h4>
                <p class="ficha-boletin-empty__text">
                    <?php if (empty($estudiante['curso_id'])): ?>
                        <strong>El estudiante no tiene curso asignado.</strong><br>
                        <small>Asigne un curso al estudiante para poder registrar notas.</small>
                    <?php else: ?>
                        <strong>No se han registrado notas para este estudiante.</strong><br>
                        <small>Las notas aparecerán aquí una vez que sean cargadas por los profesores.</small>
                    <?php endif; ?>
                </p>
                <?php if ($puedeBoletinCambioCurso && empty($estudiante['curso_id'])): ?>
                <button type="button" class="btn btn-primary" data-csp-show-modal="modalCambioCurso">
                    <i class="fas fa-graduation-cap"></i> Asignar curso
                </button>
                <?php elseif ($puedeBoletinCambioCurso && !empty($estudiante['curso_id'])): ?>
                <button type="button" class="btn btn-warning" data-csp-show-modal="modalCambioCurso">
                    <i class="fas fa-exchange-alt"></i> Cambiar de curso
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($intensificacionesFicha !== []): ?>
        <div class="ficha-boletin-subsection ficha-intensif-section">
            <div class="ficha-intensif-section__head">
                <h4 class="ficha-intensif-section__title">Intensificaciones y recuperatorios</h4>
                <?php if ($schoolYearIntensifFicha !== null): ?>
                <p class="ficha-intensif-section__meta">Ciclo lectivo <?php echo (int) $schoolYearIntensifFicha; ?>
                    · Se muestra la última calificación por materia, instancia y alcance.</p>
                <?php endif; ?>
            </div>
            <div class="ficha-intensif-table-wrap">
                <table class="ficha-intensif-table">
                    <thead>
                        <tr>
                            <th scope="col">Materia</th>
                            <th scope="col">Instancia</th>
                            <th scope="col">Alcance</th>
                            <th scope="col">Nota</th>
                            <th scope="col">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($intensificacionesFicha as $intRow): ?>
                        <?php
                        $ctx = (string) ($intRow['evaluation_context'] ?? '');
                        $fechaRaw = (string) ($intRow['fecha'] ?? '');
                        $fechaFmt = $fechaRaw;
                        if ($fechaRaw !== '' && preg_match('/^(\d{4}-\d{2}-\d{2})/', $fechaRaw, $m)) {
                            $di = \DateTimeImmutable::createFromFormat('Y-m-d', $m[1]);
                            $fechaFmt = $di ? $di->format('d/m/Y') : $fechaRaw;
                        }
                        ?>
                        <tr>
                            <td class="ficha-intensif-table__mat"><?php echo htmlspecialchars((string) ($intRow['materia_nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(ServicioBoletinNotas::etiquetaContextoEvaluacionHumano($ctx), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(ServicioBoletinNotas::etiquetaAlcanceRecuperacionHumano($intRow['recovery_scope'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="ficha-intensif-table__nota"><?php echo htmlspecialchars((string) ($intRow['calificacion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="ficha-intensif-table__fecha"><?php echo htmlspecialchars($fechaFmt, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($materiasPreviasFicha)): ?>
        <div class="ficha-previas-panel">
            <h4 class="ficha-previas-panel__title">Materias previas</h4>
            <p class="ficha-previas-panel__hint">Registro administrativo. Las calificaciones de diciembre / febrero–marzo del ciclo actual se gestionan en <strong>notas</strong> como intensificaciones; aquí solo se documenta el estado de la previa.</p>
            <ul class="ficha-previas-list">
                <?php foreach ($materiasPreviasFicha as $previa): ?>
                <?php
                $estadoPrevia = strtolower((string) ($previa['estado'] ?? 'pendiente'));
                $estadoLabel = match ($estadoPrevia) {
                    'aprobada' => 'Aprobada',
                    'reprobada' => 'Reprobada',
                    'regularizada' => 'Regularizada',
                    default => 'Pendiente',
                };
                $estadoClase = match ($estadoPrevia) {
                    'aprobada' => 'ficha-previas-pill--ok',
                    'reprobada' => 'ficha-previas-pill--rep',
                    'regularizada' => 'ficha-previas-pill--reg',
                    default => 'ficha-previas-pill--pend',
                };
                $previaId = (int) ($previa['id'] ?? 0);
                $puedeConfirmarAqui = $puedeAprobarPreviaStaff && $previaId > 0
                    && in_array($estadoPrevia, ['pendiente', 'reprobada'], true);
                ?>
                <li class="ficha-previas-item">
                    <div class="ficha-previas-item__row">
                        <span class="ficha-previas-item__materia"><?php echo htmlspecialchars((string) ($previa['materia_nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="ficha-previas-pill <?php echo htmlspecialchars($estadoClase, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($estadoLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <dl class="ficha-previas-item__meta ficha-previas-item__meta--compact">
                        <div><dt>Año de la previa</dt><dd><?php echo (int) ($previa['anio_previo'] ?? 0); ?>°</dd></div>
                        <?php if ($estadoPrevia === 'aprobada' && !empty($previa['anio_aprobacion'])): ?>
                        <div><dt>Alta</dt><dd>Año calendario <?php echo (int) $previa['anio_aprobacion']; ?></dd></div>
                        <?php endif; ?>
                    </dl>
                    <?php if ($puedeConfirmarAqui): ?>
                    <form method="post" class="ficha-previas-approve-form js-confirm-submit" data-confirm-message="¿Confirmar la aprobación de esta materia previa? No se registran mesas de examen en el sistema.">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="previa_id" value="<?php echo $previaId; ?>">
                        <button type="submit" name="aprobar_materia_previa" value="1" class="btn btn-primary btn-sm ficha-previas-approve-btn">
                            Confirmar aprobación
                        </button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Materias Recursadas Panel — Diseño Premium -->
        <div class="ficha-recursadas-panel">
            <div class="ficha-recursadas-panel__header">
                <div class="ficha-recursadas-panel__header-icon">
                    <i class="fas fa-redo-alt"></i>
                </div>
                <div>
                    <h4 class="ficha-recursadas-panel__title">Materias en Cursada Dirigida / Recursadas</h4>
                    <p class="ficha-recursadas-panel__hint">Materias de ciclos anteriores que el estudiante recursa asistiendo a un curso en el año lectivo actual.</p>
                </div>
            </div>

            <?php if (!empty($recursadas_estudiante)): ?>
            <div class="ficha-recursadas-list">
                <?php foreach ($recursadas_estudiante as $recIdx => $rec): ?>
                <?php $recId = (int) $rec['id']; ?>
                <div class="ficha-rec-card" style="animation-delay: <?php echo $recIdx * 0.08; ?>s">
                    <div class="ficha-rec-card__accent"></div>
                    <div class="ficha-rec-card__body">
                        <div class="ficha-rec-card__top">
                            <div class="ficha-rec-card__name-wrap">
                                <i class="fas fa-book-open ficha-rec-card__book-icon"></i>
                                <span class="ficha-rec-card__materia"><?php echo htmlspecialchars((string) $rec['materia_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="ficha-rec-badge">Recursando</span>
                        </div>
                        <div class="ficha-rec-card__meta">
                            <div class="ficha-rec-meta-item">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span><?php echo (int) $rec['anio']; ?>° <?php echo htmlspecialchars((string)$rec['division'], ENT_QUOTES, 'UTF-8'); ?> &mdash; <?php echo htmlspecialchars((string)($rec['especialidad'] ?? 'Ciclo Básico'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="ficha-rec-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Ciclo <?php echo (int) $rec['school_year']; ?></span>
                            </div>
                        </div>
                        <?php if ($puedeBoletinCambioCurso): ?>
                        <form method="post" class="ficha-previas-approve-form js-confirm-submit" data-confirm-message="¿Confirmar la eliminación de esta asignación de recursada? El estudiante dejará de figurar en el listado del curso inferior.">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="recursada_id" value="<?php echo $recId; ?>">
                            <button type="submit" name="eliminar_recursada" value="1" class="ficha-rec-btn-eliminar">
                                <i class="fas fa-times"></i> Quitar asignación
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="ficha-recursadas-empty">
                <div class="ficha-recursadas-empty__icon"><i class="fas fa-inbox"></i></div>
                <p class="ficha-recursadas-empty__text">Sin materias recursadas en el ciclo lectivo actual</p>
            </div>
            <?php endif; ?>

            <?php if ($puedeBoletinCambioCurso): ?>
            <div class="ficha-recursadas-form-wrap">
                <div class="ficha-recursadas-form-wrap__label">
                    <i class="fas fa-plus-circle"></i>
                    <span>Asignar nueva materia a recursar</span>
                </div>
                <form method="post" class="ficha-recursadas-form form-recursada-add">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="recursada_school_year" value="<?php echo (int) $schoolYearIntensifFicha; ?>">
                    <div class="ficha-recursadas-form__fields">
                        <div class="ficha-recursadas-form__group">
                            <label class="ficha-recursadas-form__field-label">Curso donde cursará</label>
                            <select name="recursada_curso_id" required class="ficha-recursadas-form__select js-recursada-curso-select" data-curso-materias="<?php echo htmlspecialchars(json_encode($curso_materias_map ?? []), ENT_QUOTES, 'UTF-8'); ?>">
                                <option value="">Seleccionar curso…</option>
                                <?php foreach ($cursos_disponibles as $cur): ?>
                                <option value="<?php echo (int)$cur['id']; ?>">
                                    <?php echo (int)$cur['anio']; ?>° <?php echo htmlspecialchars((string)$cur['division'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars((string)($cur['especialidad_nombre'] ?? $cur['especialidad'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ficha-recursadas-form__group">
                            <label class="ficha-recursadas-form__field-label">Materia a recursar</label>
                            <select name="recursada_materia_id" required class="ficha-recursadas-form__select js-recursada-materia-select" disabled>
                                <option value="">Seleccioná un curso primero…</option>
                            </select>
                        </div>
                        <div class="ficha-recursadas-form__group ficha-recursadas-form__group--btn">
                            <button type="submit" name="guardar_recursada" value="1" class="ficha-recursadas-form__submit">
                                <i class="fas fa-check"></i> Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
