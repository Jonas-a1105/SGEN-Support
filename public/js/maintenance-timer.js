/**
 * Maintenance Countdown Timer
 * Handles the countdown display for pending and in-process maintenances.
 */
document.addEventListener('DOMContentLoaded', function () {
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
                // Should reload to update status to 'en_proceso' via backend check
                location.reload();
                return;
            }
        } else if (status === 'en_proceso') {
            distance = endTime - now;
            label = 'Tiempo restante: ';
            badgeClass = 'bg-blue-100 text-blue-700 animate-pulse';

            if (distance < 0) {
                // Sould reload to update status to 'realizado' via backend check
                location.reload();
                return;
            }
        } else {
            return; // No timer needed
        }

        // Calculations
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Format
        let timeString = '';
        if (days > 0) timeString += `${days}d `;
        timeString += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        timerElement.innerText = label + timeString;

        // Update badge style if changed (optional)
        if (timerBadge && !timerBadge.className.includes(badgeClass)) {
            // timerBadge.className = ... could update class here
        }
    }

    // Update immediately and then every second
    updateTimer();
    setInterval(updateTimer, 1000);
});
