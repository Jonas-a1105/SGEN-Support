<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($_SESSION['tema'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ??'SGEN-Support' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/header-modern.css?v=2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/sidebar-modern.css?v=2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/toast.css?v=2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/notifications.css?v=3.1">
    
    <!-- Module Specific CSS (Preloaded for Turbo) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/dashboard-v3.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/helpdesk-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/equipos-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/empleados-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/departamentos-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-detail-v2.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-form.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/reportes-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/maintenance-modern.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/logs-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/usuarios-moderno.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bitacora-moderna.css?v=3.1">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/configuration.css?v=3.1">

    <!-- CSS for Modals (Moved to head to prevent FOUC) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-delete-modern.css?v=3.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-inventory-modern.css?v=3.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-assign-tech.css?v=3.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-stock-adjust.css?v=3.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/logout-modal.css?v=3.1">
    <style>
        /* Turbo Progress Bar Customization */
        .turbo-progress-bar {
            height: 3px;
            background-color: #3b82f6; /* Blue-500 */
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
            z-index: 9999; /* Ensure visibility above fixed headers */
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include __DIR__ . '/../components/logout-modal.php'; ?>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-opaque">
            <div class="container-fluid header-container-padded">
                
                <a class="navbar-brand fw-bold text-dark d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                    <div class="brand-icon-wrapper">
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
                
                <!-- Global Search Input in Header -->
                <div class="header-search-wrapper">
                    <div class="header-search-container">
                        <i class="bi bi-search header-search-icon"></i>
                        <input type="text" 
                               class="header-search-input" 
                               id="globalSearchInput" 
                               placeholder="Buscar..." 
                               autocomplete="off"
                               onfocus="GlobalSearch.showResults()"
                               oninput="GlobalSearch.search(this.value)">
                        <kbd class="header-search-kbd">Ctrl+K</kbd>
                        
                        <!-- Results Dropdown -->
                        <div class="header-search-results" id="globalSearchResults">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        
                        <!-- Notificaciones (New Center) -->
                        <li class="nav-item dropdown me-3 notification-wrapper">
                            
                            <button class="notification-btn" id="notificationBtn" onclick="NotificationCenter.toggle()">
                                <i class="bi bi-bell icon-md"></i>
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
                                            <i class="bi bi-check-all icon-sm"></i>
                                        </button>
                                        <button class="notification-action-btn" title="Configuración">
                                            <i class="bi bi-gear icon-xs"></i>
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
                        
                        <!-- Logout Icon (Replaced User Menu) -->
                        <li class="nav-item">
                            <button class="btn btn-link text-dark p-0 ms-2" onclick="LogoutModal.open(event)" title="Cerrar Sesión">
                                <i class="bi bi-box-arrow-right icon-lg"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/left-side-menu.php'; ?>
    
    <!-- Global Search Script -->
    <script>
        if (typeof BASE_URL === 'undefined') {
            window.BASE_URL = '<?= BASE_URL ?>';
        }
    </script>
    <script src="<?= BASE_URL ?>js/global-search.js?v=<?= time() ?>"></script>
    
    <!-- Main Content Area -->
    <main class="content-wrapper">
        <div class="container-fluid">