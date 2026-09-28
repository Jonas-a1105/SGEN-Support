<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'SGEN Support') }}</title>

        <!-- Fonts -->
        <!-- Google Fonts removed: unified to Segoe UI Semibold 600 -->

        <!-- Scripts & Styles (CSP: solo scripts con el nonce por petición) -->
        @routes(null, $cspNonce ?? null)
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead

        <script nonce="{{ $cspNonce ?? '' }}">
            (function() {
                try {
                    var theme = localStorage.getItem('inv-theme-mode') || localStorage.getItem('sgen-theme');
                    if (theme === 'light') {
                        document.documentElement.classList.add('light');
                        document.documentElement.setAttribute('data-theme', 'light');
                    }
                    var strokeW = localStorage.getItem('inv-border-width') || localStorage.getItem('sgen-border-width');
                    if (strokeW) {
                        document.documentElement.style.setProperty('--stroke-w', strokeW);
                    }
                    var accent = localStorage.getItem('sgen-accent-color');
                    if (accent) {
                        document.documentElement.style.setProperty('--orange', accent);
                    }
                } catch (e) {
                    console.error('Error applying theme settings:', e);
                }
            })();
        </script>
    </head>
    <body class="app-body">
        @inertia
    </body>
</html>
