<?php 
$es_edicion = isset($usuario) && $usuario !== null; 
$action_title = $es_edicion ? 'Editar' : 'Crear';

// Valores para los campos
$val_id = $es_edicion ? $usuario->id : '';
$val_username = $es_edicion ? $usuario->username : '';
$val_rol = $es_edicion ? $usuario->rol : 'tecnico';
$val_empleado_id = $es_edicion ? ($usuario->empleado_id ?? '') : '';
$val_departamento_id = $es_edicion ? ($usuario->departamento_id ?? '') : '';

// Roles permitidos
$roles_permitidos = $allowedRoles ?? ['admin', 'tecnico', 'consultor'];

// Obtener nombre del empleado si está vinculado
$empleado_nombre = '';
$empleado_apellido = '';
$empleado_email = '';
if ($es_edicion && !empty($val_empleado_id) && !empty($empleados)) {
    foreach ($empleados as $emp) {
        if ($emp->id == $val_empleado_id) {
            $empleado_nombre = $emp->nombre;
            $empleado_apellido = $emp->apellido;
            $empleado_email = $emp->email ?? '';
            break;
        }
    }
}

// Obtener nombre del departamento si está asignado
$departamento_nombre = '';
if ($es_edicion && !empty($val_departamento_id) && !empty($departamentos)) {
    foreach ($departamentos as $dep) {
        if ($dep->id == $val_departamento_id) {
            $departamento_nombre = $dep->nombre;
            break;
        }
    }
}
?>

<div class="users-form-wrapper">
    
    <div class="users-form-card">
        
        <!-- COLUMNA IZQUIERDA: FORMULARIO -->
        <div class="users-form-col-left">
            
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <div style="padding: 0.5rem; background: #e0e7ff; border-radius: 0.5rem; color: #6366f1;">
                        <i class="bi bi-person-plus-fill" style="font-size: 1.25rem;"></i>
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;"><?= $action_title ?> Usuario</h2>
                </div>
                <p style="color: #64748b; margin: 0; font-size: 0.875rem;">Ingresa los datos para generar la credencial digital.</p>
            </div>

            <form action="<?= BASE_URL ?>usuarios/guardar" method="POST" autocomplete="off" id="userForm">
                
                <?php if ($es_edicion): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($val_id) ?>">
                <?php endif; ?>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- Username -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Nombre de Usuario</label>
                        <input type="text" id="username" name="username" 
                               style="width: 100%; padding: 0.75rem 1rem; background: #f8fafc; border: none; border-bottom: 2px solid #e2e8f0; font-weight: 500; font-size: 1rem; color: #1e293b; outline: none; transition: border-color 0.2s;"
                               onfocus="this.style.borderBottomColor='#6366f1'" 
                               onblur="this.style.borderBottomColor='#e2e8f0'"
                               placeholder="Ej: jperez" required 
                               value="<?= htmlspecialchars($val_username) ?>" 
                               autocomplete="off"
                               oninput="updatePreview()">
                    </div>

                    <!-- Password -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                            Contraseña <?= $es_edicion ? '(Dejar vacío para mantener)' : '' ?>
                        </label>
                        <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden;">
                            <div style="padding-left: 1rem; color: #94a3b8;">
                                <i class="bi bi-key"></i>
                            </div>
                            <input type="password" id="password" name="password" 
                                   style="flex: 1; padding: 0.75rem 1rem; background: transparent; border: none; font-weight: 500; font-size: 1rem; color: #1e293b; outline: none;"
                                   placeholder="<?= $es_edicion ? '••••••••' : 'Contraseña segura' ?>" 
                                   <?= !$es_edicion ? 'required' : '' ?> 
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <!-- Rol (Solo) -->
                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                        <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Nivel de Acceso (Rol)</label>
                        <select id="rol" name="rol" required
                                style="width: 100%; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-weight: 500; font-size: 1rem; color: #475569; outline: none; cursor: pointer;"
                                onchange="updatePreview()">
                            <?php foreach ($roles_permitidos as $rol): ?>
                                <option value="<?= $rol ?>" <?= ($val_rol === $rol) ? 'selected' : '' ?>>
                                    <?= ucfirst($rol) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Advanced Options Toggle -->
                    <div style="padding: 1rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" id="showAdvanced" onchange="toggleAdvancedOptions()" 
                                   style="width: 1.125rem; height: 1.125rem; accent-color: #6366f1; cursor: pointer;"
                                   <?= ($val_empleado_id || $val_departamento_id) ? 'checked' : '' ?>>
                            <div>
                                <p style="font-size: 0.875rem; font-weight: 600; color: #0f172a; margin: 0;">Vincular a la organización</p>
                                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Asignar departamento y vincular a un empleado existente.</p>
                            </div>
                        </label>
                    </div>

                    <!-- Advanced Options (Hidden by default) -->
                    <div id="advancedOptions" style="display: <?= ($val_empleado_id || $val_departamento_id) ? 'flex' : 'none' ?>; flex-direction: column; gap: 1rem; padding: 1rem; background: #fafafa; border-radius: 0.75rem; border: 1px dashed #e2e8f0;">
                        
                        <!-- Departamento -->
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                                <i class="bi bi-building" style="margin-right: 0.25rem;"></i> Departamento
                            </label>
                            <select id="departamento_id" name="departamento_id"
                                    style="width: 100%; padding: 0.75rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-weight: 500; font-size: 1rem; color: #475569; outline: none; cursor: pointer;"
                                    onchange="updatePreview()">
                                <option value="">-- Sin asignar --</option>
                                <?php if (!empty($departamentos)): ?>
                                    <?php foreach ($departamentos as $dep): ?>
                                        <option value="<?= $dep->id ?>" <?= ($val_departamento_id == $dep->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dep->nombre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Empleado Vinculado -->
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                                <i class="bi bi-person-badge" style="margin-right: 0.25rem;"></i> Vincular a Empleado
                            </label>
                            <select id="empleado_id" name="empleado_id"
                                    style="width: 100%; padding: 0.75rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-weight: 500; font-size: 1rem; color: #475569; outline: none; cursor: pointer;"
                                    onchange="updatePreview()">
                                <option value="">-- Sin vincular --</option>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $emp): ?>
                                        <option value="<?= $emp->id ?>" 
                                                data-nombre="<?= htmlspecialchars($emp->nombre) ?>"
                                                data-apellido="<?= htmlspecialchars($emp->apellido) ?>"
                                                data-email="<?= htmlspecialchars($emp->email ?? '') ?>"
                                                <?= ($val_empleado_id == $emp->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($emp->nombre . ' ' . $emp->apellido) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div style="padding: 1rem; background: #eef2ff; border-radius: 0.75rem; border: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; font-weight: 700; color: #4338ca; margin: 0;">Credencial Digital</p>
                            <p style="font-size: 0.75rem; color: #6366f1; margin: 0;">La vista previa se actualiza en tiempo real.</p>
                        </div>
                        <div style="padding: 0.5rem; background: white; color: #6366f1; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #c7d2fe;">
                            <i class="bi bi-qr-code" style="font-size: 1.125rem;"></i>
                        </div>
                    </div>

                </div>
                
                <!-- Footer Actions -->
                <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 1rem;">
                    <button type="submit" style="flex: 1; padding: 0.875rem 1.5rem; background: #0f172a; color: white; font-weight: 700; border-radius: 0.75rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                        <i class="bi bi-check-lg"></i>
                        <?= $action_title ?> Usuario
                    </button>
                    <a href="<?= BASE_URL ?>usuarios" style="padding: 0.875rem 1.5rem; border: 1px solid #e2e8f0; color: #64748b; font-weight: 700; border-radius: 0.75rem; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>

        <!-- COLUMNA DERECHA: VISTA PREVIA -->
        <div class="users-form-col-right">
            
            <div style="position: absolute; top: 1.5rem; right: 1.5rem; padding: 0.375rem 0.75rem; background: white; border: 1px solid #e2e8f0; border-radius: 9999px; font-size: 0.625rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                Vista Previa
            </div>

            <!-- THE ID CARD -->
            <div id="previewCard" style="width: 100%; max-width: 320px; background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; transition: all 0.3s;">
                
                <!-- Header Color (Dynamic) -->
                <div id="cardHeader" style="height: 6rem; background: #3b82f6; position: relative;">
                    <div style="position: absolute; bottom: -2.5rem; left: 50%; transform: translateX(-50%); padding: 0.25rem; background: white; border-radius: 50%;">
                        <div id="avatarCircle" style="width: 5rem; height: 5rem; background: #f1f5f9; border-radius: 50%; border: 2px solid white; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; color: #94a3b8; overflow: hidden;">
                            <span id="avatarInitials"><i class="bi bi-camera" style="font-size: 1.5rem;"></i></span>
                        </div>
                    </div>
                </div>

                <div style="padding-top: 3rem; padding-bottom: 2rem; padding-left: 1.5rem; padding-right: 1.5rem; text-align: center;">
                    <h3 id="previewName" style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        Nombre Usuario
                    </h3>
                    <p id="previewEmail" style="color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 1rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        @username
                    </p>

                    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                        <span id="previewRole" style="padding: 0.25rem 0.75rem; background: #f1f5f9; color: #475569; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #e2e8f0;">
                            Técnico
                        </span>
                        <span id="previewDept" style="padding: 0.25rem 0.75rem; background: #3b82f6; color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                            Sin Depto
                        </span>
                    </div>

                    <!-- Data Grid -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left;">
                        <div>
                            <p style="font-size: 0.625rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin: 0;">Estado</p>
                            <div style="display: flex; align-items: center; gap: 0.375rem; margin-top: 0.25rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 0.75rem;"></i>
                                <span style="font-size: 0.75rem; font-weight: 500; color: #475569;">Activo</span>
                            </div>
                        </div>
                        <div>
                            <p style="font-size: 0.625rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin: 0;">ID Usuario</p>
                            <p id="previewId" style="font-size: 0.75rem; font-weight: 500; color: #475569; margin: 0.25rem 0 0 0;">
                                #USR-<?= $es_edicion ? str_pad($val_id, 4, '0', STR_PAD_LEFT) : 'NUEVO' ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code Area -->
                <div style="background: #f8fafc; padding: 1rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #94a3b8;">
                        <i class="bi bi-shield-check"></i>
                        <span>Acceso Verificado</span>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; color: #0f172a;">
                        <i class="bi bi-qr-code" style="font-size: 1.25rem;"></i>
                    </div>
                </div>
            </div>

            <p style="text-align: center; color: #94a3b8; font-size: 0.75rem; margin-top: 2rem; max-width: 200px;">
                Esta es la visualización de cómo aparecerá el usuario en el directorio global.
            </p>

        </div>

    </div>
</div>

<script>
// Colores por departamento
const deptColors = {
    '': '#64748b',
    <?php if (!empty($departamentos)): ?>
        <?php foreach ($departamentos as $i => $dep): ?>
            '<?= $dep->id ?>': '<?= ['#3b82f6', '#10b981', '#8b5cf6', '#f43f5e', '#f59e0b', '#06b6d4'][$i % 6] ?>',
        <?php endforeach; ?>
    <?php endif; ?>
};

// Colores por rol
const roleColors = {
    'admin': '#8b5cf6',
    'tecnico': '#3b82f6',
    'consultor': '#f59e0b'
};

function toggleAdvancedOptions() {
    const checkbox = document.getElementById('showAdvanced');
    const advancedOptions = document.getElementById('advancedOptions');
    
    if (checkbox.checked) {
        advancedOptions.style.display = 'flex';
    } else {
        advancedOptions.style.display = 'none';
        // Reset values when hiding
        document.getElementById('departamento_id').value = '';
        document.getElementById('empleado_id').value = '';
        updatePreview();
    }
}

function updatePreview() {
    const username = document.getElementById('username').value || 'username';
    const rol = document.getElementById('rol').value;
    const deptSelect = document.getElementById('departamento_id');
    const deptId = deptSelect ? deptSelect.value : '';
    const deptName = deptSelect && deptSelect.options[deptSelect.selectedIndex] ? deptSelect.options[deptSelect.selectedIndex].text : 'Sin Depto';
    
    const empSelect = document.getElementById('empleado_id');
    const selectedEmp = empSelect ? empSelect.options[empSelect.selectedIndex] : null;
    
    let nombre = '';
    let apellido = '';
    let email = '';
    
    if (selectedEmp && selectedEmp.value) {
        nombre = selectedEmp.getAttribute('data-nombre') || '';
        apellido = selectedEmp.getAttribute('data-apellido') || '';
        email = selectedEmp.getAttribute('data-email') || '';
    }
    
    const displayName = (nombre && apellido) ? `${nombre} ${apellido}` : username;
    const initials = nombre && apellido 
        ? (nombre.charAt(0) + apellido.charAt(0)).toUpperCase()
        : username.substring(0, 2).toUpperCase();
    
    // Update card
    document.getElementById('previewName').textContent = displayName;
    document.getElementById('previewEmail').textContent = email || `@${username}`;
    document.getElementById('previewRole').textContent = rol.charAt(0).toUpperCase() + rol.slice(1);
    document.getElementById('previewDept').textContent = deptId ? deptName.replace('-- ', '').replace(' --', '') : 'Sin Depto';
    
    // Update avatar
    if (initials) {
        document.getElementById('avatarInitials').innerHTML = initials;
    } else {
        document.getElementById('avatarInitials').innerHTML = '<i class="bi bi-camera" style="font-size: 1.5rem;"></i>';
    }
    
    // Update colors
    const headerColor = deptColors[deptId] || '#3b82f6';
    document.getElementById('cardHeader').style.background = headerColor;
    document.getElementById('previewDept').style.background = headerColor;
    
    const roleColor = roleColors[rol] || '#3b82f6';
    document.getElementById('previewRole').style.background = roleColor + '20';
    document.getElementById('previewRole').style.color = roleColor;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updatePreview);
</script>