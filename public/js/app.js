(function () {
    /**
     * ARCHIVO PRINCIPAL DE JAVASCRIPT (SGEN-SUPPORT)
     * Contiene la lógica global para alertas, confirmaciones y animaciones.
     */

    // Idempotency guard for Turbo
    if (window.SGEN_APP_LOADED) return;
    window.SGEN_APP_LOADED = true;

    // Usamos un solo "escucha" global en modo captura (el 'true' al final)
    // para interceptar TODOS los clics antes que cualquier otro script.
    document.addEventListener('click', function (e) {

        // Buscamos el elemento enlace (<a>) más cercano al clic
        // (por si el usuario hizo clic en el icono <i> dentro del enlace)
        const link = e.target.closest('a');

        // Si no se hizo clic en un enlace, no hacemos nada
        if (!link) return;

        // ----------------------------------------------------------------
        // 0. GUARD CLAUSE: PREVENT RELOAD ON SAME URL
        // Evita el "parpadeo" o recarga innecesaria si ya estamos aquí.
        // ----------------------------------------------------------------
        if (link.href === window.location.href && !link.hash && !link.hasAttribute('download') && link.target !== '_blank') {
            e.preventDefault();
            return;
        }

        // ----------------------------------------------------------------
        // 1. LÓGICA PARA BOTONES DE "ELIMINAR"
        // ----------------------------------------------------------------

        // Criterios para identificar un botón de eliminar:
        const href = link.getAttribute('href') || '';
        const onclickTexto = link.getAttribute('onclick') || '';

        const esBorrarPorUrl = href.includes('/eliminar/');       // ¿La URL dice "eliminar"?
        const esBorrarPorClase = link.classList.contains('btn-delete'); // ¿Tiene la clase btn-delete?
        const esBorrarPorOnclick = onclickTexto.includes('confirm'); // ¿Tiene el código viejo?

        if (esBorrarPorUrl || esBorrarPorClase || esBorrarPorOnclick) {

            // --- EXCEPCIÓN 1: Si el botón tiene 'data-no-global-delete'
            if (link.hasAttribute('data-no-global-delete')) {
                return;
            }

            // --- EXCEPCIÓN 2: Proteger URL de 'editar' o 'edit' (Case insensitive y clase explícita)
            const lowerHref = href.toLowerCase();
            if (lowerHref.includes('/editar/') || lowerHref.includes('/edit/') || link.classList.contains('btn-action-edit')) {
                return;
            }

            // ¡IMPORTANTE! Detenemos el evento inmediatamente
            e.preventDefault();
            e.stopPropagation();

            // Intentamos obtener el nombre del registro
            let nombreElemento = '';
            if (link.getAttribute('data-name')) {
                nombreElemento = link.getAttribute('data-name');
            } else if (esBorrarPorOnclick) {
                const match = onclickTexto.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    nombreElemento = match[1].replace(/^[¿?]+|[?]+$/g, '');
                }
            }

            // Usamos el NUEVO MODELO (SimpleDeleteModal)
            if (window.SimpleDeleteModal) {
                SimpleDeleteModal.open(href, {
                    name: nombreElemento
                });
            } else {
                // Fallback si no cargó el modal (no debería pasar)
                if (confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                    window.location.href = href;
                }
            }

            return; // Terminamos aquí
        }

        // ----------------------------------------------------------------
        // 2. LÓGICA PARA EL BOTÓN DE "CERRAR SESIÓN"
        // ----------------------------------------------------------------

        if (link.classList.contains('btn-logout')) {

            e.preventDefault();
            e.stopPropagation();

            Swal.fire({
                html: `
                    <div class="mb-3">
                        <span class="sad-animate" style="font-size: 5rem;">🥺</span>
                    </div>
                    <h3 class="fw-bold text-dark">¿Ya te vas?</h3>
                    <p class="text-muted fs-5">
                        ¿Seguro que quieres cerrar sesión?
                    </p>
                `,
                showCancelButton: true,
                confirmButtonColor: '#2E86DE', // Azul primario
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'No, me quedo',
                reverseButtons: true, // Botón de cancelar a la izquierda (mejor UX)
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    container: 'desktop-modal-container',
                    confirmButton: 'btn btn-primary btn-lg rounded-pill px-4 mx-2',
                    cancelButton: 'btn btn-secondary btn-lg rounded-pill px-4 mx-2',
                    cancelButton: 'btn btn-secondary btn-lg rounded-pill px-4 mx-2',
                    popup: 'card'
                },
                backdrop: 'rgba(0,0,0,0)' // Fix flicker
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mensaje de despedida (Toast)
                    if (window.Toast) {
                        Toast.success('¡Hasta pronto! 👋');
                    }

                    // Esperamos un poco para que se vea el mensaje antes de redirigir
                    setTimeout(() => {
                        window.location.href = href;
                    }, 1000);
                }
            });
        }

    }, true); // <--- 'true' activa la fase de captura (CRUCIAL para ganar a los onclick viejos)

    // ----------------------------------------------------------------
    // 3. RESPONSIVE SIDEBAR TOGGLE (Mobile)
    // ----------------------------------------------------------------
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const sidebar = document.querySelector('.ms-sidebar');

    if (mobileToggle && sidebar) {
        // Create backdrop dynamically
        const backdrop = document.createElement('div');
        backdrop.className = 'ms-sidebar-backdrop';
        backdrop.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
            z-index: 1055;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        `;
        document.body.appendChild(backdrop);

        const closeSidebar = () => {
            sidebar.classList.remove('open');
            backdrop.style.opacity = '0';
            backdrop.style.pointerEvents = 'none';
            document.body.style.overflow = '';
        };

        const openSidebar = () => {
            sidebar.classList.add('open');
            backdrop.style.opacity = '1';
            backdrop.style.pointerEvents = 'auto';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        };

        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        // Close on backdrop click
        backdrop.addEventListener('click', closeSidebar);

        // Close on route change (for Turbo/SPA feel)
        document.addEventListener('turbo:visit', closeSidebar);

        // Close on swipe left (optional simple implementation)
        let touchStartX = 0;
        document.addEventListener('touchstart', e => touchStartX = e.changedTouches[0].screenX);
        document.addEventListener('touchend', e => {
            if (touchStartX > 50 && e.changedTouches[0].screenX < touchStartX - 50) { // Swipe left
                if (sidebar.classList.contains('open')) closeSidebar();
            }
        });
    }

    // ----------------------------------------------------------------
    // 4. SIDEBAR ACTIVE STATE - Update on Turbo Navigation
    // ----------------------------------------------------------------
    function updateSidebarActiveState() {
        const currentPath = window.location.pathname.replace(/^\/sgen-support\/?/, '').replace(/^\//, '');
        const sidebarLinks = document.querySelectorAll('.ms-sidebar .ms-link');

        sidebarLinks.forEach(link => {
            link.classList.remove('active');

            const href = link.getAttribute('href') || '';
            // Extract the route from the href (remove base URL)
            const linkPath = href.replace(/.*\/sgen-support\/?/, '').replace(/^\//, '');

            // Check if this link matches the current path
            if (linkPath === '' && (currentPath === '' || currentPath === '/')) {
                // Dashboard (root)
                link.classList.add('active');
            } else if (linkPath !== '' && currentPath.startsWith(linkPath)) {
                // Other routes - match prefix
                link.classList.add('active');
            }
        });
    }

    // Update on initial load
    updateSidebarActiveState();

    // Update on Turbo navigation
    document.addEventListener('turbo:load', updateSidebarActiveState);
    document.addEventListener('turbo:render', updateSidebarActiveState);

})();
