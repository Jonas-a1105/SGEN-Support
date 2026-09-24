<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'SGEN Support') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead

        <script>
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
