<?php 
$es_edicion = isset($empleado) && $empleado !== null; 
$action_title = $es_edicion ? 'Editar' : 'Registrar';

// Valores para los campos
$val_id = $es_edicion ? $empleado->id : '';
$val_nombre = $es_edicion ? $empleado->nombre : '';
$val_apellido = $es_edicion ? $empleado->apellido : '';
$val_cedula = $es_edicion ? $empleado->cedula : '';
$val_cargo = $es_edicion ? ($empleado->cargo ?? '') : '';
$val_email = $es_edicion ? $empleado->email : '';
$val_departamento_id = $es_edicion && isset($empleado->departamento_id) ? $empleado->departamento_id : '';
$current_user_id = $es_edicion ? $empleado->usuario_id : '';

// Obtener nombre del departamento seleccionado
$dept_nombre = '---';
if ($val_departamento_id && isset($departamentos)) {
    foreach ($departamentos as $d) {
        if ($d->id == $val_departamento_id) {
            $dept_nombre = $d->nombre;
            break;
        }
    }
}
?>

<div class="empleados-container">
    <!-- Using standardized header container -->
    <div class="empleados-header">
        <div class="empleados-header-container" style="max-width: 1000px;">
            <div class="d-flex align-items-center gap-2">
                <div class="empleados-logo">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h1 class="empleados-title"><?= $es_edicion ? 'Editar' : 'Nuevo' ?> Empleado</h1>
                    <p class="empleados-subtitle mb-0">Registra el talento humano de tu organización.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>empleados" class="emp-back-btn">
                <i class="bi bi-arrow-left"></i> Volver al Directorio
            </a>
        </div>
    </div>

    <main class="empleados-main-container" style="max-width: 1000px;">
        <div class="empleados-content-card" style="display: flex; flex-wrap: wrap; gap: 0; padding: 0; overflow: hidden;">
            
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="emp-form-card">
                
                <form action="<?= BASE_URL ?>empleados/guardar" method="POST" id="formEmpleado">
                    
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($val_id) ?>">
                    <?php endif; ?>

                    <!-- Sección: Identidad -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Nombre</label>
                            <div style="position: relative;">
                                <i class="bi bi-person" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 1.125rem;"></i>
                                <input type="text" name="nombre" id="nombre" required placeholder="Ej. Ana" value="<?= htmlspecialchars($val_nombre) ?>"
                                       oninput="updatePreview()"
                                       style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #1e293b; outline: none;">
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Apellido</label>
                            <div style="position: relative;">
                                <i class="bi bi-person" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 1.125rem;"></i>
                                <input type="text" name="apellido" id="apellido" required placeholder="Ej. García" value="<?= htmlspecialchars($val_apellido) ?>"
                                       oninput="updatePreview()"
                                       style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #1e293b; outline: none;">
                            </div>
                        </div>
                    </div>

                    <!-- Cédula (full width) -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 1.25rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Documento de Identidad (Cédula)</label>
                        <div style="position: relative;">
                            <i class="bi bi-credit-card-2-front" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 1.125rem;"></i>
                            <input type="text" name="cedula" id="cedula" required placeholder="V-12.345.678" value="<?= htmlspecialchars($val_cedula) ?>"
                                   oninput="updatePreview()"
                                   style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; font-family: monospace; color: #475569; outline: none;">
                        </div>
                    </div>

                    <!-- Cargo (full width) -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 1.25rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Cargo o Puesto</label>
                        <div style="position: relative;">
                            <i class="bi bi-briefcase" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 1.125rem;"></i>
                            <input type="text" name="cargo" id="cargo" placeholder="Ej. Gerente de Ventas" value="<?= htmlspecialchars($val_cargo) ?>"
                                   oninput="updatePreview()"
                                   style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #475569; outline: none;">
                        </div>
                    </div>

                    <!-- Divider -->
                    <div style="height: 1px; background: #f1f5f9; margin: 1.5rem 0;"></div>

                    <!-- Sección: Contacto -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 1rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Correo Electrónico</label>
                        <div style="position: relative;">
                            <i class="bi bi-envelope" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 1.125rem;"></i>
                            <input type="email" name="email" id="email" required placeholder="empleado@empresa.com" value="<?= htmlspecialchars($val_email) ?>"
                                   oninput="updatePreview()"
                                   style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #1e293b; outline: none;">
                        </div>
                    </div>

                    <!-- Checkbox: Permitir correo compartido -->
                    <!-- Checkbox: Permitir correo compartido -->
                    <div onclick="toggleSharedEmail()" class="emp-shared-email-box">
                        <div id="checkboxContainer" style="margin-top: 0.125rem; width: 1.25rem; height: 1.25rem; border-radius: 0.25rem; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i class="bi bi-check" id="checkIcon" style="display: none; color: white; font-size: 0.875rem;"></i>
                        </div>
                        <input type="hidden" name="permitir_email_compartido" id="permitir_email_compartido" value="0">
                        <div style="flex: 1;">
                            <p style="font-size: 0.875rem; font-weight: 600; color: #475569; margin: 0; user-select: none;">Permitir correo compartido</p>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 0; user-select: none;">Habilita esta opción si múltiples empleados usarán esta misma dirección.</p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div style="height: 1px; background: #f1f5f9; margin: 1.5rem 0;"></div>

                    <!-- Sección: Vinculación -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem;">
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem;">Departamento</label>
                            <div style="position: relative;">
                                <i class="bi bi-building" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.125rem;"></i>
                                <select name="departamento_id" id="departamento_id" onchange="updatePreview()"
                                        style="width: 100%; padding: 0.875rem 2rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #475569; outline: none; appearance: none; cursor: pointer;">
                                    <option value="">-- Seleccionar --</option>
                                    <?php if (isset($departamentos) && is_array($departamentos)): ?>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <?php $selected = ($val_departamento_id == $dept->id) ? 'selected' : ''; ?>
                                            <option value="<?= $dept->id ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($dept->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <i class="bi bi-chevron-down" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem; display: flex; align-items: center; gap: 0.375rem;">
                                Cuenta de Usuario
                                <span style="background: #f1f5f9; color: #64748b; font-size: 0.625rem; padding: 0.125rem 0.375rem; border-radius: 9999px; border: 1px solid #e2e8f0;">Opcional</span>
                            </label>
                            <div style="position: relative;">
                                <i class="bi bi-link-45deg" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.125rem;"></i>
                                <select name="usuario_id" id="usuario_id"
                                        style="width: 100%; padding: 0.875rem 2rem 0.875rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 500; color: #475569; outline: none; appearance: none; cursor: pointer;">
                                    <option value="">-- No vincular --</option>
                                    <?php if (isset($usuarios_disponibles) && is_array($usuarios_disponibles)): ?>
                                        <?php foreach ($usuarios_disponibles as $u): ?>
                                            <?php $selected = ($current_user_id == $u->id) ? 'selected' : ''; ?>
                                            <option value="<?= $u->id ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($u->username) ?> (<?= ucfirst($u->rol) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <i class="bi bi-chevron-down" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <a href="<?= BASE_URL ?>empleados" style="padding: 0.875rem 1.5rem; border: 1px solid #e2e8f0; color: #475569; font-weight: 700; border-radius: 0.75rem; text-decoration: none; text-align: center;">
                            Cancelar
                        </a>
                        <button type="submit" style="flex: 1; padding: 0.875rem 1.5rem; background: #3b82f6; color: white; font-weight: 700; border-radius: 0.75rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
                            <i class="bi bi-person-plus-fill"></i>
                            <?= $action_title ?> Empleado
                        </button>
                    </div>
                </form>
            </div>

            <!-- COLUMNA DERECHA: VISTA PREVIA -->
            <div class="emp-preview-col">
                
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                    <p style="color: #64748b; font-size: 0.8rem; margin: 0;">Registra el talento humano de tu organización.</p>
                    <span style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background: white; border: 1px solid #e2e8f0; border-radius: 9999px; font-size: 0.625rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">
                        Vista Previa
                    </span>
                </div>

                <!-- Tarjeta Preview del Empleado -->
                <div style="position: relative; margin: 2rem 0;">
                    <div class="emp-preview-card">
                        
                        <!-- Avatar -->
                        <div id="avatarPreview" class="emp-preview-avatar">
                            <i class="bi bi-person" style="font-size: 2rem;"></i>
                        </div>
                        
                        <!-- Nombre -->
                        <h3 id="previewName" style="color: #0f172a; font-weight: 700; font-size: 1.125rem; margin: 0 0 0.25rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= $val_nombre ? htmlspecialchars($val_nombre . ' ' . $val_apellido) : 'Nombre Apellido' ?>
                        </h3>
                        <p id="previewEmail" style="color: #3b82f6; font-size: 0.875rem; margin: 0 0 0.25rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= $val_email ? htmlspecialchars($val_email) : 'correo@empresa.com' ?>
                        </p>
                        <p id="previewCargo" style="color: #64748b; font-size: 0.75rem; margin: 0 0 1rem 0; font-weight: 500; text-transform: uppercase; letter-spacing: 0.02em;">
                            <?= $val_cargo ? htmlspecialchars($val_cargo) : 'SIN CARGO' ?>
                        </p>
                        
                        <!-- Info Box -->
                        <div class="emp-preview-info-box">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem;">
                                <span>ID:</span>
                                <span id="previewCedula" style="font-family: monospace; color: #0f172a; font-weight: 600;"><?= $val_cedula ?: '---' ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b;">
                                <span>Depto:</span>
                                <span id="previewDept" style="color: #0f172a; font-weight: 500;"><?= $dept_nombre ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Badge -->
                    <div class="emp-preview-badge">
                        <i class="bi bi-patch-check-fill" style="font-size: 0.875rem;"></i>
                    </div>
                </div>

                <!-- Footer info -->
                <div style="text-align: center;">
                    <p style="font-size: 0.75rem; color: #94a3b8; margin: 0;">La vista previa se actualiza en tiempo real.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modular Empleado Form Script -->
<script src="<?= BASE_URL ?>js/empleado-form.js?v=<?= time() ?>"></script>