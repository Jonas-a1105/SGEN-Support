<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Esto es HTML: etiquetas para el diseño -->
        <div class="card glass-card p-5 text-center">
            
            <h1 class="display-4 fw-bold text-primary mb-4">
                🚀 <?= $mimensaje ?>
            </h1>

            <p class="fs-4 text-muted">
                Bienvenido, <strong><?= $nombre_usuario ?></strong>.
            </p>

            <div class="mt-4 p-3 bg-light rounded-pill d-inline-block">
                <span class="fs-5">
                    🕒 La hora exacta en el servidor es: 
                    <span class="text-info fw-bold"><?= $hora ?></span>
                </span>
            </div>

            <hr class="my-5">

            <div class="text-start">
                <h3>¿Qué acabas de aprender?</h3>
                <ul>
                    <li><strong><?= '...' ?></strong>: Esta etiqueta de PHP sirve para imprimir datos en la pantalla.</li>
                    <li><strong>$variables</strong>: Son los nombres que usamos para guardar información (como el mensaje o la hora).</li>
                    <li><strong>HTML</strong>: Las etiquetas como <code>&lt;h1&gt;</code> o <code>&lt;p&gt;</code> dan la forma visual.</li>
                </ul>
            </div>

            <div class="mt-5">
                <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-4">
                    Volver al Inicio
                </a>
            </div>

        </div>
    </div>
</div>
