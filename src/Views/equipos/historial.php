<?php
// src/Views/equipos/historial.php
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/equipo-detail.css?v=<?= time() ?>">

<div class="ed-container">
    <div class="ed-max-w">
        <div class="ed-top-nav">
            <a href="<?= BASE_URL ?>equipos/ver/<?= $equipo->id ?>" class="ed-back-btn">
                <i class="bi bi-arrow-left"></i>
                Volver al Equipo
            </a>
        </div>

        <div class="ed-historial-card">
            <div class="ed-historial-header">
                <div class="ed-historial-icon-box">
                    <i class="bi bi-clock-history" style="font-size: 1.5rem; color: #4f46e5;"></i>
                </div>
                <div>
                    <h1 class="ed-historial-title">Historial de Cambios</h1>
                    <p class="ed-historial-subtitle"><?= htmlspecialchars($equipo->marca . ' ' . $equipo->modelo) ?> • <?= htmlspecialchars($equipo->codigo_inventario) ?></p>
                </div>
            </div>

            <?php if (empty($historial)): ?>
            <div class="ed-empty-state-wrapper">
                <div class="ed-empty-state-icon">
                    <i class="bi bi-clock-history" style="font-size: 2rem; color: #cbd5e1;"></i>
                </div>
                <h3 class="ed-empty-state-title">Sin historial registrado</h3>
                <p class="ed-empty-state-text">No hay cambios registrados para este equipo aún.</p>
            </div>
            <?php else: ?>
            <div class="ed-timeline-container">
                <!-- Timeline Line -->
                <div class="ed-timeline-line"></div>
                
                <?php foreach ($historial as $log): ?>
                <div class="ed-timeline-item">
                    <!-- Timeline Dot -->
                    <div class="ed-timeline-dot"></div>
                    
                    <div class="ed-log-card">
                        <div class="ed-log-header">
                            <p class="ed-log-action">
                                <?= htmlspecialchars($log->accion) ?>
                            </p>
                            <span class="ed-log-date">
                                <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                            </span>
                        </div>
                        <p class="ed-log-user">
                            <i class="bi bi-person"></i>
                            <?= htmlspecialchars($log->username) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
