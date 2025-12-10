<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>



<div class="inventario-container">
    <div class="inventario-wrapper">
        
        <!-- Header -->
        <div class="inventario-header">
            <div class="inventario-title">
                <span class="inventario-title-icon">📦</span>
                <div>
                    <h1>Inventario General</h1>
                    <p>Gestión centralizada de recursos</p>
                </div>
            </div>
            
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="inventario-actions">
                <a href="<?= BASE_URL ?>inventario/crear" class="btn-nuevo-articulo">
                    <i class="bi bi-box-seam"></i>
                    Nuevo Artículo
                </a>
                <a href="<?= BASE_URL ?>equipos/crear" class="btn-nuevo-equipo">
                    <i class="bi bi-pc-display"></i>
                    Nuevo Equipo
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Search Box -->
        <div class="inventario-search">
            <i class="bi bi-search"></i>
            <form action="<?= BASE_URL ?>inventario" method="GET" id="searchForm" style="display: contents;">
                <input type="text" name="q" id="searchInput"
                       placeholder="Buscar por código, nombre, serial, marca, modelo..." 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       autocomplete="off">
            </form>
        </div>

        <!-- Main Content Card Container -->
        <div id="inventarioMain" class="inventario-content-card">

        <!-- KPI Cards -->
        <?php 
        $totalItems = count($items);
        $totalStock = 0;
        $itemsBajoStock = 0;
        foreach ($items as $item) {
            $totalStock += $item->stock_actual ?? 0;
            if (($item->stock_actual ?? 0) <= ($item->stock_minimo ?? 0)) {
                $itemsBajoStock++;
            }
        }
        ?>
        <div class="inventario-kpis">
            <!-- Total Artículos -->
            <div class="inventario-kpi blue">
                <div>
                    <p class="inventario-kpi-label">Total Artículos</p>
                    <h3 class="inventario-kpi-value"><?= $totalItems ?></h3>
                </div>
                <div class="inventario-kpi-icon blue">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>

            <!-- Stock Total -->
            <div class="inventario-kpi emerald">
                <div>
                    <p class="inventario-kpi-label">Stock Total</p>
                    <h3 class="inventario-kpi-value"><?= $totalStock ?> unidades</h3>
                </div>
                <div class="inventario-kpi-icon emerald">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>

            <!-- Stock Bajo -->
            <div class="inventario-kpi amber">
                <div>
                    <p class="inventario-kpi-label">Stock Bajo</p>
                    <h3 class="inventario-kpi-value"><?= $itemsBajoStock ?> artículos</h3>
                </div>
                <div class="inventario-kpi-icon amber">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="inventario-tabs">
            <button class="inventario-tab active" data-tab="articulos" onclick="switchInventarioTab('articulos')">
                <i class="bi bi-box-seam"></i>
                Artículos de Inventario
                <span class="inventario-tab-badge"><?= $totalItemsCount ?? count($items) ?></span>
            </button>
            <button class="inventario-tab" data-tab="equipos" onclick="switchInventarioTab('equipos')">
                <i class="bi bi-pc-display"></i>
                Equipos en Stock
                <span class="inventario-tab-badge"><?= count($equiposSinAsignar ?? []) ?></span>
            </button>
        </div>

        <!-- TAB: Artículos de Inventario -->
        <div id="tabArticulos" class="inventario-tab-content active">

        <!-- View Toggle + Bulk Controls -->
        <div class="inventario-view-toggle">
            <!-- Bulk Delete Controls -->
            <div class="bulk-controls">
                <div class="form-check form-switch mb-0" title="Activar selección múltiple">
                    <input class="form-check-input bulk-toggle" type="checkbox" id="bulkModeToggle" style="cursor: pointer; width: 3em; height: 1.5em;">
                </div>
                <div id="bulkSelectAllContainer" class="bulk-select-all">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bulkSelectAll" style="cursor: pointer; border-color: #cbd5e1;">
                        <label class="form-check-label text-muted fs-sm user-select-none" for="bulkSelectAll" style="cursor: pointer;">Todo</label>
                    </div>
                </div>
                <button id="bulkDeleteBtn" class="bulk-delete-btn">
                    <i class="bi bi-trash"></i>
                    <span id="bulkSelectedCount">0</span> seleccionados
                </button>
            </div>
            
            <button class="inventario-toggle-btn active" data-view="table" data-target="articulos">
                <i class="bi bi-list-ul"></i> Tabla
            </button>
            <button class="inventario-toggle-btn" data-view="cards" data-target="articulos">
                <i class="bi bi-grid-3x3-gap"></i> Cards
            </button>
        </div>

        <!-- Table View -->
        <div class="inventario-table-container" id="articulos-table">
            <table class="inventario-table" id="tablaInventario">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Artículo</th>
                        <th>Categoría</th>
                        <th>Ubicación</th>
                        <th class="text-center">Stock</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr data-bulk-item data-item-id="<?= $item->id ?>">
                        <td style="position: relative;">
                            <!-- Bulk Checkbox -->
                            <div class="bulk-checkbox">
                                <i class="bi bi-check-circle-fill icon-checked"></i>
                                <i class="bi bi-circle icon-unchecked"></i>
                            </div>
                            <span class="inventario-code"><?= htmlspecialchars($item->codigo ?? $item->id) ?></span>
                        </td>
                        <td>
                            <span class="inventario-article-name"><?= htmlspecialchars($item->nombre ?? '') ?></span>
                        </td>
                        <td>
                            <span class="inventario-category-badge"><?= $item->categoria ?? 'General' ?></span>
                        </td>
                        <td style="color: var(--inv-slate-600);"><?= htmlspecialchars($item->ubicacion ?? 'Almacén Central') ?></td>
                        <td class="text-center">
                            <?php if(($item->stock_actual ?? 0) <= ($item->stock_minimo ?? 0)): ?>
                                <span class="inventario-stock low"><?= $item->stock_actual ?? 0 ?></span>
                            <?php else: ?>
                                <span class="inventario-stock high"><?= $item->stock_actual ?? 0 ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="inventario-actions-cell">
                                <a href="<?= BASE_URL ?>inventario/ver/<?= $item->id ?>" 
                                   class="inventario-action-btn view" 
                                   title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" 
                                   class="inventario-action-btn structure" 
                                   title="Estructura">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                                <button class="inventario-action-btn edit" 
                                        onclick="StockAdjustmentModal.open({
                                            id: <?= $item->id ?>,
                                            name: '<?= addslashes($item->nombre) ?>',
                                            code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                                            stock: <?= $item->stock_actual ?? 0 ?>,
                                            unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                                        })"
                                        title="Recibir Stock / Ajuste">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </button>
                                <button class="inventario-action-btn delete" 
                                        onclick="InventoryModal.open({
                                            id: <?= $item->id ?>,
                                            name: '<?= addslashes($item->nombre) ?>',
                                            code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                                            stock: <?= $item->stock_actual ?? 0 ?>,
                                            unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                                        })"
                                        title="Eliminar / Baja">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Cards Carousel View -->
        <div class="inventario-carousel-wrapper" id="articulos-cards">

            <div class="inventario-carousel" id="items-carousel">
                <?php foreach ($items as $item): ?>
                <div class="inventario-card" data-bulk-item data-item-id="<?= $item->id ?>" style="position: relative;">
                    <!-- Bulk Checkbox -->
                    <div class="bulk-checkbox">
                        <i class="bi bi-check-circle-fill icon-checked"></i>
                        <i class="bi bi-circle icon-unchecked"></i>
                    </div>
                    <div class="inventario-card-header">
                        <span class="inventario-card-code"><?= htmlspecialchars($item->codigo ?? $item->id) ?></span>
                        <?php if(($item->stock_actual ?? 0) <= ($item->stock_minimo ?? 0)): ?>
                            <span class="inventario-card-stock low"><?= $item->stock_actual ?? 0 ?></span>
                        <?php else: ?>
                            <span class="inventario-card-stock high"><?= $item->stock_actual ?? 0 ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="inventario-card-title"><?= htmlspecialchars($item->nombre ?? '') ?></h4>
                    <span class="inventario-card-category"><?= $item->categoria ?? 'General' ?></span>
                    <div class="inventario-card-location">
                        <i class="bi bi-geo-alt"></i>
                        <?= htmlspecialchars($item->ubicacion ?? 'Almacén Central') ?>
                    </div>
                    <div class="inventario-card-actions">
                        <a href="<?= BASE_URL ?>inventario/ver/<?= $item->id ?>" 
                           class="inventario-action-btn view" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" 
                           class="inventario-action-btn structure" title="Estructura">
                            <i class="bi bi-diagram-3"></i>
                        </a>
                        <button class="inventario-action-btn edit" 
                                onclick="StockAdjustmentModal.open({
                                    id: <?= $item->id ?>,
                                    name: '<?= addslashes($item->nombre) ?>',
                                    code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                                    stock: <?= $item->stock_actual ?? 0 ?>,
                                    unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                                })"
                                title="Recibir Stock">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </button>
                        <button class="inventario-action-btn delete" 
                                onclick="InventoryModal.open({
                                    id: <?= $item->id ?>,
                                    name: '<?= addslashes($item->nombre) ?>',
                                    code: '<?= addslashes($item->codigo ?? $item->id) ?>',
                                    stock: <?= $item->stock_actual ?? 0 ?>,
                                    unit: '<?= addslashes($item->unidad_medida ?? 'Unidades') ?>'
                                })">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
            <!-- Modern Pagination Footer for Items -->
            <?php if ($totalPagesItems > 1 || !empty($items)): ?>
            <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; margin-top: 1rem;">
                <!-- Left: Counts & Selector -->
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                        Mostrando <strong style="color: #0f172a;"><?= count($items) ?></strong> de <strong style="color: #0f172a;"><?= $totalItemsCount ?? count($items) ?></strong> items
                    </span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select onchange="changeItemsPerPage(this.value)" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                            <option value="5" <?= isset($perPage) && $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= (!isset($perPage) || $perPage == 10) ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= isset($perPage) && $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= isset($perPage) && $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>

                <!-- Right: Navigation -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="<?= BASE_URL ?>inventario?page_items=<?= max(1, $pageItems - 1) ?>&page_equipos=<?= $pageEquipos ?>&q=<?= urlencode($search ?? '') ?>&per_page=<?= $perPage ?>" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $pageItems <= 1 ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                       onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                       onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                    
                    <span style="font-size: 0.875rem; color: #475569; font-weight: 500; padding: 0 0.5rem;">
                        Página <?= $pageItems ?> / <?= $totalPagesItems ?>
                    </span>
                    
                    <a href="<?= BASE_URL ?>inventario?page_items=<?= min($totalPagesItems, $pageItems + 1) ?>&page_equipos=<?= $pageEquipos ?>&q=<?= urlencode($search ?? '') ?>&per_page=<?= $perPage ?>" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $pageItems >= $totalPagesItems ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                       onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                       onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- End TAB: Artículos de Inventario -->

        <!-- TAB: Equipos en Stock -->
        <div id="tabEquipos" class="inventario-tab-content">
        
        <!-- Equipos Sin Asignar Section -->
        <div class="inventario-equipos-section">
            <div class="inventario-section-title">
                <i class="bi bi-pc-display"></i>
                <span>Equipos en Stock (Sin Asignar)</span>
            </div>

            <?php if (empty($equiposSinAsignar)): ?>
            <!-- Empty State -->
            <div class="inventario-empty">
                <div class="inventario-empty-icon">
                    <i class="bi bi-pc-display"></i>
                </div>
                <p class="text-main">No hay equipos sin asignar</p>
                <p class="text-sub">Los equipos disponibles aparecerán en esta lista.</p>
            </div>
            <?php else: ?>

            <!-- View Toggle + Bulk Controls for Equipos -->
            <div class="inventario-view-toggle">
                <!-- Bulk Delete Controls -->
                <div class="bulk-controls">
                    <div class="form-check form-switch mb-0" title="Activar selección múltiple">
                        <input class="form-check-input bulk-toggle" type="checkbox" id="bulkModeToggleEquipos" style="cursor: pointer; width: 3em; height: 1.5em;">
                    </div>
                    <div id="bulkSelectAllContainerEquipos" class="bulk-select-all">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bulkSelectAllEquipos" style="cursor: pointer; border-color: #cbd5e1;">
                            <label class="form-check-label text-muted fs-sm user-select-none" for="bulkSelectAllEquipos" style="cursor: pointer;">Todo</label>
                        </div>
                    </div>
                    <button id="bulkDeleteBtnEquipos" class="bulk-delete-btn">
                        <i class="bi bi-trash"></i>
                        <span id="bulkSelectedCountEquipos">0</span> seleccionados
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button class="inventario-toggle-btn active" data-view="table" data-target="equipos">
                        <i class="bi bi-list-ul"></i> Tabla
                    </button>
                    <button class="inventario-toggle-btn" data-view="cards" data-target="equipos">
                        <i class="bi bi-grid-3x3-gap"></i> Cards
                    </button>
                </div>
            </div>

            <!-- Equipos Table -->
            <div class="inventario-table-container" id="equipos-table">
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Equipo</th>
                            <th>Marca / Modelo</th>
                            <th>Serial</th>
                            <th>Estado</th>
                            <?php if ($_SESSION['rol'] !== 'tecnico'): ?>
                            <th class="text-right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equiposSinAsignar as $equipo): ?>
                        <tr data-bulk-item data-equipo-id="<?= $equipo->id ?>">
                            <td style="position: relative;">
                                <!-- Bulk Checkbox -->
                                <div class="bulk-checkbox">
                                    <i class="bi bi-check-circle-fill icon-checked"></i>
                                    <i class="bi bi-circle icon-unchecked"></i>
                                </div>
                                <span class="inventario-code"><?= htmlspecialchars($equipo->codigo_inventario ?? '') ?></span>
                            </td>
                            <td>
                                <span class="inventario-article-name"><?= htmlspecialchars(ucfirst($equipo->tipo ?? '')) ?></span>
                            </td>
                            <td>
                                <span class="inventario-category-badge"><?= htmlspecialchars($equipo->marca ?? '') ?> - <?= htmlspecialchars($equipo->modelo ?? '') ?></span>
                            </td>
                            <td style="color: var(--inv-slate-600);"><?= htmlspecialchars($equipo->numero_serie ?? '') ?></td>
                            <td>
                                <?php 
                                $estado = $equipo->estado ?? '';
                                if (empty($estado)) {
                                    echo '<span class="inventario-stock low">Sin estado</span>';
                                } else {
                                    $estadoClass = match($estado) {
                                        'nuevo', 'disponible' => 'high',
                                        default => 'low'
                                    };
                                    echo '<span class="inventario-stock ' . $estadoClass . '">' . ucfirst(str_replace('_', ' ', $estado)) . '</span>';
                                }
                                ?>
                            </td>
                            <?php if ($_SESSION['rol'] !== 'tecnico'): ?>
                            <td>
                                <div class="inventario-actions-cell">
                                    <a href="<?= BASE_URL ?>equipos/ver/<?= $equipo->id ?>?from=inventario_equipos" 
                                       class="inventario-action-btn view" 
                                       title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" 
                                       class="inventario-action-btn edit" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>equipos/eliminar/<?= $equipo->id ?>" 
                                       class="inventario-action-btn delete"
                                       data-no-global-delete="true"
                                       title="Eliminar"
                                       onclick="return confirmDeleteEquipoStock(event, this.href, '<?= addslashes(htmlspecialchars($equipo->marca . ' ' . $equipo->modelo)) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Equipos Cards Carousel -->
            <div class="inventario-carousel-wrapper" id="equipos-cards">

                <div class="inventario-carousel" id="equipos-carousel">
                    <?php foreach ($equiposSinAsignar as $equipo): ?>
                    <div class="inventario-card" data-bulk-item data-equipo-id="<?= $equipo->id ?>" style="position: relative;">
                        <!-- Bulk Checkbox -->
                        <div class="bulk-checkbox">
                            <i class="bi bi-check-circle-fill icon-checked"></i>
                            <i class="bi bi-circle icon-unchecked"></i>
                        </div>
                        <div class="inventario-card-header">
                            <span class="inventario-card-code"><?= htmlspecialchars($equipo->codigo_inventario ?? '') ?></span>
                            <?php 
                            $estado = $equipo->estado ?? '';
                            $estadoClass = match($estado) {
                                'nuevo', 'disponible' => 'high',
                                default => 'low'
                            };
                            ?>
                            <span class="inventario-card-stock <?= $estadoClass ?>"><?= ucfirst(str_replace('_', ' ', $estado ?: 'Sin estado')) ?></span>
                        </div>
                        <h4 class="inventario-card-title"><?= htmlspecialchars(ucfirst($equipo->tipo ?? '')) ?></h4>
                        <span class="inventario-card-category"><?= htmlspecialchars($equipo->marca ?? '') ?> - <?= htmlspecialchars($equipo->modelo ?? '') ?></span>
                        <div class="inventario-card-location">
                            <i class="bi bi-upc"></i>
                            <?= htmlspecialchars($equipo->numero_serie ?? 'S/N') ?>
                        </div>
                        <?php if ($_SESSION['rol'] !== 'tecnico'): ?>
                            <a href="<?= BASE_URL ?>equipos/ver/<?= $equipo->id ?>?from=inventario_equipos" 
                               class="inventario-action-btn view" 
                               title="Ver Detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" 
                               class="inventario-action-btn edit" 
                               title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= BASE_URL ?>equipos/eliminar/<?= $equipo->id ?>" 
                               class="inventario-action-btn delete"
                               data-no-global-delete="true"
                               title="Eliminar"
                               onclick="return confirmDeleteEquipoStock(event, this.href, '<?= addslashes(htmlspecialchars($equipo->marca . ' ' . $equipo->modelo)) ?>')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
            
            <!-- Modern Pagination Footer for Equipos -->
            <?php if ($totalPagesEquipos > 1 || !empty($equiposSinAsignar)): ?>
            <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; margin-top: 1rem;">
                <!-- Left: Counts & Selector -->
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-size: 0.875rem; color: #64748b;">
                         Mostrando <strong style="color: #0f172a;"><?= count($equiposSinAsignar) ?></strong> de <strong style="color: #0f172a;"><?= $totalEquiposCount ?? count($equiposSinAsignar) ?></strong> equipos
                    </span>
                    <!-- Selector compartido (o duplicado si se quiere independiente, pero usamos el mismo) -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8;">Mostrar:</span>
                        <select onchange="changeItemsPerPage(this.value)" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.75rem; color: #475569; background: white; cursor: pointer;">
                            <option value="5" <?= isset($perPage) && $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= (!isset($perPage) || $perPage == 10) ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= isset($perPage) && $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= isset($perPage) && $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                </div>

                <!-- Right: Navigation -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems ?>&page_equipos=<?= max(1, $pageEquipos - 1) ?>&q=<?= urlencode($search ?? '') ?>&per_page=<?= $perPage ?>" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $pageEquipos <= 1 ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                       onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                       onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                    
                    <span style="font-size: 0.875rem; color: #475569; font-weight: 500; padding: 0 0.5rem;">
                        Página <?= $pageEquipos ?> / <?= $totalPagesEquipos ?>
                    </span>
                    
                    <a href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems ?>&page_equipos=<?= min($totalPagesEquipos, $pageEquipos + 1) ?>&q=<?= urlencode($search ?? '') ?>&per_page=<?= $perPage ?>" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; <?= $pageEquipos >= $totalPagesEquipos ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                       onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';"
                       onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        </div><!-- End TAB: Equipos en Stock -->

        </div><!-- End inventario-content-card -->

    </div>
</div>

<!-- Modal Nuevo Equipo -->
<!-- Modal Nuevo Equipo REMOVED - Uses Full Page View -->


<!-- Modals for Items (Moved outside table) -->



<!-- Bulk Delete Assets -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/bulk-delete.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>js/bulk-delete-class.js?v=<?= time() ?>"></script>

<!-- Modern Simple Delete Modal Integration -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/modal-simple-delete-modern.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>js/modal-simple-delete-modern.js?v=<?= time() ?>"></script>

<script src="<?= BASE_URL ?>js/inventario.js?v=<?= time() ?>"></script>

<script>
    function confirmDeleteEquipoStock(e, url, name) {
        e.preventDefault();
        SimpleDeleteModal.open(url, {
            type: 'Equipo',
            name: name,
            warning: 'Esta acción eliminará el equipo del inventario permanentemente.'
        });
        return false;
    }
    
    // Tab Switching Function
    function switchInventarioTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.inventario-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`.inventario-tab[data-tab="${tabName}"]`).classList.add('active');
        
        // Update tab content
        document.querySelectorAll('.inventario-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.add('active');
    }
</script>

<script>
    // Initialize Bulk Delete for both tabs using the Class Manager
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-switch tab based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab && ['articulos', 'equipos'].includes(tab)) {
            switchInventarioTab(tab);
        }

        // 1. Inventario Artículos
        new BulkDeleteManager({
            containerId: 'tabArticulos', // Scoped to tab container
            itemSelector: '[data-bulk-item]',
            itemIdAttribute: 'data-item-id',
            deleteUrl: BASE_URL + 'inventario/eliminar_masivo',
            entityName: 'artículos',
            entityNameSingular: 'artículo',
            toggleId: 'bulkModeToggle',
            selectAllId: 'bulkSelectAll',
            selectAllContainerId: 'bulkSelectAllContainer',
            deleteButtonId: 'bulkDeleteBtn',
            countSpanId: 'bulkSelectedCount'
        });

        // 2. Equipos en Stock
        new BulkDeleteManager({
            containerId: 'tabEquipos', // Scoped to tab container
            itemSelector: '[data-bulk-item]',
            itemIdAttribute: 'data-equipo-id',
            deleteUrl: BASE_URL + 'equipos/eliminar_masivo', // Endpoint address
            entityName: 'equipos',
            entityNameSingular: 'equipo',
            toggleId: 'bulkModeToggleEquipos',
            selectAllId: 'bulkSelectAllEquipos',
            selectAllContainerId: 'bulkSelectAllContainerEquipos',
            deleteButtonId: 'bulkDeleteBtnEquipos',
            countSpanId: 'bulkSelectedCountEquipos'
        });
    });
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
