<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1 class="display-1 text-danger">404</h1>
                <h2>Página No Encontrada</h2>
                <div class="error-details my-4">
                    <p class="text-muted">Lo sentimos, la página que buscas no existe o ha sido movida.</p>
                </div>
                <div class="error-actions">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                        <i class="bi bi-house-door me-2"></i>
                        Volver al Inicio
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="bi bi-arrow-left me-2"></i>
                        Regresar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-template {
    padding: 40px 15px;
}
.error-template h1 {
    font-size: 8em;
    font-weight: bold;
}
</style>
