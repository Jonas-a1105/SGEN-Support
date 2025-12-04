<div class="centered-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-dark">
            <i class="bi bi-gear-fill me-2"></i>
            Configuración del Sistema
        </h2>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-palette-fill me-2"></i>
                Apariencia del Sistema
            </h5>
        </div>
        <div class="card-body p-4">
            <p class="text-muted">Selecciona tu preferencia de tema para el sistema.</p>

            <form id="themeForm" action="<?= BASE_URL ?>configuracion/guardar" method="POST">
                
                <?php $temaActual = $_SESSION['tema'] ?? 'light'; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="tema" id="tema-light" value="light" <?= $temaActual == 'light' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary w-100 p-4" for="tema-light">
                            <i class="bi bi-sun-fill fs-1 d-block mb-3 text-warning"></i>
                            <h5>Modo Claro</h5>
                            <p class="small text-muted mb-0">Fondo claro con texto oscuro</p>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="tema" id="tema-dark" value="dark" <?= $temaActual == 'dark' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary w-100 p-4" for="tema-dark">
                            <i class="bi bi-moon-stars-fill fs-1 d-block mb-3 text-primary"></i>
                            <h5>Modo Oscuro</h5>
                            <p class="small text-muted mb-0">Fondo oscuro con texto claro</p>
                        </label>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>

        </div>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Nota:</strong> Los cambios se aplicarán inmediatamente después de guardar.
    </div>
</div>

<script>
// Aplicar tema inmediatamente al cambiar
document.querySelectorAll('input[name="tema"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.documentElement.setAttribute('data-theme', this.value);
    });
});
</script>