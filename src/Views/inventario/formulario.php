<?php
/**
 * Vista de Formulario de Inventario - Moderno V2
 * Diseño basado en React Component 'NewItemRegistration'
 */

// Helpers
$isEdit = isset($editMode) && $editMode === true;
$item = $item ?? (object)[];

// Valores por defecto para creación
$codigo = $item->codigo ?? '';
$nombre = $item->nombre ?? '';
$categoria = $item->categoria ?? '';
$marca = $item->marca ?? '';
$modelo = $item->modelo ?? '';
$descripcion = $item->descripcion ?? '';
$unidad = $item->unidad_medida ?? 'Unidad';
$stockMin = $item->stock_minimo ?? 5;
$ubicacion = $item->ubicacion ?? 'Almacén Central';
$proveedor = $item->proveedor ?? '';
$garantia = $item->garantia_fin ?? '';
$costo = $item->valor_compra ?? '';
$stockInicial = 0; // Solo para create

?>

<!-- CSS Moderno -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/item-form-modern.css?v=<?= time() ?>">

<div class="item-form-container">
    
    <form id="itemForm" action="<?= BASE_URL ?>inventario/<?= $isEdit ? 'actualizar/' . $item->id : 'crear' ?>" method="POST" enctype="multipart/form-data">
        
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $item->id ?>">
        <?php endif; ?>

        <!-- HEADER -->
        <div class="item-form-header">
            <div>
                <h1 class="item-header-title">
                    <div class="item-header-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <?= $isEdit ? 'Editar Artículo' : 'Registrar Nuevo Artículo' ?>
                </h1>
                <p class="item-header-subtitle">
                    <?= $isEdit ? 'Modifica los datos del producto existente.' : 'Agrega un nuevo producto al catálogo global.' ?>
                </p>
            </div>
            
            <div class="item-header-actions">
                <a href="<?= BASE_URL ?>inventario" class="btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="btn-save" id="btnSubmit">
                    <i class="bi bi-check-lg"></i> 
                    <span id="submitText"><?= $isEdit ? 'Actualizar Producto' : 'Guardar Producto' ?></span>
                </button>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="item-form-grid">
            
            <!-- COLUMNA IZQUIERDA: Identidad -->
            <div class="item-col-left">
                
                <!-- Foto Uploader -->
                <div class="image-upload-area" id="imageUploadArea">
                    <input type="file" id="imageUpload" name="imagen_referencia" accept="image/*" style="display: none;">
                    <input type="hidden" name="eliminar_imagen" id="eliminarImagen" value="0">
                    
                    <!-- Preview Container -->
                    <div id="imagePreviewContainer" style="display: none; width: 100%; height: 100%; position: relative;">
                        <img id="imagePreview" src="" class="preview-image" alt="Vista previa" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.75rem;">
                        <button type="button" id="btnRemoveImage" class="btn-remove-image" title="Eliminar imagen">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    
                    <!-- Upload Placeholder -->
                    <div class="image-upload-content" id="uploadPlaceholder" onclick="document.getElementById('imageUpload').click()">
                        <div class="image-icon-wrapper">
                            <i class="bi bi-cloud-arrow-up-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <p class="image-upload-label">Subir Imagen Principal</p>
                        <p class="image-upload-sub">PNG, JPG hasta 5MB</p>
                    </div>
                </div>

                <!-- Código y SKU -->
                <div class="code-input-box">
                    <div class="form-group">
                        <label class="form-label">
                            Código SKU / Barras
                            <span class="req-badge">REQ</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-upc-scan input-icon"></i>
                            <input type="text" name="codigo" class="form-input-control has-icon mono" 
                                   placeholder="Auto-generar..." value="<?= htmlspecialchars($codigo) ?>">
                        </div>
                    </div>
                    <div class="info-tip">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Si se deja vacío, el sistema generará uno automáticamente.</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="form-group">
                    <label class="form-label">Descripción / Notas</label>
                    <textarea name="descripcion" class="form-input-control" rows="5" 
                              placeholder="Detalles técnicos, compatibilidad, color..." 
                              style="resize: none;"><?= htmlspecialchars($descripcion) ?></textarea>
                </div>

            </div>

            <!-- COLUMNA DERECHA: Detalles -->
            <div class="item-col-right">
                
                <!-- SECCIÓN 1: Ficha Técnica -->
                <section>
                    <div class="form-section-header">
                        <i class="bi bi-tags-fill form-section-icon"></i>
                        <h3 class="form-section-title">Ficha Técnica</h3>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                Nombre del Artículo
                                <span class="req-badge">REQ</span>
                            </label>
                            <input type="text" name="nombre" class="form-input-control large-text" 
                                   placeholder="Ej: Cable HDMI 4K Trenzado 2m" value="<?= htmlspecialchars($nombre) ?>" required>
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <div class="input-wrapper">
                                <select name="categoria" id="categoriaSelect" class="form-select-control">
                                    <option value="">Seleccionar...</option>
                                    <?php 
                                    $cats = ['Hardware', 'Software', 'Periféricos', 'Cables', 'Consumibles', 'Herramientas', 'Otros'];
                                    foreach ($cats as $cat): ?>
                                        <option value="<?= $cat ?>" <?= $categoria === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down select-arrow"></i>
                            </div>
                            <!-- Custom Category Input (Hidden by default) -->
                            <div id="customCategoryContainer" class="mt-2" style="display: none;">
                                <input type="text" name="categoria_otra" id="customCategoryInput" class="form-input-control" placeholder="Especifique la categoría..." disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unidad de Medida</label>
                            <div class="input-wrapper">
                                <select name="unidad_medida" class="form-select-control">
                                    <?php 
                                    $units = ['Unidad', 'Caja', 'Metro', 'Paquete', 'Litro', 'Kg'];
                                    foreach ($units as $u): ?>
                                        <option value="<?= $u ?>" <?= $unidad === $u ? 'selected' : '' ?>><?= $u ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-input-control" 
                                   placeholder="Ej: Belkin" value="<?= htmlspecialchars($marca) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" class="form-input-control" 
                                   placeholder="Ej: F3Y021bt2M" value="<?= htmlspecialchars($modelo) ?>">
                        </div>
                    </div>
                </section>

                <!-- SECCIÓN 2: Logística -->
                <section>
                    <div class="form-section-header" style="justify-content: space-between; border-bottom: none; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-layers-fill form-section-icon"></i>
                            <h3 class="form-section-title">Control de Stock</h3>
                        </div>
                        
                        <div class="location-badge">
                            <i class="bi bi-geo-alt-fill"></i>
                            Asignación: <?= htmlspecialchars($ubicacion) ?>
                        </div>
                    </div>
                    <input type="hidden" name="ubicacion" value="<?= htmlspecialchars($ubicacion) ?>">

                    <div style="background-color: var(--if-slate-50); border-radius: 0.75rem; padding: 1.5rem; border: 1px solid var(--if-slate-200);">
                        <div class="form-row cols-3" style="margin-bottom: 1.5rem;">
                            
                            <?php if (!$isEdit): ?>
                            <div class="form-group">
                                <label class="form-label">Stock Inicial</label>
                                <div class="input-wrapper">
                                    <i class="bi bi-box-seam input-icon text-blue"></i>
                                    <input type="number" name="stock_inicial" id="stock_inicial" class="form-input-control has-icon" 
                                           placeholder="0" min="0" value="0" style="color: var(--if-blue-600); font-weight: 700;">
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label">Stock Mínimo (Alerta)</label>
                                <input type="number" name="stock_minimo" class="form-input-control" 
                                       placeholder="Ej: 5" min="0" value="<?= $stockMin ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Costo Unitario ($)</label>
                                <div class="input-wrapper">
                                    <i class="bi bi-currency-dollar input-icon"></i>
                                    <input type="number" name="valor_compra" id="valor_compra" class="form-input-control has-icon" 
                                           placeholder="0.00" step="0.01" min="0" value="<?= $costo ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Value Feedback -->
                        <div class="inventory-value-card" id="inventoryValueContainer" style="display: none;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="value-icon-wrapper">
                                    <i class="bi bi-cash-stack" style="font-size: 1.25rem;"></i>
                                </div>
                                <div>
                                    <p class="value-label">Valor Total Inventario</p>
                                    <p class="value-amount" id="totalValueDisplay">$0.00</p>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 0.75rem; color: var(--if-slate-400);" id="formulaDisplay">
                                0 unidades x $0.00
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- SECCIÓN 3: Proveedor y Compra (Opcional) -->
                <div class="form-row cols-2" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Proveedor</label>
                        <div class="input-wrapper">
                            <i class="bi bi-truck input-icon"></i>
                            <input type="text" name="proveedor" class="form-input-control has-icon" 
                                   placeholder="Ej: TecnoSupply C.A." value="<?= htmlspecialchars($proveedor) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Compra</label>
                        <div class="input-wrapper">
                            <input type="date" name="fecha_compra" class="form-input-control" value="<?= $item->fecha_compra ?? '' ?>">
                        </div>
                    </div>
                </div>
                <div class="form-row cols-2" style="margin-bottom: 0;">
                    <div class="form-group">
                        <label class="form-label">Vencimiento Garantía</label>
                        <div class="input-wrapper">
                            <input type="date" name="garantia_fin" class="form-input-control" value="<?= $garantia ?>">
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="proveedor_rif" value="<?= htmlspecialchars($item->proveedor_rif ?? '') ?>">

            </div>
        </div>

    </form>
</div>

<!-- Scripts -->
<script src="<?= BASE_URL ?>js/item-form.js?v=<?= time() ?>"></script>