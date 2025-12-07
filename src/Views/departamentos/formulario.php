<?php
/**
 * Vista de Formulario de Departamentos - Moderno
 * Diseño basado en React Component 'EditDepartment'
 */

// Helpers
$isEdit = isset($departamento) && $departamento !== null;
$action_title = $isEdit ? 'Editar' : 'Crear Nuevo';

// Valores por defecto
$val_nombre = $isEdit ? $departamento->nombre : '';
$val_ubicacion = $isEdit ? ($departamento->ubicacion ?? '') : '';
$val_descripcion = $isEdit ? ($departamento->descripcion ?? '') : '';
$val_jefe_id = $isEdit ? ($departamento->jefe_area_id ?? '') : '';
$val_jefe_nombre = $isEdit ? ($departamento->jefe_area_nombre ?? '') : '';

// Preparar datos de empleados para JS
$employeesJson = isset($empleados) ? json_encode(array_map(function($e) {
    return [
        'id' => $e->id,
        'nombre' => $e->nombre,
        'apellido' => $e->apellido,
        'cargo' => $e->cargo ?? 'Empleado',
        'email' => $e->email
    ];
}, $empleados)) : '[]';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?? 'Gestión de Departamentos' ?></title>
    <!-- CSS Moderno -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/department-form-modern.css?v=<?= time() ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dept-form-wrapper">

    <div class="dept-form-container">
        
        <!-- HEADER -->
        <div class="dept-header">
            <div>
                <a href="<?= BASE_URL ?>departamentos" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Volver a Departamentos
                </a>
                <h1 class="dept-title">
                    <div class="dept-title-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <?= $action_title ?> Departamento
                </h1>
                <p class="dept-subtitle">
                    <?= $isEdit ? 'Actualiza la información estructural y de liderazgo.' : 'Establece una nueva unidad organizativa.' ?>
                </p>
            </div>
            
            <?php if ($isEdit): ?>
            <div class="status-badge">
                <i class="bi bi-check-circle-fill"></i> Activo
            </div>
            <?php endif; ?>
        </div>

        <!-- MAIN CARD -->
        <div class="dept-card" id="departmentForm">
            <form action="<?= BASE_URL ?>departamentos/guardar" method="POST" class="dept-form-body">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $departamento->id ?>">
                <?php endif; ?>

                <!-- SECCIÓN 1: Identidad -->
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="bi bi-briefcase"></i> Información General
                    </h3>
                </div>

                <div class="form-split">
                    <!-- Left Column -->
                    <div>
                        <div class="form-group">
                            <label class="form-label">
                                Nombre del Departamento <span class="req-star">*</span>
                            </label>
                            <input type="text" name="nombre" class="form-input" 
                                   placeholder="Ej: Recursos Humanos" 
                                   value="<?= htmlspecialchars($val_nombre) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-input form-textarea" rows="4" 
                                      placeholder="Breve descripción de funciones..."><?= htmlspecialchars($val_descripcion) ?></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="form-group">
                            <label class="form-label">Ubicación Física</label>
                            <div class="input-wrapper">
                                <i class="bi bi-geo-alt input-icon"></i>
                                <input type="text" name="ubicacion" class="form-input has-icon" 
                                       placeholder="Ej: Edificio Central, Piso 2"
                                       value="<?= htmlspecialchars($val_ubicacion) ?>">
                            </div>
                            
                            <!-- Fake Map Preview -->
                            <div class="map-preview">
                                <div>
                                    <i class="bi bi-map"></i> Vista Previa de Mapa
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: Liderazgo -->
                <div class="section-header" style="margin-top: 2rem;">
                    <h3 class="section-title">
                        <i class="bi bi-person-badge"></i> Liderazgo y Responsable
                    </h3>
                </div>

                <div class="manager-card">
                    <div class="manager-row">
                        <!-- Display Area (Handled by JS) -->
                        <div id="managerDisplay" style="flex: 1;">
                            <!-- JS populates this -->
                        </div>

                        <!-- Action Button -->
                        <div>
                            <button type="button" id="btnToggleSearch" class="btn-toggle-search">
                                Asignar Jefe <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Form Submission -->
                    <input type="hidden" name="jefe_area_id" id="inputManagerId" value="<?= $val_jefe_id ?>">
                    <!-- Fallback name input if you want to support non-registered managers, 
                         though the react design implied selection. We keep the field just in case controller expects it. -->
                    <?php if(!empty($val_jefe_nombre) && empty($val_jefe_id)): ?>
                        <!-- If manually entered name exists but no ID -->
                         <input type="hidden" name="jefe_area_nombre" id="inputManagerName" value="<?= htmlspecialchars($val_jefe_nombre) ?>">
                    <?php else: ?>
                         <input type="hidden" name="jefe_area_nombre" id="inputManagerName">
                    <?php endif; ?>

                    <!-- Search Area (Hidden by default) -->
                    <div id="managerSearchArea" class="manager-search-area" style="display: none;">
                        <div class="search-title">Seleccionar Empleado</div>
                        <div id="employeeGrid" class="employee-grid">
                            <!-- JS populates this -->
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="form-footer">
                    <a href="<?= BASE_URL ?>departamentos" class="btn-footer-cancel">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-footer-save">
                        <i class="bi bi-check-lg"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Init Script -->
    <script src="<?= BASE_URL ?>js/department-form.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const employees = <?= $employeesJson ?>;
            const currentManagerId = '<?= $val_jefe_id ?>';
            
            DepartmentForm.init(employees, currentManagerId);
        });
    </script>
</body>
</html>