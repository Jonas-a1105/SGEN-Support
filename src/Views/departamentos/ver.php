<?php
$badgeClass = 'bg-secondary';
if ($departamento->nombre) {
    $inicial = strtoupper($departamento->nombre[0]);
    $colores = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning'];
    $badgeClass = $colores[ord($inicial) % count($colores)];
}
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-building me-2"></i><?= htmlspecialchars($departamento->nombre) ?></h2>
        <div>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>departamentos/editar/<?= $departamento->id ?>" class="btn btn-warning me-2">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>departamentos" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Información del Departamento -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header <?= $badgeClass ?> text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong><br><?= htmlspecialchars($departamento->nombre) ?></p>
                <p><strong>Ubicación:</strong><br><?= htmlspecialchars($departamento->ubicacion ?? 'No especificada') ?></p>
            </div>
        </div>

        <!-- Asignar Equipo Form (Solo Admin) -->
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Asignar Equipo</h6>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>departamentos/asignarEquipo" method="POST">
                        <input type="hidden" name="departamento_id" value="<?= $departamento->id ?>">
                        <div class="mb-3">
                            <label for="identificador" class="form-label">Serial o Código</label>
                            <input type="text" class="form-control" id="identificador" name="identificador" required placeholder="Ej: 12345 o SN12345">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Asignar
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabs: Equipos y Empleados -->
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="deptTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="equipos-tab" data-bs-toggle="tab" data-bs-target="#equipos" type="button" role="tab">
                    <i class="bi bi-pc-display me-1"></i>Equipos (<?= count($equipos) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="empleados-tab" data-bs-toggle="tab" data-bs-target="#empleados" type="button" role="tab">
                    <i class="bi bi-people me-1"></i>Empleados (<?= count($empleados) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="deptTabsContent">
            <!-- Tab Equipos -->
            <div class="tab-pane fade show active" id="equipos" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Equipos Asignados</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($equipos)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">No hay equipos asignados a este departamento.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Tipo</th>
                                            <th>Marca/Modelo</th>
                                            <th>Estado</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($equipos as $e): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($e->codigo_inventario) ?></strong></td>
                                                <td><?= ucfirst($e->tipo) ?></td>
                                                <td><?= htmlspecialchars($e->marca ?? '') ?> <?= htmlspecialchars($e->modelo ?? '') ?></td>
                                                <td>
                                                    <?php 
                                                    $badgeClass = 'bg-secondary';
                                                    if ($e->estado == 'disponible') $badgeClass = 'bg-success';
                                                    elseif ($e->estado == 'en_uso') $badgeClass = 'bg-primary';
                                                    elseif ($e->estado == 'en_reparacion') $badgeClass = 'bg-warning text-dark';
                                                    elseif ($e->estado == 'fuera_de_servicio') $badgeClass = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $e->estado)) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= BASE_URL ?>equipos/ver/<?= $e->id ?>" class="btn btn-sm btn-outline-primary" title="Ver Equipo">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab Empleados -->
            <div class="tab-pane fade" id="empleados" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Empleados del Departamento</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($empleados)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-person-x display-4 text-muted"></i>
                                <p class="text-muted mt-2">No hay empleados asignados a este departamento.</p>
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalAsignarEmpleado">
                                        <i class="bi bi-person-plus me-1"></i> Asignar Empleado
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-end p-2 bg-light border-bottom">
                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarEmpleado">
                                        <i class="bi bi-person-plus me-1"></i> Asignar Empleado
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Email</th>
                                            <th>Usuario</th>
                                            <?php if ($_SESSION['rol'] === 'admin'): ?>
                                                <th class="text-end">Acción</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($empleados as $emp): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars("{$emp->nombre} {$emp->apellido}") ?></strong></td>
                                                <td><?= htmlspecialchars($emp->cedula) ?></td>
                                                <td><?= htmlspecialchars($emp->email) ?></td>
                                                <td>
                                                    <?php if ($emp->usuario_username): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            <?= htmlspecialchars($emp->usuario_username) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin usuario</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                                    <td class="text-end">
                                                        <a href="<?= BASE_URL ?>empleados/editar/<?= $emp->id ?>" class="btn btn-sm btn-outline-warning" title="Editar Empleado">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar Empleado -->
<div class="modal fade" id="modalAsignarEmpleado" tabindex="-1" aria-labelledby="modalAsignarEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarEmpleadoLabel">Asignar Empleado al Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Sección de Búsqueda -->
                <div id="search-section" class="mb-4">
                    <label class="form-label">Buscar Empleado por Cédula</label>
                    <div class="input-group mb-3">
                        <input type="text" id="search-cedula" class="form-control" placeholder="Ej: 12345678">
                        <button class="btn btn-primary" type="button" id="btn-search-employee">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                    
                    <!-- Mensajes y Acciones -->
                    <div id="search-message" class="alert d-none" role="alert"></div>
                    <div id="search-actions" class="d-none text-center">
                        <button type="button" id="btn-register-new" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Registrar Nuevo Empleado
                        </button>
                    </div>
                </div>

                <!-- Contenedor del Formulario (Oculto inicialmente) -->
                <div id="employee-form-container" style="display:none;">
                    <hr>
                    <h6 class="mb-3" id="form-title">Datos del Empleado</h6>
                    <?php 
                    // Variables para el formulario
                    if (!isset($departamentos)) $departamentos = [$departamento];
                    if (!isset($usuarios_disponibles)) $usuarios_disponibles = [];
                    include '../src/Views/empleados/formulario.php'; 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalAsignarEmpleado');
    if (!modal) return;

    // Elementos de Búsqueda
    const searchInput = document.getElementById('search-cedula');
    const searchBtn = document.getElementById('btn-search-employee');
    const searchMsg = document.getElementById('search-message');
    const searchActions = document.getElementById('search-actions');
    const btnRegister = document.getElementById('btn-register-new');
    const formContainer = document.getElementById('employee-form-container');

    // Elementos del Formulario
    const form = modal.querySelector('form');
    const cedulaInput = modal.querySelector('input[name="cedula"]');
    const nombreInput = modal.querySelector('input[name="nombre"]');
    const apellidoInput = modal.querySelector('input[name="apellido"]');
    const emailInput = modal.querySelector('input[name="email"]');
    const idInput = modal.querySelector('input[name="id"]');
    const deptoSelect = modal.querySelector('select[name="departamento_id"]');
    const usuarioSelect = modal.querySelector('select[name="usuario_id"]');

    // Configuración Inicial del Formulario
    if(deptoSelect) deptoSelect.value = "<?= $departamento->id ?>";

    // Crear inputs hidden necesarios
    if (!form.querySelector('input[name="id"]')) {
        const hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.name = 'id';
        form.prepend(hiddenId);
    }
    if (!form.querySelector('input[name="redirect_to"]')) {
        const redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect_to';
        redirectInput.value = window.location.href;
        form.prepend(redirectInput);
    }

    // Lógica de Búsqueda
    searchBtn.addEventListener('click', function() {
        const cedula = searchInput.value.trim();
        if (cedula.length < 3) {
            showMsg('Ingrese una cédula válida.', 'warning');
            return;
        }

        // Reset UI
        searchMsg.classList.add('d-none');
        searchActions.classList.add('d-none');
        formContainer.style.display = 'none';

        fetch(`<?= BASE_URL ?>empleados/apiBuscarPorCedula?cedula=${cedula}`)
            .then(response => {
                if (response.status === 404) throw new Error('not_found');
                if (!response.ok) throw new Error('error');
                return response.json();
            })
            .then(data => {
                // ENCONTRADO
                showMsg(`Empleado encontrado: ${data.nombre} ${data.apellido}`, 'success');
                fillForm(data);
                formContainer.style.display = 'block';
            })
            .catch(error => {
                if (error.message === 'not_found') {
                    // NO ENCONTRADO
                    showMsg('No hay empleados registrados en el sistema con esta cédula... ¿Desea registrar uno?', 'warning');
                    searchActions.classList.remove('d-none');
                } else {
                    showMsg('Error al buscar empleado.', 'danger');
                }
            });
    });

    // Lógica de Registrar Nuevo
    btnRegister.addEventListener('click', function() {
        const cedula = searchInput.value.trim();
        clearForm();
        cedulaInput.value = cedula; // Pre-llenar cédula
        formContainer.style.display = 'block';
        searchActions.classList.add('d-none'); // Ocultar botón de registro
    });

    // Funciones Auxiliares
    function showMsg(msg, type) {
        searchMsg.textContent = msg;
        searchMsg.className = `alert alert-${type}`;
        searchMsg.classList.remove('d-none');
    }

    function fillForm(data) {
        if (nombreInput) nombreInput.value = data.nombre;
        if (apellidoInput) apellidoInput.value = data.apellido;
        if (emailInput) emailInput.value = data.email;
        if (cedulaInput) cedulaInput.value = data.cedula;
        
        const hiddenId = form.querySelector('input[name="id"]');
        if (hiddenId) hiddenId.value = data.id;

        if (usuarioSelect && data.usuario_id) {
            let option = usuarioSelect.querySelector(`option[value="${data.usuario_id}"]`);
            if (!option) {
                option = document.createElement('option');
                option.value = data.usuario_id;
                option.text = data.usuario_username ? `${data.usuario_username} (Actual)` : 'Usuario Actual';
                usuarioSelect.add(option);
            }
            usuarioSelect.value = data.usuario_id;
        } else if (usuarioSelect) {
            usuarioSelect.value = "";
        }
    }

    function clearForm() {
        form.reset();
        const hiddenId = form.querySelector('input[name="id"]');
        if (hiddenId) hiddenId.value = '';
        if(deptoSelect) deptoSelect.value = "<?= $departamento->id ?>";
    }

    // Ajustes visuales al modal (remover sombras del card incluido)
    const card = modal.querySelector('.card');
    if(card) {
        card.classList.remove('shadow-sm');
        card.style.border = 'none';
        const header = card.querySelector('.card-header');
        if(header) header.style.display = 'none';
    }
    const backBtn = modal.querySelector('a.btn-secondary');
    if(backBtn) backBtn.style.display = 'none';
});
</script>
