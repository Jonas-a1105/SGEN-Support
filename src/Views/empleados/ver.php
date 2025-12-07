<?php
/**
 * Vista de Perfil de Empleado
 * Muestra información detallada del empleado
 */
?>

<style>
.empleado-profile {
    max-width: 800px;
    margin: 0 auto;
}

.profile-header {
    background: white;
    color: #1f2937;
    padding: 2rem;
    border-radius: 16px 16px 0 0;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    border: 1px solid #e5e7eb;
    border-bottom: none;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
    border: 3px solid rgba(255,255,255,0.3);
}

.profile-info h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
}

.profile-info p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.875rem;
}

.profile-body {
    background: white;
    padding: 2rem;
    border-radius: 0 0 16px 16px;
    border: 1px solid #e5e7eb;
    border-top: none;
}

.profile-section {
    margin-bottom: 1.5rem;
}

.profile-section:last-child {
    margin-bottom: 0;
}

.profile-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.profile-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
}

.profile-item i {
    width: 20px;
    color: #3b82f6;
}

.profile-item-label {
    font-size: 0.75rem;
    color: #6b7280;
    min-width: 100px;
}

.profile-item-value {
    font-weight: 500;
    color: #111827;
}

.profile-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 1rem;
}
</style>

<div class="empleado-profile">
    
    <!-- Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper(substr($empleado->nombre ?? 'E', 0, 1)) ?>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($empleado->nombre . ' ' . ($empleado->apellido ?? '')) ?></h1>
            <p>
                <i class="bi bi-building me-1"></i>
                <?= htmlspecialchars($empleado->departamento_nombre ?? 'Sin departamento') ?>
            </p>
        </div>
    </div>

    <!-- Body -->
    <div class="profile-body">
        
        <!-- Información Personal -->
        <div class="profile-section">
            <h3 class="profile-section-title">Información Personal</h3>
            
            <div class="profile-item">
                <i class="bi bi-person-vcard"></i>
                <span class="profile-item-label">Cédula:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->cedula ?? 'No registrada') ?></span>
            </div>
            
            <div class="profile-item">
                <i class="bi bi-envelope"></i>
                <span class="profile-item-label">Email:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->email ?? 'No registrado') ?></span>
            </div>
            
            <?php if (!empty($empleado->telefono)): ?>
            <div class="profile-item">
                <i class="bi bi-telephone"></i>
                <span class="profile-item-label">Teléfono:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->telefono) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($empleado->cargo)): ?>
            <div class="profile-item">
                <i class="bi bi-briefcase"></i>
                <span class="profile-item-label">Cargo:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->cargo) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Información del Sistema -->
        <div class="profile-section">
            <h3 class="profile-section-title">Información del Sistema</h3>
            
            <div class="profile-item">
                <i class="bi bi-building"></i>
                <span class="profile-item-label">Departamento:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->departamento_nombre ?? 'Sin asignar') ?></span>
            </div>
            
            <div class="profile-item">
                <i class="bi bi-person-circle"></i>
                <span class="profile-item-label">Usuario:</span>
                <span class="profile-item-value"><?= htmlspecialchars($empleado->usuario_username ?? 'Sin usuario') ?></span>
            </div>
        </div>

        <!-- Acciones -->
        <div class="profile-actions">
            <a href="<?= BASE_URL ?>soportes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Tickets
            </a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>empleados/editar/<?= $empleado->id ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            <?php endif; ?>
        </div>
        
    </div>
    
</div>
