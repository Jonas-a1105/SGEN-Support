/**
 * Maintenance Countdown Timer
 * Turbo-compatible version with interval cleanup.
 */
(function () {
    // Idempotency guard
    if (window.MAINTENANCE_TIMER_LOADED) return;
    window.MAINTENANCE_TIMER_LOADED = true;

    let timerIntervalId = null;

    function initMaintenanceTimer() {
        const timerElement = document.getElementById('maintenanceTimer');
        const timerBadge = document.getElementById('maintenanceTimerBadge');

        if (!timerElement) return;

        const startTime = new Date(timerElement.dataset.start).getTime();
        const endTime = new Date(timerElement.dataset.end).getTime();
        const status = timerElement.dataset.status;

        function updateTimer() {
            const now = new Date().getTime();
            let distance;
            let label = '';
            let badgeClass = '';

            if (status === 'pendiente') {
                distance = startTime - now;
                label = 'Inicia en: ';
                badgeClass = 'bg-amber-100 text-amber-700';

                if (distance < 0) {
                    location.reload();
                    return;
                }
            } else if (status === 'en_proceso') {
                distance = endTime - now;
                label = 'Tiempo restante: ';
                badgeClass = 'bg-blue-100 text-blue-700 animate-pulse';

                if (distance < 0) {
                    location.reload();
                    return;
                }
            } else {
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) timeString += `${days}d `;
            timeString += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            timerElement.innerText = label + timeString;
        }

        // Clear previous interval if exists
        if (timerIntervalId) {
            clearInterval(timerIntervalId);
        }

        // Update immediately and then every second
        updateTimer();
        timerIntervalId = setInterval(updateTimer, 1000);
    }

    // Cleanup on navigation away
    document.addEventListener('turbo:before-cache', function () {
        if (timerIntervalId) {
            clearInterval(timerIntervalId);
            timerIntervalId = null;
        }
    });

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initMaintenanceTimer);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initMaintenanceTimer();
    }
})();
