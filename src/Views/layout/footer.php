    </div>
</main>

<footer class="text-center py-3 mt-auto" style="background: var(--glass); border-top: 1px solid var(--border);">
    <div class="container">
        SGEN-Support &copy; <?= date('Y') ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>vendors/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="<?= BASE_URL ?>js/datatables-global.js"></script>

<script>
    // Pasamos la URL base de PHP a JavaScript
    const APP_BASE_URL = '<?= BASE_URL ?>';
</script>

<!-- Dark Mode Initialization -->
<script>
    // Cargar tema guardado desde PHP session
    const savedTheme = '<?= $_SESSION['tema'] ?? 'light' ?>';
    document.documentElement.setAttribute('data-theme', savedTheme);
</script>

<!-- Global Pagination Preferences (using cookies for PHP access) -->
<script>
    window.PaginationPrefs = {
        KEY: 'sgen_pagination_per_page',
        DEFAULT: 10,
        
        get: function() {
            // Leer de cookie
            const match = document.cookie.match(new RegExp('(^| )' + this.KEY + '=([^;]+)'));
            return match ? parseInt(match[2]) : this.DEFAULT;
        },
        
        set: function(value) {
            // Guardar en cookie (expira en 1 año)
            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 1);
            document.cookie = this.KEY + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        },
        
        // Aplica la preferencia a todos los selectores de paginación en la página
        apply: function() {
            const savedValue = this.get();
            document.querySelectorAll('[data-pagination-selector], #itemsPerPage, #pageLength').forEach(select => {
                if (select.tagName === 'SELECT') {
                    const optionExists = Array.from(select.options).some(opt => opt.value == savedValue);
                    if (optionExists) {
                        select.value = savedValue;
                    }
                }
            });
        },
        
        init: function() {
            this.apply();
            
            document.querySelectorAll('[data-pagination-selector], #itemsPerPage, #pageLength').forEach(select => {
                select.addEventListener('change', (e) => {
                    this.set(e.target.value);
                });
            });
        }
    };
    
    document.addEventListener('DOMContentLoaded', () => PaginationPrefs.init());
</script>

<script src="<?= BASE_URL ?>js/inactivity-logout.js"></script>
<script src="<?= BASE_URL ?>js/utils.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>js/toast.js?v=<?= time() ?>"></script>

<!-- Delete Modal Assets -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/modal-delete-modern.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>js/modal-delete.js?v=<?= time() ?>"></script>

<!-- Global Delete Modal -->
<div id="deleteModalOverlay" class="delete-modal-overlay">
    <div class="delete-modal-container">
        <!-- Header -->
        <div class="delete-modal-header">
            <h3 class="delete-modal-title">
                <div class="icon-box-red">
                    <i class="bi bi-trash"></i>
                </div>
                Eliminar Elemento
            </h3>
            <button id="btnCloseModal" class="close-btn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="delete-modal-body">
            <p class="confirm-text">
                ¿Estás seguro de que deseas eliminar este elemento? Esta acción eliminará el ticket permanentemente.
            </p>

            <!-- Context Card -->
            <div class="context-card">
                <div class="context-icon">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="context-info">
                    <span id="deleteContextId" class="context-id">#000</span>
                    <p id="deleteContextTitle" class="context-title">Título del elemento</p>
                    <span id="deleteContextAuthor" class="context-author">Por: Usuario</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="delete-modal-footer">
            <button id="btnCancelDelete" class="btn-cancel">Cancelar</button>
            <button id="btnConfirmDelete" class="btn-delete">Eliminar</button>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Map PHP flash types to Toast types
            const type = '<?= $_SESSION['flash_message']['type'] ?>';
            const message = '<?= addslashes($_SESSION['flash_message']['message']) ?>';
            
            // Normalize types if necessary
            // PHP might use 'danger' which maps to 'error' in our toast system
            let toastType = type;
            if (type === 'danger') toastType = 'error';
            if (type === 'warning') toastType = 'error'; // or neutral
            if (type === 'info') toastType = 'neutral';
            
            if (window.Toast) {
                Toast.show(toastType, message);
            }
        });
    </script>
    
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- ========================================== -->
<!-- MODERN INVENTORY WRITE-OFF MODAL           -->
<!-- ========================================== -->
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/modal-inventory-modern.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>public/js/modal-inventory.js?v=<?= time() ?>"></script>

<div id="inventoryWriteOffModal" class="inventory-modal-overlay">
    <div class="inventory-modal-container">
        
        <!-- Header -->
        <div class="inventory-modal-header">
            <div class="inventory-modal-title">
                <i id="invModalTitleIcon" class="bi bi-box-seam" style="font-size: 1.25rem;"></i>
                <div style="line-height: 1;">
                    <span id="invModalTitleText">Reportar Daño / Baja</span>
                    <p id="invModalArticleName" class="inventory-modal-subtitle">Producto: ...</p>
                </div>
            </div>
            <button class="inventory-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="inventory-modal-body">
            
            <!-- Stock Context -->
            <div class="stock-context-card">
                <div class="stock-info-group">
                    <div class="stock-icon-box">
                        <i class="bi bi-info-lg"></i>
                    </div>
                    <div>
                        <span class="stock-label">Stock Actual</span>
                        <div id="invModalStockCurrent" class="stock-value">0 Unidades</div>
                    </div>
                </div>
                
                <div id="invModalNewStockGroup" class="new-stock-preview" style="display: none;">
                    <span class="stock-label">Nuevo Stock</span>
                    <div id="invModalNewStockValue" class="stock-value">0 Unidades</div>
                </div>
            </div>

            <!-- Form -->
            <div class="space-y-4">
                <div class="form-group">
                    <label>Cantidad a descontar <span class="text-danger">*</span></label>
                    <input type="number" id="invModalQuantity" class="form-input number-large" placeholder="0" min="1">
                    <p class="input-error-msg" style="display: none;">
                        <i class="bi bi-exclamation-octagon"></i> No puedes descontar más del stock disponible.
                    </p>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label>Motivo / Causa <span class="text-danger">*</span></label>
                        <span style="font-size: 0.75rem; color: #94a3b8;">Min. 5 caracteres</span>
                    </div>
                    <textarea id="invModalReason" rows="2" class="form-input" placeholder="Describe el motivo de la baja..."></textarea>
                    
                    <!-- Quick Tags -->
                    <div class="quick-tags">
                        <button class="tag-btn" data-reason="Dañado">Dañado</button>
                        <button class="tag-btn" data-reason="Vencido">Vencido</button>
                        <button class="tag-btn" data-reason="Pérdida/Robo">Pérdida</button>
                        <button class="tag-btn" data-reason="Obsoleto">Obsoleto</button>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <label class="danger-zone-box">
                <div class="custom-checkbox-label">
                    <div class="relative flex items-center">
                        <input type="checkbox" id="invModalDeleteCheck" class="peer sr-only" style="display: none;"> <!-- Hidden native checkbox -->
                        <div class="checkbox-visual">
                            <i class="bi bi-check-lg" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <div>
                        <span class="checkbox-title">Eliminar este artículo del catálogo completamente</span>
                        <p class="checkbox-desc">
                            Esta acción eliminará la ficha del producto, historial y configuración. Solo debe usarse si el ítem no volverá a stock.
                        </p>
                    </div>
                </div>
            </label>

        </div>

        <!-- Footer -->
        <div class="inventory-modal-footer">
            <button class="btn-cancel">Cancelar</button>
            <button id="invModalSubmitBtn" class="btn-confirm orange" disabled>
                <span id="invModalSubmitText">Confirmar Baja</span>
                <div id="invModalSpinner" class="spinner-sm" style="display: none;"></div>
            </button>
        </div>

    </div>
</div>

<!-- Assign Technician Modal -->
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/modal-assign-tech.css?v=<?= time() ?>">
<div id="assignTechOverlay" class="at-overlay">
    <div id="assignTechModal" class="at-modal">
        <!-- Header -->
        <div class="at-header">
            <div>
                <h2 class="at-title">
                    <div class="at-icon-box">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    Asignar Técnico
                </h2>
                <div class="at-subtitle">
                    <span id="atTicketId" class="at-id-badge">#74</span>
                    <div class="at-dot"></div>
                    <span id="atTicketTitle" class="at-ticket-title">Fallo de conexión en Sala de Juntas</span>
                </div>
            </div>
            <button class="at-close-btn"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="at-body">
            <!-- Left Info -->
            <div class="at-col-left">
                <div>
                    <span class="at-section-label">Detalles del Soporte</span>
                    <div class="at-info-item">
                        <i class="bi bi-display at-info-icon"></i>
                        <div class="at-info-content">
                            <span class="at-info-label">Equipo</span>
                            <span id="atDevice" class="at-info-value">Router Cisco X200</span>
                        </div>
                    </div>
                    <div class="at-info-item">
                        <i class="bi bi-geo-alt at-info-icon"></i>
                        <div class="at-info-content">
                            <span class="at-info-label">Ubicación</span>
                            <span id="atLocation" class="at-info-value">Piso 2 - Ala Norte</span>
                        </div>
                    </div>
                    <div class="at-info-item">
                        <i class="bi bi-briefcase at-info-icon"></i>
                        <div class="at-info-content">
                            <span class="at-info-label">Categoría</span>
                            <span id="atCategory" class="at-info-value">Redes</span>
                        </div>
                    </div>
                </div>

                <div class="at-priority-card">
                    <div class="at-priority-header">
                        <span class="at-info-label">Prioridad</span>
                        <span id="atPriorityBadge" class="at-priority-badge at-priority-high">ALTA</span>
                    </div>
                    <div class="at-info-item" style="margin-bottom: 0;">
                        <i class="bi bi-clock at-info-icon"></i>
                        <div class="at-info-content">
                            <span class="at-info-label">Tiempo Transcurrido</span>
                            <span id="atTime" class="at-info-value">2h 15m</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Selection -->
            <div class="at-col-right">
                <div class="at-search-box">
                    <div class="at-search-wrapper">
                        <i class="bi bi-search at-search-icon"></i>
                        <input id="atSearchInput" type="text" class="at-search-input" placeholder="Buscar técnico por nombre o especialidad...">
                    </div>
                </div>

                <div id="atTechList" class="at-tech-list">
                    <!-- Dynamic Items -->
                </div>

                <div class="at-footer">
                    <div id="atWarningBox" class="at-warning-box" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Advertencia: Este técnico tiene una carga alta.</span>
                    </div>
                    
                    <div class="at-actions">
                        <button class="at-btn-cancel">Cancelar</button>
                        <button id="atConfirmBtn" class="at-btn-confirm" disabled>
                            Confirmar Asignación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>public/js/modal-assign-tech.js?v=<?= time() ?>"></script>

</body>
</html>

<!-- ========================================== -->
<!-- MODERN STOCK ADJUSTMENT MODAL (ENTRADA)    -->
<!-- ========================================== -->
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/modal-stock-adjust.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>public/js/modal-stock-adjust.js?v=<?= time() ?>"></script>

<div id="stockAdjustmentModal" class="stock-adjust-overlay">
    <div class="stock-adjust-container">
        
        <!-- Header -->
        <div class="stock-adjust-header">
            <div>
                <h3 class="stock-adjust-title">
                    <div class="header-icon-box">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                    Recibir Stock
                </h3>
                <p id="adjModalArticleName" class="stock-adjust-subtitle">
                    Sumar existencias a ...
                </p>
            </div>
            <button class="stock-adjust-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="stock-adjust-body">
            
            <!-- Type Selector -->
            <div class="type-grid">
                <button id="adjTypePurchase" class="type-btn active purchase">
                    <i class="bi bi-cart"></i>
                    <span class="type-label">Nueva Compra</span>
                </button>
                <button id="adjTypeReturn" class="type-btn return">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span class="type-label">Devolución</span>
                </button>
                <button id="adjTypeAudit" class="type-btn audit">
                    <i class="bi bi-clipboard-check"></i>
                    <span class="type-label">Ajuste</span>
                </button>
            </div>

            <!-- Math Visualizer -->
            <div class="math-viz-card">
                <div class="math-col">
                    <span class="math-label" style="color: #94a3b8;">Actual</span>
                    <span id="adjMathCurrent" class="math-val current">0</span>
                </div>
                <div class="math-icon">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <div class="math-col">
                    <span class="math-label" style="color: #059669;">Entrada</span>
                    <span id="adjMathInput" class="math-val input">+0</span>
                </div>
                <div class="math-icon">
                    <i class="bi bi-arrow-right"></i>
                </div>
                <div class="math-col">
                    <span class="math-label" style="color: #1e293b;">Total</span>
                    <span id="adjMathTotal" class="math-val total">0</span>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="form-section">
                <div class="form-row">
                    <div class="field-group">
                        <label>Cantidad <span style="color:#ef4444">*</span></label>
                        <input type="number" id="adjInputQuantity" class="field-input number-lg" placeholder="0" min="1" autofocus>
                    </div>
                    <div class="field-group">
                        <label>Ref. / Factura</label>
                        <input type="text" id="adjInputReference" class="field-input" placeholder="#INV-001">
                    </div>
                </div>
                <div class="field-group" style="margin-top: 1rem;">
                    <label>Notas Adicionales</label>
                    <textarea id="adjInputNotes" rows="2" class="field-textarea" placeholder="Proveedor, condiciones, etc."></textarea>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="stock-adjust-footer">
            <button class="btn-cancel">Cancelar</button>
            <button id="adjBtnSubmit" class="btn-confirm" disabled>
                <i class="bi bi-save"></i>
                <span id="adjSubmitText">Confirmar Entrada</span>
                <div id="adjSpinner" class="spinner-loader" style="display: none;"></div>
            </button>
        </div>

    </div>
</div>
