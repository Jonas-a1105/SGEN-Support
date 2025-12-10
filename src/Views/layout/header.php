<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Anti-cache para páginas protegidas -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $titulo ?? 'SGEN-Support' ?></title>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>

    <!-- Critical CSS - MUST come BEFORE theme script to have styles ready -->
    <style>
        /* Default (no data-theme): Light Mode - prevents any flash */
        html, body {
            background-color: #ffffff !important;
            color: #0f172a;
        }
        
        /* Light Mode explicit */
        html[data-theme="light"],
        html[data-theme="light"] body {
            background-color: #ffffff !important;
            color: #0f172a;
        }
        
        /* Dark Mode */
        html[data-theme="dark"],
        html[data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f1f5f9;
        }
    </style>

    <!-- Theme Initialization - Sets data-theme immediately -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || '<?= $_SESSION['tema'] ?? 'system' ?>';
                let themeToApply = savedTheme;
                
                if (savedTheme === 'system') {
                    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    themeToApply = systemDark ? 'dark' : 'light';
                }
                
                document.documentElement.setAttribute('data-theme', themeToApply);
                
                // Keep system preference listener active if needed
                if (savedTheme === 'system') {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                        if (!localStorage.getItem('theme') || localStorage.getItem('theme') === 'system') {
                            document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                        }
                    });
                }
            } catch (e) { console.error('Theme init error:', e); }
        })();
    </script>

    <!-- Script de verificación de sesión (verifica con el servidor) -->
    <script>
        (function() {
            // Verificar sesión con el servidor usando fetch síncrono-like
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= BASE_URL ?>api/check-session', false); // síncrono
            try {
                xhr.send();
                if (xhr.status === 401 || (xhr.status === 200 && xhr.responseText === 'false')) {
                    window.location.replace('<?= BASE_URL ?>auth/login');
                }
            } catch(e) {
                // Si hay error de red, dejar que continúe
            }
        })();
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/header-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/sidebar-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/toast.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/notifications.css?v=2.5.0">
    
    <!-- Module Specific CSS (Preloaded for Turbo) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/dashboard-v3.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/helpdesk-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/equipos-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/empleados-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/departamentos-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/inventario-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-detail-v2.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/ticket-form.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/reportes-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/maintenance-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/logs-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/usuarios-moderno.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bitacora-moderna.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/configuration.css?v=2.5.0">

    <!-- CSS for Modals (Moved to head to prevent FOUC) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-delete-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-inventory-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-assign-tech.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-stock-adjust.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/logout-modal.css?v=2.5.0">
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
                        <!-- Same logo as sidebar -->
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
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
                        
                        <!-- Notificaciones HIDDEN - functionality not complete
                        <li class="nav-item dropdown me-3 notification-wrapper">
                            
                            <button class="notification-btn" id="notificationBtn" onclick="NotificationCenter.toggle()">
                                <i class="bi bi-bell icon-md"></i>
                                <div id="notificationBadgeContainer" class="notification-badge-container">
                                </div>
                            </button>

                            <div class="notification-dropdown" id="notificationDropdown">
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

                                <div class="notification-tabs" id="notificationTabs">
                                </div>

                                <ul class="notification-list" id="notificationList">
                                </ul>

                                <div class="notification-footer">
                                    <button class="notification-view-all">
                                        Ver historial completo <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </li>

                        <script src="<?= BASE_URL ?>js/notifications.js?v=<?= time() ?>"></script>
                        -->
                        
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
    

    
    <!-- User Preferences Module (must load before view-specific scripts) -->
    <script src="<?= BASE_URL ?>js/user-preferences.js?v=<?= time() ?>"></script>
    
    <script src="<?= BASE_URL ?>js/global-search.js?v=<?= time() ?>"></script>
    
    <!-- Main Content Area -->
    <main class="content-wrapper">
        <div class="container-fluid">