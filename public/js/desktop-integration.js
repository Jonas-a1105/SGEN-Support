/**
 * Desktop Integration Script (Premium UX)
 * Handles Electron-specific UI interactions with Glassmorphism styles.
 * Turbo-compatible version.
 */

(function () {
    // Idempotency guard - Electron handlers should only be registered once
    if (window.DESKTOP_INTEGRATION_LOADED) return;
    window.DESKTOP_INTEGRATION_LOADED = true;

    function initDesktopIntegration() {
        if (!window.electronAPI) return;

        // Common SweetAlert Config for Glassmorphism
        // Backdrop matched to system default: Dark dim (0.4) + subtle blur (2px)
        const glassSwal = Swal.mixin({
            customClass: {
                container: 'desktop-modal-container',
                popup: 'desktop-modal-popup',
                title: 'desktop-modal-title',
                htmlContainer: 'desktop-modal-text',
                confirmButton: 'desktop-btn-confirm',
                cancelButton: 'desktop-btn-cancel',
                actions: 'desktop-action-wrapper'
            },
            buttonsStyling: false,
            showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },
            // backdrop: handled dynamically per fire() call to support theme switching without reload
            // and to prevent CSS override flickering.
        });

        // Helper: Determine backdrop color based on theme
        const getBackdropColor = () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            // Match CSS variables: Light: rgba(255,255,255,0.6), Dark: rgba(0,0,0,0.5)
            return isDark ? 'rgba(0, 0, 0, 0.5)' : 'rgba(255, 255, 255, 0.6)';
        };

        // --- EXIT CONFIRMATION ---
        window.electronAPI.onShowExitConfirm(() => {
            glassSwal.fire({
                html: `
                <div class="desktop-icon-badge">
                    <i class="bi bi-power"></i>
                </div>
                <h3>¿Salir de la aplicación?</h3>
                <p>Se cerrarán todas las ventanas y procesos activos.</p>
            `,
                showCancelButton: true,
                confirmButtonText: 'Sí, Salir',
                cancelButtonText: 'Cancelar',
                focusCancel: true,
                backdrop: getBackdropColor()
            }).then((result) => {
                if (result.isConfirmed) {
                    window.electronAPI.exitApp();
                }
            });
        });

        // --- AUTO UPDATER UI ---

        // 1. Update Available
        window.electronAPI.onUpdateAvailable((info) => {
            glassSwal.fire({
                html: `
                <div class="desktop-icon-badge">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </div>
                <h3>Actualización Disponible</h3>
                <p>La versión <strong>${info.version}</strong> está lista para descargar.</p>
            `,
                showCancelButton: true,
                confirmButtonText: 'Descargar Ahora',
                cancelButtonText: 'Más tarde',
                backdrop: getBackdropColor()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show Download Progress Modal (Persistent)
                    glassSwal.fire({
                        html: `
                        <div class="desktop-icon-badge">
                            <i class="bi bi-download"></i>
                        </div>
                        <h3>Descargando...</h3>
                        <div class="desktop-progress-wrapper">
                            <div class="desktop-progress-track">
                                <div class="desktop-progress-fill" id="updateProgressFill" style="width: 0%"></div>
                            </div>
                            <div class="desktop-progress-stats">
                                <span id="updatePercent">0%</span>
                                <span id="updateSpeed" style="color: var(--accent-color); font-weight: 600;">-- MB/s</span>
                                <span id="updateSize">Calculando...</span>
                            </div>
                        </div>
                    `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        backdrop: getBackdropColor()
                    });

                    // Start the download now (since autoDownload is disabled)
                    window.electronAPI.downloadUpdate();
                }
            });
        });

        // 2. Download Progress
        window.electronAPI.onUpdateProgress((progressObj) => {
            const fill = document.getElementById('updateProgressFill');
            const percentText = document.getElementById('updatePercent');
            const sizeText = document.getElementById('updateSize');
            const speedText = document.getElementById('updateSpeed');

            if (fill && percentText) {
                const percent = Math.round(progressObj.percent);
                fill.style.width = `${percent}%`;
                percentText.innerText = `${percent}%`;

                if (sizeText && progressObj.total > 0) {
                    const transferred = (progressObj.transferred / 1024 / 1024).toFixed(1);
                    const total = (progressObj.total / 1024 / 1024).toFixed(1);
                    sizeText.innerText = `${transferred}MB / ${total}MB`;
                }

                if (speedText && progressObj.bytesPerSecond) {
                    const speed = (progressObj.bytesPerSecond / 1024 / 1024).toFixed(1);
                    speedText.innerText = `${speed} MB/s`;
                }
            }
        });

        // 3. Update Downloaded
        window.electronAPI.onUpdateDownloaded((info) => {
            glassSwal.fire({
                html: `
                <div class="desktop-icon-badge" style="color: #10b981; border-color: #10b981">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3>¡Descarga Completada!</h3>
                <p>La nueva versión está lista. Reinicia ahora para aplicar los cambios.</p>
            `,
                showCancelButton: true,
                confirmButtonText: 'Reiniciar e Instalar',
                cancelButtonText: 'Instalar Luego',
                allowOutsideClick: false,
                backdrop: getBackdropColor()
            }).then((result) => {
                if (result.isConfirmed) {
                    window.electronAPI.installUpdate();
                }
            });
        });

        // --- TEST SIMULATION ---
        window.testUpdateUI = () => {
            // Trigger Update Available
            const info = { version: '10.0.0-Beta' };

            glassSwal.fire({
                html: `
                <div class="desktop-icon-badge">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </div>
                <h3>Actualización Disponible</h3>
                <p>La versión <strong>${info.version}</strong> está lista para descargar.</p>
            `,
                showCancelButton: true,
                confirmButtonText: 'Descargar Ahora',
                cancelButtonText: 'Más tarde',
                backdrop: getBackdropColor()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show Progress
                    glassSwal.fire({
                        html: `
                        <div class="desktop-icon-badge">
                            <i class="bi bi-download"></i>
                        </div>
                        <h3>Descargando...</h3>
                        <div class="desktop-progress-wrapper">
                            <div class="desktop-progress-track">
                                <div class="desktop-progress-fill" id="updateProgressFill" style="width: 0%"></div>
                            </div>
                            <div class="desktop-progress-stats">
                                <span id="updatePercent">0%</span>
                                <span id="updateSpeed" style="color: var(--accent-color); font-weight: 600;">0.0 MB/s</span>
                                <span id="updateSize">0 MB / 50.0 MB</span>
                            </div>
                        </div>
                    `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        backdrop: getBackdropColor()
                    });

                    // Simulate Progress
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 1; // Slower to see speed changes
                        const fill = document.getElementById('updateProgressFill');
                        const percentText = document.getElementById('updatePercent');
                        const sizeText = document.getElementById('updateSize');
                        const speedText = document.getElementById('updateSpeed');

                        if (fill) {
                            fill.style.width = `${progress}%`;
                            percentText.innerText = `${progress}%`;
                            sizeText.innerText = `${(progress * 0.5).toFixed(1)} MB / 50.0 MB`;

                            // Variable simulated speed
                            const simulatedSpeed = (Math.random() * (2.8 - 1.2) + 1.2).toFixed(1);
                            if (speedText) speedText.innerText = `${simulatedSpeed} MB/s`;
                        }

                        if (progress >= 100) {
                            clearInterval(interval);
                            // Trigger Completed
                            glassSwal.fire({
                                html: `
                                <div class="desktop-icon-badge" style="color: #10b981; border-color: #10b981">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <h3>¡Descarga Completada!</h3>
                                <p>La nueva versión está lista. Reinicia ahora para aplicar los cambios.</p>
                            `,
                                showCancelButton: true,
                                confirmButtonText: 'Reiniciar e Instalar',
                                cancelButtonText: 'Instalar Luego',
                                allowOutsideClick: false,
                                backdrop: getBackdropColor()
                            }).then((res) => {
                                if (res.isConfirmed) {
                                    glassSwal.fire({
                                        icon: 'info',
                                        title: 'Simulación Finalizada',
                                        text: 'En producción, la app se reiniciaría ahora para instalar la actualización.',
                                        backdrop: getBackdropColor()
                                    });
                                }
                            });
                        }
                    }, 80);
                }
            });
        };

    } // End of initDesktopIntegration function

    // Initialize on Turbo navigation (for pages that use Turbo)
    document.addEventListener('turbo:load', initDesktopIntegration);

    // Also initialize on DOMContentLoaded (for pages without Turbo, like login)
    document.addEventListener('DOMContentLoaded', initDesktopIntegration);

    // And run immediately if already loaded
    if (document.readyState !== 'loading') {
        initDesktopIntegration();
    }
})();
