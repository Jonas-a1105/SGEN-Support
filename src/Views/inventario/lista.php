<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-moderno.css?v=<?= time() ?>">

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

        <!-- View Toggle -->
        <div class="inventario-view-toggle">
            <button class="inventario-toggle-btn active" data-view="table" data-target="items">
                <i class="bi bi-list-ul"></i> Tabla
            </button>
            <button class="inventario-toggle-btn" data-view="cards" data-target="items">
                <i class="bi bi-grid-3x3-gap"></i> Cards
            </button>
        </div>

        <!-- Table View -->
        <div class="inventario-table-container" id="items-table">
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
                    <tr>
                        <td>
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
        <div class="inventario-carousel-wrapper" id="items-cards">
            <button class="inventario-carousel-nav prev" data-carousel="items-carousel">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="inventario-carousel" id="items-carousel">
                <?php foreach ($items as $item): ?>
                <div class="inventario-card">
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
            <button class="inventario-carousel-nav next" data-carousel="items-carousel">
                <i class="bi bi-chevron-right"></i>
            </button>
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

            <!-- View Toggle for Equipos -->
            <div class="inventario-view-toggle">
                <button class="inventario-toggle-btn active" data-view="table" data-target="equipos">
                    <i class="bi bi-list-ul"></i> Tabla
                </button>
                <button class="inventario-toggle-btn" data-view="cards" data-target="equipos">
                    <i class="bi bi-grid-3x3-gap"></i> Cards
                </button>
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
                        <tr>
                            <td>
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
                                    <a href="<?= BASE_URL ?>equipos/ver/<?= $equipo->id ?>" 
                                       class="inventario-action-btn view" 
                                       title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" 
                                       class="inventario-action-btn edit" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="inventario-action-btn delete delete-equipo-btn" 
                                            data-id="<?= $equipo->id ?>" 
                                            data-tipo="<?= htmlspecialchars($equipo->tipo ?? '', ENT_QUOTES) ?>"
                                            type="button">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                <button class="inventario-carousel-nav prev" data-carousel="equipos-carousel">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="inventario-carousel" id="equipos-carousel">
                    <?php foreach ($equiposSinAsignar as $equipo): ?>
                    <div class="inventario-card">
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
                        <div class="inventario-card-actions">
                            <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" 
                               class="inventario-action-btn view" title="Asignar">
                                <i class="bi bi-person-plus"></i>
                            </a>
                            <button class="inventario-action-btn delete delete-equipo-btn" 
                                    data-id="<?= $equipo->id ?>" 
                                    data-tipo="<?= htmlspecialchars($equipo->tipo ?? '', ENT_QUOTES) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="inventario-carousel-nav next" data-carousel="equipos-carousel">
                    <i class="bi bi-chevron-right"></i>
                </button>
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

    </div>
</div>

<!-- Modal Nuevo Equipo -->
<!-- Modal Nuevo Equipo REMOVED - Uses Full Page View -->


<!-- Modals for Items (Moved outside table) -->



<!-- Modal de Confirmación para Eliminar Equipo -->
<div class="modal fade" id="modalConfirmarEliminarEquipo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <strong>¿Estás seguro de eliminar este equipo?</strong>
                </div>
                <p id="equipoDetalleEliminar" class="mb-0"></p>
                <p class="text-muted mt-2 mb-0">
                    <small><i class="bi bi-info-circle me-1"></i>Esta acción no se puede deshacer.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="bi bi-trash-fill me-1"></i>Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to delete equipment (Global scope)
window.eliminarEquipo = function(btn) {
    const equipoId = btn.getAttribute('data-id');
    const equipoTipo = btn.getAttribute('data-tipo');
    
    // Show equipment details in modal
    const detalleElement = document.getElementById('equipoDetalleEliminar');
    detalleElement.innerHTML = '<strong>Tipo:</strong> ' + (equipoTipo || 'Sin especificar');
    
    // Show the custom confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminarEquipo'));
    modal.show();
    
    // Handle confirmation button click
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    btnConfirmar.onclick = function() {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>public/index.php?url=equipos/eliminar/' + equipoId;
        document.body.appendChild(form);
        form.submit();
    };
};

// Ajustes para el formulario dentro del modal
document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners to delete buttons
    document.querySelectorAll('.delete-equipo-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            eliminarEquipo(this);
        });
    });

    // Modal styled tweaks removed.

    // Auto-submit search with debounce
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let timeout = null;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                searchForm.submit();
            }, 500);
        });
        
        // Focus input if it has value (after reload)
        if (searchInput.value.trim() !== '') {
            searchInput.focus();
            // Move cursor to end
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
    }
});

// Función para manejar el cambio de items por página
// Función para manejar el cambio de items por página
function changeItemsPerPage(perPage) {
    if (!perPage) {
        // Fallback si se llama sin argumentos (legacy)
        const el = document.getElementById('itemsPerPage');
        if (el) perPage = el.value;
        else return;
    }
    
    // Guardar preferencia globalmente via cookie (usando PaginationPrefs si existe, o manual)
    if (window.PaginationPrefs) {
        PaginationPrefs.set(perPage);
    } else {
        // Fallback manual si no cargó el footer aún
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        document.cookie = 'sgen_pagination_per_page=' + perPage + ';expires=' + expires.toUTCString() + ';path=/';
    }
    
    // Recargar página manteniendo parámetros
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page_items', '1'); // Resetear página a 1
    url.searchParams.set('page_equipos', '1');
    window.location.href = url.toString();
}

    // Toggle fields for "Eliminar Artículo" in Baja modal (Event Delegation)
    document.body.addEventListener('change', function(e) {
        if (e.target && e.target.matches('input[name="eliminar_completo"]')) {
            const checkbox = e.target;
            const form = checkbox.closest('form');
            const cantidadInput = form.querySelector('input[name="cantidad"]');
            const motivoInput = form.querySelector('textarea[name="motivo"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Find the parent divs to hide/show
            const divCantidad = cantidadInput.closest('.mb-3');
            const divMotivo = motivoInput.closest('.mb-3');

            if (checkbox.checked) {
                // Hide fields and remove required
                divCantidad.style.display = 'none';
                divMotivo.style.display = 'none';
                cantidadInput.removeAttribute('required');
                motivoInput.removeAttribute('required');
                
                // Update button
                submitBtn.textContent = 'Eliminar Artículo';
                submitBtn.classList.remove('btn-danger');
                submitBtn.classList.add('btn-dark'); // Visual cue
            } else {
                // Show fields and add required
                divCantidad.style.display = 'block';
                divMotivo.style.display = 'block';
                cantidadInput.setAttribute('required', 'required');
                motivoInput.setAttribute('required', 'required');
                
                // Update button
                submitBtn.textContent = 'Confirmar Baja';
                submitBtn.classList.remove('btn-dark');
                submitBtn.classList.add('btn-danger');
            }
        }
    });

    // ===== VIEW TOGGLE =====
    document.querySelectorAll('.inventario-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const target = this.dataset.target;
            
            // Toggle button active state
            this.parentElement.querySelectorAll('.inventario-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Toggle view visibility
            const tableView = document.getElementById(target + '-table');
            const cardsView = document.getElementById(target + '-cards');
            
            if (view === 'table') {
                if (tableView) tableView.classList.remove('hidden');
                if (cardsView) cardsView.classList.remove('active');
            } else {
                if (tableView) tableView.classList.add('hidden');
                if (cardsView) cardsView.classList.add('active');
            }
        });
    });

    // ===== CAROUSEL NAVIGATION =====
    document.querySelectorAll('.inventario-carousel-nav').forEach(btn => {
        btn.addEventListener('click', function() {
            const carouselId = this.dataset.carousel;
            const carousel = document.getElementById(carouselId);
            const scrollAmount = 300;
            
            if (this.classList.contains('prev')) {
                carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        });
    });
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
