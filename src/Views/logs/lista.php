<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">Auditoría de Accesos</h2>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Mostrando los últimos 200 registros de inicio y cierre de sesión.
    </div>

    <div class="table-responsive">
        <table id="logs-table" class="table table-striped table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th data-priority="0">Usuario (Username)</th>
                    <th data-priority="1" class="d-none d-md-table-cell">Fecha Inicio de Sesión</th>
                    <th data-priority="2" class="d-none d-md-table-cell">Fecha Fin de Sesión</th>
                    <th data-priority="3">Duración</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log->username) ?> (ID: <?= $log->usuario_id ?>)</td>
                        <td class="d-none d-md-table-cell"><?= $log->fecha_inicio ?></td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($log->fecha_fin): ?>
                                <?= $log->fecha_fin ?>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-circle-fill me-1"></i>
                                    Sesión Activa
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // Cálculo de la duración (lógica PHP sin cambios)
                            if ($log->fecha_fin) {
                                try {
                                    $inicio = new \DateTime($log->fecha_inicio);
                                    $fin = new \DateTime($log->fecha_fin);
                                    $intervalo = $inicio->diff($fin);
                                    echo $intervalo->format('%h h, %i min, %s seg');
                                } catch (Exception $e) {
                                    echo 'Error cálculo';
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>