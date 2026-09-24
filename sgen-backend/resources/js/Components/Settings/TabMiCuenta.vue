<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

interface AuthUser {
    id: number;
    username: string;
    rol?: string;
}

interface PageProps {
    auth?: {
        user?: AuthUser;
    };
    [key: string]: unknown;
}

const page = usePage<PageProps>();
const user = page.props.auth?.user;
</script>

<template>
    <div class="settings-subpane">
        <div class="subpane-header">
            <h3 class="section-title">Mi Cuenta</h3>
            <span class="section-subtitle">Información del perfil de usuario y credenciales activas.</span>
        </div>

        <div class="account-card">
            <div class="account-avatar-wrap">
                <div class="account-avatar">
                    {{ (user?.username || 'OP').substring(0, 2).toUpperCase() }}
                </div>
            </div>

            <div class="account-fields-grid">
                <div class="field-item">
                    <span class="field-label">Nombre de Usuario</span>
                    <strong class="field-value">{{ user?.username || 'Usuario' }}</strong>
                </div>

                <div class="field-item">
                    <span class="field-label">Rol en el Sistema</span>
                    <strong class="field-value role-tag">{{ user?.rol || 'Administrador' }}</strong>
                </div>

                <div class="field-item">
                    <span class="field-label">Identificador de Cuenta (ID)</span>
                    <span class="field-value">{{ user?.id || 1 }}</span>
                </div>

                <div class="field-item">
                    <span class="field-label">Estado de Cuenta</span>
                    <span class="status-active">
                        <span class="dot"></span>
                        Activa
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.settings-subpane {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    padding: 24px;
    box-shadow: none !important;
}
.subpane-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}
.section-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 4px 0;
}
.section-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}

.account-card {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 20px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    flex-wrap: wrap;
    box-shadow: none !important;
}
.account-avatar-wrap {
    flex-shrink: 0;
}
.account-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #4f46e5;
    border: var(--stroke-w, 2px) solid #4f46e5;
    display: grid;
    place-items: center;
    color: #ffffff;
    font-size: 22px;
    font-weight: 700 !important;
    box-shadow: none !important;
}
.account-fields-grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}
.field-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.field-label {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}
.field-value {
    font-size: 14px;
    color: var(--text, #f4f4f6);
}
.role-tag {
    color: #4f46e5;
    text-transform: capitalize;
}
.status-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #10b981;
    font-weight: 600 !important;
}
.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}
</style>
