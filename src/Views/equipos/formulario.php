<?php
// --- Lógica de Formulario ---
$es_edicion = isset($equipo) && $equipo !== null;
$action_title = $es_edicion ? 'Editar' : 'Registrar';

// Valores por defecto
$val_codigo       = $es_edicion ? ($equipo->codigo_inventario ?? '') : '';
$val_serie        = $es_edicion ? ($equipo->numero_serie ?? '') : '';
$val_nombre       = $es_edicion ? ($equipo->tipo ?? '') : ''; // Usamos 'tipo' como nombre principal visualmente si no hay campo nombre explicito, pero el diseño pide nombre
// El model original no tenia 'nombre' explícito para equipos, usaba 'tipo'. 
// Pero el diseño pide "Nombre / Título". Usaremos 'tipo' mejorado o agregaremos un campo visual. Al guardar, mapearemos.
// Revisando el controlador: 'tipo' es enum. 
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
$val_imagen       = $es_edicion ? ($equipo->imagen ?? '') : '';

// Arrays de opciones
if (!isset($tipos_equipo)) {
    $tipos_equipo = ['computadora', 'laptop', 'impresora', 'escaner', 'servidor', 'monitor', 'teclado', 'raton', 'switch', 'router', 'ups', 'otro'];
}
if (!isset($estados_equipo)) {
    $estados_equipo = ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible', 'en_reserva'];
}
?>

<!-- Load Assets -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/equipment-form-modern.css?v=<?= time() ?>">

<div class="eq-wizard-wrapper">
    
    <!-- Sidebar -->
    <div class="eq-sidebar">
        <div class="eq-header">
            <a href="<?= !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : BASE_URL . 'inventario' ?>" class="eq-back-link">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h1 class="eq-title">
                <div class="eq-icon-box">
                     <i class="bi bi-laptop"></i>
                </div>
                <?= $action_title ?> Equipo
            </h1>
            <p class="eq-subtitle">Complete los datos para dar de alta el activo en inventario.</p>
        </div>

        <div class="eq-stepper">
            <!-- Step 1 -->
            <button class="eq-step-btn active" data-step="0">
                <div class="eq-step-indicator"></div>
                <div class="eq-step-icon"><i class="bi bi-laptop"></i></div>
                <div class="eq-step-info">
                    <span class="eq-step-label">Información Básica</span>
                    <span class="eq-step-desc">Identificación del activo</span>
                </div>
            </button>
            <!-- Step 2 -->
            <button class="eq-step-btn" data-step="1">
                <div class="eq-step-indicator"></div>
                <div class="eq-step-icon"><i class="bi bi-cpu"></i></div>
                <div class="eq-step-info">
                    <span class="eq-step-label">Especificaciones</span>
                    <span class="eq-step-desc">Hardware y Software</span>
                </div>
            </button>
            <!-- Step 3 -->
            <button class="eq-step-btn" data-step="2">
                <div class="eq-step-indicator"></div>
                <div class="eq-step-icon"><i class="bi bi-geo-alt"></i></div>
                <div class="eq-step-info">
                    <span class="eq-step-label">Ubicación</span>
                    <span class="eq-step-desc">Asignación física</span>
                </div>
            </button>
            <!-- Step 4 -->
            <button class="eq-step-btn" data-step="3">
                <div class="eq-step-indicator"></div>
                <div class="eq-step-icon"><i class="bi bi-receipt"></i></div>
                <div class="eq-step-info">
                    <span class="eq-step-label">Adquisición</span>
                    <span class="eq-step-desc">Garantía y Costos</span>
                </div>
            </button>
        </div>

        <div class="eq-photo-box" id="photoBox" style="cursor: pointer; position: relative;">
             <div class="eq-photo-circle" id="photoPreview" style="background-image: <?= $val_imagen ? "url('".BASE_URL."uploads/equipos/".$val_imagen."')" : 'none' ?>; background-size: cover; background-position: center; border: 2px solid var(--eq-slate-200);">
                 <?php if (!$val_imagen): ?>
                    <i class="bi bi-camera" style="font-size: 1.5rem;"></i>
                 <?php endif; ?>
             </div>
             
             <!-- Botón Eliminar -->
             <button type="button" id="removePhotoBtn" style="display: <?= $val_imagen ? 'flex' : 'none' ?>; position: absolute; top: -5px; right: -5px; width: 24px; height: 24px; border-radius: 50%; background: #ef4444; color: white; border: 2px solid white; align-items: center; justify-content: center; cursor: pointer; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                <i class="bi bi-x" style="font-size: 1.2rem;"></i>
             </button>

             <p style="font-size: 0.75rem; font-weight: 600; color: var(--eq-slate-600); margin-top: 5px;">
                <span id="photoText"><?= $val_imagen ? 'Cambiar foto' : 'Subir foto' ?></span>
             </p>
        </div>
    </div>

    <!-- Content -->
    <div class="eq-content">
        <form action="<?= BASE_URL ?>equipos/guardar" method="POST" id="equipmentForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; height: 100%;">
            
            <?php if ($es_edicion): ?>
                <input type="hidden" name="id" value="<?= $equipo->id ?>">
            <?php endif; ?>
            
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '') ?>">
            
            <!-- Hidden Image Inputs (Moved inside form) -->
            <input type="file" name="imagen" id="imagenInput" accept="image/*" style="display: none;">
            <input type="hidden" name="eliminar_imagen" id="eliminarImagenInput" value="0">

            <div class="eq-form-container">
                <div class="eq-step-header">
                    <h2 class="eq-step-title">Información Básica</h2>
                    <span class="eq-step-badge">Paso 1 de 4</span>
                </div>

                <!-- STEP 0: BASICA -->
                <div class="step-pane active" data-step="0">
                    <div class="eq-grid">
                        
                        <!-- Codigo -->
                        <div class="eq-field">
                            <label>Código Inventario <span class="eq-required">*</span></label>
                            <div class="eq-input-wrapper">
                                <i class="bi bi-hash eq-input-icon"></i>
                                <input type="text" name="codigo_inventario" class="eq-input" placeholder="SGEN-001" required value="<?= htmlspecialchars($val_codigo) ?>">
                            </div>
                        </div>

                        <!-- Serial -->
                        <div class="eq-field">
                            <label>Nro de Serie <span class="eq-required">*</span></label>
                            <div class="eq-input-wrapper">
                                <i class="bi bi-upc eq-input-icon"></i>
                                <input type="text" name="numero_serie" class="eq-input" placeholder="XJ9-22001" required value="<?= htmlspecialchars($val_serie) ?>">
                            </div>
                        </div>

                        <!-- Tipo -->
                        <div class="eq-field">
                            <label>Tipo de Equipo <span class="eq-required">*</span></label>
                            <div class="eq-select-wrapper">
                                <i class="bi bi-laptop eq-input-icon"></i>
                                <select name="tipo" id="tipo" class="eq-select" required>
                                    <option value="" disabled <?= !$es_edicion ? 'selected' : '' ?>>Seleccione...</option>
                                    <?php 
                                    $is_custom = !in_array($val_tipo, $tipos_equipo) && !empty($val_tipo);
                                    foreach ($tipos_equipo as $t): 
                                    ?>
                                        <option value="<?= $t ?>" <?= ($val_tipo == $t || ($t == 'otro' && $is_custom)) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down eq-select-chevron"></i>
                            </div>
                        </div>

                        <!-- Otro Tipo -->
                        <div class="eq-field" id="tipo_otro_container" style="display: none;">
                            <label>Especifique Tipo <span class="eq-required">*</span></label>
                            <div class="eq-input-wrapper">
                                <input type="text" name="tipo_otro" id="tipo_otro" class="eq-input no-icon" placeholder="Eje: Proyector" value="<?= $is_custom ? htmlspecialchars($val_tipo) : '' ?>">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="eq-field">
                            <label>Estado Inicial <span class="eq-required">*</span></label>
                            <div class="eq-select-wrapper">
                                <i class="bi bi-check-circle eq-input-icon"></i>
                                <select name="estado" class="eq-select" required>
                                    <?php foreach ($estados_equipo as $e): ?>
                                        <option value="<?= $e ?>" <?= ($val_estado == $e) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $e)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down eq-select-chevron"></i>
                            </div>
                        </div>

                        <!-- Marca -->
                        <div class="eq-field">
                            <label>Marca <span class="eq-required">*</span></label>
                            <div class="eq-input-wrapper">
                                <input type="text" name="marca" class="eq-input no-icon" placeholder="Dell, HP..." required value="<?= htmlspecialchars($val_marca) ?>">
                            </div>
                        </div>

                        <!-- Modelo -->
                        <div class="eq-field">
                            <label>Modelo <span class="eq-required">*</span></label>
                            <div class="eq-input-wrapper">
                                <input type="text" name="modelo" class="eq-input no-icon" placeholder="Modelo exacto" required value="<?= htmlspecialchars($val_modelo) ?>">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 1: TECNICA -->
                <div class="step-pane" data-step="1">
                    <div class="eq-callout mb-4">
                        <i class="bi bi-cpu eq-callout-icon"></i>
                        <div>
                            <h4 class="eq-callout-title">Hardware Principal</h4>
                            <p class="eq-callout-text">Especifique los componentes clave para el control de renovaciones.</p>
                        </div>
                    </div>
                    
                    <div class="eq-grid">
                        <div class="eq-field">
                             <label>Procesador (CPU)</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-cpu eq-input-icon"></i>
                                <input type="text" name="procesador" class="eq-input" placeholder="Intel Core i7" value="<?= htmlspecialchars($val_procesador) ?>">
                             </div>
                        </div>
                        <div class="eq-field">
                             <label>Memoria RAM</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-memory eq-input-icon"></i>
                                <input type="text" name="memoria_ram" class="eq-input" placeholder="16 GB" value="<?= htmlspecialchars($val_ram) ?>">
                             </div>
                        </div>
                        <div class="eq-field">
                             <label>Almacenamiento</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-hdd eq-input-icon"></i>
                                <input type="text" name="almacenamiento" class="eq-input" placeholder="512 GB SSD" value="<?= htmlspecialchars($val_almacenamiento) ?>">
                             </div>
                        </div>
                        <div class="eq-field">
                             <label>Sistema Operativo</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-windows eq-input-icon"></i>
                                <input type="text" name="sistema_operativo" class="eq-input" placeholder="Windows 11" value="<?= htmlspecialchars($val_so) ?>">
                             </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: UBICACION -->
                <div class="step-pane" data-step="2">
                    <div class="eq-grid">
                        
                        <div class="eq-field">
                            <label>Departamento Asignado</label>
                            <div class="eq-select-wrapper">
                                <i class="bi bi-building eq-input-icon"></i>
                                <select name="departamento_id" class="eq-select">
                                    <option value="">Sin Asignar</option>
                                    <?php if (isset($departamentos)): foreach ($departamentos as $d): ?>
                                        <option value="<?= $d->id ?>" <?= ($val_dept_id == $d->id) ? 'selected' : '' ?>><?= htmlspecialchars($d->nombre) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                                <i class="bi bi-chevron-down eq-select-chevron"></i>
                            </div>
                        </div>

                        <div class="eq-field">
                            <label>Usuario Responsable</label>
                            <div class="eq-select-wrapper">
                                <i class="bi bi-person eq-input-icon"></i>
                                <select name="empleado_id" class="eq-select">
                                    <option value="">Sin Asignar</option>
                                    <?php if (isset($empleados)): foreach ($empleados as $e): ?>
                                        <option value="<?= $e->id ?>" <?= ($val_emp_id == $e->id) ? 'selected' : '' ?>><?= htmlspecialchars($e->nombre . ' ' . $e->apellido) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                                <i class="bi bi-chevron-down eq-select-chevron"></i>
                            </div>
                        </div>

                        <div class="eq-field eq-col-span-2">
                             <label>Ubicación Física Detallada</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-geo-alt eq-input-icon"></i>
                                <input type="text" name="ubicacion_fisica" class="eq-input" placeholder="Edificio A, Piso 2, Oficina 204" value="<?= htmlspecialchars($val_ubicacion) ?>">
                             </div>
                        </div>

                        <div class="eq-field eq-col-span-2">
                             <label>Dirección IP (Opcional)</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-wifi eq-input-icon"></i>
                                <input type="text" name="direccion_ip" class="eq-input" placeholder="192.168.1.XXX" value="<?= htmlspecialchars($val_ip) ?>">
                             </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 3: ADQUISICION -->
                <div class="step-pane" data-step="3">
                    <div class="eq-grid">
                        
                        <div class="eq-field">
                             <label>Fecha de Compra</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-calendar3 eq-input-icon"></i>
                                <input type="date" name="fecha_compra" class="eq-input" value="<?= htmlspecialchars($val_fecha_compra) ?>">
                             </div>
                        </div>

                        <div class="eq-field">
                             <label>Vencimiento Garantía</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-shield-check eq-input-icon"></i>
                                <input type="date" name="garantia" class="eq-input" value="<?= htmlspecialchars($val_garantia) ?>">
                             </div>
                        </div>

                        <div class="eq-field eq-col-span-2">
                             <label>Proveedor</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-shop eq-input-icon"></i>
                                <input type="text" name="proveedor" class="eq-input" placeholder="Nombre del proveedor" value="<?= htmlspecialchars($val_proveedor) ?>">
                             </div>
                        </div>

                        <div class="eq-field">
                             <label>RIF / Tax ID</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-hash eq-input-icon"></i>
                                <input type="text" name="proveedor_rif" class="eq-input" placeholder="J-12345678-9" value="<?= htmlspecialchars($val_proveedor_rif) ?>">
                             </div>
                        </div>

                        <div class="eq-field">
                             <label>Valor de Compra</label>
                             <div class="eq-input-wrapper">
                                <i class="bi bi-currency-dollar eq-input-icon"></i>
                                <input type="number" step="0.01" name="valor_compra" class="eq-input" placeholder="0.00" value="<?= htmlspecialchars($val_valor) ?>">
                             </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="eq-footer">
                <button type="button" class="btn-nav-prev" disabled>Atrás</button>
                
                <button type="button" class="btn-nav-next">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </button>
                
                <button type="submit" class="btn-nav-save" style="display: none;">
                    <i class="bi bi-save"></i> <?= $es_edicion ? 'Guardar Cambios' : 'Registrar Equipo' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>js/equipment-form.js?v=<?= time() ?>"></script>
