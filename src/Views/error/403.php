<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4 p-md-5">
                <div class="card-body">
                    <h1 class="display-1 fw-bold text-danger">403</h1>
                    <h2 class="h3 fw-bold">Acceso Denegado</h2>
                    <p class="text-muted fs-5">
                        No tienes los permisos necesarios para acceder a esta página.
                    </p>
                    <p>
                        Tu rol actual es: 
                        <strong class="text-primary"><?= ucfirst(htmlspecialchars($_SESSION['rol'] ?? 'Desconocido')) ?></strong>
                    </p>
                    <a href="<?= BASE_URL ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-house-door-fill me-1"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>