<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Listado de Usuarios</h2>
        <a class="btn btn-primary shadow-sm" href="<?= BASE_URL ?>usuarios/crear" title="Crear Nuevo Usuario">
            <i class="bi bi-person-plus-fill me-1"></i>
            Crear Nuevo Usuario
        </a>
    </div>

    <div class="table-responsive">
        <table id="usuarios-table" class="table table-striped table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th data-priority="0" class="d-none d-md-table-cell">ID</th>
                    <th data-priority="1">Usuario (Username)</th>
                    <th data-priority="2" class="d-none d-md-table-cell">Nivel de Acceso</th>
                    <th data-priority="3" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <?php
                        // --- Lógica para Badges de Rol ---
                        $rol_class = '';
                        $rol_texto = ucfirst($u->rol);
                        switch ($u->rol) {
                            case 'admin':
                                $rol_class = 'badge bg-primary';
                                break;
                            case 'tecnico':
                                $rol_class = 'badge bg-warning text-dark';
                                break;
                            case 'consultor':
                                $rol_class = 'badge bg-secondary';
                                break;
                        }
                    ?>
                    <tr>
                        <td class="d-none d-md-table-cell"><?= $u->id ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($u->username) ?></td>
                        <td class="d-none d-md-table-cell">
                            <span class="<?= $rol_class ?>"><?= $rol_texto ?></span>
                        </td>
                        <td class="text-end">
                            <a title="Editar" href="<?= BASE_URL ?>usuarios/editar/<?= $u->id ?>" class="text-warning me-2">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <?php if ($u->id != 1): ?>
                               <a title="Eliminar" 
                                  href="<?= BASE_URL ?>usuarios/eliminar/<?= $u->id ?>" 
                                  class="text-danger btn-delete" 
                                  data-name="<?= htmlspecialchars($u->username) ?>">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>