<?php require_once '../src/Views/layout/header.php'; ?>
<?php require_once '../src/Views/layout/left-side-menu.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="centered-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0 text-dark">📦 Inventario General</h2>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <div>
                    <a href="index.php?url=inventario/crear" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Artículo
                    </a>
                    <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#modalNuevoEquipo">
                        <i class="bi bi-pc-display"></i> Nuevo Equipo
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="<?= BASE_URL ?>inventario" method="GET" id="searchForm">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" 
                                   placeholder="Buscar por código, nombre, serial, marca, modelo..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>" 
                                   id="searchInput" autocomplete="off">
                            <?php if (!empty($search)): ?>
                                <a href="<?= BASE_URL ?>inventario" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
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
                <div class="col-md-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Artículos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalItems ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Stock Total
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalStock ?> unidades</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cubes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Stock Bajo
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $itemsBajoStock ?> artículos</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaInventario">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Artículo</th>
                            <th>Categoría</th>
                            <th>Ubicación</th>
                            <th>Stock</th>
                            <th style="width: 150px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($item->codigo ?? '') ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($item->nombre ?? '') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($item->descripcion ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-info text-dark"><?= $item->categoria ?? '' ?></span></td>
                            <td><?= htmlspecialchars($item->ubicacion ?? '') ?></td>
                            <td>
                                <?php if(($item->stock_actual ?? 0) <= ($item->stock_minimo ?? 0)): ?>
                                    <span class="badge bg-danger fs-6"><?= $item->stock_actual ?? 0 ?> (Bajo)</span>
                                <?php else: ?>
                                    <span class="badge bg-success fs-6"><?= $item->stock_actual ?? 0 ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= BASE_URL ?>inventario/ver/<?= $item->id ?>" 
                                       class="btn btn-sm btn-primary text-white" 
                                       data-bs-toggle="tooltip" 
                                       title="Ver Detalles">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="<?= BASE_URL ?>inventario/distribucion/<?= $item->id ?>" 
                                       class="btn btn-sm btn-info text-white" 
                                       data-bs-toggle="tooltip" 
                                       title="Ver Distribución">
                                        <i class="bi bi-diagram-3-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning text-dark" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalMovimiento<?= $item->id ?>" 
                                            title="Ajuste Rápido">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalBaja<?= $item->id ?>" 
                                            title="Reportar Daño / Baja">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>


                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination for Items -->
            <?php if ($totalPagesItems > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($pageItems <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems - 1 ?>&page_equipos=<?= $pageEquipos ?>&q=<?= urlencode($search ?? '') ?>">Anterior</a>
                    </li>
                    <?php for($i = 1; $i <= $totalPagesItems; $i++): ?>
                        <li class="page-item <?= ($pageItems == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $i ?>&page_equipos=<?= $pageEquipos ?>&q=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($pageItems >= $totalPagesItems) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems + 1 ?>&page_equipos=<?= $pageEquipos ?>&q=<?= urlencode($search ?? '') ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Equipos Sin Asignar -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary">
                <i class="bi bi-pc-display-horizontal me-2"></i>Equipos en Stock (Sin Asignar)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Equipo</th>
                            <th>Serial</th>
                            <th>Estado</th>
                            <?php if ($_SESSION['rol'] !== 'tecnico'): ?>
                            <th class="text-center">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equiposSinAsignar)): ?>
                            <tr>
                                <td colspan="<?= ($_SESSION['rol'] === 'tecnico') ? '4' : '5' ?>" class="text-center py-3 text-muted">No hay equipos sin asignar en este momento.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equiposSinAsignar as $equipo): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($equipo->codigo_inventario ?? '') ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars(ucfirst($equipo->tipo ?? '')) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($equipo->marca ?? '') ?> - <?= htmlspecialchars($equipo->modelo ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($equipo->numero_serie ?? '') ?></td>
                                <td>
                                    <?php 
                                    $estado = $equipo->estado ?? '';
                                    if (empty($estado)) {
                                        echo '<span class="badge bg-warning text-dark">Sin estado</span>';
                                    } else {
                                        $estadoClass = match($estado) {
                                            'nuevo' => 'bg-success',
                                            'usado' => 'bg-info text-dark',
                                            'en_reparacion' => 'bg-warning text-dark',
                                            'fuera_de_servicio' => 'bg-danger',
                                            'disponible' => 'bg-primary',
                                            'en_reserva' => 'bg-info text-dark',
                                            'en_uso' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                        echo '<span class="badge ' . $estadoClass . '">' . ucfirst(str_replace('_', ' ', $estado)) . '</span>';
                                    }
                                    ?>
                                </td>
                                <?php if ($_SESSION['rol'] !== 'tecnico'): ?>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" 
                                           class="btn btn-sm btn-primary text-white" 
                                           data-bs-toggle="tooltip" 
                                           title="Asignar a Empleado/Departamento">
                                            <i class="bi bi-person-plus-fill me-1"></i> Asignar
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-equipo-btn" 
                                                data-id="<?= $equipo->id ?>" 
                                                data-tipo="<?= htmlspecialchars($equipo->tipo ?? '', ENT_QUOTES) ?>"
                                                type="button">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination for Equipos -->
            <?php if ($totalPagesEquipos > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($pageEquipos <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems ?>&page_equipos=<?= $pageEquipos - 1 ?>&q=<?= urlencode($search ?? '') ?>">Anterior</a>
                    </li>
                    <?php for($i = 1; $i <= $totalPagesEquipos; $i++): ?>
                        <li class="page-item <?= ($pageEquipos == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems ?>&page_equipos=<?= $i ?>&q=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($pageEquipos >= $totalPagesEquipos) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>inventario?page_items=<?= $pageItems ?>&page_equipos=<?= $pageEquipos + 1 ?>&q=<?= urlencode($search ?? '') ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

<!-- Modal Nuevo Equipo -->
<div class="modal fade" id="modalNuevoEquipo" tabindex="-1" aria-labelledby="modalNuevoEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoEquipoLabel">Registrar Nuevo Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php 
                // Variables para el formulario
                $equipo = null; 
                // $departamentos y $empleados ya están disponibles en la vista
                include '../src/Views/equipos/formulario.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Items (Moved outside table) -->
<?php foreach ($items as $item): ?>
<!-- Modal for Stock Adjustment -->
<div class="modal fade" id="modalMovimiento<?= $item->id ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>inventario/movimiento" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Stock: <?= $item->nombre ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" value="<?= $item->id ?>">
                    <div class="mb-3">
                        <label>Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="ENTRADA">Entrada (Compra/Devolución)</option>
                            <option value="SALIDA">Salida (Asignación/Uso)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label>Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej: Entrega a RRHH" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Baja/Daño -->
<div class="modal fade" id="modalBaja<?= $item->id ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>inventario/baja" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reportar Daño / Baja: <?= $item->nombre ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Esta acción descontará el stock y registrará una pérdida permanente.
                    </div>
                    <input type="hidden" name="item_id" value="<?= $item->id ?>">
                    <div class="mb-3">
                        <label>Cantidad Dañada/Perdida</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label>Motivo / Causa</label>
                        <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: Dañado por corto circuito, Garantía, Obsoleto..." required></textarea>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="eliminarArticulo<?= $item->id ?>" name="eliminar_completo" value="1">
                        <label class="form-check-label text-danger fw-bold" for="eliminarArticulo<?= $item->id ?>">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i>
                            Eliminar este artículo del inventario completamente
                        </label>
                        <small class="d-block text-muted mt-1">
                            ⚠️ Esta acción eliminará el artículo de forma permanente (no solo dar de baja)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Baja</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>


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

    const modal = document.getElementById('modalNuevoEquipo');
    if(modal) {
        // Ocultar el botón de regresar dentro del modal ya que tiene la X de cerrar
        const backBtn = modal.querySelector('a.btn-secondary');
        if(backBtn) backBtn.style.display = 'none';
        
        // Quitar sombra y borde del card incluido
        const card = modal.querySelector('.card');
        if(card) {
            card.classList.remove('shadow-sm');
            card.style.border = 'none';
        }
    }

    // Auto-submit search with debounce
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let timeout = null;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                searchForm.submit();
            }, 500); // Wait 500ms after typing stops
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
});
</script>

<?php require_once '../src/Views/layout/footer.php'; ?>
