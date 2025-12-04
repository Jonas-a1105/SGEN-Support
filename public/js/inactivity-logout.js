/**
 * Sistema de Cierre de Sesión por Inactividad (Versión Estética)
 */
(function() {
    
    let inactivityTimer;
    
    // Tiempo de inactividad: 2 minutos (120,000 ms)
    // Puedes cambiarlo aquí si lo necesitas
    const inactivityTime = 600000; 
    
    // URL de logout (definida en el footer.php)
    const logoutUrl = APP_BASE_URL + 'auth/logout';

    // Función que se ejecuta cuando se completa el tiempo
    function doLogout() {
        // Usamos SweetAlert2 en lugar del alert() feo
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
            timer: 3000, // Se cierra solo en 3 segundos
            timerProgressBar: true,
            showConfirmButton: false, // No hace falta botón, se cierra solo
            allowOutsideClick: false, // No deja cerrar haciendo clic fuera
            allowEscapeKey: false,
            customClass: {
                popup: 'card' // Hereda el estilo "Liquid Glass" de tu CSS
            },
            didOpen: () => {
                // Pequeño script para la cuenta regresiva visual
                const timer = Swal.getHtmlContainer().querySelector('.timer');
                let timeLeft = 2;
                const interval = setInterval(() => {
                    if(timer) timer.textContent = timeLeft;
                    timeLeft--;
                }, 1000);
            }
        }).then(() => {
            // Cuando termina el tiempo, redirige
            window.location.href = logoutUrl;
        });
    }

    // Función para reiniciar el contador
    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(doLogout, inactivityTime);
    }

    // Iniciar al cargar
    window.onload = resetTimer;
    
    // Reiniciar con cualquier actividad
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onclick = resetTimer;
    document.onscroll = resetTimer;
    document.ontouchstart = resetTimer;

})();