<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Bitácora de Acciones</h2>
    </div>

    <!-- Controles: Selector de registros y Búsqueda -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <label for="pageLength" class="mb-0">Mostrar</label>
            <select id="pageLength" class="form-select form-select-sm" style="width: auto;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>registros</span>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <label for="searchInput" class="mb-0">Buscar:</label>
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width: 250px;" placeholder="Buscar en bitácora...">
        </div>
    </div>

    <div class="table-responsive">
        <table id="bitacora-table" class="table table-striped table-hover" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Usuario</th>
                    <th>Acción Realizada</th>
                    <th class="text-center">Enlace</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($acciones)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">No hay acciones registradas.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($acciones as $accion): ?>
                        <tr>
                            <td>
                                <small><?= date('d/m/Y', strtotime($accion->created_at)) ?></small><br>
                                <strong><?= date('h:i A', strtotime($accion->created_at)) ?></strong>
                            </td>
                            <td>
                                <i class="bi bi-person-circle text-primary me-1"></i>
                                <?= htmlspecialchars($accion->username) ?>
                            </td>
                            <td><?= htmlspecialchars($accion->accion) ?></td>
                            
                            <td class="text-center">
                                <?php if ($accion->enlace_tipo === 'soporte'): ?>
                                    <a href="<?= BASE_URL ?>soportes/ver/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-ticket-detailed me-1"></i> Ver Ticket
                                    </a>
                                <?php elseif ($accion->enlace_tipo === 'equipo'): ?>
                                    <a href="<?= BASE_URL ?>equipos/editar/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pc-display me-1"></i> Ver Equipo
                                    </a>
                                <?php elseif ($accion->enlace_tipo === 'usuario'): ?>
                                    <a href="<?= BASE_URL ?>usuarios/editar/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-person me-1"></i> Ver Usuario
                                    </a>
                                <?php elseif ($accion->enlace_tipo === 'empleado'): ?>
                                    <a href="<?= BASE_URL ?>empleados/editar/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-person-badge me-1"></i> Ver Empleado
                                    </a>
                                <?php elseif ($accion->enlace_tipo === 'departamento'): ?>
                                    <a href="<?= BASE_URL ?>departamentos/editar/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-building me-1"></i> Ver Depto
                                    </a>
                                <?php elseif ($accion->enlace_tipo === 'inventario'): ?>
                                    <a href="<?= BASE_URL ?>inventario/ver/<?= $accion->enlace_id ?>" class="btn btn-sm btn-outline-purple">
                                        <i class="bi bi-box-seam me-1"></i> Ver Ítem
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginación -->
<?php if ($totalPages > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Paginación de bitácora">
                <ul class="pagination justify-content-center">
                    
                    <!-- Primer página -->
                    <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>bitacora?page=1&per_page=<?= $perPage ?>">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                    
                    <!-- Página anterior -->
                    <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>bitacora?page=<?= $currentPage - 1 ?>&per_page=<?= $perPage ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Páginas numeradas -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>bitacora?page=<?= $i ?>&per_page=<?= $perPage ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Página siguiente -->
                    <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>bitacora?page=<?= $currentPage + 1 ?>&per_page=<?= $perPage ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    
                    <!-- Última página -->
                    <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>bitacora?page=<?= $totalPages ?>&per_page=<?= $perPage ?>">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>
                    
                </ul>
            </nav>
            
            <!-- Info de paginación -->
            <p class="text-center text-muted">
                Página <?= $currentPage ?> de <?= $totalPages ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<style>
/* Separación extra de celdas en bitácora */
#bitacora-table td,
#bitacora-table th {
    padding: 15px 18px !important;
}

/* Custom purple outline for inventory button */
.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
}

.btn-outline-purple:hover {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
</style>

<script>
// Funcionalidad del selector de registros por página y búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('pageLength');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('bitacora-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Establecer el valor actual si existe en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPerPage = urlParams.get('per_page') || '20';
    selector.value = currentPerPage;
    
    // Manejar cambio de selector
    selector.addEventListener('change', function() {
        const perPage = this.value;
        window.location.href = '<?= BASE_URL ?>bitacora?per_page=' + perPage;
    });
    
    // Funcionalidad de búsqueda en tiempo real
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            // Evitar filtrar la fila de "no hay registros"
            if (row.querySelector('.bi-inbox')) {
                return;
            }
            
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>