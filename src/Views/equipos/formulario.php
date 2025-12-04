<?php
// --- Lógica de Formulario ---
$es_edicion = isset($equipo) && $equipo !== null;
$action_title = $es_edicion ? 'Editar' : 'Registrar';

// Valores por defecto
$val_codigo       = $es_edicion ? ($equipo->codigo_inventario ?? '') : '';
$val_serie        = $es_edicion ? ($equipo->numero_serie ?? '') : '';
$val_tipo         = $es_edicion ? ($equipo->tipo ?? '') : '';
$val_marca        = $es_edicion ? ($equipo->marca ?? '') : '';
$val_modelo       = $es_edicion ? ($equipo->modelo ?? '') : '';
$val_procesador   = $es_edicion ? ($equipo->procesador ?? '') : '';
$val_ram          = $es_edicion ? ($equipo->memoria_ram ?? '') : '';
$val_almacenamiento = $es_edicion ? ($equipo->almacenamiento ?? '') : '';
$val_so           = $es_edicion ? ($equipo->sistema_operativo ?? '') : '';
$val_ip           = $es_edicion ? ($equipo->direccion_ip ?? '') : '';
$val_driver       = $es_edicion ? ($equipo->driver ?? '') : '';
$val_toner        = $es_edicion ? ($equipo->toner ?? '') : '';
$val_dept_id      = $es_edicion ? ($equipo->departamento_id ?? '') : '';
$val_emp_id       = $es_edicion ? ($equipo->empleado_id ?? '') : '';
$val_ubicacion    = $es_edicion ? ($equipo->ubicacion_fisica ?? '') : '';
$val_estado       = $es_edicion ? ($equipo->estado ?? 'disponible') : 'disponible';
$val_fecha_compra = $es_edicion ? ($equipo->fecha_compra ?? '') : '';
$val_proveedor    = $es_edicion ? ($equipo->proveedor ?? '') : '';
$val_proveedor_rif = $es_edicion ? ($equipo->proveedor_rif ?? '') : '';
$val_garantia     = $es_edicion ? ($equipo->garantia ?? '') : '';
$val_valor        = $es_edicion ? ($equipo->valor_compra ?? '') : '';

// Arrays de opciones - si no están definidos desde el controlador, usar valores por defecto
if (!isset($tipos_equipo)) {
    $tipos_equipo = ['computadora', 'impresora', 'escaner', 'servidor', 'monitor', 'teclado', 'raton', 'otro'];
}
if (!isset($estados_equipo)) {
    $estados_equipo = ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible', 'en_reserva'];
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $action_title ?> Equipo</h5>
            </div>
            
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>equipos/guardar" method="POST">
                    
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id" value="<?= $equipo->id ?>">
                    <?php endif; ?>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="equipoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basica-tab" data-bs-toggle="tab" data-bs-target="#basica" type="button" role="tab">Información Básica</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tecnica-tab" data-bs-toggle="tab" data-bs-target="#tecnica" type="button" role="tab">Características Técnicas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ubicacion-tab" data-bs-toggle="tab" data-bs-target="#ubicacion" type="button" role="tab">Ubicación y Asignación</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="adquisicion-tab" data-bs-toggle="tab" data-bs-target="#adquisicion" type="button" role="tab">Adquisición y Mantenimiento</button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="equipoTabsContent">
                        
                        <!-- Tab 1: Información Básica -->
                        <div class="tab-pane fade show active" id="basica" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="codigo_inventario" name="codigo_inventario" placeholder="Código" required value="<?= htmlspecialchars($val_codigo) ?>">
                                        <label for="codigo_inventario">Código de Inventario *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="numero_serie" name="numero_serie" placeholder="Serial" required value="<?= htmlspecialchars($val_serie) ?>">
                                        <label for="numero_serie">Número de Serie *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="" disabled <?= !$es_edicion ? 'selected' : '' ?>>Seleccione...</option>
                                            <?php 
                                            $is_custom = !in_array($val_tipo, $tipos_equipo) && !empty($val_tipo);
                                            foreach ($tipos_equipo as $t): 
                                            ?>
                                                <option value="<?= $t ?>" <?= ($val_tipo == $t || ($t == 'otro' && $is_custom)) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="tipo">Tipo de Equipo *</label>
                                    </div>
                                </div>
                                <div class="col-md-6" id="tipo_otro_container" style="display: <?= $is_custom ? 'block' : 'none' ?>;">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="tipo_otro" name="tipo_otro" placeholder="Especifique el tipo" value="<?= $is_custom ? htmlspecialchars($val_tipo) : '' ?>">
                                        <label for="tipo_otro">Especifique el tipo *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="estado" name="estado" required>
                                            <?php foreach ($estados_equipo as $e): ?>
                                                <option value="<?= $e ?>" <?= ($val_estado == $e) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $e)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="estado">Estado *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="marca" name="marca" placeholder="Marca" required value="<?= htmlspecialchars($val_marca) ?>">
                                        <label for="marca">Marca *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Modelo" required value="<?= htmlspecialchars($val_modelo) ?>">
                                        <label for="modelo">Modelo *</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Características Técnicas -->
                        <div class="tab-pane fade" id="tecnica" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="procesador" name="procesador" placeholder="CPU" value="<?= htmlspecialchars($val_procesador) ?>">
                                        <label for="procesador">Procesador (CPU)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="memoria_ram" name="memoria_ram" placeholder="RAM" value="<?= htmlspecialchars($val_ram) ?>">
                                        <label for="memoria_ram">Memoria RAM</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="almacenamiento" name="almacenamiento" placeholder="HDD/SSD" value="<?= htmlspecialchars($val_almacenamiento) ?>">
                                        <label for="almacenamiento">Almacenamiento</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="sistema_operativo" name="sistema_operativo" placeholder="OS" value="<?= htmlspecialchars($val_so) ?>">
                                        <label for="sistema_operativo">Sistema Operativo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="direccion_ip" name="direccion_ip" placeholder="IP (Opcional)" value="<?= htmlspecialchars($val_ip) ?>">
                                        <label for="direccion_ip">Dirección IP (Opcional)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="driver" name="driver" placeholder="Driver (Opcional)" value="<?= htmlspecialchars($val_driver) ?>">
                                        <label for="driver">Driver (URL/Nombre) (Opcional)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="toner" name="toner" placeholder="Toner (Opcional)" value="<?= htmlspecialchars($val_toner) ?>">
                                        <label for="toner">Toner (Modelo) (Opcional)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Ubicación y Asignación -->
                        <div class="tab-pane fade" id="ubicacion" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="departamento_id" name="departamento_id">
                                            <option value="">Sin Asignar</option>
                                            <?php if (isset($departamentos)): foreach ($departamentos as $d): ?>
                                                <option value="<?= $d->id ?>" <?= ($val_dept_id == $d->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($d->nombre) ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <label for="departamento_id">Departamento</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="empleado_id" name="empleado_id">
                                            <option value="">Sin Asignar</option>
                                            <?php if (isset($empleados)): foreach ($empleados as $e): ?>
                                                <option value="<?= $e->id ?>" <?= ($val_emp_id == $e->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($e->nombre . ' ' . $e->apellido) ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <label for="empleado_id">Usuario Asignado</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="ubicacion_fisica" name="ubicacion_fisica" placeholder="Ubicación Física (Opcional)" value="<?= htmlspecialchars($val_ubicacion) ?>">
                                        <label for="ubicacion_fisica">Ubicación Física Detallada (Opcional)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Adquisición -->
                        <div class="tab-pane fade" id="adquisicion" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" id="fecha_compra" name="fecha_compra" value="<?= htmlspecialchars($val_fecha_compra) ?>">
                                        <label for="fecha_compra">Fecha de Compra</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="proveedor" name="proveedor" placeholder="Proveedor" value="<?= htmlspecialchars($val_proveedor) ?>">
                                        <label for="proveedor">Proveedor (Nombre)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="proveedor_rif" name="proveedor_rif" placeholder="RIF" value="<?= htmlspecialchars($val_proveedor_rif) ?>">
                                        <label for="proveedor_rif">RIF del Proveedor</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" id="garantia" name="garantia" value="<?= htmlspecialchars($val_garantia) ?>">
                                        <label for="garantia">Vencimiento Garantía</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" class="form-control" id="valor_compra" name="valor_compra" placeholder="Valor" value="<?= htmlspecialchars($val_valor) ?>">
                                        <label for="valor_compra">Valor de Compra</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- End Tab Content -->

                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>equipos" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Regresar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> <?= $action_title ?> Equipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const container = document.getElementById('tipo_otro_container');
    const input = document.getElementById('tipo_otro');

    function toggleOtro() {
        if (tipoSelect.value === 'otro') {
            container.style.display = 'block';
            input.required = true;
        } else {
            container.style.display = 'none';
            input.required = false;
            // Only clear if we are hiding it, not on initial load if it was custom
            if (document.activeElement === tipoSelect) {
                 input.value = '';
            }
        }
    }

    tipoSelect.addEventListener('change', toggleOtro);
    
    // Run on load to set initial state correctly
    toggleOtro();
});
</script>

<?php if (!$es_edicion || !isset($_GET['url']) || strpos($_GET['url'], 'equipos/editar') === false): ?>
<?php require_once '../src/Views/layout/footer.php'; ?>
<?php endif; ?>
