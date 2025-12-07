<?php 
$temaActual = $_SESSION['tema'] ?? 'light';
$colorActual = $_SESSION['color_acento'] ?? 'indigo';
$densidadActual = $_SESSION['densidad'] ?? 'comfortable';
?>

<div style="background: #f8fafc; padding: 2rem; display: flex; justify-content: center; width: 100vw; position: relative; left: 50%; margin-left: -50vw;">
    
    <div style="max-width: 1150px; min-width: 1150px; width: 100%; background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; overflow: hidden; display: flex;">
        
        <!-- SIDEBAR DE NAVEGACIÓN -->
        <aside style="width: 280px; background: #f8fafc; border-right: 1px solid #f1f5f9; display: flex; flex-direction: column;">
            
            <!-- Header del Sidebar -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-gear-fill" style="color: #6366f1;"></i>
                    Configuración
                </h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0;">Gestiona tus preferencias globales.</p>
            </div>
            
            <!-- Navegación -->
            <nav style="flex: 1; padding: 1rem; display: flex; flex-direction: column; gap: 0.25rem; overflow-y: auto;">
                
                <button onclick="switchConfigTab('general')" id="tab-general" class="config-tab" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: none; text-align: left; cursor: pointer; background: transparent; color: #475569;">
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: transparent;">
                        <i class="bi bi-globe" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">General</p>
                        <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Idioma y región</p>
                    </div>
                </button>

                <button onclick="switchConfigTab('appearance')" id="tab-appearance" class="config-tab config-tab-active" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; text-align: left; cursor: pointer; background: white; color: #6366f1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: #eef2ff;">
                        <i class="bi bi-palette" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Apariencia</p>
                        <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Temas y colores</p>
                    </div>
                </button>

                <button onclick="switchConfigTab('notifications')" id="tab-notifications" class="config-tab" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: none; text-align: left; cursor: pointer; background: transparent; color: #475569;">
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: transparent;">
                        <i class="bi bi-bell" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Notificaciones</p>
                        <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Alertas y correos</p>
                    </div>
                </button>

                <button onclick="switchConfigTab('security')" id="tab-security" class="config-tab" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: none; text-align: left; cursor: pointer; background: transparent; color: #475569;">
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: transparent;">
                        <i class="bi bi-shield-lock" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Seguridad</p>
                        <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Contraseña y acceso</p>
                    </div>
                </button>

                <button onclick="switchConfigTab('account')" id="tab-account" class="config-tab" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: none; text-align: left; cursor: pointer; background: transparent; color: #475569;">
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: transparent;">
                        <i class="bi bi-person" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Mi Cuenta</p>
                        <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Perfil y datos</p>
                    </div>
                </button>

            </nav>
            
            <!-- Footer del Sidebar -->
            <div style="padding: 1rem; text-align: center; border-top: 1px solid #f1f5f9;">
                <p style="font-size: 0.625rem; color: #94a3b8; margin: 0;">Versión del Sistema v2.4.0</p>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main style="flex: 1; display: flex; flex-direction: column;">
            
            <!-- Header del Contenido -->
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: white;">
                <div>
                    <h1 id="content-title" style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Apariencia</h1>
                    <p id="content-desc" style="color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0 0;">Personaliza cómo se ve y se siente la aplicación.</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" onclick="location.reload()" style="padding: 0.5rem 1rem; color: #64748b; font-weight: 500; background: transparent; border: none; cursor: pointer; border-radius: 0.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.375rem;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Restaurar
                    </button>
                    <button type="submit" form="configForm" style="padding: 0.5rem 1rem; background: #0f172a; color: white; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; display: flex; align-items: center; gap: 0.375rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <i class="bi bi-check2"></i>
                        Guardar
                    </button>
                </div>
            </div>

            <!-- Contenido Scrolleable -->
            <div style="flex: 1; padding: 2rem; overflow-y: auto;">
                
                <form id="configForm" action="<?= BASE_URL ?>configuracion/guardar" method="POST">
                
                <!-- PANEL: Apariencia -->
                <div id="panel-appearance" class="config-panel">
                    
                    <!-- SECCIÓN 1: TEMA -->
                    <section style="margin-bottom: 2.5rem;">
                        <h3 style="font-size: 0.75rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-sun" style="color: #94a3b8;"></i>
                            Tema del Interfaz
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                            
                            <!-- Opción CLARO -->
                            <label style="cursor: pointer;">
                                <input type="radio" name="tema" value="light" <?= $temaActual == 'light' ? 'checked' : '' ?> style="display: none;" onchange="selectTheme(this)">
                                <div id="theme-light" class="theme-card <?= $temaActual == 'light' ? 'theme-selected' : '' ?>" style="border-radius: 1rem; border: 2px solid <?= $temaActual == 'light' ? '#6366f1' : '#e2e8f0' ?>; overflow: hidden; transition: all 0.2s;">
                                    <div style="background: #f8fafc; padding: 1rem; height: 100px; display: flex; flex-direction: column; gap: 0.5rem;">
                                        <div style="height: 8px; width: 75%; background: white; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"></div>
                                        <div style="height: 8px; width: 50%; background: white; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"></div>
                                        <div style="flex: 1; background: white; border-radius: 8px; margin-top: 0.5rem; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-sun-fill" style="font-size: 1.5rem; color: #fbbf24; opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                    <div style="padding: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background: white;">
                                        <span style="font-weight: 600; font-size: 0.875rem; color: #0f172a;">Modo Claro</span>
                                        <div id="check-light" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid <?= $temaActual == 'light' ? '#6366f1' : '#e2e8f0' ?>; display: flex; align-items: center; justify-content: center; background: <?= $temaActual == 'light' ? '#eef2ff' : 'white' ?>;">
                                            <?php if ($temaActual == 'light'): ?>
                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- Opción OSCURO -->
                            <label style="cursor: pointer;">
                                <input type="radio" name="tema" value="dark" <?= $temaActual == 'dark' ? 'checked' : '' ?> style="display: none;" onchange="selectTheme(this)">
                                <div id="theme-dark" class="theme-card <?= $temaActual == 'dark' ? 'theme-selected' : '' ?>" style="border-radius: 1rem; border: 2px solid <?= $temaActual == 'dark' ? '#6366f1' : '#e2e8f0' ?>; overflow: hidden; transition: all 0.2s;">
                                    <div style="background: #0f172a; padding: 1rem; height: 100px; display: flex; flex-direction: column; gap: 0.5rem;">
                                        <div style="height: 8px; width: 75%; background: #1e293b; border-radius: 4px;"></div>
                                        <div style="height: 8px; width: 50%; background: #1e293b; border-radius: 4px;"></div>
                                        <div style="flex: 1; background: #1e293b; border-radius: 8px; margin-top: 0.5rem; border: 1px solid #334155; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-moon-stars-fill" style="font-size: 1.5rem; color: #818cf8; opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                    <div style="padding: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background: white;">
                                        <span style="font-weight: 600; font-size: 0.875rem; color: #0f172a;">Modo Oscuro</span>
                                        <div id="check-dark" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid <?= $temaActual == 'dark' ? '#6366f1' : '#e2e8f0' ?>; display: flex; align-items: center; justify-content: center; background: <?= $temaActual == 'dark' ? '#eef2ff' : 'white' ?>;">
                                            <?php if ($temaActual == 'dark'): ?>
                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- Opción SISTEMA -->
                            <label style="cursor: pointer;">
                                <input type="radio" name="tema" value="system" <?= $temaActual == 'system' ? 'checked' : '' ?> style="display: none;" onchange="selectTheme(this)">
                                <div id="theme-system" class="theme-card <?= $temaActual == 'system' ? 'theme-selected' : '' ?>" style="border-radius: 1rem; border: 2px solid <?= $temaActual == 'system' ? '#6366f1' : '#e2e8f0' ?>; overflow: hidden; transition: all 0.2s;">
                                    <div style="background: linear-gradient(135deg, #f8fafc 50%, #0f172a 50%); padding: 1rem; height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-laptop" style="font-size: 2.5rem; color: #64748b;"></i>
                                    </div>
                                    <div style="padding: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background: white;">
                                        <span style="font-weight: 600; font-size: 0.875rem; color: #0f172a;">Automático</span>
                                        <div id="check-system" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid <?= $temaActual == 'system' ? '#6366f1' : '#e2e8f0' ?>; display: flex; align-items: center; justify-content: center; background: <?= $temaActual == 'system' ? '#eef2ff' : 'white' ?>;">
                                            <?php if ($temaActual == 'system'): ?>
                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </label>

                        </div>
                    </section>

                    <!-- Divider -->
                    <div style="height: 1px; background: #f1f5f9; margin: 2rem 0;"></div>

                    <!-- SECCIÓN 2: COLOR DE ACENTO -->
                    <section style="margin-bottom: 2.5rem;">
                        <h3 style="font-size: 0.75rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-palette" style="color: #94a3b8;"></i>
                            Color de Acento
                        </h3>
                        <p style="font-size: 0.875rem; color: #64748b; margin: 0 0 1rem 0;">Elige el color principal para botones, enlaces y estados activos.</p>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            <?php 
                            $colors = [
                                'indigo' => '#6366f1',
                                'blue' => '#3b82f6',
                                'emerald' => '#10b981',
                                'rose' => '#f43f5e',
                                'amber' => '#f59e0b',
                                'slate' => '#475569',
                            ];
                            foreach ($colors as $name => $hex): 
                            ?>
                            <label style="cursor: pointer;">
                                <input type="radio" name="color_acento" value="<?= $name ?>" <?= $colorActual == $name ? 'checked' : '' ?> style="display: none;">
                                <div onclick="selectColor('<?= $name ?>')" id="color-<?= $name ?>" style="width: 48px; height: 48px; border-radius: 50%; background: <?= $hex ?>; display: flex; align-items: center; justify-content: center; transition: all 0.2s; <?= $colorActual == $name ? 'box-shadow: 0 0 0 4px white, 0 0 0 6px ' . $hex . ';' : '' ?>">
                                    <?php if ($colorActual == $name): ?>
                                    <i class="bi bi-check-lg" style="color: white; font-size: 1.25rem; font-weight: bold;"></i>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Divider -->
                    <div style="height: 1px; background: #f1f5f9; margin: 2rem 0;"></div>

                    <!-- SECCIÓN 3: DENSIDAD -->
                    <section>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="font-size: 0.75rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Densidad de Información</h3>
                            <span style="font-size: 0.625rem; font-weight: 500; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: #64748b;">Próximamente</span>
                        </div>
                        
                        <div style="background: #f8fafc; padding: 0.25rem; border-radius: 0.75rem; display: flex;">
                            <button type="button" onclick="selectDensity('compacta')" id="density-compacta" style="flex: 1; padding: 0.625rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; <?= $densidadActual == 'compacta' ? 'background: white; color: #0f172a; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : 'background: transparent; color: #64748b;' ?>">
                                Compacta
                            </button>
                            <button type="button" onclick="selectDensity('comfortable')" id="density-comfortable" style="flex: 1; padding: 0.625rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; <?= $densidadActual == 'comfortable' ? 'background: white; color: #0f172a; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : 'background: transparent; color: #64748b;' ?>">
                                Confortable
                            </button>
                            <button type="button" onclick="selectDensity('amplia')" id="density-amplia" style="flex: 1; padding: 0.625rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; <?= $densidadActual == 'amplia' ? 'background: white; color: #0f172a; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : 'background: transparent; color: #64748b;' ?>">
                                Amplia
                            </button>
                        </div>
                        <input type="hidden" name="densidad" id="densidad" value="<?= $densidadActual ?>">
                    </section>

                </div>

                <!-- PANEL: General (placeholder) -->
                <div id="panel-general" class="config-panel" style="display: none;">
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <i class="bi bi-globe" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h3 style="margin: 1rem 0 0.5rem; color: #0f172a; font-weight: 600;">Configuración General</h3>
                        <p style="font-size: 0.875rem;">Opciones de idioma y región. <br>Próximamente disponible.</p>
                    </div>
                </div>

                <!-- PANEL: Notificaciones (placeholder) -->
                <div id="panel-notifications" class="config-panel" style="display: none;">
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <i class="bi bi-bell" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h3 style="margin: 1rem 0 0.5rem; color: #0f172a; font-weight: 600;">Notificaciones</h3>
                        <p style="font-size: 0.875rem;">Configura alertas y notificaciones por correo. <br>Próximamente disponible.</p>
                    </div>
                </div>

                <!-- PANEL: Seguridad (placeholder) -->
                <div id="panel-security" class="config-panel" style="display: none;">
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <i class="bi bi-shield-lock" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h3 style="margin: 1rem 0 0.5rem; color: #0f172a; font-weight: 600;">Seguridad</h3>
                        <p style="font-size: 0.875rem;">Configuración de contraseña y autenticación. <br>Próximamente disponible.</p>
                    </div>
                </div>

                <!-- PANEL: Mi Cuenta (placeholder) -->
                <div id="panel-account" class="config-panel" style="display: none;">
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <i class="bi bi-person" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h3 style="margin: 1rem 0 0.5rem; color: #0f172a; font-weight: 600;">Mi Cuenta</h3>
                        <p style="font-size: 0.875rem;">Gestiona tu perfil y datos personales. <br>Próximamente disponible.</p>
                    </div>
                </div>

                </form>
            </div>

        </main>

    </div>
</div>

<style>
.config-tab:hover {
    background: #f1f5f9 !important;
}
.theme-card:hover {
    border-color: #c7d2fe !important;
}
</style>

<script>
const tabTitles = {
    'general': { title: 'General', desc: 'Configuración de idioma y región.' },
    'appearance': { title: 'Apariencia', desc: 'Personaliza cómo se ve y se siente la aplicación.' },
    'notifications': { title: 'Notificaciones', desc: 'Configura alertas y notificaciones del sistema.' },
    'security': { title: 'Seguridad', desc: 'Gestiona contraseña y opciones de acceso.' },
    'account': { title: 'Mi Cuenta', desc: 'Administra tu perfil y datos personales.' }
};

function switchConfigTab(tab) {
    // Hide all panels
    document.querySelectorAll('.config-panel').forEach(el => el.style.display = 'none');
    
    // Reset all tabs
    document.querySelectorAll('.config-tab').forEach(el => {
        el.style.background = 'transparent';
        el.style.color = '#475569';
        el.style.border = 'none';
        el.style.boxShadow = 'none';
        el.querySelector('div:first-child').style.background = 'transparent';
    });
    
    // Show selected panel
    document.getElementById('panel-' + tab).style.display = 'block';
    
    // Activate selected tab
    const selectedTab = document.getElementById('tab-' + tab);
    selectedTab.style.background = 'white';
    selectedTab.style.color = '#6366f1';
    selectedTab.style.border = '1px solid #e2e8f0';
    selectedTab.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
    selectedTab.querySelector('div:first-child').style.background = '#eef2ff';
    
    // Update header
    document.getElementById('content-title').textContent = tabTitles[tab].title;
    document.getElementById('content-desc').textContent = tabTitles[tab].desc;
}

function selectTheme(input) {
    const value = input.value;
    
    // Reset all theme cards
    ['light', 'dark', 'system'].forEach(theme => {
        const card = document.getElementById('theme-' + theme);
        const check = document.getElementById('check-' + theme);
        card.style.borderColor = '#e2e8f0';
        check.style.borderColor = '#e2e8f0';
        check.style.background = 'white';
        check.innerHTML = '';
    });
    
    // Activate selected
    const card = document.getElementById('theme-' + value);
    const check = document.getElementById('check-' + value);
    card.style.borderColor = '#6366f1';
    check.style.borderColor = '#6366f1';
    check.style.background = '#eef2ff';
    check.innerHTML = '<div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></div>';
    
    // Apply theme immediately
    document.documentElement.setAttribute('data-theme', value);
}

function selectColor(color) {
    const colors = {
        'indigo': '#6366f1',
        'blue': '#3b82f6',
        'emerald': '#10b981',
        'rose': '#f43f5e',
        'amber': '#f59e0b',
        'slate': '#475569',
    };
    
    // Reset all
    Object.keys(colors).forEach(c => {
        const el = document.getElementById('color-' + c);
        el.style.boxShadow = 'none';
        el.innerHTML = '';
    });
    
    // Activate selected
    const el = document.getElementById('color-' + color);
    el.style.boxShadow = '0 0 0 4px white, 0 0 0 6px ' + colors[color];
    el.innerHTML = '<i class="bi bi-check-lg" style="color: white; font-size: 1.25rem; font-weight: bold;"></i>';
    
    // Check radio
    document.querySelector('input[name="color_acento"][value="' + color + '"]').checked = true;
}

function selectDensity(density) {
    ['compacta', 'comfortable', 'amplia'].forEach(d => {
        const btn = document.getElementById('density-' + d);
        if (d === density) {
            btn.style.background = 'white';
            btn.style.color = '#0f172a';
            btn.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        } else {
            btn.style.background = 'transparent';
            btn.style.color = '#64748b';
            btn.style.boxShadow = 'none';
        }
    });
    document.getElementById('densidad').value = density;
}
</script>