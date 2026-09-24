<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits<{
    (e: 'update-password', payload: {
        current_password: string;
        new_password: string;
        new_password_confirmation: string;
    }): void;
}>();

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const errorMessage = ref('');

function handlePasswordSubmit() {
    errorMessage.value = '';
    if (newPassword.value.length < 6) {
        errorMessage.value = 'La nueva contraseña debe tener al menos 6 caracteres.';
        return;
    }
    if (newPassword.value !== confirmPassword.value) {
        errorMessage.value = 'Las contraseñas no coinciden.';
        return;
    }

    emit('update-password', {
        current_password: currentPassword.value,
        new_password: newPassword.value,
        new_password_confirmation: confirmPassword.value,
    });

    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';
}
</script>

<template>
    <div class="settings-subpane">
        <div class="subpane-header">
            <h3 class="section-title">Seguridad</h3>
            <span class="section-subtitle">Administra tu contraseña de acceso y las políticas de sesión.</span>
        </div>

        <form class="settings-form" @submit.prevent="handlePasswordSubmit">
            <div v-if="errorMessage" class="error-alert">
                {{ errorMessage }}
            </div>

            <div class="form-group">
                <label class="form-label" for="inputCurrentPass">Contraseña Actual</label>
                <input
                    id="inputCurrentPass"
                    v-model="currentPassword"
                    type="password"
                    class="form-input"
                    placeholder="Tu contraseña actual"
                    required
                    autocomplete="current-password"
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="inputNewPass">Nueva Contraseña</label>
                <input
                    id="inputNewPass"
                    v-model="newPassword"
                    type="password"
                    class="form-input"
                    placeholder="Mínimo 6 caracteres"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="inputConfirmPass">Confirmar Nueva Contraseña</label>
                <input
                    id="inputConfirmPass"
                    v-model="confirmPassword"
                    type="password"
                    class="form-input"
                    placeholder="Repite la nueva contraseña"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="form-actions">
                <button class="btn-save" type="submit">Actualizar Contraseña</button>
            </div>
        </form>

        <div class="security-meta-box">
            <strong class="meta-title">Autenticación en Dos Pasos (2FA)</strong>
            <p class="meta-desc">Añade una capa adicional de protección a tu cuenta mediante un código de verificación temporal.</p>
            <button class="btn-outline" type="button" disabled title="Próximamente disponible">
                Configurar 2FA (Próximamente)
            </button>
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
.error-alert {
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(239, 68, 68, 0.12);
    border: var(--stroke-w, 2px) solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    font-size: 12px;
    margin-bottom: 16px;
}
.form-group {
    margin-bottom: 16px;
}
.form-label {
    display: block;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin-bottom: 6px;
}
.form-input {
    width: 100%;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    padding: 10px 14px;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    outline: none;
    box-shadow: none !important;
}
.form-input:focus {
    border-color: #4f46e5;
}
.form-actions {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
.btn-save {
    background: #4f46e5;
    border: var(--stroke-w, 2px) solid #4f46e5;
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 700 !important;
    cursor: pointer;
    box-shadow: none !important;
}

.security-meta-box {
    margin-top: 32px;
    padding-top: 20px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}
.meta-title {
    display: block;
    font-size: 14px;
    color: var(--text, #f4f4f6);
    margin-bottom: 4px;
}
.meta-desc {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin: 0 0 14px 0;
}
.btn-outline {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 12px;
    cursor: not-allowed;
    box-shadow: none !important;
}
</style>
