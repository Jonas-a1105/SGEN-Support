<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGEN-Support - Iniciar Sesión</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>img/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/login-modern.css">
    <!-- Preload JS -->
    <!-- Preload JS -->
    <script src="<?= BASE_URL ?>js/login-modern.js" defer></script>
    
    <!-- SweetAlert2 for Desktop Exit Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Desktop Integration for Electron exit confirmation -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/desktop-modal.css">
    <script src="<?= BASE_URL ?>js/desktop-integration.js" defer></script>
    
    <!-- Theme Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'system';
            let themeToApply = savedTheme;
            if (savedTheme === 'system') {
                themeToApply = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', themeToApply);
        })();
    </script>
</head>
<body>
    <div class="login-page">
        <!-- Background Pattern -->
        <div class="bg-pattern"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            
            <!-- Header -->
            <div class="login-header">
                <!-- NanoLogo - Same as sidebar -->
                <div class="logo-container">
                    <div class="logo-icon" style="background-color: #0f172a;">
                        <!-- Same logo as sidebar -->
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                        </svg>
                    </div>
                    <div class="logo-text-wrapper">
                        <h1 class="logo-title">SGEN-Support</h1>
                    </div>
                </div>
                
                <h2 class="header-title">Bienvenido</h2>
                <p class="header-subtitle">Ingresa tus credenciales</p>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <?php $flash = $_SESSION['flash_message']; ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" action="<?= BASE_URL ?>auth/procesar" id="login-form" class="login-form" autocomplete="off">
                
                <!-- Use hidden input to prevent autocomplete issues if needed, or just let browser handle it -->
                
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-wrapper">
                        <!-- User Icon -->
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        
                        <input type="text" id="username" name="username" class="form-input" placeholder="usuario@sgen.com" required autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="form-label">
                        <span>Contraseña</span>
                        <!-- Optional: Link logic here if available -->
                        <!-- <a href="#" class="label-link">¿Olvidaste?</a> -->
                    </div>
                    <div class="input-wrapper">
                        <!-- Lock Icon -->
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••" style="padding-right: 2.25rem;" required>
                        
                        <button type="button" id="togglePassword" class="btn-toggle-pass">
                            <!-- Eye Icon -->
                            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                            <!-- EyeOff Icon (Initially hidden via JS) -->
                            <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit">
                    <span id="btn-text">Entrar</span>
                    <!-- Loader (Initially hidden) -->
                    <svg id="btn-loader" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p class="footer-text">SGEN-Support v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></p>
            </div>
        </div>
    </div>
</body>
</html>
