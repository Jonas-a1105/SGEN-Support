<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BaseCard from '@/Components/UI/BaseCard.vue';

/**
 * Fase 4: superficie administrativa accesible desde la Configuración —
 * Reúne los tres portales operativos del sistema (antes esconden tras URL).
 */
</script>

<template>
    <div class="sistema-tab-wrap">
        <BaseCard padding="lg">
            <header class="sistema-header">
                <h3>Portales de administración</h3>
                <p>Acceso directo a la gestión de plataforma (roles, papelera, claves globales).</p>
            </header>

            <div class="portal-grid">
                <Link href="/configuracion/sistema" class="portal-card">
                    <div class="portal-icon portal-icon-config">
                        <svg viewBox="0 0 24 24"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" /></svg>
                    </div>
                    <div>
                        <strong>Claves globales</strong>
                        <span>SLA, intentos de login, sesiones, autocierre, cooldown correos</span>
                    </div>
                </Link>

                <Link href="/roles" class="portal-card">
                    <div class="portal-icon portal-icon-roles">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                    </div>
                    <div>
                        <strong>Roles y permisos</strong>
                        <span>Editor RBAC Spatie (rol de sistema bloqueado)</span>
                    </div>
                </Link>

                <Link href="/papelera" class="portal-card">
                    <div class="portal-icon portal-icon-bindo">
                        <svg viewBox="0 0 24 24"><path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                    </div>
                    <div>
                        <strong>Papelera universal</strong>
                        <span>Restaurar lo retirado por error; purga definitiva segura</span>
                    </div>
                </Link>
            </div>
        </BaseCard>
    </div>
</template>

<style scoped>
.sistema-tab-wrap { width: 100%; }
.sistema-header { margin-bottom: 18px; }
.sistema-header h3 { margin: 0; font-size: 17px; color: var(--text); }
.sistema-header p { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }
.portal-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-top: 8px; }
.portal-card { display: flex; flex-direction: row; align-items: center; gap: 14px; padding: 16px; border: var(--stroke-w, 2px) solid var(--stroke, #31343a); border-radius: var(--card-radius, 14px); background: var(--bg-sub, #1e2024); text-decoration: none; transition: border-color .15s ease, transform .15s ease; box-shadow: none !important; }
.portal-card:hover { border-color: var(--color-primary, #10b981); transform: translateY(-2px); }
.portal-icon { width: 42px; height: 42px; flex-shrink: 0; border-radius: 10px; display: grid; place-items: center; background: rgba(var(--orange-rgb), 0.12); color: var(--orange); }
.portal-icon svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 2; }
.portal-icon-roles { background: rgba(59, 130, 246, 0.12); color: var(--blue, #3b82f6); }
.portal-icon-bindo { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.portal-card strong { display: block; font-size: 14px; color: var(--text); }
.portal-card span { display: block; font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
</style>
