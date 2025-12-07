/**
 * Modern Delete Modal Logic
 * Handles the opening, context updates, and deletion action.
 */

const DeleteModal = {
    overlay: null,
    container: null,
    deleteBtn: null,
    deleteUrl: null,
    isDeleting: false,

    elements: {
        id: null,
        title: null,
        author: null
    },

    init() {
        this.overlay = document.getElementById('deleteModalOverlay');
        if (!this.overlay) return;

        this.container = this.overlay.querySelector('.delete-modal-container');
        this.deleteBtn = document.getElementById('btnConfirmDelete');

        // Context Elements
        this.elements.id = document.getElementById('deleteContextId');
        this.elements.title = document.getElementById('deleteContextTitle');
        this.elements.author = document.getElementById('deleteContextAuthor');

        // Binds
        this.deleteBtn.addEventListener('click', () => this.handleDelete());

        // Close triggers
        document.getElementById('btnCancelDelete').addEventListener('click', () => this.close());
        document.getElementById('btnCloseModal').addEventListener('click', () => this.close());
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });
    },

    /**
     * Opens the modal
     * @param {string} url - The deletion URL
     * @param {object} meta - { id, title, type, author }
     */
    open(url, meta = {}) {
        if (!this.overlay) this.init(); // Lazy init

        this.deleteUrl = url;
        this.isDeleting = false;
        this.resetBtn();

        // Update UI
        if (this.elements.id) this.elements.id.textContent = meta.id || '#???';
        if (this.elements.title) this.elements.title.textContent = meta.title || 'Elemento sin título';
        if (this.elements.author) this.elements.author.textContent = meta.author ? `Por: ${meta.author}` : '';

        // Show
        this.overlay.classList.add('open');
    },

    close() {
        if (this.isDeleting) return; // Prevent closing while deleting
        this.overlay.classList.remove('open');
    },

    handleDelete() {
        if (!this.deleteUrl) return;

        this.isDeleting = true;
        this.setLoading(true);

        // Simulate network delay for UX (optional, matches React demo)
        setTimeout(() => {
            window.location.href = this.deleteUrl;
        }, 500);
    },

    setLoading(loading) {
        if (loading) {
            this.deleteBtn.disabled = true;
            this.deleteBtn.innerHTML = '<div class="spinner"></div>';
        } else {
            this.resetBtn();
        }
    },

    resetBtn() {
        this.deleteBtn.disabled = false;
        this.deleteBtn.innerHTML = 'Eliminar';
    }
};

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
    DeleteModal.init();
});
