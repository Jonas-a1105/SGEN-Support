<?php 
$temaActual = $_SESSION['tema'] ?? 'light';
$colorActual = $_SESSION['color_acento'] ?? 'indigo';
$densidadActual = $_SESSION['densidad'] ?? 'comfortable';
?>


<div class="config-layout-wrapper">
    
    <div class="config-card">
        
        <!-- SIDEBAR DE NAVEGACIÓN -->
        <aside style="width: 280px; background: #f8fafc; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column;">
            
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
            <div class="config-content-scroll">
                
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

                <!-- PANEL: General -->
                <div id="panel-general" class="config-panel" style="display: none;">
                    
                    <div class="account-container">
                        
                        <!-- Header & Intro -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Configuración General</h2>
                            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Define los parámetros globales del sistema y preferencias regionales.</p>
                        </div>

                        <div class="account-form-grid">
                            
                            <!-- SECCIÓN: REGIONAL -->
                            <div class="col-span-md-2">
                                <h4 class="account-subtitle"><i class="bi bi-globe" style="margin-right: 0.5rem; color: #6366f1;"></i> Regionalización</h4>
                            </div>

                            <!-- Idioma -->
                            <div>
                                <label class="sec-label">Idioma del Sistema</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon"><i class="bi bi-translate"></i></div>
                                    <select class="sec-input" name="app_lang">
                                        <option value="es" selected>Español (Latinoamérica)</option>
                                        <option value="en">English (US)</option>
                                        <option value="pt">Português (Brasil)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Zona Horaria -->
                            <div>
                                <label class="sec-label">Zona Horaria</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon"><i class="bi bi-clock"></i></div>
                                    <select class="sec-input" name="app_timezone">
                                        <option value="America/Caracas" selected>America/Caracas (GMT-4)</option>
                                        <option value="America/Bogota">America/Bogota (GMT-5)</option>
                                        <option value="America/New_York">America/New_York (GMT-5)</option>
                                        <option value="UTC">UTC (GMT+0)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="height: 1px; background: #f1f5f9; margin: 1rem 0;" class="col-span-md-2"></div>

                            <!-- SECCIÓN: SISTEMA -->
                            <div class="col-span-md-2">
                                <h4 class="account-subtitle"><i class="bi bi-cpu" style="margin-right: 0.5rem; color: #6366f1;"></i> Sistema</h4>
                            </div>

                            <!-- Paginación -->
                            <div>
                                <label class="sec-label">Registros por Página</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon"><i class="bi bi-list-ol"></i></div>
                                    <select class="sec-input" name="app_pagination">
                                        <option value="10">10 registros</option>
                                        <option value="25" selected>25 registros</option>
                                        <option value="50">50 registros</option>
                                        <option value="100">100 registros</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Timeout -->
                            <div>
                                <label class="sec-label">Tiempo de Sesión (Minutos)</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon"><i class="bi bi-hourglass-split"></i></div>
                                    <input type="number" class="sec-input" value="120" min="5" max="1440">
                                </div>
                            </div>

                            <!-- Modo Mantenimiento -->
                            <div class="col-span-md-2">
                                <div style="background: #fff; border: 1px solid #e2e8f0; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <div style="width: 3rem; height: 3rem; background: #fee2e2; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                                            <i class="bi bi-cone-striped" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h4 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0;">Modo Mantenimiento</h4>
                                            <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0;">
                                                Impide el acceso a usuarios no administradores.
                                            </p>
                                        </div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="app_maintenance">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            </div>

                        </div>
                    </div>

                <!-- PANEL: Notificaciones -->
                <div id="panel-notifications" class="config-panel" style="display: none;">
                    
                    <!-- Tabs: Bandeja / Configuración -->
                    <div style="display: flex; gap: 1.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                        <button type="button" onclick="switchNotifTab('inbox')" id="notif-tab-inbox" class="notif-tab" style="padding-bottom: 0.75rem; padding-left: 0.5rem; padding-right: 0.5rem; font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; border: none; background: transparent; cursor: pointer; position: relative; color: #6366f1;">
                            <span style="position: relative;">
                                <i class="bi bi-envelope" style="font-size: 1rem;"></i>
                                <span id="inbox-unread-dot" style="position: absolute; top: -2px; right: -4px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid white;"></span>
                            </span>
                            Bandeja de Entrada
                            <div id="inbox-tab-indicator" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: #6366f1; border-radius: 2px 2px 0 0;"></div>
                        </button>
                        
                        <button type="button" onclick="switchNotifTab('settings')" id="notif-tab-settings" class="notif-tab" style="padding-bottom: 0.75rem; padding-left: 0.5rem; padding-right: 0.5rem; font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; border: none; background: transparent; cursor: pointer; position: relative; color: #64748b;">
                            <i class="bi bi-gear" style="font-size: 1rem;"></i>
                            Configuración y Canales
                            <div id="settings-tab-indicator" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: #6366f1; border-radius: 2px 2px 0 0; display: none;"></div>
                        </button>
                    </div>

                    <!-- INBOX VIEW -->
                    <div id="notif-inbox-view">
                        
                        <!-- Toolbar -->
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; background: white; padding: 0.5rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
                            
                            <!-- Filters -->
                            <div style="display: flex; background: #f1f5f9; padding: 0.25rem; border-radius: 0.5rem;">
                                <button type="button" onclick="filterNotifications('all')" id="filter-all" class="notif-filter-btn active" style="padding: 0.375rem 1rem; font-size: 0.75rem; font-weight: 700; border-radius: 0.375rem; border: none; cursor: pointer; background: white; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    Todas
                                </button>
                                <button type="button" onclick="filterNotifications('unread')" id="filter-unread" class="notif-filter-btn" style="padding: 0.375rem 1rem; font-size: 0.75rem; font-weight: 700; border-radius: 0.375rem; border: none; cursor: pointer; background: transparent; color: #64748b;">
                                    No Leídas
                                </button>
                                <button type="button" onclick="filterNotifications('alert')" id="filter-alert" class="notif-filter-btn" style="padding: 0.375rem 1rem; font-size: 0.75rem; font-weight: 700; border-radius: 0.375rem; border: none; cursor: pointer; background: transparent; color: #64748b;">
                                    Solo Alertas
                                </button>
                            </div>
                            
                            <!-- Search + Mark All -->
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="position: relative;">
                                    <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
                                    <input type="text" id="notif-search" placeholder="Buscar..." oninput="searchNotifications(this.value)" style="padding: 0.375rem 1rem 0.375rem 2.25rem; font-size: 0.875rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; width: 200px; outline: none;">
                                </div>
                                <button type="button" onclick="markAllNotificationsRead()" title="Marcar todo como leído" style="padding: 0.5rem; color: #64748b; background: transparent; border: none; cursor: pointer; border-radius: 0.5rem; display: flex;">
                                    <i class="bi bi-check2-all" style="font-size: 1.125rem;"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div id="notification-list" style="background: white; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
                            <!-- Populated by JS -->
                        </div>
                        
                    </div>

                    <!-- SETTINGS VIEW -->
                    <div id="notif-settings-view" style="display: none;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                            
                            <!-- LEFT COLUMN -->
                            <div style="display: flex; flex-direction: column; gap: 2rem;">
                                
                                <!-- Canales de Comunicación -->
                                <section style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <h2 style="font-weight: 700; color: #1e293b; margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                                        <i class="bi bi-gear" style="color: #94a3b8;"></i> Canales de Comunicación
                                    </h2>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        
                                        <!-- Email -->
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.75rem; border-radius: 0.75rem; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <div style="display: flex; gap: 1rem;">
                                                <div style="padding: 0.625rem; background: #f1f5f9; color: #64748b; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-envelope" style="font-size: 1.25rem;"></i>
                                                </div>
                                                <div>
                                                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Notificaciones por Email</h4>
                                                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0; max-width: 280px; line-height: 1.4;">Recibe resúmenes y alertas críticas.</p>
                                                </div>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_notifications" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>

                                        <!-- Push -->
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.75rem; border-radius: 0.75rem; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <div style="display: flex; gap: 1rem;">
                                                <div style="padding: 0.625rem; background: #f1f5f9; color: #64748b; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-phone" style="font-size: 1.25rem;"></i>
                                                </div>
                                                <div>
                                                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Notificaciones Push</h4>
                                                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0; max-width: 280px; line-height: 1.4;">Alertas instantáneas en tu dispositivo.</p>
                                                </div>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="push_notifications" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>

                                        <!-- Desktop -->
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.75rem; border-radius: 0.75rem; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <div style="display: flex; gap: 1rem;">
                                                <div style="padding: 0.625rem; background: #f1f5f9; color: #64748b; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-display" style="font-size: 1.25rem;"></i>
                                                </div>
                                                <div>
                                                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0;">Alertas de Escritorio</h4>
                                                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0; max-width: 280px; line-height: 1.4;">Pop-ups del navegador.</p>
                                                </div>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="desktop_notifications">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                </section>

                                <!-- Categorías Suscritas -->
                                <section style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <h2 style="font-weight: 700; color: #1e293b; margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                                        <i class="bi bi-funnel" style="color: #94a3b8;"></i> Categorías Suscritas
                                    </h2>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        
                                        <!-- Tickets -->
                                        <label class="category-card checked">
                                            <input type="checkbox" name="cat_tickets" checked style="display: none;">
                                            <div class="category-content">
                                                <div>
                                                    <h4>Tickets de Soporte</h4>
                                                    <p>Asignaciones, respuestas...</p>
                                                </div>
                                                <div class="category-check"><i class="bi bi-check"></i></div>
                                            </div>
                                        </label>

                                        <!-- Inventario -->
                                        <label class="category-card checked">
                                            <input type="checkbox" name="cat_inventario" checked style="display: none;">
                                            <div class="category-content">
                                                <div>
                                                    <h4>Inventario y Stock</h4>
                                                    <p>Bajos stocks, movimientos...</p>
                                                </div>
                                                <div class="category-check"><i class="bi bi-check"></i></div>
                                            </div>
                                        </label>

                                        <!-- Mantenimientos -->
                                        <label class="category-card">
                                            <input type="checkbox" name="cat_mantenimientos" style="display: none;">
                                            <div class="category-content">
                                                <div>
                                                    <h4>Mantenimientos</h4>
                                                    <p>Recordatorios preventivos...</p>
                                                </div>
                                                <div class="category-check"><i class="bi bi-check"></i></div>
                                            </div>
                                        </label>

                                        <!-- Sistema -->
                                        <label class="category-card checked">
                                            <input type="checkbox" name="cat_sistema" checked style="display: none;">
                                            <div class="category-content">
                                                <div>
                                                    <h4>Sistema y Seguridad</h4>
                                                    <p>Inicios de sesión, backups...</p>
                                                </div>
                                                <div class="category-check"><i class="bi bi-check"></i></div>
                                            </div>
                                        </label>
                                        
                                    </div>
                                </section>
                                
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                
                                <!-- Modo No Molestar -->
                                <div style="background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); color: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 10px 25px -5px rgba(67, 56, 202, 0.4); position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: 0; right: 0; width: 8rem; height: 8rem; background: white; opacity: 0.05; border-radius: 50%; transform: translate(50%, -50%);"></div>
                                    
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                                        <h3 style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin: 0; font-size: 0.9375rem;">
                                            <i class="bi bi-moon"></i> Modo No Molestar
                                        </h3>
                                        <label class="toggle-switch dnd">
                                            <input type="checkbox" name="dnd_mode" checked>
                                            <span class="toggle-slider dnd"></span>
                                        </label>
                                    </div>
                                    
                                    <p style="font-size: 0.75rem; color: rgba(199, 210, 254, 0.9); margin: 0 0 1rem; line-height: 1.4;">Pausa todas las notificaciones (excepto críticas) durante este horario.</p>
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                        <div>
                                            <label style="display: block; font-size: 0.6875rem; color: #a5b4fc; margin-bottom: 0.25rem;">Desde</label>
                                            <div style="background: rgba(49, 46, 129, 0.5); border-radius: 0.5rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(99, 102, 241, 0.5);">
                                                <i class="bi bi-clock" style="font-size: 0.875rem; color: #a5b4fc;"></i>
                                                <span style="font-size: 0.875rem;">22:00</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 0.6875rem; color: #a5b4fc; margin-bottom: 0.25rem;">Hasta</label>
                                            <div style="background: rgba(49, 46, 129, 0.5); border-radius: 0.5rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(99, 102, 241, 0.5);">
                                                <i class="bi bi-clock" style="font-size: 0.875rem; color: #a5b4fc;"></i>
                                                <span style="font-size: 0.875rem;">07:00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sonidos -->
                                <div style="background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <h3 style="font-weight: 700; color: #1e293b; margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9375rem;">
                                        <i class="bi bi-volume-up" style="color: #94a3b8;"></i> Sonidos
                                    </h3>
                                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                                            <div class="radio-dot selected"></div>
                                            <span style="font-size: 0.875rem; color: #334155; font-weight: 500;">Predeterminado (Ding)</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; opacity: 0.5;">
                                            <div class="radio-dot"></div>
                                            <span style="font-size: 0.875rem; color: #475569;">Sutil</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; opacity: 0.5;">
                                            <div class="radio-dot"></div>
                                            <span style="font-size: 0.875rem; color: #475569;">Silencio</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Guardar -->
                                <button type="button" onclick="saveNotificationPreferences()" style="width: 100%; padding: 0.875rem; background: #1e293b; color: white; font-weight: 700; border: none; border-radius: 0.75rem; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); transition: all 0.2s; font-size: 0.9375rem;" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
                                    Guardar Preferencias
                                </button>
                                
                            </div>
                            
                        </div>
                    </div>
                    
                </div>

                <!-- PANEL: Seguridad -->
                <div id="panel-security" class="config-panel" style="display: none;">
                    
                    <div class="security-container">
                        
                        <!-- Header -->
                        <div class="security-header">
                            <div class="security-icon-box">
                                <i class="bi bi-shield-check" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">Seguridad de la Cuenta</h2>
                                <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0;">
                                    Actualiza tu contraseña periódicamente para mantener tu cuenta segura.
                                </p>
                            </div>
                        </div>

                        <div class="security-content">
                            
                            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                            <div class="security-form">
                                
                                <!-- Contraseña Actual -->
                                <div class="sec-form-group">
                                    <label class="sec-label">Contraseña Actual</label>
                                    <div class="sec-input-wrapper">
                                        <div class="sec-input-icon">
                                            <i class="bi bi-key" style="font-size: 1.125rem;"></i>
                                        </div>
                                        <input type="password" id="currentPassword" class="sec-input" placeholder="••••••••••••">
                                        <button type="button" class="sec-toggle-btn" onclick="ConfigSecurity.toggleVisibility('currentPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div style="text-align: right;">
                                        <a href="#" style="font-size: 0.75rem; font-weight: 700; color: #2563eb; text-decoration: none;">¿Olvidaste tu contraseña?</a>
                                    </div>
                                </div>

                                <div style="height: 1px; background: #f1f5f9; margin: 1.5rem 0;"></div>

                                <!-- Nueva Contraseña -->
                                <div class="sec-form-group">
                                    <label class="sec-label">Nueva Contraseña</label>
                                    <div class="sec-input-wrapper">
                                        <div class="sec-input-icon">
                                            <i class="bi bi-lock" style="font-size: 1.125rem;"></i>
                                        </div>
                                        <input type="password" id="newPassword" class="sec-input" placeholder="Nueva contraseña segura" oninput="ConfigSecurity.checkPasswordStrength(this.value)">
                                        <button type="button" class="sec-toggle-btn" onclick="ConfigSecurity.toggleVisibility('newPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Barra de Fuerza -->
                                    <div class="sec-strength-bar-bg">
                                        <div id="strength-bar" class="sec-strength-bar"></div>
                                    </div>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="sec-form-group">
                                    <label class="sec-label">Confirmar Contraseña</label>
                                    <div class="sec-input-wrapper">
                                        <div class="sec-input-icon">
                                            <i class="bi bi-lock" style="font-size: 1.125rem;"></i>
                                        </div>
                                        <input type="password" id="confirmPassword" class="sec-input" placeholder="Repite la nueva contraseña" oninput="ConfigSecurity.checkMatch()">
                                        <div id="match-icon" class="sec-input-icon-right" style="display: none;">
                                            <i class="bi bi-check" style="font-size: 1.25rem;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de Acción -->
                                <div style="padding-top: 0.5rem;">
                                    <button type="button" id="btn-update-pass" class="sec-btn-submit" onclick="ConfigSecurity.handleSubmit()" disabled>
                                        <i class="bi bi-save"></i> Actualizar Contraseña
                                    </button>
                                </div>

                            </div>

                            <!-- COLUMNA DERECHA: REQUISITOS -->
                            <div class="sec-requirements-box">
                                <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-exclamation-circle" style="color: #3b82f6;"></i>
                                    Requisitos de seguridad
                                </h3>
                                
                                <ul class="sec-req-list">
                                    <li class="sec-req-item" id="req-length">
                                        <div class="sec-req-dot"><i class="bi bi-dot"></i></div>
                                        Mínimo 8 caracteres
                                    </li>
                                    <li class="sec-req-item" id="req-number">
                                        <div class="sec-req-dot"><i class="bi bi-dot"></i></div>
                                        Al menos un número (0-9)
                                    </li>
                                    <li class="sec-req-item" id="req-special">
                                        <div class="sec-req-dot"><i class="bi bi-dot"></i></div>
                                        Un carácter especial (!@#$)
                                    </li>
                                    <li class="sec-req-item" id="req-match">
                                        <div class="sec-req-dot"><i class="bi bi-dot"></i></div>
                                        Las contraseñas coinciden
                                    </li>
                                </ul>

                                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; font-size: 0.75rem; color: #64748b; line-height: 1.5;">
                                    <p style="font-weight: 700; color: #475569; margin: 0 0 0.25rem;">Nota de Seguridad:</p>
                                    Al cambiar tu contraseña, tu sesión se mantendrá activa en este dispositivo, pero podrías ser desconectado de otros.
                                </div>
                            </div>


                            </div>
                        </div>
                    </div>

                <!-- PANEL: Mi Cuenta -->
                <div id="panel-account" class="config-panel" style="display: none;">
                    
                    <div class="account-container">
                        
                        <!-- 1. Tarjeta de Identidad -->
                        <div class="profile-header">
                            <div class="profile-avatar-wrapper">
                                <div class="profile-avatar">UA</div>
                                <div class="profile-edit-btn">
                                    <i class="bi bi-camera" style="font-size: 1rem;"></i>
                                </div>
                            </div>
                            <div class="profile-info">
                                <h3 class="profile-name">Usuario Administrador</h3>
                                <p class="profile-role">IT Manager • Nivel 1</p>
                                <div class="profile-badges">
                                    <span class="badge badge-active">
                                        <div class="pulse-dot"></div>
                                        Activo
                                    </span>
                                    <span class="badge badge-member">
                                        <i class="bi bi-calendar3" style="font-size: 0.75rem;"></i>
                                        Miembro desde 2023
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="height: 1px; background: #f1f5f9; margin-bottom: 2rem;"></div>

                        <!-- 2. Formulario de Datos Personales -->
                        <div class="account-form-grid">
                            <div class="col-span-md-2">
                                <h4 class="account-subtitle">Información Personal</h4>
                            </div>

                            <!-- Nombre Completo -->
                            <div>
                                <label class="sec-label">Nombre Completo</label>
                                <input type="text" value="Jonas Mendoza" class="sec-input" style="padding-left: 1rem;">
                            </div>

                            <!-- Cargo / Título -->
                            <div>
                                <label class="sec-label">Cargo / Título</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon">
                                        <i class="bi bi-briefcase"></i>
                                    </div>
                                    <input type="text" value="IT Manager" class="sec-input">
                                </div>
                            </div>

                            <!-- Correo -->
                            <div>
                                <label class="sec-label">Correo Electrónico</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon">
                                        <i class="bi bi-envelope"></i>
                                    </div>
                                    <input type="email" value="admin@sgen.com" class="sec-input">
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="sec-label">Teléfono</label>
                                <div class="sec-input-wrapper">
                                    <div class="sec-input-icon">
                                        <i class="bi bi-telephone"></i>
                                    </div>
                                    <input type="tel" value="+58 (412) 555-0199" class="sec-input">
                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="col-span-md-2">
                                <label class="sec-label">Bio / Notas</label>
                                <textarea rows="3" class="account-textarea">Administrador principal del sistema SGEN. Encargado de la supervisión de infraestructura y soporte técnico.</textarea>
                            </div>

                            <!-- Detalles Corporativos -->
                            <div class="col-span-md-2" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem; margin-top: 0.5rem;">
                                <h4 class="account-subtitle" style="margin-bottom: 1.5rem;">Detalles Corporativos</h4>
                                
                                <div class="corporate-box">
                                    
                                    <!-- ID -->
                                    <div class="corp-item">
                                        <label class="corp-label">ID de Empleado</label>
                                        <div class="corp-value" style="font-family: monospace;">
                                            <i class="bi bi-hash"></i>
                                            SGEN-001
                                        </div>
                                    </div>

                                    <!-- Departamento -->
                                    <div class="corp-item">
                                        <label class="corp-label">Departamento</label>
                                        <div class="corp-value">
                                            <i class="bi bi-building"></i>
                                            Tecnología e Informática
                                        </div>
                                    </div>

                                    <!-- Ubicación -->
                                    <div class="corp-item">
                                        <label class="corp-label">Ubicación / Sede</label>
                                        <div class="corp-value">
                                            <i class="bi bi-geo-alt"></i>
                                            Edificio Central, Piso 2
                                        </div>
                                    </div>

                                    <!-- Firma -->
                                    <div class="corp-item">
                                        <label class="corp-label">Firma para Tickets</label>
                                        <div class="corp-value corp-link">
                                            <i class="bi bi-pen-fill" style="color: inherit;"></i>
                                            Configurar Firma Digital
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                </form>
        </main>

    </div>
</div>



<script src="<?= BASE_URL ?>js/configuracion.js?v=<?= time() ?>"></script>

