<?php 
$es_edicion = isset($usuario) && $usuario !== null; 
$action_title = $es_edicion ? 'Editar' : 'Crear';

// Valores para los campos
$val_id = $es_edicion ? $usuario->id : '';
$val_username = $es_edicion ? $usuario->username : '';
$val_rol = $es_edicion ? $usuario->rol : '';
$val_empleado_id = $es_edicion ? ($usuario->empleado_id ?? '') : '';
$val_departamento_id = $es_edicion ? ($usuario->departamento_id ?? '') : '';

// Roles permitidos
$roles_permitidos = $allowedRoles ?? ['admin', 'tecnico', 'consultor'];
?>

<div class="row">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $action_title ?> Usuario</h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="<?= BASE_URL ?>usuarios/guardar" method="POST" autocomplete="off">
                    
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($val_id) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Username -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de Usuario" required value="<?= htmlspecialchars($val_username) ?>" autocomplete="off">
                                <label for="username">Nombre de Usuario *</label>
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="" disabled <?= !$es_edicion ? 'selected' : '' ?>>Seleccionar Rol</option>
                                    <?php foreach ($roles_permitidos as $rol): ?>
                                        <option value="<?= $rol ?>" <?= ($val_rol === $rol) ? 'selected' : '' ?>>
                                            <?= ucfirst($rol) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="rol">Rol *</label>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" <?= !$es_edicion ? 'required' : '' ?> autocomplete="new-password">
                                <label for="password">Contraseña <?= $es_edicion ? '(Dejar en blanco para mantener)' : '*' ?></label>
                            </div>
                            <?php if ($es_edicion): ?>
                                <small class="text-muted">Dejar en blanco para no cambiar la contraseña actual.</small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-muted mb-3">Asignación (Opcional pero recomendada para Técnicos/Consultores)</h6>
                        </div>

                        <!-- Empleado Vinculado -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="empleado_id" name="empleado_id">
                                    <option value="">-- Sin vincular --</option>
                                    <?php if (!empty($empleados)): ?>
                                        <?php foreach ($empleados as $emp): ?>
                                            <option value="<?= $emp->id ?>" <?= ($val_empleado_id == $emp->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($emp->nombre . ' ' . $emp->apellido) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="empleado_id">Vincular a Empleado</label>
                                <small class="text-muted">Si se selecciona, hereda el departamento del empleado.</small>
                            </div>
                        </div>

                        <!-- Departamento Directo -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="departamento_id" name="departamento_id">
                                    <option value="">-- Sin asignar --</option>
                                    <?php if (!empty($departamentos)): ?>
                                        <?php foreach ($departamentos as $dep): ?>
                                            <option value="<?= $dep->id ?>" <?= ($val_departamento_id == $dep->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dep->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="departamento_id">Asignar Departamento Directamente</label>
                                <small class="text-muted">Tiene prioridad sobre el departamento del empleado.</small>
                            </div>
                        </div>

                    </div> 

                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>usuarios" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Regresar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            <?= $action_title ?> Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>