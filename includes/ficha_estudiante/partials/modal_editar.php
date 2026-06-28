<?php
/** Variables: $csrfToken, $estudiante, $turnos_lista */
?>
<div id="editModal" class="modal modal--ficha modal--edit">
    <div class="modal-content modal-content--edit">
        <div class="modal-header">
            <h3>Editar información del estudiante</h3>
            <span class="close" role="button" tabindex="0" data-csp-hide-modal="editModal" data-csp-modal-lock-body="1">&times;</span>
        </div>
        <div class="modal-body">
            <div class="ficha-edit-tabs" role="tablist" aria-label="Secciones de edición">
                <button type="button" class="ficha-edit-tabs__btn is-active" role="tab" aria-selected="true" aria-controls="ficha-panel-contacto" id="ficha-tab-btn-contacto" data-ficha-tab="contacto">Contacto y turno</button>
                <button type="button" class="ficha-edit-tabs__btn" role="tab" aria-selected="false" aria-controls="ficha-panel-responsable" id="ficha-tab-btn-responsable" data-ficha-tab="responsable">Responsable</button>
                <button type="button" class="ficha-edit-tabs__btn" role="tab" aria-selected="false" aria-controls="ficha-panel-emergencia" id="ficha-tab-btn-emergencia" data-ficha-tab="emergencia">Emergencia</button>
            </div>

            <div class="ficha-edit-panels">
                <div class="ficha-edit-panel is-active" id="ficha-panel-contacto" role="tabpanel" aria-labelledby="ficha-tab-btn-contacto">
                    <form method="POST" class="form-container" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <h4>Información de Contacto</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="domicilio">Domicilio:</label>
                                <textarea name="domicilio" id="domicilio" placeholder="Dirección completa"><?php echo htmlspecialchars((string) ($estudiante['domicilio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="telefono_fijo">Teléfono Fijo:</label>
                                <input type="tel" name="telefono_fijo" id="telefono_fijo" value="<?php echo htmlspecialchars((string) ($estudiante['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="20">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_celular">Teléfono Celular:</label>
                                <input type="tel" name="telefono_celular" id="telefono_celular" value="<?php echo htmlspecialchars((string) ($estudiante['telefono_celular'] ?? ($estudiante['telefono'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars((string) ($estudiante['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="100">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="foto">Foto de perfil (JPG/PNG, máx. 5MB):</label>
                                <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/gif">
                                <?php if (!empty($estudiante['foto'])): ?>
                                    <div style="margin-top: 5px; display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="eliminar_foto" id="eliminar_foto" value="1">
                                        <label for="eliminar_foto" style="display: inline; font-size: 0.9em; color: #dc2626; font-weight: normal; cursor: pointer;">Eliminar foto actual</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="grupo_sanguineo">Grupo Sanguíneo:</label>
                                <?php $grupoSanguineoActual = (string) ($estudiante['grupo_sanguineo'] ?? ''); ?>
                                <select name="grupo_sanguineo" id="grupo_sanguineo">
                                    <option value="">Seleccionar</option>
                                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $opt): ?>
                                    <option value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $grupoSanguineoActual === $opt ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="obra_social">Obra Social:</label>
                                <input type="text" name="obra_social" id="obra_social" value="<?php echo htmlspecialchars((string) ($estudiante['obra_social'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="100">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dni_responsable_portal">DNI del responsable (portal familias):</label>
                                <input type="text" name="dni_responsable_portal" id="dni_responsable_portal" maxlength="20"
                                       pattern="[0-9]{7,8}"
                                       placeholder="7 u 8 dígitos — vaciar para desactivar el acceso"
                                       value="<?php echo htmlspecialchars((string) ($estudiante['dni_responsable'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <small class="ficha-help">Con este DNI el familiar ingresa al portal. Dejá vacío solo si querés quitar el acceso.</small>
                            </div>
                            <div class="form-group">
                                <label for="grupo_taller">Grupo Taller:</label>
                                <?php $grupoTallerActual = (string) ($estudiante['grupo_taller'] ?? ''); ?>
                                <select name="grupo_taller" id="grupo_taller">
                                    <option value="">Sin grupo / Asignar después</option>
                                    <?php foreach (['A', 'B', 'C', 'D', 'E'] as $g): ?>
                                    <option value="<?php echo $g; ?>" <?php echo $grupoTallerActual === $g ? 'selected' : ''; ?>>
                                        Grupo <?php echo $g; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="ficha-help">Grupo de taller (A-E) asignado a este estudiante.</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                                       value="<?php echo htmlspecialchars((string) ($estudiante['fecha_nacimiento'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="fecha_ingreso">Fecha de Ingreso:</label>
                                <input type="date" name="fecha_ingreso" id="fecha_ingreso" 
                                       value="<?php echo htmlspecialchars((string) ($estudiante['fecha_ingreso'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <?php if (!empty($estudiante['anio']) && !empty($estudiante['division'])): ?>
                        <h4>Curso y Turno</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Curso actual:</label>
                                <div class="ficha-muted">
                                    <?php echo (int) $estudiante['anio'] . '° ' . htmlspecialchars((string) $estudiante['division'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (!empty($estudiante['especialidad'])): ?> — <?php echo htmlspecialchars((string) $estudiante['especialidad'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nuevo_turno_id">Turno:</label>
                                <select name="nuevo_turno_id" id="nuevo_turno_id">
                                    <option value="">Mantener turno actual (<?php echo htmlspecialchars((string) ($estudiante['turno'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>)</option>
                                    <?php foreach ($turnos_lista as $t): ?>
                                    <option value="<?php echo (int) $t['id']; ?>" <?php echo (!empty($estudiante['turno_id']) && (int) $estudiante['turno_id'] === (int) $t['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string) $t['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="ficha-help">Si eliges otro turno, se reasignará al curso del mismo año y división con ese turno (si existe).</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-actions">
                            <button type="submit" name="actualizar_estudiante" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                            <button type="button" class="btn btn-secondary" data-csp-hide-modal="editModal" data-csp-modal-lock-body="1">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="ficha-edit-panel" id="ficha-panel-responsable" role="tabpanel" aria-labelledby="ficha-tab-btn-responsable">
                    <form method="POST" class="form-container">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <h4>Agregar responsable</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_resp">Nombre:</label>
                                <input type="text" name="nombre" id="nombre_resp" required maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="apellido_resp">Apellido:</label>
                                <input type="text" name="apellido" id="apellido_resp" required maxlength="50">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dni_resp">DNI:</label>
                                <input type="text" name="dni" id="dni_resp" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="telefono_resp">Teléfono:</label>
                                <input type="tel" name="telefono" id="telefono_resp" required maxlength="20">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_resp">Email:</label>
                                <input type="email" name="email" id="email_resp" maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="parentesco_resp">Parentesco:</label>
                                <select name="parentesco" id="parentesco_resp" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Tutor">Tutor</option>
                                    <option value="Abuelo/a">Abuelo/a</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Tío/a">Tío/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="es_contacto_emergencia" value="1">
                                    Es contacto de emergencia
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="guardar_responsable" class="btn btn-success">
                                <i class="fas fa-plus"></i> Agregar responsable
                            </button>
                        </div>
                    </form>
                </div>

                <div class="ficha-edit-panel" id="ficha-panel-emergencia" role="tabpanel" aria-labelledby="ficha-tab-btn-emergencia">
                    <form method="POST" class="form-container">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <h4>Agregar contacto de emergencia</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_contacto">Nombre:</label>
                                <input type="text" name="nombre" id="nombre_contacto" required maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="telefono_contacto">Teléfono:</label>
                                <input type="tel" name="telefono" id="telefono_contacto" required maxlength="20">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parentesco_contacto">Parentesco:</label>
                                <select name="parentesco" id="parentesco_contacto" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Tutor">Tutor</option>
                                    <option value="Abuelo/a">Abuelo/a</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Tío/a">Tío/a</option>
                                    <option value="Vecino/a">Vecino/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="guardar_contacto" class="btn btn-danger">
                                <i class="fas fa-plus"></i> Agregar contacto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
