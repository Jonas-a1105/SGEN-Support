<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Anti-cache para páginas protegidas -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="turbo-cache-control" content="no-cache">
    <title><?= $titulo ?? 'Sgen-support' ?></title>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <!-- Hotwire Turbo -->
    <script type="module">
        import hotwiredTurbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.0/+esm';
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
    <!-- Script de verificación de sesión (Asíncrono para evitar bloqueo) -->
    <script>
        (function() {
            // Verificar sesión con el servidor de forma asíncrona
            // Esto evita que la página se congele si la red es lenta
            fetch('<?= BASE_URL ?>api/check-session')
                .then(response => {
                    if (response.status === 401) {
                         // Si es 401, redirigir a login
                        throw new Error('Unauthorized');
                    }
                    return response.text();
                })
                .then(text => {
                   if (text === 'false') {
                       window.location.replace('<?= BASE_URL ?>auth/login');
                   }
                })
                .catch(err => {
                    if (err.message === 'Unauthorized') {
                         window.location.replace('<?= BASE_URL ?>auth/login');
                    }
                    // Si es error de red (offline), no bloqueamos la navegación por ahora
                });
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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/equipos-moderno.css?v=2.6.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-simple-delete-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/empleados-moderno.css?v=2.6.0">

    <!-- Global Scripts (Moved from Footer for Turbo Compatibility) -->
    <!-- Defer ensures they run after HTML parsing but before DOMContentLoaded -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="<?= BASE_URL ?>vendors/jquery/jquery.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js" defer></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js" defer></script>
    
    <!-- App Scripts -->
    <script src="<?= BASE_URL ?>js/utils.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/datatables-global.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/inactivity-logout.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/app.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/toast.js?v=2.6.0" defer></script>
    
    <!-- Modal Logics -->
    <script src="<?= BASE_URL ?>js/modal-simple-delete-modern.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/modal-inventory.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/modal-stock-adjust.js?v=2.6.0" defer></script>
    <script src="<?= BASE_URL ?>js/modal-assign-tech.js?v=2.6.0" defer></script> 
    
    <!-- Bulk Delete Global -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bulk-delete.css?v=2.6.0">
    <script src="<?= BASE_URL ?>js/bulk-delete.js?v=2.6.0" defer></script>

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

    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-inventory-modern.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-assign-tech.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/modal-stock-adjust.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/logout-modal.css?v=2.5.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/desktop-modal.css?v=2.6.0">

    <!-- Desktop Integration (Electron) -->
    <script src="<?= BASE_URL ?>js/desktop-integration.js?v=2.6.0" defer></script>

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
    
    <!-- React Replica Delete Modal (JS-Injected) -->
    <script src="<?= BASE_URL ?>js/modal-simple-delete-modern.js?v=2.6.0" defer></script>

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
                    Sgen-support
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

                        <script src="<?= BASE_URL ?>js/notifications.js?v=2.6.0"></script>

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
    <script src="<?= BASE_URL ?>js/user-preferences.js?v=2.6.0"></script>
    <script src="<?= BASE_URL ?>js/global-search.js?v=2.6.0"></script>
    
    <!-- Class-based Bulk Delete (Required for Inventory) -->
    <script src="<?= BASE_URL ?>js/bulk-delete-class.js?v=2.6.0" defer></script>
    
    <!-- Main Content Area -->
    <main class="content-wrapper">
        <div class="container-fluid">