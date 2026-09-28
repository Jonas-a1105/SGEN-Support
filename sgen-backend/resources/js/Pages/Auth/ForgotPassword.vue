<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import BaseInput from '@/Components/UI/BaseInput.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';

const form = useForm({ email: '' });

const submit = (): void => {
    form.post('/forgot-password');
};
</script>

<template>
    <div class="auth-viewport">
        <BaseCard padding="lg" class="auth-card">
            <header class="auth-header">
                <div class="brand-badge">
                    <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" /><polyline points="3.27 6.96 12 12.01 20.73 6.96" /><line x1="12" y1="22.08" x2="12" y2="12" /></svg>
                </div>
                <h1 class="auth-title">Recuperar contraseña</h1>
                <p class="auth-subtitle">Te enviamos un enlace firmado (expira en 60 minutos); al usarlo, todas las otras sesiones se cierran.</p>
            </header>

            <form class="auth-form" @submit.prevent="submit">
                <BaseInput v-model="form.email" type="email" label="Correo institucional" placeholder="tu@empresa.com" :error="form.errors.email" required />

                <BaseButton type="submit" variant="primary" :loading="form.processing" class="w-full">
                    Enviar enlace de recuperación
                </BaseButton>

                <p class="auth-help">
                    También puedes solicitarle una clave temporal al administrador del sistema.
                </p>

                <a href="/login" class="auth-back">← Volver al inicio de sesión</a>
            </form>
        </BaseCard>
    </div>
</template>

<style scoped>
.auth-viewport { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-4); background-color: var(--bg); }
.auth-card { width: 100%; max-width: 26rem; }
.auth-header { text-align: center; margin-bottom: var(--space-5); display: flex; flex-direction: column; align-items: center; }
.brand-badge { width: 52px; height: 52px; border-radius: var(--radius-md); background: rgba(var(--orange-rgb), 0.12); color: var(--orange); border: var(--stroke-w) solid var(--orange); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-3); }
.brand-badge svg { width: 28px; height: 28px; stroke: currentColor; fill: none; stroke-width: 2; }
.auth-title { font-size: 18px !important; font-weight: var(--weight-semibold) !important; color: var(--text); margin: 0; }
.auth-subtitle { font-size: 12px; color: var(--text-muted); margin: var(--space-1) 0 0; }
.auth-form { display: flex; flex-direction: column; gap: var(--space-4); margin-top: var(--space-4); }
.auth-help { margin: 0; font-size: 12px; line-height: 1.5; color: var(--text-muted); text-align: center; border-top: var(--stroke-w) solid var(--stroke-subtle); padding-top: 12px; }
.auth-back { font-size: 12px; color: var(--text-muted); text-decoration: none; text-align: center; }
.w-full { width: 100%; }
</style>
