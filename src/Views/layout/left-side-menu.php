<?php
$currentUri = $_GET['url'] ?? '/';
function isActive($link, $currentUri) {
    if ($link === '/') {
        return ($currentUri === '/') ? 'active' : '';
    }
    return (strpos($currentUri, $link) === 0) ? 'active' : '';
}
?>
<style>
/* Opaque Glass Sidebar (Corporate Style) */
.glass-sidebar {
    position: fixed;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    height: 95vh;
    width: 85px;
    background: var(--glass-opaque, rgba(255, 255, 255, 0.95));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: var(--glass-border, 1px solid rgba(226, 232, 240, 0.8));
    border-radius: 30px;
    box-shadow: var(--shadow-soft, 0 4px 6px -1px rgba(0, 0, 0, 0.05));
    z-index: 1050;
    transition: width 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), background 0.3s;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 30px 0;
}

.glass-sidebar:hover {
    width: 300px;
    background: #FFFFFF;
}

/* Brand / Logo Area */
.sidebar-brand {
    display: flex;
    align-items: center;
    padding: 0 28px;
    margin-bottom: 30px;
    height: 50px;
    overflow: hidden;
    white-space: nowrap;
}

.sidebar-brand-icon {
    min-width: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.8rem;
    color: var(--primary, #0F172A);
}

.sidebar-brand-text {
    margin-left: 15px;
    font-weight: 800;
    font-size: 1.4rem;
    color: var(--primary, #0F172A);
    opacity: 0;
    transition: opacity 0.3s ease 0.1s;
}

.glass-sidebar:hover .sidebar-brand-text {
    opacity: 1;
}

/* Navigation Links */
.sidebar-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Hide scrollbar */
.sidebar-nav::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 28px;
    color: var(--muted, #6C7A92);
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    position: relative;
}

.sidebar-link i {
    font-size: 1.4rem;
    min-width: 30px;
    text-align: center;
    transition: color 0.3s;
}

.sidebar-link span {
    margin-left: 15px;
    font-weight: 500;
    font-size: 1rem;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.glass-sidebar:hover .sidebar-link span {
    opacity: 1;
    transform: translateX(0);
}



/* Hover & Active States */
.sidebar-link:hover {
    color: var(--primary, #0F172A);
    background: rgba(0,0,0,0.02);
}

.sidebar-link:hover i {
    transform: scale(1.1);
    color: var(--accent, #3B82F6);
}

.sidebar-link.active {
    color: var(--primary, #0F172A) !important; /* Force dark color */
    background: rgba(59, 130, 246, 0.08);
}

.sidebar-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 70%;
    width: 4px;
    background: var(--accent, #3B82F6);
    border-radius: 0 4px 4px 0;
}

.sidebar-link.active i {
    color: var(--accent, #3B82F6);
}

/* Divider */
.sidebar-divider {
    height: 1px;
    background: rgba(0,0,0,0.06);
    margin: 10px 20px;
}

/* Section Labels (only visible on hover) */
.sidebar-label {
    padding: 10px 28px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted, #94A3B8);
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.3s;
    white-space: nowrap;
}

.glass-sidebar:hover .sidebar-label {
    opacity: 1;
    transition-delay: 0.1s;
}

/* Footer / User Profile */
.sidebar-footer {
    margin-top: auto;
    padding: 0; /* Remove padding to allow full centering */
    width: 100%;
    display: flex;
    justify-content: center;
}

.glass-sidebar:hover .sidebar-footer {
    padding: 0 20px; /* Restore padding on hover */
    display: block; /* Reset to block for normal flow */
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 15px;
    transition: background 0.3s;
    cursor: pointer;
    justify-content: center; /* Center by default (collapsed) */
    width: 100%; /* Full width to center properly */
}

.glass-sidebar:hover .user-profile {
    justify-content: flex-start; /* Left align when expanded */
    width: auto; /* Auto width when expanded */
}

.user-profile:hover {
    background: rgba(0,0,0,0.03);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary, #0F172A);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    flex-shrink: 0;
}

.user-info {
    margin-left: 12px;
    opacity: 0;
    transition: opacity 0.3s;
    white-space: nowrap;
    display: none; /* Hide completely when collapsed to avoid layout shifts */
}

.glass-sidebar:hover .user-info {
    opacity: 1;
    display: block; /* Show when expanded */
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text, #1F2430);
    display: block;
}

.user-role {
    font-size: 0.75rem;
    color: var(--muted, #6C7A92);
}
</style>

<div class="glass-sidebar" id="glassSidebar">
    
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-cloud-fill"></i>
        </div>
        <span class="sidebar-brand-text">SGEN</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        
        <!-- Dashboard (todos los roles) -->
        <a href="<?= BASE_URL ?>" class="sidebar-link <?= isActive('/', $currentUri) ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        <!-- Operaciones -->
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Operaciones</div>
        
        <!-- Tickets (todos los roles) -->
        <a href="<?= BASE_URL ?>soportes" class="sidebar-link <?= isActive('soportes', $currentUri) ?>">
            <i class="bi bi-ticket-perforated-fill"></i>
            <span>Tickets</span>
        </a>
        
        <!-- Inventario (admin y tecnico) -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
        <a href="<?= BASE_URL ?>inventario" class="sidebar-link <?= isActive('inventario', $currentUri) && strpos($currentUri, 'inventario/departamento') === false ? 'active' : '' ?>">
            <i class="bi bi-box-seam-fill"></i>
            <span>Inventario General</span>
        </a>
        <a href="<?= BASE_URL ?>inventario/departamento" class="sidebar-link <?= isActive('inventario/departamento', $currentUri) ? 'active' : '' ?>">
            <i class="bi bi-shop"></i>
            <span>Inv. por Depto</span>
        </a>
        <?php endif; ?>
        
        <!-- Equipos (admin, tecnico y consultor) -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor'])): ?>
        <a href="<?= BASE_URL ?>equipos" class="sidebar-link <?= isActive('equipos', $currentUri) ?>">
            <i class="bi bi-pc-display-horizontal"></i>
            <span>Equipos</span>
        </a>
        <?php endif; ?>
        
        <!-- Mantenimientos (solo admin y tecnico) -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
        <a href="<?= BASE_URL ?>mantenimientos" class="sidebar-link <?= isActive('mantenimientos', $currentUri) ?>">
            <i class="bi bi-tools"></i>
            <span>Mantenimientos</span>
        </a>
        <?php endif; ?>

        <!-- Organización (admin, tecnico y consultor) -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor'])): ?>
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Organización</div>

        <a href="<?= BASE_URL ?>empleados" class="sidebar-link <?= isActive('empleados', $currentUri) ?>">
            <i class="bi bi-people-fill"></i>
            <span>Empleados</span>
        </a>
        <a href="<?= BASE_URL ?>departamentos" class="sidebar-link <?= isActive('departamentos', $currentUri) ?>">
            <i class="bi bi-building-fill"></i>
            <span>Departamentos</span>
        </a>
        <?php endif; ?>



        <!-- Admin Section (solo admin) -->
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <div class="sidebar-divider"></div>
            <div class="sidebar-label">Admin</div>
            
            <a href="<?= BASE_URL ?>usuarios" class="sidebar-link <?= isActive('usuarios', $currentUri) ?>">
                <i class="bi bi-person-badge-fill"></i>
                <span>Usuarios</span>
            </a>
            <a href="<?= BASE_URL ?>reportes" class="sidebar-link <?= isActive('reportes', $currentUri) ?>">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Reportes</span>
            </a>
            <a href="<?= BASE_URL ?>configuracion" class="sidebar-link <?= isActive('configuracion', $currentUri) ?>">
                <i class="bi bi-gear-fill"></i>
                <span>Configuración</span>
            </a>
        <?php endif; ?>

        <!-- Logs y Bitácora (Admin, Consultor, Tecnico, Empleado) -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'consultor', 'tecnico', 'empleado'])): ?>
            <div class="sidebar-divider"></div>
            <div class="sidebar-label">Auditoría</div>

            <a href="<?= BASE_URL ?>logs" class="sidebar-link <?= isActive('logs', $currentUri) ?>">
                <i class="bi bi-file-text-fill"></i>
                <span>Logs Sesión</span>
            </a>
            
            <?php if (in_array($_SESSION['rol'], ['admin', 'consultor', 'tecnico'])): ?>
            <a href="<?= BASE_URL ?>bitacora" class="sidebar-link <?= isActive('bitacora', $currentUri) ?>">
                <i class="bi bi-clipboard-data-fill"></i>
                <span>Bitácora</span>
            </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Acerca de (todos los usuarios) -->
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>about" class="sidebar-link <?= isActive('about', $currentUri) ?>">
            <i class="bi bi-info-circle-fill"></i>
            <span>Acerca de</span>
        </a>

    </nav>

    <!-- User Profile (Bottom) -->
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['usuario'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></span>
                <span class="user-role"><?= ucfirst($_SESSION['rol'] ?? 'Invitado') ?></span>
            </div>
        </div>
    </div>

</div>
