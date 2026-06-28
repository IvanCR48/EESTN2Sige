<?php
/** Variables: $csrfToken, $llamados_amonestacion_verbal, $cursos_disponibles, $puede_cambiar_curso_ficha (opcional) */
?>
<!-- Modal de confirmación para eliminar responsable -->
<div id="modalEliminarResponsable" class="modal modal--ficha">
    <div class="modal-content modal-content--narrow">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            <span class="close" role="button" tabindex="0" data-csp-hide-modal="modalEliminarResponsable">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar al responsable <strong id="nombreResponsable"></strong>?</p>
            <p class="modal-warn"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            <form method="POST" class="modal-form-actions">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="responsable_id" id="responsableId">
                <div class="modal-form-actions__buttons">
                    <button type="button" class="btn btn-secondary" data-csp-hide-modal="modalEliminarResponsable">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="eliminar_responsable" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalEliminarContacto" class="modal modal--ficha">
    <div class="modal-content modal-content--narrow">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            <span class="close" role="button" tabindex="0" data-csp-hide-modal="modalEliminarContacto">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar el contacto de emergencia <strong id="nombreContacto"></strong>?</p>
            <p class="modal-warn"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            <form method="POST" class="modal-form-actions">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="contacto_id" id="contactoId">
                <div class="modal-form-actions__buttons">
                    <button type="button" class="btn btn-secondary" data-csp-hide-modal="modalEliminarContacto">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="eliminar_contacto" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($puede_cambiar_curso_ficha)): ?>
<div id="modalCambioCurso" class="modal modal--ficha">
    <div class="modal-content modal-content--curso">
        <div class="modal-header">
            <h3><i class="fas fa-exchange-alt"></i> Cambio de Curso</h3>
            <span class="close" role="button" tabindex="0" data-csp-hide-modal="modalCambioCurso">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-curso-info">
                <h4><i class="fas fa-exclamation-triangle"></i> Información Importante</h4>
                <ul>
                    <li>El estudiante tiene <strong><?php echo (int) $llamados_amonestacion_verbal; ?> amonestaciones verbales</strong></li>
                    <li>Se <strong>mantendrán todas las notas</strong> del curso actual</li>
                    <li>Se eliminarán las materias previas del curso actual</li>
                    <li>Se registrará un llamado de atención por el cambio de curso</li>
                    <li>El estudiante conservará su progreso académico</li>
                </ul>
            </div>
            <form method="POST" class="modal-form-actions">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="nuevo_curso_id">Nuevo Curso:</label>
                    <select name="nuevo_curso_id" id="nuevo_curso_id" required class="form-control">
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos_disponibles as $curso): ?>
                        <option value="<?php echo (int) $curso['id']; ?>">
                            <?php echo htmlspecialchars((string) $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad'] . ' (' . $curso['turno'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-form-actions__buttons">
                    <button type="button" class="btn btn-secondary" data-csp-hide-modal="modalCambioCurso">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="cambiar_curso" class="btn btn-danger" data-confirm-message="<?php echo htmlspecialchars('¿Estás seguro de que deseas cambiar al estudiante de curso? Se mantendrán las notas pero se eliminarán las materias previas del curso actual.', ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-exchange-alt"></i> Confirmar Cambio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
