/**
 * Sistema de Cierre de Sesión por Inactividad (Versión Estética)
 * Refactorizado para compatibilidad con Turbo Drive (Singleton Pattern)
 */
(function () {

    // Prevent multiple instances with Turbo Drive
    if (window.InactivityManager) {
        window.InactivityManager.reset(); // Reset existing timer on navigation
        return;
    }

    window.InactivityManager = {
        timer: null,
        // Tiempo de inactividad: 30 minutos (1800000 ms) - Aumentado para comodidad
        limit: 1800000,
        logoutUrl: window.APP_BASE_URL + 'auth/logout',

        init: function () {
            this.start();
            this.bindEvents();
            console.log('🛡️ Inactivity Manager Started');
        },

        start: function () {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.doLogout(), this.limit);
        },

        reset: function () {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.doLogout(), this.limit);
        },

        bindEvents: function () {
            // Bind to window/document to catch all activity
            const events = ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'];
            events.forEach(event => {
                document.addEventListener(event, () => this.reset(), { passive: true });
            });
        },

        doLogout: function () {
            Swal.fire({
                html: `
                    <div class="mb-3">
                        <i class="bi bi-shield-lock-fill text-warning" 
                           style="font-size: 5rem; animation: pulse 2s infinite;"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Sesión Expirada</h3>
                    <p class="text-muted fs-5">
                        Has estado inactivo por demasiado tiempo.<br>
                        Tu sesión se cerrará por seguridad.
                    </p>
                    <div class="mt-3 text-muted small">
                        Redirigiendo en <b class="timer">3</b> segundos...
                    </div>
                `,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    popup: 'card'
                },
                didOpen: () => {
                    const timer = Swal.getHtmlContainer().querySelector('.timer');
                    let timeLeft = 2;
                    const interval = setInterval(() => {
                        if (timer) timer.textContent = timeLeft;
                        timeLeft--;
                    }, 1000);
                }
            }).then(() => {
                window.location.href = this.logoutUrl;
            });
        }
    };

    // Initialize
    window.InactivityManager.init();

})();