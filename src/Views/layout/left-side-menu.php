<?php
$currentUri = $_GET['url'] ?? '/';
function isActive($link, $currentUri) {
    if ($link === '/') {
        return ($currentUri === '/') ? 'active' : '';
    }
    return (strpos($currentUri, $link) === 0) ? 'active' : '';
}
?>

<aside class="ms-sidebar">
    <!-- 1. Area del Logo -->
    <div class="ms-brand-area">
        <a href="<?= BASE_URL ?>" class="ms-brand-group">
            <div class="ms-logo-box">
                <!-- Logo SVG simple -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sidebar-logo-svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                </svg>
            </div>
            <span class="ms-brand-text">SGEN-Support</span>
        </a>
    </div>

    <!-- 2. Items de Navegacion -->
    <div class="ms-nav-container">
        
        <!-- Dashboard Wrapper -->
        <div class="ms-section">
            <div class="ms-nav-list">
                <a href="<?= BASE_URL ?>" class="ms-link <?= isActive('/', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/LayoutDashboard -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <span class="ms-link-text">Dashboard</span>
                </a>
            </div>
        </div>

        <!-- OPERACIONES -->
        <div class="ms-section">
            <div class="ms-section-title">Operaciones</div>
            <div class="ms-nav-list">
                <!-- Tickets -->
                <a href="<?= BASE_URL ?>soportes" class="ms-link <?= isActive('soportes', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Ticket -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path>
                            <path d="M13 5v2"></path>
                            <path d="M13 17v2"></path>
                            <path d="M13 11v2"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Tickets</span>
                </a>

                <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <!-- Inventario General -->
                <a href="<?= BASE_URL ?>inventario" class="ms-link <?= isActive('inventario', $currentUri) && strpos($currentUri, 'inventario/departamento') === false ? 'active' : '' ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Box -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <span class="ms-link-text">Inventario General</span>
                </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor'])): ?>
                <!-- Equipos -->
                <a href="<?= BASE_URL ?>equipos" class="ms-link <?= isActive('equipos', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Laptop -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Equipos</span>
                </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico'])): ?>
                <!-- Mantenimientos -->
                <a href="<?= BASE_URL ?>mantenimientos" class="ms-link <?= isActive('mantenimientos', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Wrench -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Mantenimientos</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ORGANIZACION -->
        <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico', 'consultor'])): ?>
        <div class="ms-section">
            <div class="ms-section-title">Organización</div>
            <div class="ms-nav-list">
                <a href="<?= BASE_URL ?>empleados" class="ms-link <?= isActive('empleados', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Users -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Empleados</span>
                </a>
                <a href="<?= BASE_URL ?>departamentos" class="ms-link <?= isActive('departamentos', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Briefcase -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Departamentos</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- AUDITORIA / ADMIN EXTRAS -->
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <div class="ms-section">
            <div class="ms-section-title">Admin</div>
            <div class="ms-nav-list">
                <a href="<?= BASE_URL ?>usuarios" class="ms-link <?= isActive('usuarios', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/PersonBadge => UserCog or similar -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <span class="ms-link-text">Usuarios</span>
                </a>
                
                <a href="<?= BASE_URL ?>bitacora" class="ms-link <?= isActive('bitacora', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/ClipboardList -->
                         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M12 11h4"></path>
                            <path d="M12 16h4"></path>
                            <path d="M8 11h.01"></path>
                            <path d="M8 16h.01"></path>
                        </svg>
                    </div>
                    <span class="ms-link-text">Bitácora</span>
                </a>

                <a href="<?= BASE_URL ?>logs" class="ms-link <?= isActive('logs', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/FileText -->
                         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <span class="ms-link-text">Log Sesión</span>
                </a>
                <a href="<?= BASE_URL ?>reportes" class="ms-link <?= isActive('reportes', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                         <!-- icons/BarChart -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <span class="ms-link-text">Reportes</span>
                </a>
                <!-- Configuración HIDDEN - functionality not complete
                <a href="<?= BASE_URL ?>configuracion" class="ms-link <?= isActive('configuracion', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12.22 2h-.44a2 2 0 0 1-2 1.08l-.68.61a2 2 0 0 1-2.48.54l-.84-.44a2 2 0 0 0-2.22.46l-.54.54a2 2 0 0 0-.46 2.22l.44.84a2 2 0 0 1-.54 2.48l-.61.68a2 2 0 0 1-1.08 2v.44a2 2 0 0 1 1.08 2l.61.68a2 2 0 0 1 .54 2.48l-.44.84a2 2 0 0 0 .46 2.22l.54.54a2 2 0 0 0 2.22-.46l.84-.44a2 2 0 0 1 2.48.54l.68.61a2 2 0 0 1 2 1.08h.44a2 2 0 0 1 2-1.08l.68-.61a2 2 0 0 1 2.48-.54l.84.44a2 2 0 0 0 2.22-.46l.54-.54a2 2 0 0 0 .46-2.22l-.44-.84a2 2 0 0 1 .54-2.48l.61-.68a2 2 0 0 1 1.08-2v-.44a2 2 0 0 1-1.08-2l-.61-.68a2 2 0 0 1-.54-2.48l.44-.84a2 2 0 0 0-.46-2.22l-.54-.54a2 2 0 0 0-2.22.46l-.84.44a2 2 0 0 1-2.48-.54l-.68-.61a2 2 0 0 1-2-1.08z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <span class="ms-link-text">Configuración</span>
                </a>
                -->
            </div>
        </div>
        <?php endif; ?>
        <!-- INFORMACIÓN -->
        <div class="ms-section">
            <div class="ms-section-title">Información</div>
            <div class="ms-nav-list">
                <a href="<?= BASE_URL ?>about" class="ms-link <?= isActive('about', $currentUri) ?>">
                    <div class="ms-active-indicator"></div>
                    <div class="ms-icon-box">
                        <!-- icons/Info -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </div>
                    <span class="ms-link-text">Acerca de</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Footer de Usuario (Solo muestra nombre, sin enlace) -->
    <div class="ms-footer">
        <div class="ms-user-btn">
            <div class="ms-avatar">
                <?= strtoupper(substr($_SESSION['usuario'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="ms-user-info">
                <p class="ms-user-name"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></p>
                <p class="ms-user-role"><?= ucfirst($_SESSION['rol'] ?? 'Invitado') ?></p>
            </div>
        </div>
    </div>
</aside>
