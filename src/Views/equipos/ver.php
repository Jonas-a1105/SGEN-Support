<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $titulo ?></h2>
    <div>
    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>equipos/editar/<?= $equipo->id ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
    <?php endif; ?>
        <a href="<?= BASE_URL ?>equipos" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Columna Izquierda: Información Principal -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <div class="display-1 text-muted mb-3">
                    <i class="bi bi-pc-display"></i>
                </div>
                <h4 class="card-title"><?= htmlspecialchars(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($equipo->codigo_inventario ?? 'N/A') ?></p>
                <span class="badge bg-<?= $equipo->estado == 'disponible' ? 'success' : ($equipo->estado == 'en_uso' ? 'primary' : 'warning') ?> fs-6">
                    <?= ucfirst(str_replace('_', ' ', $equipo->estado)) ?>
                </span>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <strong>Serial:</strong>
                    <span><?= htmlspecialchars($equipo->numero_serie ?? 'N/A') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <strong>Tipo:</strong>
                    <span><?= ucfirst($equipo->tipo) ?></span>
                </li>
            </ul>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Ubicación</h5>
            </div>
            <div class="card-body">
                <p><strong>Departamento:</strong> <br> 
                    <?= $equipo->departamento_nombre ?? 'No Asignado' ?>
                </p>
                <p><strong>Usuario:</strong> <br> 
                    <?= $equipo->empleado_nombre ?? 'No Asignado' ?>
                </p>
                <p><strong>Ubicación Física:</strong> <br> 
                    <?= htmlspecialchars($equipo->ubicacion_fisica ?? 'No especificada') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Detalles Técnicos y Adquisición -->
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="verEquipoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tecnica-tab" data-bs-toggle="tab" data-bs-target="#tecnica" type="button" role="tab">Especificaciones</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="adquisicion-tab" data-bs-toggle="tab" data-bs-target="#adquisicion" type="button" role="tab">Adquisición</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="soportes-tab" data-bs-toggle="tab" data-bs-target="#soportes" type="button" role="tab">
                    Historial Soportes
                    <?php if (!empty($soportes)): ?>
                        <span class="badge bg-danger ms-1"><?= count($soportes) ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mantenimiento-tab" data-bs-toggle="tab" data-bs-target="#mantenimiento" type="button" role="tab">Historial Mantenimiento</button>
            </li>
        </ul>

        <div class="tab-content" id="verEquipoTabsContent">
            <!-- Especificaciones -->
            <div class="tab-pane fade show active" id="tecnica" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Procesador</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->procesador ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Memoria RAM</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->memoria_ram ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Almacenamiento</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->almacenamiento ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Sistema Operativo</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->sistema_operativo ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Dirección IP</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->direccion_ip ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Driver</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->driver ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Toner</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->toner ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adquisición -->
            <div class="tab-pane fade" id="adquisicion" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Fecha de Compra</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->fecha_compra ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Valor de Compra</label>
                                <p class="fw-bold"><?= $equipo->valor_compra ? '$' . number_format($equipo->valor_compra, 2) : 'N/A' ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Proveedor</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->proveedor ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">RIF Proveedor</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->proveedor_rif ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Garantía Hasta</label>
                                <p class="fw-bold"><?= htmlspecialchars($equipo->garantia ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Soportes -->
            <div class="tab-pane fade" id="soportes" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Fecha Reporte</th>
                                        <th>Estado</th>
                                        <th>Descripción</th>
                                        <th>Técnico</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($soportes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-3">No hay tickets de soporte registrados para este equipo.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($soportes as $s): ?>
                                            <?php
                                            $estadoClass = '';
                                            switch ($s->estado) {
                                                case 'pendiente':
                                                    $estadoClass = 'bg-warning text-dark';
                                                    break;
                                                case 'en_proceso':
                                                    $estadoClass = 'bg-info text-dark';
                                                    break;
                                                case 'en_espera':
                                                    $estadoClass = 'bg-secondary';
                                                    break;
                                                case 'resuelto':
                                                    $estadoClass = 'bg-success';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td><strong>#<?= $s->id ?></strong></td>
                                                <td><?= date('d/m/Y H:i', strtotime($s->fecha)) ?></td>
                                                <td><span class="badge <?= $estadoClass ?>"><?= ucfirst(str_replace('_', ' ', $s->estado)) ?></span></td>
                                                <td><?= htmlspecialchars(substr($s->descripcion, 0, 50)) ?><?= strlen($s->descripcion) > 50 ? '...' : '' ?></td>
                                                <td><?= htmlspecialchars($s->tecnico_asignado ?? 'Sin asignar') ?></td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>soportes/ver/<?= $s->id ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 text-end bg-light">
                            <a href="<?= BASE_URL ?>soportes/crear?equipo_id=<?= $equipo->id ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Crear Nuevo Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mantenimiento -->
            <div class="tab-pane fade" id="mantenimiento" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Realizado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($mantenimientos)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3">No hay mantenimientos registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($mantenimientos as $m): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($m->fecha)) ?></td>
                                                <td><?= ucfirst($m->tipo_mantenimiento) ?></td>
                                                <td><?= htmlspecialchars(substr($m->descripcion, 0, 50)) ?>...</td>
                                                <td><?= htmlspecialchars($m->realizado_por) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 text-end">
                            <a href="<?= BASE_URL ?>mantenimientos/crear?equipo_id=<?= $equipo->id ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
