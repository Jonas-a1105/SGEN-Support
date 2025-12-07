<?php
// src/Views/reportes/index.php
?>

<div style="background: #f8fafc; min-height: 100vh; padding: 2rem;">
    <div style="max-width: 1150px; margin: 0 auto;">

        <!-- HEADER -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="padding: 0.5rem; background: #e0e7ff; border-radius: 0.5rem; color: #6366f1;">
                    <i class="bi bi-pie-chart-fill" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Reportes & Analíticas</h1>
                    <p style="color: #64748b; margin: 0; font-size: 0.875rem;">Genera, exporta y analiza el rendimiento de tu operación.</p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="<?= BASE_URL ?>reportes/historial" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 500; border-radius: 0.75rem; text-decoration: none; font-size: 0.875rem;">
                    <i class="bi bi-clock-history"></i>
                    Historial
                </a>
            </div>
        </div>

        <!-- MAIN BUILDER CARD -->
        <div style="background: white; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 2rem;">
            
            <!-- Card Header -->
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="padding: 0.5rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <i class="bi bi-file-earmark-text" style="color: #6366f1; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h2 style="font-weight: 700; color: #0f172a; margin: 0; font-size: 1rem;">Generador de Reportes</h2>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Configura los parámetros para tu exportación</p>
                    </div>
                </div>
                <!-- Tabs -->
                <div style="display: flex; background: rgba(226, 232, 240, 0.5); padding: 0.25rem; border-radius: 0.5rem;">
                    <button onclick="switchTab('soportes')" id="tab-soportes" style="padding: 0.5rem 1rem; background: white; color: #0f172a; font-size: 0.75rem; font-weight: 700; border-radius: 0.375rem; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">Soportes</button>
                    <button onclick="switchTab('inventario')" id="tab-inventario" style="padding: 0.5rem 1rem; background: transparent; color: #64748b; font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer;">Inventario</button>
                    <button onclick="switchTab('mantenimientos')" id="tab-mantenimientos" style="padding: 0.5rem 1rem; background: transparent; color: #64748b; font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer;">Mantenimiento</button>
                </div>
            </div>

            <!-- Card Body -->
            <div style="padding: 1.5rem 2rem;">
                <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem;">
                    
                    <!-- COLUMNA IZQUIERDA: FILTROS -->
                    <div>
                        <!-- Tab Content: Soportes -->
                        <form action="<?= BASE_URL ?>reportes/soportes" method="GET" target="_blank" id="formReporteSoportes">
                            <div id="content-soportes" class="tab-content">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                                    
                                    <!-- Rango de Fechas -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Rango de Fechas</label>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="position: relative; flex: 1;">
                                                <i class="bi bi-calendar3" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
                                                <input type="date" name="fecha_inicio" style="width: 100%; padding: 0.625rem 0.75rem 0.625rem 2.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; color: #475569; outline: none;">
                                            </div>
                                            <span style="color: #94a3b8;">-</span>
                                            <div style="position: relative; flex: 1;">
                                                <i class="bi bi-calendar3" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
                                                <input type="date" name="fecha_fin" style="width: 100%; padding: 0.625rem 0.75rem 0.625rem 2.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; color: #475569; outline: none;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Categoría -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Categoría</label>
                                        <div style="position: relative;">
                                            <select name="categoria_id" style="width: 100%; padding: 0.625rem 2rem 0.625rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; color: #475569; outline: none; appearance: none; cursor: pointer;">
                                                <option value="">Todas las categorías</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="bi bi-chevron-down" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Estado</label>
                                        <div style="position: relative;">
                                            <select name="estado" style="width: 100%; padding: 0.625rem 2rem 0.625rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; color: #475569; outline: none; appearance: none; cursor: pointer;">
                                                <option value="">Cualquier estado</option>
                                                <option value="pendiente">Pendiente</option>
                                                <option value="en_proceso">En Proceso</option>
                                                <option value="resuelto">Resuelto</option>
                                            </select>
                                            <i class="bi bi-chevron-down" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                                        </div>
                                    </div>

                                    <!-- Prioridad -->
                                    <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                        <label style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Prioridad</label>
                                        <div style="position: relative;">
                                            <select name="prioridad" style="width: 100%; padding: 0.625rem 2rem 0.625rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.875rem; color: #475569; outline: none; appearance: none; cursor: pointer;">
                                                <option value="">Todas</option>
                                                <option value="alta">Alta</option>
                                                <option value="media">Media</option>
                                                <option value="baja">Baja</option>
                                            </select>
                                            <i class="bi bi-chevron-down" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Box -->
                                <div style="padding: 1rem; background: #eef2ff; border-radius: 0.75rem; border: 1px solid #c7d2fe; display: flex; align-items: center; gap: 1rem;">
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

                    <!-- COLUMNA DERECHA: ACCIONES -->
                    <div style="border-left: 1px solid #f1f5f9; padding-left: 2rem; display: flex; flex-direction: column; justify-content: flex-end;">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <button type="submit" form="formReporteSoportes" id="btnPDF" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 1rem 1.5rem; background: #6366f1; color: white; font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                                <i class="bi bi-file-earmark-pdf" style="font-size: 1.25rem;"></i>
                                Descargar PDF
                            </button>
                            
                            <button type="button" onclick="exportarExcel()" id="btnExcel" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 0.875rem 1.5rem; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 500; border-radius: 0.75rem; cursor: pointer;">
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
        </div>

        <!-- QUICK REPORTS GRID -->
        <div>
            <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; padding-left: 0.25rem;">Reportes Predefinidos</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                
                <!-- Card: Inventario -->
                <a href="<?= BASE_URL ?>reportes/inventario" target="_blank" style="text-decoration: none; background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; transition: all 0.2s; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="padding: 0.75rem; border-radius: 0.75rem; background: #d1fae5;">
                            <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #10b981;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #0f172a; font-size: 1.125rem; margin: 0 0 0.5rem 0;">Inventario General</h4>
                    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.5; margin: 0 0 1.5rem 0;">Valoración total, stock por almacén y alertas de stock bajo.</p>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #f8fafc;">
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
                <a href="<?= BASE_URL ?>reportes/mantenimientos" target="_blank" style="text-decoration: none; background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; transition: all 0.2s; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="padding: 0.75rem; border-radius: 0.75rem; background: #fef3c7;">
                            <i class="bi bi-tools" style="font-size: 1.5rem; color: #f59e0b;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #0f172a; font-size: 1.125rem; margin: 0 0 0.5rem 0;">Mantenimientos</h4>
                    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.5; margin: 0 0 1.5rem 0;">Historial de reparaciones, costos acumulados y proyecciones.</p>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #f8fafc;">
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
                <a href="<?= BASE_URL ?>reportes/rendimiento" style="text-decoration: none; background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; transition: all 0.2s; display: block;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="padding: 0.75rem; border-radius: 0.75rem; background: #dbeafe;">
                            <i class="bi bi-bar-chart-line" style="font-size: 1.5rem; color: #3b82f6;"></i>
                        </div>
                        <i class="bi bi-download" style="color: #cbd5e1; font-size: 1.25rem;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #0f172a; font-size: 1.125rem; margin: 0 0 0.5rem 0;">Rendimiento del Equipo</h4>
                    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.5; margin: 0 0 1.5rem 0;">KPIs de técnicos, tiempos de respuesta y cierre de tickets.</p>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #f8fafc;">
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

<style>
/* Hover effects for cards */
a[href*="reportes/"]:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: #c7d2fe !important;
}
a[href*="reportes/"]:hover h4 {
    color: #6366f1 !important;
}

/* Hover verde para botón Excel */
#btnExcel {
    transition: all 0.2s ease;
}
#btnExcel:hover {
    background: #d1fae5 !important;
    border-color: #10b981 !important;
    color: #059669 !important;
}
#btnExcel:hover i {
    color: #059669 !important;
}

/* Hover para botón PDF */
#btnPDF {
    transition: all 0.2s ease;
}
#btnPDF:hover {
    background: #4f46e5 !important;
    transform: translateY(-1px);
}
</style>

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
