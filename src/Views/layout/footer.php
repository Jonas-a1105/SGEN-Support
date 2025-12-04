    </div>
</main>

<footer class="text-center py-3 mt-auto" style="background: var(--glass); border-top: 1px solid var(--border);">
    <div class="container">
        SGEN-Support &copy; <?= date('Y') ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>vendors/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="<?= BASE_URL ?>js/datatables-global.js"></script>

<script>
    // Pasamos la URL base de PHP a JavaScript
    const APP_BASE_URL = '<?= BASE_URL ?>';
</script>

<!-- Dark Mode Initialization -->
<script>
    // Cargar tema guardado desde PHP session
    const savedTheme = '<?= $_SESSION['tema'] ?? 'light' ?>';
    document.documentElement.setAttribute('data-theme', savedTheme);
</script>

<script src="<?= BASE_URL ?>js/inactivity-logout.js"></script>
<script src="<?= BASE_URL ?>js/app.js"></script>

<?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<?= $_SESSION['flash_message']['type'] === 'error' ? '¡Error!' : '¡Éxito!' ?>',
                text: '<?= addslashes($_SESSION['flash_message']['message']) ?>',
                icon: '<?= $_SESSION['flash_message']['type'] === 'error' ? 'error' : 'success' ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        });
    </script>
    
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

</body>
</html>