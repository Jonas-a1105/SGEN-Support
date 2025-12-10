/**
 * Logic for Inventario View
 */

// Function to delete equipment (Global scope)
window.eliminarEquipo = function (btn) {
    const equipoId = btn.getAttribute('data-id');
    const equipoTipo = btn.getAttribute('data-tipo');

    // Show equipment details in modal
    const detalleElement = document.getElementById('equipoDetalleEliminar');
    if (detalleElement) detalleElement.innerHTML = '<strong>Tipo:</strong> ' + (equipoTipo || 'Sin especificar');

    // Show the custom confirmation modal
    const modalEl = document.getElementById('modalConfirmarEliminarEquipo');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Handle confirmation button click
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.onclick = function () {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            // Note: BASE_URL is defined in global scope (header)
            form.action = BASE_URL + 'equipos/eliminar/' + equipoId;
            document.body.appendChild(form);
            form.submit();
        };
    }
};

// Función para manejar el cambio de items por página
function changeItemsPerPage(perPage) {
    if (!perPage) {
        // Fallback si se llama sin argumentos (legacy)
        const el = document.getElementById('itemsPerPage');
        if (el) perPage = el.value;
        else return;
    }

    // Guardar preferencia globalmente via cookie (usando PaginationPrefs si existe, o manual)
    if (window.PaginationPrefs) {
        PaginationPrefs.set(perPage);
    } else {
        // Fallback manual si no cargó el footer aún
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        document.cookie = 'sgen_pagination_per_page=' + perPage + ';expires=' + expires.toUTCString() + ';path=/';
    }

    // Recargar página manteniendo parámetros
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page_items', '1'); // Resetear página a 1
    url.searchParams.set('page_equipos', '1');
    window.location.href = url.toString();
}

(function () {
    const init = () => {
        // Attach event listeners to delete buttons
        document.querySelectorAll('.delete-equipo-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                eliminarEquipo(this);
            });
        });

        // Auto-submit search with debounce
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timeout = null;

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    searchForm.submit();
                }, 500);
            });

            // Focus input if it has value (after reload)
            if (searchInput.value.trim() !== '') {
                searchInput.focus();
                // Move cursor to end
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        }

        // Toggle fields for "Eliminar Artículo" in Baja modal (Event Delegation)
        document.body.addEventListener('change', function (e) {
            if (e.target && e.target.matches('input[name="eliminar_completo"]')) {
                const checkbox = e.target;
                const form = checkbox.closest('form');
                const cantidadInput = form.querySelector('input[name="cantidad"]');
                const motivoInput = form.querySelector('textarea[name="motivo"]');
                const submitBtn = form.querySelector('button[type="submit"]');

                if (!cantidadInput || !motivoInput || !submitBtn) return;

                // Find the parent divs to hide/show
                const divCantidad = cantidadInput.closest('.mb-3');
                const divMotivo = motivoInput.closest('.mb-3');

                if (checkbox.checked) {
                    // Hide fields and remove required
                    if (divCantidad) divCantidad.style.display = 'none';
                    if (divMotivo) divMotivo.style.display = 'none';
                    cantidadInput.removeAttribute('required');
                    motivoInput.removeAttribute('required');

                    // Update button
                    submitBtn.textContent = 'Eliminar Artículo';
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.classList.add('btn-dark'); // Visual cue
                } else {
                    // Show fields and add required
                    if (divCantidad) divCantidad.style.display = 'block';
                    if (divMotivo) divMotivo.style.display = 'block';
                    cantidadInput.setAttribute('required', 'required');
                    motivoInput.setAttribute('required', 'required');

                    // Update button
                    submitBtn.textContent = 'Confirmar Baja';
                    submitBtn.classList.remove('btn-dark');
                    submitBtn.classList.add('btn-danger');
                }
            }
        });

        // ===== VIEW TOGGLE =====
        const setView = (view, target) => {
            const tableView = document.getElementById(target + '-table');
            const cardsView = document.getElementById(target + '-cards');

            // Find buttons for this target
            const container = document.querySelector(`.inventario-toggle-btn[data-target="${target}"]`).parentElement;

            // Update buttons
            container.querySelectorAll('.inventario-toggle-btn').forEach(b => {
                if (b.dataset.view === view) b.classList.add('active');
                else b.classList.remove('active');
            });

            // Update visibility
            if (view === 'table') {
                if (tableView) tableView.classList.remove('hidden');
                if (cardsView) cardsView.classList.remove('active');
            } else {
                if (tableView) tableView.classList.add('hidden');
                if (cardsView) cardsView.classList.add('active');
            }

            // Persist
            localStorage.setItem('sgen_view_' + target, view);
        };

        document.querySelectorAll('.inventario-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const view = this.dataset.view;
                const target = this.dataset.target;
                setView(view, target);
            });
        });

        // Restore View Preferences
        ['articulos', 'equipos'].forEach(target => {
            const savedView = localStorage.getItem('sgen_view_' + target);
            if (savedView) {
                // Check if elements exist before setting
                if (document.querySelector(`.inventario-toggle-btn[data-target="${target}"]`)) {
                    setView(savedView, target);
                }
            }
        });

        // ===== CAROUSEL NAVIGATION =====
        document.querySelectorAll('.inventario-carousel-nav').forEach(btn => {
            btn.addEventListener('click', function () {
                const carouselId = this.dataset.carousel;
                const carousel = document.getElementById(carouselId);
                const scrollAmount = 300;

                if (!carousel) return;

                if (this.classList.contains('prev')) {
                    carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                } else {
                    carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            });
        });
    };
    init();
})();

