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

        <div style="background: white; border-radius: 1.25rem; border: 1px solid #e2e8f0; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-clock-history" style="font-size: 1.5rem; color: #4f46e5;"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Historial de Cambios</h1>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;"><?= htmlspecialchars($equipo->marca . ' ' . $equipo->modelo) ?> • <?= htmlspecialchars($equipo->codigo_inventario) ?></p>
                </div>
            </div>

            <?php if (empty($historial)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="width: 5rem; height: 5rem; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="bi bi-clock-history" style="font-size: 2rem; color: #cbd5e1;"></i>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem;">Sin historial registrado</h3>
                <p style="font-size: 0.875rem; color: #64748b; margin: 0;">No hay cambios registrados para este equipo aún.</p>
            </div>
            <?php else: ?>
            <div style="position: relative;">
                <!-- Timeline Line -->
                <div style="position: absolute; left: 1rem; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, #e2e8f0, #f8fafc);"></div>
                
                <?php foreach ($historial as $log): ?>
                <div style="position: relative; padding-left: 3rem; padding-bottom: 1.5rem;">
                    <!-- Timeline Dot -->
                    <div style="position: absolute; left: 0.5rem; top: 0.25rem; width: 1rem; height: 1rem; background: #4f46e5; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px #e0e7ff;"></div>
                    
                    <div style="background: #fafbfc; border: 1px solid #f1f5f9; border-radius: 0.75rem; padding: 1rem; transition: all 0.2s;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <p style="font-size: 0.875rem; font-weight: 600; color: #0f172a; margin: 0;">
                                <?= htmlspecialchars($log->accion) ?>
                            </p>
                            <span style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">
                                <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                            </span>
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-person" style="color: #94a3b8;"></i>
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
