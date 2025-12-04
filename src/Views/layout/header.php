<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($_SESSION['tema'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ??'SGEN-Support' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/soporte-moderno.css">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>img/favicon.ico">
</head>
<body class="d-flex flex-column min-vh-100">

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-opaque">
            <div class="container-fluid" style="padding-left: 100px;">
                
                <a class="navbar-brand fw-bold text-dark" href="<?= BASE_URL ?>">SGEN-Support</a>
                
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
                        
                        <!-- Notificaciones -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell text-dark" style="font-size: 1.2rem;"></i>
                          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">3 tickets pendientes</a></li>
                                <li><a class="dropdown-item" href="#">2 tickets vencidos</a></li>
                            </ul>
                        </li>
                        
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