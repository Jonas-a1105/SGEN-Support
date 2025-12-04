<?php
// Determinar si es una creación o una edición
$es_edicion = ($soporte !== null && $soporte->id);
$valor_id = $es_edicion ? $soporte->id : '';
$valor_equipo_id = $es_edicion ? $soporte->equipo_id : '';
$valor_descripcion = $es_edicion ? $soporte->descripcion : '';
?>

<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark"><?= $es_edicion ? 'Editar' : 'Crear' ?> Ticket de Soporte</h2>
        <a href="<?= BASE_URL ?>soportes" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="<?= BASE_URL ?>soportes/guardar" method="POST">
        
        <input type="hidden" name="id" value="<?= htmlspecialchars($valor_id) ?>">

        <div class="row g-3">
            
            <!-- Búsqueda de Equipo -->
            <div class="col-12">
                <label class="form-label">Buscar Equipo (Serial, Código o Cédula)</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="busqueda_equipo" placeholder="Ingrese Serial, Código o Cédula del Empleado">
                    <button class="btn btn-outline-primary" type="button" id="btn_buscar_equipo">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div id="mensaje_busqueda" class="form-text text-danger d-none">Equipo no encontrado.</div>
            </div>

            <!-- Datos del Equipo (Readonly) -->
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control bg-light" id="equipo_info" readonly placeholder="Equipo" value="<?= $es_edicion ? 'Equipo Seleccionado' : '' ?>">
                    <label for="equipo_info">Equipo Seleccionado</label>
                </div>
                <input type="hidden" id="equipo_id" name="equipo_id" value="<?= htmlspecialchars($valor_equipo_id) ?>" required>
            </div>

            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control bg-light" id="departamento_nombre" readonly placeholder="Departamento">
                    <label for="departamento_nombre">Departamento</label>
                </div>
                <input type="hidden" id="departamento_id" name="departamento_id" value="">
            </div>

            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <select class="form-select" id="prioridad" name="prioridad" required>
                        <option value="baja" <?= (isset($soporte) && $soporte->prioridad == 'baja') ? 'selected' : '' ?>>🟢 Baja - Puede esperar</option>
                        <option value="media" <?= (!isset($soporte) || $soporte->prioridad == 'media') ? 'selected' : '' ?>>🟡 Media - Normal</option>
                        <option value="alta" <?= (isset($soporte) && $soporte->prioridad == 'alta') ? 'selected' : '' ?>>🔴 Alta - Urgente</option>
                    </select>
                    <label for="prioridad">Prioridad</label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <select class="form-select" id="categoria_id" name="categoria_id">
                        <option value="">-- Seleccionar Categoría --</option>
                        <?php foreach ($categoria_list as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (isset($soporte) && $soporte->categoria_id == $cat->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="categoria_id">Categoría del Problema</label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-floating mb-3">
                    <textarea id="descripcion" name="descripcion" class="form-control" style="height: 150px" placeholder="Descripción" required><?= htmlspecialchars($valor_descripcion) ?></textarea>
                    <label for="descripcion">Descripción del Problema</label>
                </div>
            </div>

        </div>
        
        <hr class="my-4">
        
        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>soportes" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i>
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>
                <?= $es_edicion ? 'Actualizar' : 'Guardar' ?> Ticket
            </button>
        </div>
    </form>
</div>

<!-- Modal para seleccionar equipo cuando hay múltiples -->
<div class="modal fade" id="modalSeleccionEquipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">El empleado tiene múltiples equipos asignados. Seleccione uno:</p>
                <div id="lista_equipos" class="list-group">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBuscar = document.getElementById('btn_buscar_equipo');
    const inputBusqueda = document.getElementById('busqueda_equipo');
    const mensajeBusqueda = document.getElementById('mensaje_busqueda');
    
    const inputEquipoInfo = document.getElementById('equipo_info');
    const inputEquipoId = document.getElementById('equipo_id');
    const inputDeptoNombre = document.getElementById('departamento_nombre');
    const inputDeptoId = document.getElementById('departamento_id');

    function llenarCamposEquipo(data) {
        inputEquipoId.value = data.id;
        inputEquipoInfo.value = `${data.tipo.toUpperCase()} - ${data.marca} ${data.modelo} (S/N: ${data.serial})`;
        
        if (data.departamento_nombre) {
            inputDeptoNombre.value = data.departamento_nombre;
            inputDeptoId.value = data.departamento_id;
        } else {
            inputDeptoNombre.value = 'Sin Asignar';
            inputDeptoId.value = '';
        }
    }

    function limpiarCamposEquipo() {
        inputEquipoId.value = '';
        inputEquipoInfo.value = '';
        inputDeptoNombre.value = '';
    }

    function mostrarModalSeleccion(equipos) {
        const listaEquipos = document.getElementById('lista_equipos');
        listaEquipos.innerHTML = '';
        
        equipos.forEach(equipo => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';
            item.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${equipo.tipo.toUpperCase()} - ${equipo.marca} ${equipo.modelo}</h6>
                    <small class="text-muted">${equipo.codigo_inventario}</small>
                </div>
                <p class="mb-1"><strong>Serial:</strong> ${equipo.serial}</p>
                <small><strong>Departamento:</strong> ${equipo.departamento_nombre || 'Sin Asignar'}</small>
            `;
            item.addEventListener('click', function() {
                llenarCamposEquipo(equipo);
                const modalEl = document.getElementById('modalSeleccionEquipo');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
            listaEquipos.appendChild(item);
        });
        
        const modalEl = document.getElementById('modalSeleccionEquipo');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    btnBuscar.addEventListener('click', function() {
        const query = inputBusqueda.value.trim();
        if (!query) return;

        fetch('<?= BASE_URL ?>equipos/apiBuscar?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    mensajeBusqueda.classList.add('d-none');
                    
                    // Verificar si hay múltiples equipos
                    if (data.multiple) {
                        mostrarModalSeleccion(data.equipos);
                    } else {
                        // Un solo equipo - llenar directamente
                        llenarCamposEquipo(data);
                    }
                } else {
                    mensajeBusqueda.classList.remove('d-none');
                    if (data.error) {
                        mensajeBusqueda.textContent = data.error;
                    } else {
                        mensajeBusqueda.textContent = 'Equipo no encontrado.';
                    }
                    limpiarCamposEquipo();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mensajeBusqueda.textContent = 'Error al buscar equipo.';
                mensajeBusqueda.classList.remove('d-none');
            });
    });

    // Si es edición, cargar datos iniciales (opcional, si tuviéramos una API para getById)
    // Por ahora, en edición, el usuario ve "Equipo Seleccionado" pero si quiere cambiarlo debe buscar.
});
</script>