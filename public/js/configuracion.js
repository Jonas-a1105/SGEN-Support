/**
 * Logic for Configuration View
 */

if (!window.tabTitles) {
    window.tabTitles = {
        'general': { title: 'General', desc: 'Configuración de idioma y región.' },
        'appearance': { title: 'Apariencia', desc: 'Personaliza cómo se ve y se siente la aplicación.' },
        'notifications': { title: 'Notificaciones', desc: 'Configura alertas y notificaciones del sistema.' },
        'security': { title: 'Seguridad', desc: 'Gestiona contraseña y opciones de acceso.' },
        'account': { title: 'Mi Cuenta', desc: 'Administra tu perfil y datos personales.' }
    };
}

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
    if (selectedTab) {
        selectedTab.style.background = 'white';
        selectedTab.style.color = '#6366f1';
        selectedTab.style.border = '1px solid #e2e8f0';
        selectedTab.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        selectedTab.querySelector('div:first-child').style.background = '#eef2ff';
    }

    // Update header
    const titleEl = document.getElementById('content-title');
    const descEl = document.getElementById('content-desc');

    if (titleEl && window.tabTitles[tab]) titleEl.textContent = window.tabTitles[tab].title;
    if (descEl && window.tabTitles[tab]) descEl.textContent = window.tabTitles[tab].desc;

    // Trigger specific initializations
    if (tab === 'notifications') {
        if (typeof window.ConfigNotifications !== 'undefined') window.ConfigNotifications.init();
        initCategoryCards();
    } else if (tab === 'account') {
        if (typeof window.ConfigAccount !== 'undefined') window.ConfigAccount.init();
    }
}

function selectTheme(input) {
    const value = input.value;

    // Reset all theme cards
    ['light', 'dark', 'system'].forEach(theme => {
        const card = document.getElementById('theme-' + theme);
        const check = document.getElementById('check-' + theme);
        if (card) card.style.borderColor = '#e2e8f0';
        if (check) {
            check.style.borderColor = '#e2e8f0';
            check.style.background = 'white';
            check.innerHTML = '';
        }
    });

    // Activate selected
    const card = document.getElementById('theme-' + value);
    const check = document.getElementById('check-' + value);
    if (card) card.style.borderColor = '#6366f1';
    if (check) {
        check.style.borderColor = '#6366f1';
        check.style.background = '#eef2ff';
        check.innerHTML = '<div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></div>';
    }

    // Apply theme immediately
    if (value === 'system') {
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', systemDark ? 'dark' : 'light');
    } else {
        document.documentElement.setAttribute('data-theme', value);
    }

    // Save to LocalStorage
    localStorage.setItem('theme', value);
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
        if (el) {
            el.style.boxShadow = 'none';
            el.innerHTML = '';
        }
    });

    // Activate selected
    const el = document.getElementById('color-' + color);
    if (el) {
        el.style.boxShadow = '0 0 0 4px white, 0 0 0 6px ' + colors[color];
        el.innerHTML = '<i class="bi bi-check-lg" style="color: white; font-size: 1.25rem; font-weight: bold;"></i>';
    }

    // Check radio
    const radio = document.querySelector('input[name="color_acento"][value="' + color + '"]');
    if (radio) radio.checked = true;
}

function selectDensity(density) {
    ['compacta', 'comfortable', 'amplia'].forEach(d => {
        const btn = document.getElementById('density-' + d);
        if (btn) {
            if (d === density) {
                btn.style.background = 'white';
                btn.style.color = '#0f172a';
                btn.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            } else {
                btn.style.background = 'transparent';
                btn.style.color = '#64748b';
                btn.style.boxShadow = 'none';
            }
        }
    });
    const densityInput = document.getElementById('densidad');
    if (densityInput) densityInput.value = density;
}

// =============================================
// NOTIFICATIONS INBOX LOGIC
// =============================================

if (!window.ConfigNotifications) {
    window.ConfigNotifications = {
        data: [
            { id: 1, type: 'alert', title: 'Stock Crítico Detectado', message: 'El artículo "Mousepack" ha alcanzado 0 unidades en Almacén Central.', time: 'Hace 10 min', read: false, category: 'Inventario' },
            { id: 2, type: 'task', title: 'Nuevo Ticket Asignado', message: 'Has sido asignado al ticket #455: "Fallo de Impresora RRHH".', time: 'Hace 45 min', read: false, category: 'Soporte' },
            { id: 3, type: 'info', title: 'Backup Completado', message: 'La copia de seguridad semanal del sistema finalizó con éxito.', time: 'Ayer, 02:00 AM', read: true, category: 'Sistema' },
            { id: 4, type: 'alert', title: 'Mantenimiento Vencido', message: 'El mantenimiento preventivo del Servidor Dell está retrasado 2 días.', time: 'Ayer, 09:30 AM', read: true, category: 'Mantenimiento' },
            { id: 5, type: 'success', title: 'Solicitud Aprobada', message: 'Tu solicitud de compra de periféricos #REQ-099 ha sido aprobada.', time: '2 días atrás', read: true, category: 'Compras' },
        ],
        currentFilter: 'all',
        searchQuery: '',

        init() {
            this.render();
        },

        getIcon(type) {
            switch (type) {
                case 'alert': return 'bi-exclamation-triangle';
                case 'task': return 'bi-person';
                case 'success': return 'bi-check-circle';
                default: return 'bi-info-circle';
            }
        },

        getColorStyle(type) {
            switch (type) {
                case 'alert': return { bg: '#fef2f2', color: '#ef4444' };
                case 'task': return { bg: '#eff6ff', color: '#3b82f6' };
                case 'success': return { bg: '#ecfdf5', color: '#10b981' };
                default: return { bg: '#f1f5f9', color: '#64748b' };
            }
        },

        getFiltered() {
            let result = this.data;

            if (this.currentFilter === 'unread') {
                result = result.filter(n => !n.read);
            } else if (this.currentFilter === 'alert') {
                result = result.filter(n => n.type === 'alert');
            }

            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(n =>
                    n.title.toLowerCase().includes(q) ||
                    n.message.toLowerCase().includes(q) ||
                    n.category.toLowerCase().includes(q)
                );
            }

            return result;
        },

        render() {
            const container = document.getElementById('notification-list');
            if (!container) return;

            const filtered = this.getFiltered();
            const unreadCount = this.data.filter(n => !n.read).length;

            // Update unread dot
            const dot = document.getElementById('inbox-unread-dot');
            if (dot) dot.style.display = unreadCount > 0 ? 'block' : 'none';

            if (filtered.length === 0) {
                container.innerHTML = `
                <div style="padding: 5rem 2rem; text-align: center; display: flex; flex-direction: column; align-items: center;">
                    <div style="width: 5rem; height: 5rem; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="bi bi-bell" style="font-size: 2rem; color: #cbd5e1;"></i>
                    </div>
                    <h3 style="color: #475569; font-weight: 500; margin: 0;">Todo limpio</h3>
                    <p style="color: #94a3b8; font-size: 0.875rem; margin: 0.25rem 0 0;">No tienes notificaciones en esta categoría.</p>
                </div>
            `;
                return;
            }

            container.innerHTML = filtered.map(n => {
                const colors = this.getColorStyle(n.type);
                const icon = this.getIcon(n.type);
                return `
                <div class="notif-item" data-id="${n.id}" style="padding: 1.25rem; display: flex; gap: 1rem; position: relative; border-bottom: 1px solid #f8fafc; transition: background 0.2s; ${n.read ? 'opacity: 0.7;' : 'background: rgba(239, 246, 255, 0.3);'}">
                    ${!n.read ? '<div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: #6366f1;"></div>' : ''}
                    
                    <div style="width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: ${colors.bg}; color: ${colors.color};">
                        <i class="bi ${icon}" style="font-size: 1.25rem;"></i>
                    </div>
                    
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                            <h3 style="font-size: 0.875rem; font-weight: 700; color: ${n.read ? '#475569' : '#0f172a'}; margin: 0;">${n.title}</h3>
                            <span style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap; margin-left: 0.5rem;">${n.time}</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #64748b; line-height: 1.5; margin: 0 0 0.5rem;">${n.message}</p>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.625rem; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">${n.category}</span>
                            <div class="notif-actions" style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem; opacity: 0; transition: opacity 0.2s;">
                                ${!n.read ? `<button onclick="window.ConfigNotifications.markRead(${n.id})" style="font-size: 0.75rem; font-weight: 500; color: #6366f1; background: transparent; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;"><i class="bi bi-check"></i> Marcar leída</button>` : ''}
                                <button onclick="window.ConfigNotifications.delete(${n.id})" style="color: #94a3b8; background: transparent; border: none; cursor: pointer; padding: 0.25rem;"><i class="bi bi-trash" style="font-size: 1rem;"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }).join('');
        },

        markRead(id) {
            this.data = this.data.map(n => n.id === id ? { ...n, read: true } : n);
            this.render();
        },

        markAllRead() {
            this.data = this.data.map(n => ({ ...n, read: true }));
            this.render();
        },

        delete(id) {
            this.data = this.data.filter(n => n.id !== id);
            this.render();
        },

        filter(type) {
            this.currentFilter = type;

            // Update filter button styles
            ['all', 'unread', 'alert'].forEach(f => {
                const btn = document.getElementById('filter-' + f);
                if (btn) {
                    if (f === type) {
                        btn.style.background = 'white';
                        btn.style.color = '#1e293b';
                        btn.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
                    } else {
                        btn.style.background = 'transparent';
                        btn.style.color = '#64748b';
                        btn.style.boxShadow = 'none';
                    }
                }
            });

            this.render();
        },

        search(query) {
            this.searchQuery = query;
            this.render();
        }
    };
}

function switchNotifTab(tab) {
    const inboxView = document.getElementById('notif-inbox-view');
    const settingsView = document.getElementById('notif-settings-view');
    const inboxTab = document.getElementById('notif-tab-inbox');
    const settingsTab = document.getElementById('notif-tab-settings');
    const inboxIndicator = document.getElementById('inbox-tab-indicator');
    const settingsIndicator = document.getElementById('settings-tab-indicator');

    if (tab === 'inbox') {
        if (inboxView) inboxView.style.display = 'block';
        if (settingsView) settingsView.style.display = 'none';
        if (inboxTab) inboxTab.style.color = '#6366f1';
        if (settingsTab) settingsTab.style.color = '#64748b';
        if (inboxIndicator) inboxIndicator.style.display = 'block';
        if (settingsIndicator) settingsIndicator.style.display = 'none';
    } else {
        if (inboxView) inboxView.style.display = 'none';
        if (settingsView) settingsView.style.display = 'block';
        if (inboxTab) inboxTab.style.color = '#64748b';
        if (settingsTab) settingsTab.style.color = '#6366f1';
        if (inboxIndicator) inboxIndicator.style.display = 'none';
        if (settingsIndicator) settingsIndicator.style.display = 'block';
    }
}

function filterNotifications(type) {
    window.ConfigNotifications.filter(type);
}

function searchNotifications(query) {
    window.ConfigNotifications.search(query);
}

function markAllNotificationsRead() {
    window.ConfigNotifications.markAllRead();
}


function initCategoryCards() {
    document.querySelectorAll('.category-card input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const card = this.closest('.category-card');
            if (this.checked) {
                card.classList.add('checked');
            } else {
                card.classList.remove('checked');
            }
        });
    });
}

function saveNotificationPreferences() {
    // Collect preferences
    const emailEl = document.querySelector('input[name="email_notifications"]');
    const pushEl = document.querySelector('input[name="push_notifications"]');
    const desktopEl = document.querySelector('input[name="desktop_notifications"]');
    const dndEl = document.querySelector('input[name="dnd_mode"]');

    // Categories
    const catTicketsEl = document.querySelector('input[name="cat_tickets"]');
    const catInvEl = document.querySelector('input[name="cat_inventario"]');
    const catMantEl = document.querySelector('input[name="cat_mantenimientos"]');
    const catSysEl = document.querySelector('input[name="cat_sistema"]');

    const prefs = {
        email: emailEl ? emailEl.checked : false,
        push: pushEl ? pushEl.checked : false,
        desktop: desktopEl ? desktopEl.checked : false,
        dnd: dndEl ? dndEl.checked : false,
        categories: {
            tickets: catTicketsEl ? catTicketsEl.checked : false,
            inventario: catInvEl ? catInvEl.checked : false,
            mantenimientos: catMantEl ? catMantEl.checked : false,
            sistema: catSysEl ? catSysEl.checked : false,
        }
    };

    console.log('Saving preferences:', prefs);

    // Show success feedback (placeholder - would normally send to server)
    const btn = event.target || document.querySelector('button[onclick="saveNotificationPreferences()"]');
    if (btn) {
        const originalText = btn.textContent;
        btn.textContent = '¡Guardado!';
        btn.style.background = '#10b981';
        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '#1e293b';
        }, 1500);
    }
}


// =============================================
// SEGURIDAD VIEW LOGIC
// =============================================

if (!window.ConfigSecurity) {
    window.ConfigSecurity = {
        requirements: {
            length: false,
            number: false,
            special: false,
            match: false
        },

        toggleVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            }
        },

        checkPasswordStrength(password) {
            // Update requirements
            this.requirements.length = password.length >= 8;
            this.requirements.number = /\d/.test(password);
            this.requirements.special = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            this.updateRequirementsUI();
            this.updateStrengthBar();
            this.checkMatch(); // Re-check match if new password changes
        },

        checkMatch() {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            const confirmInput = document.getElementById('confirmPassword');
            const matchIcon = document.getElementById('match-icon');

            this.requirements.match = newPass && newPass === confirmPass;

            // Update UI for match
            if (this.requirements.match) {
                confirmInput.classList.add('match-success');
                if (matchIcon) matchIcon.style.display = 'flex';
            } else {
                confirmInput.classList.remove('match-success');
                if (matchIcon) matchIcon.style.display = 'none';
            }

            this.updateRequirementsUI();
            this.updateSubmitButton();
        },

        calculateStrength() {
            let score = 0;
            if (this.requirements.length) score += 33;
            if (this.requirements.number) score += 33;
            if (this.requirements.special) score += 34;
            return score;
        },

        updateStrengthBar() {
            const strength = this.calculateStrength();
            const bar = document.getElementById('strength-bar');

            if (bar) {
                bar.style.width = strength + '%';

                if (strength < 33) {
                    bar.style.background = '#ef4444'; // Red
                } else if (strength < 66) {
                    bar.style.background = '#f59e0b'; // Amber
                } else {
                    bar.style.background = '#10b981'; // Emerald
                }
            }
        },

        updateRequirementsUI() {
            this.updateRequirementItem('req-length', this.requirements.length);
            this.updateRequirementItem('req-number', this.requirements.number);
            this.updateRequirementItem('req-special', this.requirements.special);
            this.updateRequirementItem('req-match', this.requirements.match);
        },

        updateRequirementItem(id, met) {
            const el = document.getElementById(id);
            if (el) {
                if (met) {
                    el.classList.add('met');
                } else {
                    el.classList.remove('met');
                }
            }
        },

        updateSubmitButton() {
            const btn = document.getElementById('btn-update-pass');
            const strength = this.calculateStrength();
            const isValid = strength >= 100 && this.requirements.match;

            if (btn) {
                btn.disabled = !isValid;
            }
        },

        handleSubmit() {
            const btn = document.getElementById('btn-update-pass');
            if (!btn || btn.disabled) return;

            const originalContent = btn.innerHTML;
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat sec-spin"></i> Actualizando...';

            // Real API call
            fetch(window.BASE_URL + 'configuracion/cambiarPassword', {
                method: 'POST',
                credentials: 'same-origin', // IMPORTANT: Send session cookies
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    password_actual: currentPassword,
                    password_nuevo: newPassword,
                    password_confirmar: confirmPassword
                })
            })
                .then(response => {
                    // First check if response is ok
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Server error response:', text.substring(0, 500));
                            throw new Error('Server returned ' + response.status);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Success state
                        btn.innerHTML = '<i class="bi bi-check-lg"></i> ¡Contraseña Actualizada!';
                        btn.classList.add('success');

                        // Show toast if available
                        if (window.Toast) {
                            window.Toast.show('success', data.message);
                        }

                        // Reset form after delay
                        setTimeout(() => {
                            btn.classList.remove('success');
                            btn.innerHTML = originalContent;
                            btn.disabled = true;

                            // Clear inputs
                            document.getElementById('currentPassword').value = '';
                            document.getElementById('newPassword').value = '';
                            document.getElementById('confirmPassword').value = '';

                            // Reset UI
                            this.requirements = {
                                length: false,
                                number: false,
                                special: false,
                                match: false
                            };
                            this.updateStrengthBar();
                            this.updateRequirementsUI();

                            document.getElementById('strength-bar').style.width = '0%';
                            const confirmInput = document.getElementById('confirmPassword');
                            const matchIcon = document.getElementById('match-icon');
                            confirmInput.classList.remove('match-success');
                            if (matchIcon) matchIcon.style.display = 'none';

                        }, 2000);
                    } else {
                        // Error state
                        btn.innerHTML = '<i class="bi bi-x-lg"></i> Error';
                        btn.style.background = '#ef4444';

                        if (window.Toast) {
                            window.Toast.show('error', data.message);
                        }

                        setTimeout(() => {
                            btn.innerHTML = originalContent;
                            btn.style.background = '';
                            btn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    btn.innerHTML = '<i class="bi bi-x-lg"></i> Error';
                    btn.style.background = '#ef4444';

                    if (window.Toast) {
                        window.Toast.show('error', 'Error de conexión. Intenta de nuevo.');
                    }

                    setTimeout(() => {
                        btn.innerHTML = originalContent;
                        btn.style.background = '';
                        btn.disabled = false;
                    }, 2000);
                });
        }
    };
}


// =============================================
// ACCOUNT VIEW LOGIC
// =============================================

if (!window.ConfigAccount) {
    window.ConfigAccount = {
        init() {
            // Initialize logic if needed
            this.setupFormInterceptor();
        },

        setupFormInterceptor() {
            const form = document.getElementById('configForm');
            if (form && !form.dataset.accountInterfaced) {
                form.addEventListener('submit', (e) => {
                    // Check if Account panel is active
                    const accountPanel = document.getElementById('panel-account');
                    if (accountPanel && accountPanel.style.display !== 'none') {
                        e.preventDefault();
                        this.save();
                    }
                });
                form.dataset.accountInterfaced = 'true';
            }
        },

        save() {
            const btn = document.querySelector('button[form="configForm"]');
            if (!btn) return;

            const originalContent = btn.innerHTML;
            const originalBg = btn.style.backgroundColor;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat sec-spin"></i> Guardando...';

            // Simulate API
            setTimeout(() => {
                // Success state
                btn.innerHTML = '<i class="bi bi-check-lg"></i> ¡Cambios Guardados!';
                btn.style.backgroundColor = '#10b981'; // Emerald

                // Revert
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.style.backgroundColor = originalBg || '#0f172a';
                    btn.disabled = false;
                }, 2000);
            }, 1000);
        }
    };
}



