<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGEN-Support - Iniciar Sesión</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>img/favicon.ico">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/login-modern.css">
    <!-- Preload JS -->
    <script src="<?= BASE_URL ?>js/login-modern.js" defer></script>
</head>
<body>
    <div class="login-page">
        <!-- Background Pattern -->
        <div class="bg-pattern"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            
            <!-- Header -->
            <div class="login-header">
                <!-- NanoLogo -->
                <div class="logo-container">
                    <div class="logo-icon">
                        <!-- Icon Background -->
                        <svg viewBox="0 0 40 40" style="width: 100%; height: 100%; color: var(--blue-600);" fill="currentColor">
                            <rect width="40" height="40" rx="8" />
                        </svg>
                        <!-- 'S' Graphic -->
                        <svg viewBox="0 0 24 24" style="position: absolute; width: 50%; height: 50%; color: white;" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 17l4.5-9 5 9" stroke-opacity="0.5" /> 
                            <path d="M12 17v-5" />       
                            <circle cx="12" cy="7" r="2" fill="currentColor" stroke="none" />
                            <circle cx="7" cy="17" r="2" fill="currentColor" stroke="none" />
                            <circle cx="17" cy="17" r="2" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <div class="logo-text-wrapper">
                        <h1 class="logo-title">SGEN</h1>
                        <p class="logo-subtitle">Support</p>
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
                <p class="footer-text">SGEN Systems v2.0</p>
            </div>
        </div>
    </div>
</body>
</html>
