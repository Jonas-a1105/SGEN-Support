    </div>
</main>

<footer class="text-center py-3 mt-auto" style="background: var(--glass); border-top: 1px solid var(--border);">
    <div class="container">
        <p style="font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin: 0;">
            SGEN-Support &copy; <?= date('Y') ?>
        </p>
    </div>
</footer>



<?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        (function() {
            // Map PHP flash types to Toast types
            const type = '<?= $_SESSION['flash_message']['type'] ?>';
            const message = '<?= addslashes($_SESSION['flash_message']['message']) ?>';
            
            // Normalize types if necessary
            let toastType = type;
            if (type === 'danger') toastType = 'error';
            if (type === 'warning') toastType = 'error';
            if (type === 'info') toastType = 'neutral';
            
            // Run immediately as script is executed by Turbo after render
            if (window.Toast) {
                Toast.show(toastType, message);
            }
        })();
    </script>
    
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- ========================================== -->
<!-- MODERN INVENTORY WRITE-OFF MODAL           -->
<!-- ========================================== -->

<!-- MODERN INVENTORY WRITE-OFF MODAL           -->

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
<script src="<?= BASE_URL ?>js/modal-assign-tech.js?v=FIX_CACHE_999"></script>


<!-- ========================================== -->
<!-- MODERN STOCK ADJUSTMENT MODAL (ENTRADA)    -->
<!-- ========================================== -->

<!-- STOCK ADJUSTMENT MODAL           -->

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

</body>
</html>
