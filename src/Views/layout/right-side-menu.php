<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="offcanvasRightMenu" aria-labelledby="offcanvasRightMenuLabel">
    
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasRightMenuLabel">
            <i class="bi bi-bell-fill"></i> Notificaciones
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-0">
        
        <div class="list-group list-group-flush">
            
            <?php if (empty($notifications_list)): ?>
                <div class="list-group-item text-center text-muted p-3">
                    No tienes notificaciones nuevas.
                </div>
            <?php else: ?>
                <?php foreach ($notifications_list as $notif): ?>
                    <?php 
                        $item_class = $notif->leido ? 'text-muted' : 'list-group-item-light fw-bold';
                        $icon = $notif->leido ? 'bi-check-all' : 'bi-dot fs-1';
                    ?>
                    <a href="<?= BASE_URL ?>notificaciones/leer/<?= $notif->id ?>" class="list-group-item list-group-item-action <?= $item_class ?> py-2">
                        <div class="d-flex w-100 align-items-center">
                            <i class="bi <?= $icon ?> me-2"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0 small"><?= htmlspecialchars($notif->mensaje) ?></p>
                                <small class="opacity-75"><?= date('d/m/Y h:i A', strtotime($notif->created_at)) ?></small>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
        </div>
        
    </div>

    <?php if (isset($unread_count) && $unread_count > 0): ?>
    <div class="offcanvas-footer p-3 border-top bg-light">
        <a href="<?= BASE_URL ?>notificaciones/marcar-todas-leidas" class="btn btn-sm btn-outline-primary w-100">
            Marcar todas como leídas
        </a>
    </div>
    <?php endif; ?>

</div>