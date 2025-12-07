<?php
// Vista de Historial de Movimientos para un Departamento
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-detail.css">

<div class="id-container">
    <div class="id-max-w-6xl">

        <!-- --- BREADCRUMB --- -->
        <div class="id-breadcrumb-row">
            <div class="id-breadcrumb">
                <a href="<?= BASE_URL ?>inventario/departamento/<?= $departamento->id ?>">
                    <i class="bi bi-arrow-left"></i>
                    Volver al Inventario
                </a>
                <span class="id-breadcrumb-sep">/</span>
                <span><?= htmlspecialchars($departamento->nombre) ?></span>
                <span class="id-breadcrumb-sep">/</span>
                <span class="id-breadcrumb-current">Historial de Movimientos</span>
            </div>
        </div>

        <!-- --- HEADER --- -->
        <div class="id-history-page-header">
            <div class="id-history-page-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="id-history-page-info">
                <h1>Historial de Movimientos</h1>
                <p>Departamento: <strong><?= htmlspecialchars($departamento->nombre) ?></strong></p>
            </div>
            <div class="id-history-page-stats">
                <span class="id-history-count"><?= count($movimientos) ?> registros</span>
            </div>
        </div>

        <!-- --- HISTORY TIMELINE --- -->
        <div class="id-history-section">
            <div class="id-history-container" style="max-height: none;">
                <?php if (empty($movimientos)): ?>
                    <div class="id-history-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No hay movimientos registrados para este departamento.</p>
                    </div>
                <?php else: ?>
                    <div class="id-timeline">
                        <?php foreach ($movimientos as $mov): 
                            // Determine icon and color based on movement type
                            $icon = 'bi-arrow-repeat';
                            $colorClass = 'blue';
                            $label = $mov->tipo_movimiento ?? 'MOVIMIENTO';
                            
                            switch ($mov->tipo_movimiento) {
                                case 'ENTRADA':
                                    $icon = 'bi-box-arrow-in-down';
                                    $colorClass = 'green';
                                    break;
                                case 'SALIDA':
                                    $icon = 'bi-box-arrow-up';
                                    $colorClass = 'red';
                                    break;
                                case 'TRANSFERENCIA':
                                    $icon = 'bi-arrow-left-right';
                                    $colorClass = 'blue';
                                    break;
                                case 'CONSUMO':
                                    $icon = 'bi-dash-circle';
                                    $colorClass = 'orange';
                                    break;
                                case 'BAJA':
                                    $icon = 'bi-trash';
                                    $colorClass = 'red';
                                    break;
                            }
                            
                            // Determine direction for transfers
                            $direction = '';
                            if ($mov->tipo_movimiento === 'TRANSFERENCIA') {
                                if ($mov->origen_departamento_id == $departamento->id) {
                                    $direction = '→ ' . ($mov->destino_nombre ?? 'Almacén Central');
                                } else {
                                    $direction = '← ' . ($mov->origen_nombre ?? 'Almacén Central');
                                }
                            }
                        ?>
                            <div class="id-timeline-item">
                                <div class="id-timeline-icon <?= $colorClass ?>">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div class="id-timeline-content">
                                    <div class="id-timeline-header">
                                        <span class="id-timeline-type <?= $colorClass ?>"><?= $label ?></span>
                                        <span class="id-timeline-date">
                                            <?= date('d/m/Y H:i', strtotime($mov->fecha)) ?>
                                        </span>
                                    </div>
                                    <div class="id-timeline-body">
                                        <strong><?= htmlspecialchars($mov->item_nombre ?? 'Item') ?></strong>
                                        <span class="id-timeline-code"><?= htmlspecialchars($mov->item_codigo ?? '') ?></span>
                                        <span class="id-timeline-qty">× <?= $mov->cantidad ?></span>
                                        <?php if ($direction): ?>
                                            <span class="id-timeline-direction"><?= $direction ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($mov->motivo)): ?>
                                        <div class="id-timeline-reason">
                                            <i class="bi bi-chat-left-text"></i>
                                            <?= htmlspecialchars($mov->motivo) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="id-timeline-user">
                                        <i class="bi bi-person"></i>
                                        <?= htmlspecialchars($mov->username ?? 'Sistema') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
