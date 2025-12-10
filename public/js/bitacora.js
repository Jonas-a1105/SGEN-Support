/**
 * Bitacora View Logic
 */

if (typeof currentEntityFilter === 'undefined') window.currentEntityFilter = 'all';

function filterActions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.accion-item');

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const entity = item.dataset.entity;

        let showBySearch = text.includes(searchTerm);
        let showByFilter = currentEntityFilter === 'all' || entity === currentEntityFilter;

        if (showBySearch && showByFilter) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });

    // Ocultar grupos de fecha sin items visibles
    document.querySelectorAll('.fecha-grupo').forEach(grupo => {
        let nextSibling = grupo.nextElementSibling;
        let hasVisibleItems = false;

        while (nextSibling && !nextSibling.classList.contains('fecha-grupo')) {
            if (nextSibling.classList.contains('accion-item') && nextSibling.style.display !== 'none') {
                hasVisibleItems = true;
                break;
            }
            nextSibling = nextSibling.nextElementSibling;
        }

        grupo.style.display = hasVisibleItems ? 'flex' : 'none';
    });
}

function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function setEntityFilter(entity) {
    currentEntityFilter = entity;
    document.getElementById('filterDropdown').style.display = 'none';
    filterActions();
}

function changeItemsPerPage() {
    const perPage = document.querySelector('.per-page-select').value;
    // Guardar preferencia globalmente
    // Guardar preferencia específica para bitácora
    document.cookie = "sgen_bitacora_per_page=" + perPage + "; path=/; max-age=31536000";

    // Get base URL from existing window location or define it globally if needed
    // Assuming BASE_URL is handled via PHP echo in the view, we might need to pass it or use current path
    const url = new URL(window.location.href);
    url.searchParams.set('page', '1');
    url.searchParams.set('per_page', perPage);
    window.location.href = url.toString();
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function (e) {
    const dropdown = document.getElementById('filterDropdown');
    const btn = document.getElementById('filterBtn');
    if (dropdown && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
