<?php 
$es_edicion = isset($empleado) && $empleado !== null; 
$action_title = $es_edicion ? 'Editar' : 'Registrar';

// Valores para los campos
$val_id = $es_edicion ? $empleado->id : '';
$val_nombre = $es_edicion ? $empleado->nombre : '';
$val_apellido = $es_edicion ? $empleado->apellido : '';
$val_cedula = $es_edicion ? $empleado->cedula : '';
$val_email = $es_edicion ? $empleado->email : '';
$current_user_id = $es_edicion ? $empleado->usuario_id : '';
?>

<div class="row">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $action_title ?> Empleado</h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="<?= BASE_URL ?>empleados/guardar" method="POST">
                    
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($val_id) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($val_nombre) ?>">
                                <label for="nombre">Nombre</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" required value="<?= htmlspecialchars($val_apellido) ?>">
                                <label for="apellido">Apellido</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula" required value="<?= htmlspecialchars($val_cedula) ?>">
                                <label for="cedula">Cédula (V-12345678)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($val_email) ?>">
                                <label for="email">Email</label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check mt-2 ms-2">
                                <input class="form-check-input" type="checkbox" id="permitir_email_compartido" name="permitir_email_compartido" value="1">
                                <label class="form-check-label text-muted" for="permitir_email_compartido">
                                    <small>
                                        <i class="bi bi-share-fill me-1"></i>
                                        Permitir correo compartido (múltiples empleados pueden usar el mismo email)
                                    </small>
                                </label>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="departamento_id" name="departamento_id">
                                    <option value="">-- SIN DEPARTAMENTO --</option>
                                    <?php if (isset($departamentos) && is_array($departamentos)): ?>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <?php $selected = ($es_edicion && isset($empleado->departamento_id) && $empleado->departamento_id == $dept->id) ? 'selected' : ''; ?>
                                            <option value="<?= $dept->id ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($dept->nombre) ?>
                                                <?php if (!empty($dept->ubicacion)): ?>
                                                    - <?= htmlspecialchars($dept->ubicacion) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="departamento_id">Departamento</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="usuario_id" name="usuario_id">
                                    <option value="">-- NO VINCULAR --</option>
                                    <?php if (isset($usuarios_disponibles) && is_array($usuarios_disponibles)): ?>
                                        <?php foreach ($usuarios_disponibles as $u): ?>
                                            <?php $selected = ($current_user_id == $u->id) ? 'selected' : ''; ?>
                                            <option value="<?= $u->id ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($u->username) ?> (<?= ucfirst($u->rol) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="usuario_id">Cuenta de Usuario</label>
                                <small class="form-text text-muted">
                                    Solo aparecen usuarios no vinculados.
                                </small>
                            </div>
                        </div>

                    </div> <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>empleados" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Regresar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            <?= $action_title ?> Empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>