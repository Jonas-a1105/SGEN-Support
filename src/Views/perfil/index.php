<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Cambiar mi Contraseña</h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="<?= BASE_URL ?>perfil/actualizar" method="POST" autocomplete="off">

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password_actual" name="password_actual" placeholder="Contraseña Actual" required>
                        <label for="password_actual">Contraseña Actual</label>
                    </div>

                    <hr class="my-4">

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" placeholder="Nueva Contraseña" required autocomplete="new-password">
                        <label for="password_nuevo">Nueva Contraseña</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" placeholder="Confirmar Nueva Contraseña" required autocomplete="new-password">
                        <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-key-fill me-1"></i>
                            Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>