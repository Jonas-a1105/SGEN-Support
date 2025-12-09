<?php
// src/Views/reportes/index.php
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/reportes-moderno.css?v=<?= time() ?>">


<div style="max-width: 1350px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 2rem;">

        <!-- HEADER (Outside Container) -->
        <div class="reportes-header">
            <div class="reportes-title-group">
                <div class="reportes-icon">
                    <i class="bi bi-pie-chart-fill" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h1 class="reportes-title">Reportes & Analíticas</h1>
                    <p class="reportes-subtitle">Genera, exporta y analiza el rendimiento de tu operación.</p>
                </div>
            </div>
            <div>
                <a href="<?= BASE_URL ?>reportes/historial" class="reportes-btn-history">
                    <i class="bi bi-clock-history"></i>
                    Historial
                </a>
            </div>
        </div>

        <div class="reportes-white-container">

        <!-- MAIN GENERATOR SECTION -->
        <div class="reportes-generator-section">
            
            <!-- Section Header -->
            <div class="reportes-section-header">
                <h2 class="reportes-section-title">
                    <i class="bi bi-file-earmark-text" style="color: #6366f1;"></i>
                    Generador de Reportes
                </h2>
                
                <!-- Tabs -->
                <div class="reportes-tabs">
                    <button onclick="switchTab('soportes')" id="tab-soportes" class="reportes-tab active">Soportes</button>
                    <button onclick="switchTab('inventario')" id="tab-inventario" class="reportes-tab">Inventario</button>
                    <button onclick="switchTab('mantenimientos')" id="tab-mantenimientos" class="reportes-tab">Mantenimiento</button>
                </div>
            </div>

            <!-- Generator Content -->
            <div class="reportes-generator-content">
                <div class="reportes-grid-layout">
                    
                    <!-- LEFT COLUMN: FILTERS -->
                    <div>
                        <!-- Tab Content: Soportes -->
                        <form action="<?= BASE_URL ?>reportes/soportes" method="GET" target="_blank" id="formReporteSoportes">
                            <div id="content-soportes" class="tab-content">
                                <div class="reportes-form-grid">
                                    
                                    <!-- Date Range -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label class="reportes-label">Rango de Fechas</label>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="reportes-input-wrapper" style="flex: 1;">
                                                <i class="bi bi-calendar3 reportes-input-icon"></i>
                                                <input type="date" name="fecha_inicio" class="reportes-input">
                                            </div>
                                            <span style="color: #94a3b8;">-</span>
                                            <div class="reportes-input-wrapper" style="flex: 1;">
                                                <i class="bi bi-calendar3 reportes-input-icon"></i>
                                                <input type="date" name="fecha_fin" class="reportes-input">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label class="reportes-label">Categoría</label>
                                        <div class="reportes-input-wrapper">
                                            <select name="categoria_id" class="reportes-select">
                                                <option value="">Todas las categorías</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="bi bi-chevron-down reportes-chevron"></i>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label class="reportes-label">Estado</label>
                                        <div class="reportes-input-wrapper">
                                            <select name="estado" class="reportes-select">
                                                <option value="">Cualquier estado</option>
                                                <option value="pendiente">Pendiente</option>
                                                <option value="en_proceso">En Proceso</option>
                                                <option value="resuelto">Resuelto</option>
                                            </select>
                                            <i class="bi bi-chevron-down reportes-chevron"></i>
                                        </div>
                                    </div>

                                    <!-- Priority -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label class="reportes-label">Prioridad</label>
                                        <div class="reportes-input-wrapper">
                                            <select name="prioridad" class="reportes-select">
                                                <option value="">Todas</option>
                                                <option value="alta">Alta</option>
                                                <option value="media">Media</option>
                                                <option value="baja">Baja</option>
                                            </select>
                                            <i class="bi bi-chevron-down reportes-chevron"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Box -->
                                <div class="reportes-preview-box">
                                    <div style="flex: 1;">
                                        <p style="font-size: 0.7rem; font-weight: 700; color: #4338ca; margin: 0 0 0.25rem 0; text-transform: uppercase;">Resumen Previo</p>
                                        <p style="font-size: 0.875rem; color: #6366f1; margin: 0;">Se exportarán los registros basados en tu selección actual.</p>
                                    </div>
                                    <div style="display: flex; gap: 0.25rem; align-items: flex-end;">
                                        <div style="width: 0.5rem; height: 1rem; background: #a5b4fc; border-radius: 0.125rem 0.125rem 0 0;"></div>
                                        <div style="width: 0.5rem; height: 1.5rem; background: #818cf8; border-radius: 0.125rem 0.125rem 0 0;"></div>
                                        <div style="width: 0.5rem; height: 0.75rem; background: #a5b4fc; border-radius: 0.125rem 0.125rem 0 0;"></div>
                                        <div style="width: 0.5rem; height: 2rem; background: #6366f1; border-radius: 0.125rem 0.125rem 0 0;"></div>
                                        <div style="width: 0.5rem; height: 1.25rem; background: #818cf8; border-radius: 0.125rem 0.125rem 0 0;"></div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Tab Content: Inventario -->
                        <div id="content-inventario" class="tab-content" style="display: none;">
                            <div style="text-align: center; padding: 2rem;">
                                <div style="width: 64px; height: 64px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #10b981;"></i>
                                </div>
                                <h3 style="font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">Reporte de Inventario</h3>
                                <p style="color: #64748b; font-size: 0.875rem; margin: 0 0 1rem 0;">Estado actual del inventario, valor total y stock por ubicación.</p>
                            </div>
                        </div>

                        <!-- Tab Content: Mantenimientos -->
                        <div id="content-mantenimientos" class="tab-content" style="display: none;">
                            <div style="text-align: center; padding: 2rem;">
                                <div style="width: 64px; height: 64px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-tools" style="font-size: 1.5rem; color: #f59e0b;"></i>
                                </div>
                                <h3 style="font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">Reporte de Mantenimientos</h3>
                                <p style="color: #64748b; font-size: 0.875rem; margin: 0 0 1rem 0;">Historial de reparaciones, costos y programación futura.</p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: ACTIONS -->
                    <div class="reportes-actions-col">
                        <button type="submit" form="formReporteSoportes" id="btnPDF" class="btn-download-pdf">
                            <i class="bi bi-file-earmark-pdf" style="font-size: 1.25rem;"></i>
                            Descargar PDF
                        </button>
                        
                        <button type="button" onclick="exportarExcel()" id="btnExcel" class="btn-export-excel">
                            <i class="bi bi-file-earmark-spreadsheet" style="font-size: 1.25rem; color: #10b981;"></i>
                            Exportar CSV / Excel
                        </button>

                        <p style="font-size: 0.75rem; text-align: center; color: #94a3b8; margin: 0.5rem 0 0 0;">
                            El reporte incluirá gráficos y tablas detalladas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK REPORTS SECTION -->
        <div class="reportes-predefined-section">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem;">Reportes Predefinidos</h3>
            <div class="reportes-grid-cards">
                
                <!-- Card: Inventario -->
                <a href="<?= BASE_URL ?>reportes/inventario" target="_blank" class="reportes-card">
                    <div class="reportes-card-header">
                        <div class="reportes-card-icon" style="background: #d1fae5;">
                            <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #10b981;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 class="reportes-card-title">Inventario General</h4>
                    <p class="reportes-card-desc">Valoración total, stock por almacén y alertas de stock bajo.</p>
                    <div class="reportes-card-footer">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94a3b8; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-clock" style="font-size: 0.625rem;"></i>
                            Actualizado
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #6366f1; display: flex; align-items: center; gap: 0.25rem;">
                            Generar <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Card: Mantenimientos -->
                <a href="<?= BASE_URL ?>reportes/mantenimientos" target="_blank" class="reportes-card">
                    <div class="reportes-card-header">
                        <div class="reportes-card-icon" style="background: #fef3c7;">
                            <i class="bi bi-tools" style="font-size: 1.5rem; color: #f59e0b;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 class="reportes-card-title">Mantenimientos</h4>
                    <p class="reportes-card-desc">Historial de reparaciones, costos acumulados y proyecciones.</p>
                    <div class="reportes-card-footer">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94a3b8; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-clock" style="font-size: 0.625rem;"></i>
                            Actualizado
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #6366f1; display: flex; align-items: center; gap: 0.25rem;">
                            Generar <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Card: Rendimiento -->
                <a href="<?= BASE_URL ?>reportes/rendimiento" class="reportes-card">
                    <div class="reportes-card-header">
                        <div class="reportes-card-icon" style="background: #dbeafe;">
                            <i class="bi bi-bar-chart-line" style="font-size: 1.5rem; color: #3b82f6;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 class="reportes-card-title">Rendimiento del Equipo</h4>
                    <p class="reportes-card-desc">KPIs de técnicos, tiempos de respuesta y cierre de tickets.</p>
                    <div class="reportes-card-footer">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94a3b8; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-clock" style="font-size: 0.625rem;"></i>
                            Tiempo real
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #6366f1; display: flex; align-items: center; gap: 0.25rem;">
                            Generar <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </a>

            </div>
        </div>

    </div>
</div>



<script>
function switchTab(tab) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    
    // Reset all tab buttons
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color = '#64748b';
        btn.style.fontWeight = '500';
        btn.style.boxShadow = 'none';
    });
    
    // Show selected content
    document.getElementById('content-' + tab).style.display = 'block';
    
    // Highlight selected tab
    const activeTab = document.getElementById('tab-' + tab);
    activeTab.style.background = 'white';
    activeTab.style.color = '#0f172a';
    activeTab.style.fontWeight = '700';
    activeTab.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
    
    // Update form action and buttons
    const form = document.getElementById('formReporteSoportes');
    const btnPDF = document.getElementById('btnPDF');
    
    if (tab === 'soportes') {
        form.action = '<?= BASE_URL ?>reportes/soportes';
        btnPDF.setAttribute('form', 'formReporteSoportes');
    } else if (tab === 'inventario') {
        btnPDF.onclick = function() { window.open('<?= BASE_URL ?>reportes/inventario', '_blank'); };
        btnPDF.removeAttribute('form');
    } else if (tab === 'mantenimientos') {
        btnPDF.onclick = function() { window.open('<?= BASE_URL ?>reportes/mantenimientos', '_blank'); };
        btnPDF.removeAttribute('form');
    }
}

function exportarExcel() {
    const form = document.getElementById('formReporteSoportes');
    const originalAction = form.action;
    form.action = '<?= BASE_URL ?>reportes/soportes_excel';
    form.submit();
    form.action = originalAction;
}
</script>
