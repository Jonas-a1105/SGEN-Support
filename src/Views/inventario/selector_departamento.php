<?php
/**
 * Vista de Selector de Departamentos para Inventario - Diseño Moderno
 */
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-selector.css?v=<?= time() ?>">

<div class="ds-container">
    
    <div class="ds-max-w-5xl ds-space-y-8">
        
        <!-- --- HEADER SECTION --- -->
        <div class="ds-header">
            <div class="ds-icon-wrapper">
                <i class="bi bi-building text-primary fs-3"></i>
            </div>
            <h1 class="ds-title">Selecciona un Espacio de Trabajo</h1>
            <p class="ds-subtitle">Elige el departamento para gestionar su inventario, asignaciones y reportes específicos.</p>
        </div>

        <!-- --- SEARCH BAR --- -->
        <div class="ds-search-container">
            <i class="bi bi-search ds-search-icon"></i>
            <input 
                type="text" 
                id="deptSearchInput" 
                class="ds-search-input" 
                placeholder="Buscar departamento o sede..."
                autofocus
            >
        </div>

        <!-- --- GRID DE DEPARTAMENTOS --- -->
        <div class="ds-grid">
            <?php foreach ($departamentos as $index => $d): 
                // Lógica de tema y color
                $themes = ['ds-theme-blue', 'ds-theme-orange', 'ds-theme-slate', 'ds-theme-rose', 'ds-theme-emerald', 'ds-theme-indigo'];
                $theme = $themes[$d->id % count($themes)];
                
                // Lógica de ícono
                $icon = 'bi-bank'; 
                $nameLower = strtolower($d->nombre);
                
                if (strpos($nameLower, 'ingenier') !== false || strpos($nameLower, 'obra') !== false) {
                    $icon = 'bi-cone-striped'; 
                } elseif (strpos($nameLower, 'recursos') !== false || strpos($nameLower, 'personal') !== false) {
                    $icon = 'bi-people'; 
                } elseif (strpos($nameLower, 'admin') !== false || strpos($nameLower, 'finan') !== false) {
                    $icon = 'bi-briefcase'; 
                } elseif (strpos($nameLower, 'catastr') !== false) {
                    $icon = 'bi-bank'; 
                } elseif (strpos($nameLower, 'info') !== false || strpos($nameLower, 'tec') !== false) {
                     $icon = 'bi-cpu'; 
                } else {
                    $icon = 'bi-building'; 
                }

                $location = $d->ubicacion ?? 'Sede Principal';
                $address = $d->descripcion ?? 'Oficina Central'; 
                if (strlen($address) > 35) $address = substr($address, 0, 35) . '...';
            ?>
                <a href="<?= BASE_URL ?>inventario/departamento/<?= $d->id ?>" 
                   class="ds-card <?= $theme ?>"
                   data-name="<?= htmlspecialchars($d->nombre) ?>"
                   data-location="<?= htmlspecialchars($location . ' ' . $address) ?>">
                    
                    <!-- Background Decorator -->
                    <div class="ds-card-bg-deco"></div>

                    <div class="ds-card-content">
                        
                        <!-- Icon Box -->
                        <div class="ds-card-icon-box">
                            <i class="bi <?= $icon ?>"></i>
                        </div>

                        <!-- Content -->
                        <div class="ds-card-text">
                            <h3 class="ds-card-title"><?= htmlspecialchars($d->nombre) ?></h3>
                            
                            <div class="ds-card-location">
                                <i class="bi bi-geo-alt"></i>
                                <span class="text-truncate"><?= htmlspecialchars($location) ?> &bull; <?= htmlspecialchars($address) ?></span>
                            </div>

                            <!-- Stats Row -->
                            <div class="ds-stats-row">
                                <div class="ds-stat">
                                    <i class="bi bi-box"></i>
                                    <span class="ds-stat-val"><?= $d->equipos_count ?? 0 ?></span>
                                    <span class="ds-stat-label">Items</span>
                                </div>
                                <div class="ds-stat">
                                    <i class="bi bi-people"></i>
                                    <span class="ds-stat-val"><?= $d->empleados_count ?? 0 ?></span>
                                    <span class="ds-stat-label">Personal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enter Arrow -->
                    <div class="ds-arrow-wrapper">
                        <div class="ds-arrow-circle">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
            
            <?php if (empty($departamentos)): ?>
                <div class="ds-empty-state" style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #64748b;">
                    <i class="bi bi-search fs-1 mb-3"></i>
                    <p>No se encontraron departamentos.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- --- FOOTER ACTION --- -->
        <div class="ds-footer-action">
            <a href="<?= BASE_URL ?>dashboard" class="ds-btn-back">
                <i class="bi bi-chevron-left"></i>
                Volver al Dashboard
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deptSearchInput');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.ds-card');
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const location = card.getAttribute('data-location').toLowerCase();
            
            if (name.includes(searchTerm) || location.includes(searchTerm)) {
                card.classList.remove('hidden');
                card.style.display = 'block';
            } else {
                card.classList.add('hidden');
                card.style.display = 'none';
            }
        });
    });
});
</script>
