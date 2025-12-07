<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($_SESSION['tema'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ??'SGEN-Support' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/header-modern.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/sidebar-modern.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/soporte-moderno.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/toast.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/notifications.css?v=<?= time() ?>">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>img/favicon.ico">
</head>
<body class="d-flex flex-column min-vh-100">

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-opaque">
            <div class="container-fluid" style="padding-left: 6rem;">
                
                <a class="navbar-brand fw-bold text-dark d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                    <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 shadow-sm" style="width: 36px; height: 36px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 6h6a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h8"></path>
                            <circle cx="7" cy="6" r="2" fill="currentColor" stroke="none"></circle>
                            <circle cx="17" cy="18" r="2" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </div>
                    SGEN-Support
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        
                        <!-- Dark Mode Toggle -->
                        <li class="nav-item me-3">
                            <button id="darkModeToggle" class="btn btn-outline-secondary btn-sm" title="Cambiar Tema">
                                <i id="themeIcon" class="bi <?= ($_SESSION['tema'] ?? 'light') === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' ?>"></i>
                            </button>
                        </li>
                        
                        <!-- Notificaciones (New Center) -->
                        <li class="nav-item dropdown me-3 notification-wrapper">
                            
                            <button class="notification-btn" id="notificationBtn" onclick="NotificationCenter.toggle()">
                                <i class="bi bi-bell" style="font-size: 1.3rem;"></i>
                                <div id="notificationBadgeContainer" class="notification-badge-container">
                                    <!-- Populated by JS -->
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="notification-dropdown" id="notificationDropdown">
                                <!-- Header -->
                                <div class="notification-header">
                                    <h3 class="notification-title">Notificaciones</h3>
                                    <div class="notification-actions">
                                        <button class="notification-action-btn" title="Marcar todo como leído" onclick="NotificationCenter.markAllRead()">
                                            <i class="bi bi-check-all" style="font-size: 1.2rem;"></i>
                                        </button>
                                        <button class="notification-action-btn" title="Configuración">
                                            <i class="bi bi-gear" style="font-size: 1.1rem;"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabs -->
                                <div class="notification-tabs" id="notificationTabs">
                                    <!-- Populated by JS -->
                                </div>

                                <!-- List -->
                                <ul class="notification-list" id="notificationList">
                                    <!-- Populated by JS -->
                                </ul>

                                <!-- Footer -->
                                <div class="notification-footer">
                                    <button class="notification-view-all">
                                        Ver historial completo <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </li>

                        <!-- Load Notification Logic -->
                        <script src="<?= BASE_URL ?>js/notifications.js?v=<?= time() ?>"></script>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar-small me-2">
                                    <?= strtoupper(substr($_SESSION['usuario'] ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="text-dark"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text"><small class="text-muted"><?= ucfirst($_SESSION['rol'] ?? '') ?></small></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>perfil"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>configuracion"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/left-side-menu.php'; ?>
    
    <!-- Main Content Area -->
    <main class="content-wrapper">
        <div class="container-fluid">