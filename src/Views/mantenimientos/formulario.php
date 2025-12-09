<?php
/**
 * Vista de Formulario de Mantenimiento - Diseño Moderno
 */
$isEdit = isset($mantenimiento);
$tipo = $isEdit ? $mantenimiento->tipo_mantenimiento : 'preventivo'; 
// Default to preventive if new

$tituloPagina = $isEdit ? 'Editar Mantenimiento' : 'Programar Mantenimiento';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/maintenance-form-modern.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="mf-container">

    <form action="<?= BASE_URL ?>mantenimientos/guardar" method="POST" id="mainForm">
        <?php if($isEdit): ?>
            <input type="hidden" name="id" value="<?= $mantenimiento->id ?>">
        <?php endif; ?>
        
        <!-- Hidden Fields managed by JS -->
        <input type="hidden" name="equipo_id" id="inputDeviceID" value="<?= $isEdit ? $mantenimiento->equipo_id : ($equipo_preseleccionado->id ?? '') ?>">
        <input type="hidden" name="tipo_mantenimiento" id="inputTypeMantenimiento" value="<?= $tipo ?>">
        <input type="hidden" name="checklist" id="inputChecklistJson" value='<?= $isEdit ? ($mantenimiento->checklist ?? '[]') : '[]' ?>'>
        
        <!-- Store initial checklist for JS to read -->
        <textarea id="initialChecklist" style="display:none;"><?= $isEdit ? ($mantenimiento->checklist ?? '[]') : '[]' ?></textarea>

        <!-- --- HEADER --- -->
        <header class="mf-header">
            <div class="mf-max-w-6xl mf-header-content">
                <div class="mf-breadcrumb">
                    <a href="<?= BASE_URL ?>mantenimientos">Mantenimientos</a>
                    <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                    <span><?= $isEdit ? 'Editar' : 'Programar' ?></span>
                </div>
                
                <div class="mf-header-main">
                    <div class="mf-title-row">
                        <div id="headerIconWrapper" class="mf-icon-wrapper <?= $tipo == 'correctivo' ? 'corrective' : 'preventive' ?>">
                            <i id="iconPreventive" class="bi bi-calendar-plus-fill fs-4" style="<?= $tipo == 'correctivo' ? 'display:none' : '' ?>"></i>
                            <i id="iconCorrective" class="bi bi-exclamation-triangle-fill fs-4" style="<?= $tipo == 'preventivo' ? 'display:none' : '' ?>"></i>
                        </div>
                        <div class="mf-title-text">
                            <h1 id="headerTitle"><?= $isEdit && $tipo == 'correctivo' ? 'Reporte Mantenimiento Correctivo' : ($tipo == 'correctivo' ? 'Reportar Mantenimiento Correctivo' : 'Programar Mantenimiento') ?></h1>
                            <p>Complete los detalles técnicos y logísticos para la orden de trabajo.</p>
                        </div>
                    </div>

                    <div class="mf-header-actions">
                        <a href="<?= BASE_URL ?>mantenimientos" class="mf-btn-cancel">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" id="btnSave" class="mf-btn-save <?= $tipo == 'correctivo' ? 'corrective' : 'preventive' ?>">
                            <i class="bi bi-save"></i> Guardar Orden
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Container -->
        <div class="mf-main-content-card">

        <!-- --- MAIN CONTENT --- -->
        <main class="mf-max-w-6xl mf-grid">
            
            <!-- LEFT COLUMN (2/3) -->
            <div>
                
                <!-- SECTION 1: ASSET SELECTION -->
                <section class="mf-section">
                    <div class="mf-section-header">
                        <i class="bi bi-pc-display text-muted"></i>
                        <h3 class="mf-section-title">1. Selección del Activo</h3>
                    </div>
                    <div class="mf-section-body">
                        
                        <!-- Search View -->
                        <div id="deviceSearchView" style="<?= ($isEdit || isset($equipo_preseleccionado)) ? 'display:none' : '' ?>">
                            <label class="mf-label">Buscar Equipo</label>
                            <select id="selectDevice" class="mf-select">
                                <option value="">Seleccione un equipo...</option>
                                <?php foreach($equipos as $eq): ?>
                                    <option value="<?= $eq->id ?>" 
                                            data-code="<?= $eq->codigo_inventario ?>"
                                            data-location="<?= $eq->departamento_nombre ?? '' ?>"
                                            data-type="<?= $eq->tipo ?>">
                                        <?= $eq->nombre ?? $eq->tipo . ' ' . $eq->marca . ' ' . $eq->modelo ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-muted small mt-2">Puede buscar por nombre, marca o código de inventario.</p>
                        </div>

                        <!-- Selected View -->
                        <div id="deviceSelectedView" style="<?= ($isEdit || isset($equipo_preseleccionado)) ? '' : 'display:none' ?>">
                            <div class="mf-device-selected">
                                <div class="mf-device-info">
                                    <?php 
                                        $eqNombre = 'Equipo Seleccionado';
                                        $eqMeta = 'Detalles';
                                        
                                        if($isEdit && isset($mantenimiento->equipo_nombre)) {
                                            $eqNombre = $mantenimiento->equipo_nombre;
                                            $eqMeta = ($mantenimiento->equipo_codigo ?? '') . ' • ' . ($mantenimiento->equipo_ubicacion ?? 'Ubicación desconocida');
                                        } elseif(isset($equipo_preseleccionado)) {
                                            $eqNombre = $equipo_preseleccionado->nombre ?? ($equipo_preseleccionado->tipo . ' ' . $equipo_preseleccionado->marca);
                                            $eqMeta = ($equipo_preseleccionado->codigo_inventario ?? '') . ' • ' . ($equipo_preseleccionado->departamento_nombre ?? '');
                                        }
                                    ?>
                                    <h4 id="selectedDeviceName"><?= $eqNombre ?></h4>
                                    <p id="selectedDeviceMeta"><i class="bi bi-geo-alt"></i> <?= $eqMeta ?></p>
                                </div>
                                <button type="button" class="mf-btn-change-device" onclick="clearDeviceSelection()">Cambiar</button>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- SECTION 2: JOB DETAILS -->
                <section class="mf-section">
                    <div class="mf-section-header">
                        <i class="bi bi-file-text text-muted"></i>
                        <h3 class="mf-section-title">2. Detalle del Trabajo</h3>
                    </div>
                    <div class="mf-section-body">
                        
                        <div class="row" style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <div class="col" style="flex: 1; min-width: 250px;">
                                <label class="mf-label">Tipo de Mantenimiento</label>
                                <div class="mf-toggle-group">
                                    <div id="btnTypePreventive" class="mf-toggle-btn <?= $tipo != 'correctivo' ? 'active preventive' : '' ?>">Preventivo</div>
                                    <div id="btnTypeCorrective" class="mf-toggle-btn <?= $tipo == 'correctivo' ? 'active corrective' : '' ?>">Correctivo</div>
                                </div>
                            </div>
                            <div class="col" style="flex: 1; min-width: 250px;">
                                <div class="mf-form-group">
                                    <label class="mf-label">Estado Inicial</label>
                                    <select name="estado" class="mf-select">
                                        <option value="pendiente" <?= ($isEdit && $mantenimiento->estado == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="programado" <?= ($isEdit && $mantenimiento->estado == 'programado') ? 'selected' : '' ?>>Programado</option>
                                        <option value="en_proceso" <?= ($isEdit && $mantenimiento->estado == 'en_proceso') ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="realizado" <?= ($isEdit && $mantenimiento->estado == 'realizado') ? 'selected' : '' ?>>Realizado / Completado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mf-form-group">
                            <label class="mf-label">Descripción General</label>
                            <textarea name="descripcion" class="mf-textarea" rows="4" placeholder="Describa el objetivo o la falla detectada..."><?= $isEdit ? $mantenimiento->descripcion : '' ?></textarea>
                        </div>

                        <!-- Checklist -->
                        <div class="mf-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="mf-label" style="margin: 0;"><i class="bi bi-check2-square text-primary"></i> Checklist de Tareas</label>
                                <span class="text-muted small" id="taskCount">0 tareas</span>
                            </div>
                            
                            <div class="mf-checklist-container">
                                <div id="checklistItems">
                                    <!-- Items rendered by JS -->
                                </div>
                                
                                <div class="mf-add-task-row" id="formAddTask">
                                    <i class="bi bi-plus-lg mf-add-task-icon"></i>
                                    <input type="text" id="inputAddTask" class="mf-input-add-task" placeholder="Añadir nueva tarea y presionar Enter...">
                                </div>
                            </div>
                        </div>

                        <div class="mf-form-group">
                            <label class="mf-label">Observaciones Adicionales</label>
                            <textarea name="observaciones" class="mf-textarea" rows="2" placeholder="Notas internas..."><?= $isEdit ? $mantenimiento->observaciones : '' ?></textarea>
                        </div>

                    </div>
                </section>

            </div>

            <!-- RIGHT COLUMN (1/3) -->
            <div>
                
                <!-- SECTION 3: PLANNING -->
                <section class="mf-section">
                    <div class="mf-section-header">
                        <i class="bi bi-clock text-muted"></i>
                        <h3 class="mf-section-title">Planificación</h3>
                    </div>
                    <div class="mf-section-body">
                        
                        <div class="mf-form-group">
                            <label class="mf-label">Fecha Programada</label>
                            <input type="date" name="fecha" required class="mf-input" value="<?= $isEdit ? date('Y-m-d', strtotime($mantenimiento->fecha)) : date('Y-m-d') ?>">
                        </div>

                        <div id="recurrenceContainer" style="<?= $tipo == 'correctivo' ? 'display:none' : '' ?>">
                            <div class="mf-form-group">
                                <label class="mf-label">Recurrencia (Próxima Fecha Auto.)</label>
                                <select name="frecuencia" class="mf-select">
                                    <option value="unica" <?= ($isEdit && $mantenimiento->frecuencia == 'unica') ? 'selected' : '' ?>>Una sola vez</option>
                                    <option value="semanal" <?= ($isEdit && $mantenimiento->frecuencia == 'semanal') ? 'selected' : '' ?>>Semanal</option>
                                    <option value="mensual" <?= ($isEdit && $mantenimiento->frecuencia == 'mensual') ? 'selected' : '' ?>>Mensual</option>
                                    <option value="trimestral" <?= ($isEdit && $mantenimiento->frecuencia == 'trimestral') ? 'selected' : '' ?>>Trimestral</option>
                                    <option value="semestral" <?= ($isEdit && $mantenimiento->frecuencia == 'semestral') ? 'selected' : '' ?>>Semestral</option>
                                    <option value="anual" <?= ($isEdit && $mantenimiento->frecuencia == 'anual') ? 'selected' : '' ?>>Anual</option>
                                </select>
                            </div>
                            
                            <div class="mf-form-group">
                                <label class="mf-label">Próxima Fecha (Estimada)</label>
                                <input type="date" name="proxima_fecha" class="mf-input" value="<?= $isEdit ? ($mantenimiento->proxima_fecha ?? '') : '' ?>">
                            </div>
                        </div>

                        <hr style="border: 0; border-top: 1px solid var(--mf-slate-200); margin: 1.5rem 0;">

                        <div class="mf-section-header" style="background: transparent; padding: 0; margin-bottom: 1rem; border: none;">
                            <i class="bi bi-box-seam text-muted"></i>
                            <h3 class="mf-section-title">Recursos</h3>
                        </div>

                        <div class="mf-form-group">
                            <label class="mf-label">Asignado a (Técnico)</label>
                            <!-- Assuming only admin can change this or logic is handled in backend defaults -->
                             <?php if($_SESSION['rol'] === 'admin'): ?>
                                <select name="tecnico_id" class="mf-select">
                                    <!-- Populate via controller if available, otherwise just current user or 'admin' placeholder -->
                                    <option value="<?= $_SESSION['user_id'] ?>"><?= $_SESSION['username'] ?> (Yo)</option>
                                    <!-- If $tecnicos passed from controller, loop here. -->
                                </select>
                             <?php else: ?>
                                <input type="text" class="mf-input" value="<?= $_SESSION['username'] ?>" readonly disabled>
                             <?php endif; ?>
                        </div>

                        <div class="mf-form-group">
                            <label class="mf-label">Costo Estimado ($)</label>
                            <input type="number" step="0.01" name="costo" class="mf-input" placeholder="0.00" value="<?= $isEdit ? $mantenimiento->costo : '' ?>">
                        </div>

                    </div>
                </section>

            </div>

        </main>

        </div><!-- End mf-main-content-card -->

        <!-- Mobile Footer -->
        <div class="mf-mobile-footer">
            <a href="<?= BASE_URL ?>mantenimientos" class="mf-mob-btn mf-mob-cancel">Cancelar</a>
            <button type="submit" class="mf-mob-btn mf-mob-save">Guardar</button>
        </div>

    </form>

    <script src="<?= BASE_URL ?>js/maintenance-form.js?v=<?= time() ?>"></script>
</body>
</html>
