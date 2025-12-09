/**
 * Logic for Logs View
 */

// Variables globales para el estado
// Variables globales para el estado
if (typeof currentFilter === 'undefined') window.currentFilter = 'all';
if (typeof currentPage === 'undefined') window.currentPage = 1;
// itemsPerPage should be initialized from the DOM or script tag if needed, 
// but here we can try to read it from the select element or cookie
if (typeof itemsPerPage === 'undefined') window.itemsPerPage = 10;

// Initial setup
document.addEventListener('DOMContentLoaded', function () {
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    if (itemsPerPageSelect) {
        itemsPerPage = parseInt(itemsPerPageSelect.value);
    }
    updatePagination();

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('dateDropdown');
        const btn = document.getElementById('dateFilterBtn');
        if (dropdown && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Cerrar modal al hacer clic fuera
    const detalleModal = document.getElementById('detalleModal');
    if (detalleModal) {
        detalleModal.addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    }
});

function updatePagination() {
    const allRows = document.querySelectorAll('.log-row');
    const currentPageEl = document.getElementById('currentPage');
    const totalPagesEl = document.getElementById('totalPages');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const visibleCountEl = document.getElementById('visibleCount');

    if (!currentPageEl || !totalPagesEl) return;

    const filteredRows = getFilteredRows();
    const totalFiltered = filteredRows.length;
    const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;

    if (currentPage > totalPages) currentPage = totalPages;

    currentPageEl.textContent = currentPage;
    totalPagesEl.textContent = totalPages;

    // Enable/disable buttons
    if (btnPrev) btnPrev.disabled = currentPage === 1;
    if (btnNext) btnNext.disabled = currentPage === totalPages;

    // Show/hide rows based on pagination
    let visibleCount = 0;
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;

    // We need to iterate over ALL rows to hide/show them correctly
    // But getFilteredRows only returns matches.
    // So we iterate all, and check if it's in the filtered set AND in the current page range.

    // Optimization: getFilteredRows returns DOM elements.
    // We can hide all first? No, getFilteredRows is expensive if called repeatedly.
    // Let's rely on the logic in getFilteredRows to KNOW if it matches, then check index.

    // Actually, the original logic iterated allRows and checked filters inline.
    // Let's replicate that for "showing" the slice.

    let filteredIndex = 0;
    allRows.forEach(row => {
        // Re-check filter condition
        if (matchesFilters(row)) {
            if (filteredIndex >= start && filteredIndex < end) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
            filteredIndex++;
        } else {
            row.style.display = 'none';
        }
    });

    if (visibleCountEl) visibleCountEl.textContent = visibleCount;
}

function matchesFilters(row) {
    const isActive = row.classList.contains('active-session');
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const text = row.textContent.toLowerCase();

    let showByFilter = currentFilter === 'all' || (currentFilter === 'active' && isActive);
    let showBySearch = text.includes(searchTerm);

    // Check date filter if applied (stored in data-date-filtered)
    // Note: The original code simulated date filtering client-side.
    // If setDateFilter was called, it updated data-date-filtered attribute.
    // If attribute is not present, assume true?
    // Original code: row.dataset.dateFiltered = 'true'/'false'.
    // If not set (default), we should treat as true (show).
    let showByDate = true;
    if (row.dataset.dateFiltered === 'false') {
        showByDate = false;
    }

    return showByFilter && showBySearch && showByDate;
}

function getFilteredRows() {
    const allRows = document.querySelectorAll('.log-row');
    return Array.from(allRows).filter(row => matchesFilters(row));
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
    }
}

function nextPage() {
    const filteredRows = getFilteredRows();
    const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
    }
}

function changeItemsPerPage() {
    const select = document.getElementById('itemsPerPage');
    if (select) itemsPerPage = parseInt(select.value);

    // Guardar preferencia globalmente
    if (window.PaginationPrefs) {
        PaginationPrefs.set(itemsPerPage);
    }
    currentPage = 1;
    updatePagination();
}

function filterLogs() {
    currentPage = 1;
    updatePagination();
}

function setFilter(filter) {
    currentFilter = filter;
    currentPage = 1;

    // Update button styles
    const btnAll = document.getElementById('btn-all');
    const btnActive = document.getElementById('btn-active');

    if (btnAll && btnActive) {
        if (filter === 'all') {
            btnAll.style.background = '#f1f5f9';
            btnAll.style.color = '#0f172a';
            btnActive.style.background = 'transparent';
            btnActive.style.color = '#64748b';
        } else {
            btnAll.style.background = 'transparent';
            btnAll.style.color = '#64748b';
            btnActive.style.background = '#d1fae5';
            btnActive.style.color = '#059669';
        }
    }

    updatePagination();
}

// Date filter dropdown
function toggleDateDropdown() {
    const dropdown = document.getElementById('dateDropdown');
    if (dropdown) dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function setDateFilter(days, label) {
    const labelEl = document.getElementById('dateFilterLabel');
    const dropdown = document.getElementById('dateDropdown');
    if (labelEl) labelEl.textContent = label;
    if (dropdown) dropdown.style.display = 'none';

    // Filter by date (client-side simulation - for real filtering, would need server-side)
    const now = new Date();
    const allRows = document.querySelectorAll('.log-row');

    allRows.forEach(row => {
        if (days === 'all') {
            // Remove the attribute so it defaults to true
            delete row.dataset.dateFiltered;
        } else {
            const rowDate = new Date(row.dataset.fecha);
            const diffDays = (now - rowDate) / (1000 * 60 * 60 * 24);
            // If within range, dateFiltered = true (actually, original logic was weird: diffDays > parseInt(days) ? 'true' : 'false' ... wait.
            // "Últimos 7 días": diffDays < 7 should be true.
            // Original code: row.dataset.dateFiltered = diffDays > parseInt(days) ? 'true' : 'false';
            // Wait, if diffDays > 7, then it's OLDER than 7 days.
            // So if I want "Last 7 days", I want diffDays <= 7.
            // Original code logic: `diffDays > parseInt(days) ? 'true' : 'false'`.
            // If diffDays (10) > 7 => 'true'. The filter logic used to check `row.dataset.dateFiltered === 'true'`.
            // Wait, original updatePagination didn't use dateFiltered explicitly in the snippet I saw?
            // Ah, line 386 in original: `row.dataset.dateFiltered = 'false'`.
            // I need to check how original updatePagination used it.
            // Original updatePagination didn't check dateFiltered!!
            // Wait, let me check the original file content provided.
            // Lines 307-316 (getFilteredRows) and 283-302 (updatePagination loop)
            // They DON'T seem to check `dataset.dateFiltered`?
            // Wait, looking closer at the provided Logs content.
            // Line 382: setDateFilter function sets `row.dataset.dateFiltered`.
            // But `updatePagination` (lines 262-305) DOES NOT READ IT.
            // This suggests the date filter was BROKEN in the original code or I missed something.
            // Let's look at `getFilteredRows` in original:
            // return showByFilter && showBySearch;
            // It completely ignores date!

            // I will FIX this in the refactor.
            // Functional intention: "Últimos X días".
            row.dataset.dateFiltered = (diffDays <= parseInt(days)) ? 'true' : 'false';
        }
    });

    currentPage = 1;
    updatePagination();
}

// Export function
function exportLogs() {
    let csvContent = "Usuario,ID,Fecha Inicio,Fecha Fin,Duración,Estado\n";
    const allRows = document.querySelectorAll('.log-row');

    allRows.forEach(row => {
        // Only export visible?? Or all? Usually export all matches? 
        // Original exported ALL rows regardless of filter.

        const username = row.querySelector('.log-username').textContent;
        const id = row.querySelector('.log-userid').textContent.replace('ID: ', '');
        const items = row.querySelectorAll('.log-detail-item span');
        const fecha = items[0]?.textContent || '';
        // Duracion is usually second item's text, but if active it has different structure.
        // Let's grab text content roughly.
        let duracion = '';
        if (items.length > 1) duracion = items[1].textContent.replace('Duración: ', '');

        const estado = row.classList.contains('active-session') ? 'Activa' : 'Cerrada';

        csvContent += `"${username}","${id}","${fecha}","","${duracion}","${estado}"\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'auditoria_accesos_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

// Ver detalle de sesión
function verDetalle(username, userId, fechaInicio, fechaFin, duracion, estado) {
    const modal = document.getElementById('detalleModal');
    if (!modal) return;

    document.getElementById('modal-username').textContent = username;
    document.getElementById('modal-userId').textContent = userId;
    document.getElementById('modal-fechaInicio').textContent = fechaInicio;
    document.getElementById('modal-fechaFin').textContent = fechaFin || 'Sesión activa';
    document.getElementById('modal-duracion').textContent = duracion;
    const estadoEl = document.getElementById('modal-estado');
    estadoEl.textContent = estado;
    estadoEl.style.color = estado === 'Activa' ? '#059669' : '#64748b';
    estadoEl.style.background = estado === 'Activa' ? '#d1fae5' : '#f1f5f9';

    // Iniciales
    document.getElementById('modal-avatar').textContent = username.substring(0, 2).toUpperCase();

    modal.style.display = 'flex';
}

function cerrarModal() {
    const modal = document.getElementById('detalleModal');
    if (modal) modal.style.display = 'none';
}
