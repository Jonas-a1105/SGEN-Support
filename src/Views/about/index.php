<?php 
// Modern About Page View
// Loaded via Controller with $systemInfo, $techInfo, $credits, $changelog
?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/about-modern.css?v=<?= time() ?>">

<div class="about-wrapper">
    <div class="about-card">
        
        <!-- SIDEBAR -->
        <div class="about-sidebar">
            <div class="sidebar-header">
                <div class="logo-box">
                    <!-- Same logo as sidebar -->
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="app-title"><?= htmlspecialchars($systemInfo['nombre']) ?></h1>
                    <span class="app-version">v<?= htmlspecialchars($systemInfo['version']) ?> (Stable)</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <button class="nav-btn active" data-target="tab-general">
                    <i class="bi bi-info-circle nav-btn-icon"></i>
                    Visión General
                </button>
                <button class="nav-btn" data-target="tab-modules">
                    <i class="bi bi-grid nav-btn-icon"></i>
                    Módulos
                </button>
                <button class="nav-btn" data-target="tab-technical">
                    <i class="bi bi-hdd-network nav-btn-icon"></i>
                    Info. Técnica
                </button>
                <button class="nav-btn" data-target="tab-changelog">
                    <i class="bi bi-clock-history nav-btn-icon"></i>
                    Versiones
                </button>
                <button class="nav-btn" data-target="tab-credits">
                    <i class="bi bi-people nav-btn-icon"></i>
                    Créditos
                </button>
            </nav>

            <div class="sidebar-footer">
                <div style="font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="bi bi-shield-check"></i> Licencia MIT
                </div>
                <p style="font-size: 0.625rem; color: #cbd5e1; margin: 0;">© <?= date('Y') ?> SGEN-Support Inc.</p>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="about-content">
            
            <!-- 1. GENERAL TAB -->
            <div id="tab-general" class="tab-pane active">
                <h2 class="section-title">Acerca del Sistema</h2>
                <p class="section-desc">
                    <?= htmlspecialchars($systemInfo['descripcion']) ?>. 
                    Centraliza incidencias, inventario y personal en una plataforma unificada y escalable.
                </p>

                <div class="tech-grid">
                    <div class="tech-card">
                        <div class="tech-icon-box indigo"><i class="bi bi-code-square" style="font-size: 1.25rem;"></i></div>
                        <div class="tech-info">
                            <span class="tech-label">Backend</span>
                            <p class="tech-value">PHP <?= htmlspecialchars($techInfo['php_version']) ?></p>
                            <span class="tech-sub">Laravel Like (MVC)</span>
                        </div>
                    </div>
                    <div class="tech-card">
                        <div class="tech-icon-box cyan"><i class="bi bi-window-desktop" style="font-size: 1.25rem;"></i></div>
                        <div class="tech-info">
                            <span class="tech-label">Frontend</span>
                            <p class="tech-value">Vanilla JS / CSS</p>
                            <span class="tech-sub">Modern UI System</span>
                        </div>
                    </div>
                    <div class="tech-card">
                        <div class="tech-icon-box blue"><i class="bi bi-database" style="font-size: 1.25rem;"></i></div>
                        <div class="tech-info">
                            <span class="tech-label">Base de Datos</span>
                            <p class="tech-value">MySQL</p>
                            <span class="tech-sub"><?= htmlspecialchars($techInfo['db_driver']) ?></span>
                        </div>
                    </div>
                    <div class="tech-card">
                        <div class="tech-icon-box emerald"><i class="bi bi-cpu" style="font-size: 1.25rem;"></i></div>
                        <div class="tech-info">
                            <span class="tech-label">Arquitectura</span>
                            <p class="tech-value">MVC Modular</p>
                            <span class="tech-sub">Scalable Design</span>
                        </div>
                    </div>
                </div>

                <div class="status-box">
                    <div class="status-icon-wrapper">
                        <div class="status-ping-dot"></div>
                        <div class="status-icon-inner">
                            <i class="bi bi-activity"></i>
                        </div>
                    </div>
                    <div class="status-content" style="margin-right: auto;">
                        <span class="status-label-new">ESTADO GENERAL</span>
                        <h3 class="status-value-new">100% Operativo</h3>
                    </div>
                    
                    <div class="status-separator d-none d-md-block"></div>
                    
                    <div class="status-details d-none d-md-flex">
                        <div class="status-detail-item">
                            <span class="status-detail-label">Uptime</span>
                            <span class="status-detail-value"><?= htmlspecialchars($techInfo['uptime'] ?? 'N/A') ?></span>
                        </div>
                        <div class="status-detail-item">
                            <span class="status-detail-label">Latencia</span>
                            <span class="status-detail-value"><?= htmlspecialchars($techInfo['latency'] ?? '0') ?>ms</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. MODULES TAB -->
            <div id="tab-modules" class="tab-pane">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                    <h2 class="section-title" style="margin: 0;">Módulos Instalados</h2>
                    <span style="font-size: 0.75rem; font-weight: 600; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: #64748b;"><?= count($systemInfo['modulos']) ?> Activos</span>
                </div>

                <div class="tech-grid">
                    <?php 
                    $colors = ['mod-rose', 'mod-blue', 'mod-amber', 'mod-emerald', 'mod-indigo', 'mod-cyan'];
                    foreach($systemInfo['modulos'] as $idx => $mod): 
                        $colorClass = $colors[$idx % count($colors)];
                    ?>
                    <div class="module-card">
                        <div class="module-icon <?= $colorClass ?>">
                            <i class="bi <?= htmlspecialchars($mod['icono']) ?>"></i>
                        </div>
                        <h3 style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; font-size: 1rem;"><?= htmlspecialchars($mod['nombre']) ?></h3>
                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0; line-height: 1.5;"><?= htmlspecialchars($mod['descripcion']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. TECHNICAL TAB -->
            <div id="tab-technical" class="tab-pane">
                <h2 class="section-title">Especificaciones Técnicas</h2>
                <p class="section-desc">Detalles del entorno de ejecución.</p>

                <div class="spec-box">
                    <div class="spec-header">
                        <i class="bi bi-server"></i> Entorno del Servidor
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">OS</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['os']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Servidor Web</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['server_software']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">PHP Version</span>
                        <span class="spec-val" style="background: var(--indigo-50); color: var(--indigo-600); padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 700;"><?= htmlspecialchars($techInfo['php_version']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Base de Datos</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['db_driver']) ?></span>
                    </div>
                </div>

                <div class="spec-box">
                    <div class="spec-header">
                        <i class="bi bi-lightning"></i> Límites de Memoria (PHP.ini)
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Memory Limit</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['memory_limit']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Max Upload Size</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['upload_max_filesize']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Max Execution Time</span>
                        <span class="spec-val"><?= htmlspecialchars($techInfo['max_execution_time']) ?>s</span>
                    </div>
                </div>
            </div>

            <!-- 4. CHANGELOG TAB -->
            <div id="tab-changelog" class="tab-pane">
                <h2 class="section-title" style="margin-bottom: 2rem;">Historial de Cambios</h2>
                
                <div class="changelog-wrapper">
                    <?php foreach($changelog as $idx => $log): 
                        $isLatest = ($log['type'] === 'release' || $idx === count($changelog) - 1); // Logic assumption: last in array is usually latest, but user mapped differently. Let's assume passed order.
                        // Actually in controller array: idx 0 is 1.0.0, idx 1 is 1.0.1. So last item is latest.
                        $isLatest = ($idx === count($changelog) - 1);
                    ?>
                    <div class="version-item">
                        <div class="version-dot <?= $isLatest ? 'dot-latest' : 'dot-old' ?>"></div>
                        <div class="version-header">
                            <span class="version-num">v<?= htmlspecialchars($log['version']) ?></span>
                            <?php if($isLatest): ?>
                                <span class="badge-latest">LATEST</span>
                            <?php endif; ?>
                            <span class="version-date"><?= date('d M, Y', strtotime($log['date'])) ?></span>
                        </div>
                        <ul class="change-list">
                            <?php foreach($log['changes'] as $change): ?>
                            <li class="change-item">
                                <span class="change-bullet">•</span> <?= htmlspecialchars($change) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5. CREDITS TAB -->
            <div id="tab-credits" class="tab-pane">
                <h2 class="section-title">Equipo de Desarrollo</h2>
                <p class="section-desc">Mentes detrás del código.</p>
                
                <?php 
                // Primary dev from credits
                $devName = 'Jonas Mendoza';
                $devRole = 'Lead Fullstack Developer';
                if(!empty($credits['development'][0])) {
                    $devName = $credits['development'][0]['role']; // In controller struct was: name=>Desarrollador, role=>Jonas. Swapped logic to match display name
                    $devName = $credits['development'][0]['role']; // Jonas
                    $roleLabel = str_replace(':', '', $credits['development'][0]['name']); // Desarrollador
                }
                ?>

                <div class="dev-banner">
                    <div class="dev-glow"></div>
                    <div class="dev-content">
                        <div class="dev-avatar">
                            <?= strtoupper(substr($devName, 0, 2)) ?>
                        </div>
                        <div style="text-align: center; md:text-align: left;">
                            <h3 class="dev-name"><?= htmlspecialchars($devName) ?></h3>
                            <p class="dev-role"><?= htmlspecialchars($roleLabel ?? 'Fullstack Developer') ?></p>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>js/about.js?v=<?= time() ?>"></script>
