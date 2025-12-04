<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/left-side-menu.php'; ?>

<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3 fw-bold">
                <i class="bi bi-info-circle-fill me-2"></i>
                Acerca de SGEN-Support
            </h1>
        </div>
    </div>

    <!-- Información Principal -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card glass-card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-cloud-fill text-primary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 fw-bold"><?= htmlspecialchars($systemInfo['nombre']) ?></h2>
                        <p class="text-muted mb-0">Versión <?= htmlspecialchars($systemInfo['version']) ?></p>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="px-md-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-file-text me-2 text-primary"></i>
                            Descripción
                        </h5>
                        <p class="text-muted mb-4">
                            <?= htmlspecialchars($systemInfo['descripcion']) ?>. Una solución integral 
                            para la gestión eficiente de tickets de soporte, inventario de equipos, 
                            mantenimientos programados y administración de personal.
                        </p>

                        <h5 class="fw-bold mb-3 mt-4">
                            <i class="bi bi-stack me-2 text-primary"></i>
                            Tecnologías
                        </h5>
                        <div class="row g-3 mb-4">
                            <?php foreach ($systemInfo['stack'] as $tech => $value): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 rounded glass-card">
                                    <i class="bi bi-check-circle-fill text-success me-3"></i>
                                    <div>
                                        <strong><?= htmlspecialchars($tech) ?>:</strong>
                                        <span class="text-muted"><?= htmlspecialchars($value) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-center py-3 border-top">
                            <p class="text-muted mb-0">
                                <i class="bi bi-c-circle me-1"></i>
                                <?= $systemInfo['year'] ?> SGEN-Support. Todos los derechos reservados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos del Sistema -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                Módulos del Sistema
            </h4>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($systemInfo['modulos'] as $modulo): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card glass-card border-0 h-100 hoverable-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="bi <?= htmlspecialchars($modulo['icono']) ?> text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($modulo['nombre']) ?></h5>
                            <p class="card-text text-muted small mb-0">
                                <?= htmlspecialchars($modulo['descripcion']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Información Técnica del Sistema -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-gear-wide-connected me-2"></i>
                Información Técnica
            </h4>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Servidor -->
        <div class="col-md-6">
            <div class="card glass-card border-0 h-100">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="bi bi-server me-2 text-primary"></i>Servidor
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Software del Servidor</span>
                        <span class="text-muted small"><?= htmlspecialchars($techInfo['server_software']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Sistema Operativo</span>
                        <span class="text-muted small"><?= htmlspecialchars($techInfo['os']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Versión de PHP</span>
                        <span class="badge bg-primary"><?= htmlspecialchars($techInfo['php_version']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Base de Datos</span>
                        <span class="text-muted small"><?= htmlspecialchars($techInfo['db_driver']) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Configuración PHP -->
        <div class="col-md-6">
            <div class="card glass-card border-0 h-100">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="bi bi-gear-fill me-2 text-primary"></i>Configuración PHP
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Límite de Memoria</span>
                        <span class="fw-bold"><?= htmlspecialchars($techInfo['memory_limit']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Max Upload Size</span>
                        <span class="fw-bold"><?= htmlspecialchars($techInfo['upload_max_filesize']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Max Post Size</span>
                        <span class="fw-bold"><?= htmlspecialchars($techInfo['post_max_size']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small">Max Execution Time</span>
                        <span class="fw-bold"><?= htmlspecialchars($techInfo['max_execution_time']) ?>s</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Aplicación -->
        <div class="col-12">
            <div class="card glass-card border-0">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="bi bi-app-indicator me-2 text-primary"></i>Aplicación
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">URL Base</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-link"></i></span>
                                <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($techInfo['base_url']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Estado del Sistema</label>
                            <div>
                                <span class="badge bg-success p-2"><i class="bi bi-check-circle me-1"></i> Operativo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Créditos -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-people-fill me-2"></i>
                Créditos
            </h4>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Desarrollado por -->
        <div class="col-12">
            <div class="card glass-card border-0">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="bi bi-code-slash me-2 text-primary"></i>Desarrollado por: Jonas Mendoza
                </div>
                <div class="card-body">
                    <?php foreach ($credits['development'] as $member): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($member['name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($member['role']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Changelog / Historial de Versiones -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-clock-history me-2"></i>
                Historial de Versiones
            </h4>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            <div class="card glass-card border-0">
                <div class="card-body p-4">
                    <?php foreach ($changelog as $index => $version): ?>
                    <div class="version-entry <?= $index < count($changelog) - 1 ? 'mb-4 pb-4 border-bottom' : '' ?>">
                        <div class="d-flex align-items-center mb-3">
                            <?php
                            $badgeClass = $version['type'] === 'release' ? 'bg-success' : 'bg-info';
                            $badgeIcon = $version['type'] === 'release' ? 'bi-rocket-takeoff-fill' : 'bi-arrow-up-circle-fill';
                            ?>
                            <span class="badge <?= $badgeClass ?> me-2 p-2">
                                <i class="bi <?= $badgeIcon ?> me-1"></i>
                                v<?= htmlspecialchars($version['version']) ?>
                            </span>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('d/m/Y', strtotime($version['date'])) ?>
                            </small>
                        </div>
                        <ul class="changes-list mb-0">
                            <?php foreach ($version['changes'] as $change): ?>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right-short text-primary me-1"></i>
                                <span class="small"><?= htmlspecialchars($change) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Contacto/Soporte -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-headset me-2 text-primary"></i>
                        Soporte y Asistencia
                    </h5>
                    <p class="text-muted mb-3">
                        Para obtener ayuda o reportar problemas, contacta al administrador del sistema.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>soportes/crear" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Crear Ticket
                        </a>
                        <a href="<?= BASE_URL ?>configuracion" class="btn btn-outline-secondary">
                            <i class="bi bi-gear me-2"></i>
                            Configuración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hoverable-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hoverable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.glass-card {
    background: var(--glass-opaque, rgba(255, 255, 255, 0.95));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
</style>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
