<?php 
$es_edicion = isset($departamento) && $departamento !== null; 
$action_title = $es_edicion ? 'Editar' : 'Crear';

// Valor para el campo
$val_nombre = $es_edicion ? $departamento->nombre : '';
$val_ubicacion = $es_edicion ? ($departamento->ubicacion ?? '') : '';
?>

<div class="row">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $action_title ?> Departamento</h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="<?= BASE_URL ?>departamentos/guardar" method="POST">
                    
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id" value="<?= $departamento->id ?>">
                    <?php endif; ?>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del Departamento" required value="<?= htmlspecialchars($val_nombre) ?>">
                        <label for="nombre">Nombre delDepartamento</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ubicación" value="<?= htmlspecialchars($val_ubicacion) ?>">
                        <label for="ubicacion">Ubicación (Edificio, Piso, etc.)</label>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>departamentos" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Regresar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            <?= $action_title ?> Departamento
                        </button>
                    </div>
                </form>
            </div> </div> </div> </div>