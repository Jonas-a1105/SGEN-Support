/**
 * Ticket Bulk Delete - Selection and Batch Deletion
 * Modular, Turbo-compatible version.
 */
(function () {
    if (window.TICKET_BULK_DELETE_LOADED) return;
    window.TICKET_BULK_DELETE_LOADED = true;

    const getBaseUrl = () => window.BASE_URL || document.body.dataset.baseUrl || '/';

    function initTicketBulkDelete() {
        const checkBulkMode = document.getElementById('checkBulkMode');
        const btnBulkDelete = document.getElementById('btnBulkTicketDelete');
        const selectedCountSpan = document.getElementById('ticketSelectedCount');
        const feed = document.getElementById('ticketFeed');

        if (!checkBulkMode || !feed) return; // Not on ticket list page

        let isSelectionMode = false;
        let selectedIds = new Set();

        // Move Modal to Body to escape stacking context
        const modalEl = document.getElementById('customDeleteModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Toggle Selection Mode via Checkbox
        checkBulkMode.addEventListener('change', function () {
            isSelectionMode = this.checked;
            const containerSelectAll = document.getElementById('containerSelectAll');

            if (isSelectionMode) {
                feed.classList.add('selection-active');
                if (containerSelectAll) containerSelectAll.classList.remove('d-none');
            } else {
                feed.classList.remove('selection-active');
                if (containerSelectAll) containerSelectAll.classList.add('d-none');
                selectedIds.clear();

                const checkSelectAll = document.getElementById('checkSelectAll');
                if (checkSelectAll) checkSelectAll.checked = false;

                const selectedTickets = feed.querySelectorAll('.helpdesk-ticket.selected');
                selectedTickets.forEach(t => t.classList.remove('selected'));

                updateUI();
            }
        });

        // Select All Logic
        const checkSelectAll = document.getElementById('checkSelectAll');
        if (checkSelectAll) {
            checkSelectAll.addEventListener('change', function () {
                const isChecked = this.checked;
                const visibleTickets = feed.querySelectorAll('.helpdesk-ticket');
                visibleTickets.forEach(ticket => {
                    const id = ticket.dataset.ticketId;
                    if (isChecked) {
                        selectedIds.add(id);
                        ticket.classList.add('selected');
                    } else {
                        selectedIds.delete(id);
                        ticket.classList.remove('selected');
                    }
                });
                updateUI();
            });
        }

        // Event Delegation for Clicks (Selection)
        feed.addEventListener('click', function (e) {
            if (!isSelectionMode) return;

            const ticket = e.target.closest('.helpdesk-ticket');
            if (ticket) {
                e.preventDefault();
                e.stopPropagation();

                const id = ticket.dataset.ticketId;
                if (selectedIds.has(id)) {
                    selectedIds.delete(id);
                    ticket.classList.remove('selected');
                } else {
                    selectedIds.add(id);
                    ticket.classList.add('selected');
                }
                updateUI();
            }
        });

        // Helper to Close Modal
        function closeDeleteModal() {
            document.getElementById('customDeleteModal').classList.remove('active');
            document.getElementById('deleteConfirmationInput').value = '';
            document.getElementById('confirmDeleteBtn').disabled = true;
            document.getElementById('confirmDeleteBtn').classList.remove('active');
            document.getElementById('unlockIcon').classList.add('d-none');
            document.getElementById('deleteConfirmationInput').classList.remove('match');
        }

        // Close Modal Events
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const backdrop = document.querySelector('.custom-modal-backdrop');

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeDeleteModal);
        if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        if (backdrop) backdrop.addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('customDeleteModal')?.classList.contains('active')) {
                closeDeleteModal();
            }
        });

        // Input Validation
        const deleteInput = document.getElementById('deleteConfirmationInput');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const unlockIcon = document.getElementById('unlockIcon');

        if (deleteInput) {
            deleteInput.addEventListener('input', function () {
                const val = this.value.toUpperCase();

                if (val === 'ELIMINAR') {
                    this.classList.add('match');
                    confirmBtn.classList.add('active');
                    confirmBtn.disabled = false;
                    unlockIcon.classList.remove('d-none');
                } else {
                    this.classList.remove('match');
                    confirmBtn.classList.remove('active');
                    confirmBtn.disabled = true;
                    unlockIcon.classList.add('d-none');
                }
            });
        }

        // Bulk Delete Action (Open Modal)
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function () {
                if (selectedIds.size === 0) return;

                document.getElementById('deleteCountDisplay').textContent = `${selectedIds.size} tickets`;
                document.getElementById('customDeleteModal').classList.add('active');
                setTimeout(() => document.getElementById('deleteConfirmationInput').focus(), 100);
            });
        }

        // Confirm Delete Action
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (deleteInput.value.toUpperCase() !== 'ELIMINAR') return;

                confirmBtn.disabled = true;
                confirmBtn.querySelector('.normal-state').classList.add('d-none');
                confirmBtn.querySelector('.loading-state').classList.remove('d-none');
                deleteInput.disabled = true;

                fetch(getBaseUrl() + 'soportes/eliminar_tickets_masivos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: Array.from(selectedIds) })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            selectedIds.forEach(id => {
                                const el = feed.querySelector(`.helpdesk-ticket[data-ticket-id="${id}"]`);
                                if (el) {
                                    el.style.opacity = '0';
                                    el.style.transform = 'scale(0.9)';
                                    setTimeout(() => el.remove(), 300);
                                }
                            });

                            setTimeout(() => {
                                closeDeleteModal();
                                checkBulkMode.checked = false;
                                document.getElementById('checkSelectAll').checked = false;
                                document.getElementById('containerSelectAll').classList.add('d-none');
                                feed.classList.remove('selection-active');
                                selectedIds.clear();
                                updateUI();

                                confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                                confirmBtn.querySelector('.loading-state').classList.add('d-none');
                                deleteInput.disabled = false;

                                if (window.Toast) Toast.show('success', data.message);
                                else if (window.Swal) Swal.fire('Eliminados', data.message, 'success');
                            }, 500);

                        } else {
                            closeDeleteModal();
                            confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                            confirmBtn.querySelector('.loading-state').classList.add('d-none');
                            deleteInput.disabled = false;
                            if (window.Swal) Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        closeDeleteModal();
                        confirmBtn.querySelector('.normal-state').classList.remove('d-none');
                        confirmBtn.querySelector('.loading-state').classList.add('d-none');
                        deleteInput.disabled = false;
                        if (window.Swal) Swal.fire('Error', 'Error de conexión', 'error');
                    });
            });
        }

        function updateUI() {
            if (selectedCountSpan) selectedCountSpan.textContent = selectedIds.size;
            if (btnBulkDelete) {
                if (selectedIds.size > 0) btnBulkDelete.classList.add('visible');
                else btnBulkDelete.classList.remove('visible');
            }
        }
    }

    // Initialize on Turbo navigation
    document.addEventListener('turbo:load', initTicketBulkDelete);

    // Also run immediately if already loaded
    if (document.readyState !== 'loading') {
        initTicketBulkDelete();
    }
})();
