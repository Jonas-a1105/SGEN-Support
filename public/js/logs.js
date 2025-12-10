/**
 * Logic for Logs View (Server-Side Pagination & Filtering)
 * Updated to work with backend filtering
 */

// Inicialización
document.addEventListener('DOMContentLoaded', function () {
    // Mover modal al body para evitar problemas de stacking context
    const modal = document.getElementById('detalleModal');
    if (modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('dateDropdown');
        const btn = document.getElementById('dateFilterBtn');
        if (dropdown && btn && !btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    }

    // Initialize Search Input with URL param
    const urlParams = new URLSearchParams(window.location.search);
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = urlParams.get('username') || '';

        // Add Enter key listener
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    }
});

function applyFilters() {
    const searchInput = document.getElementById('searchInput');
    const username = searchInput ? searchInput.value.trim() : '';

    const url = new URL(window.location.href);

    if (username) {
        url.searchParams.set('username', username);
    } else {
        url.searchParams.delete('username');
    }

    // Reset to page 1 on filter change
    url.searchParams.set('page', 1);

    window.location.href = url.toString();
}

function changeItemsPerPage() {
    const select = document.getElementById('itemsPerPage');
    if (!select) return;

    const perPage = select.value;

    // Set cookie
    document.cookie = "sgen_logs_per_page=" + perPage + "; path=/; max-age=31536000"; // 1 year

    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // Reset to page 1
    window.location.href = url.toString();
}

function toggleDateDropdown() {
    const dropdown = document.getElementById('dateDropdown');
    if (dropdown) dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function setDateFilter(option, label) {
    const url = new URL(window.location.href);
    const today = new Date();

    // Clear existing date filters
    url.searchParams.delete('fecha_desde');
    url.searchParams.delete('fecha_hasta');

    if (option !== 'all') {
        const pastDate = new Date();
        pastDate.setDate(today.getDate() - parseInt(option));

        // Format YYYY-MM-DD
        const fromDate = pastDate.toISOString().split('T')[0];
        const toDate = today.toISOString().split('T')[0]; // Optional, default is until now

        url.searchParams.set('fecha_desde', fromDate);
        // url.searchParams.set('fecha_hasta', toDate); 
    }

    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

// Export functionality (Client-side for visible page only for now, can be upgraded)
function exportLogs() {
    let csvContent = "Usuario,ID,Fecha Inicio,Fecha Fin,Duración,Estado\n";
    const allRows = document.querySelectorAll('.log-row');

    allRows.forEach(row => {
        const username = row.querySelector('.log-username').textContent.trim();
        const id = row.querySelector('.log-userid').textContent.replace('ID: ', '').trim();
        const items = row.querySelectorAll('.log-detail-item span');
        const fecha = items[0]?.textContent.trim() || '';

        let duracion = '';
        if (items.length > 1) {
            // Handle active session span
            if (items[1].classList.contains('active-status')) {
                duracion = 'En curso';
            } else {
                duracion = items[1].textContent.replace('Duración: ', '').trim();
            }
        } else {
            // Fallback check
            if (row.querySelector('.log-detail-item span[style*="color: #059669"]')) {
                duracion = 'En curso';
            }
        }

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

    // Asegurar que está en el body (doble check)
    if (modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }

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
    const iniciales = username ? username.substring(0, 2).toUpperCase() : '??';
    document.getElementById('modal-avatar').textContent = iniciales;

    // Show modal with animation
    modal.style.display = 'flex';
    // Force reflow
    modal.offsetHeight;
    modal.classList.add('active');
}

function cerrarModal() {
    const modal = document.getElementById('detalleModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}
