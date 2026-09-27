<template>
    <div v-if="payload" class="creds-modal-root" role="dialog" aria-live="polite" @click.self="emit('close')">
        <div class="creds-modal-card">
            <h3 class="creds-title">Credencial temporal preparada</h3>
            <p class="creds-sub">
                Pásale esta clave al usuario (deberá cambiarla al ingresar). La contraseña no se vuelve a mostrar.
            </p>
            <div class="creds-password-row">
                <code class="creds-password" @click="copiar">{{ payload.password }}</code>
                <button type="button" class="creds-copy-btn" @click="copiar">Copiar</button>
            </div>
            <p class="creds-meta">Usuario destino: <strong>#{{ payload.user_id }}</strong> · Solo visible hasta que cierres esta tarjeta.</p>
            <div class="creds-actions">
                <BaseButton variant="primary" size="sm" @click="emit('close')">Listo, listo</BaseButton>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    payload: { user_id: number; password: string } | null;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

function copiar(): void {
    if (props.payload?.password !== undefined) {
        navigator.clipboard.writeText(props.payload.password).catch(() => {});
    }
}
</script>

<style scoped>
.creds-modal-root {
    position: fixed;
    inset: 0;
    background: rgba(10, 11, 14, 0.55);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
    z-index: 120;
    padding: 16px;
}
.creds-modal-card {
    width: min(460px, 100%);
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 14px;
    padding: 24px 22px;
    box-shadow: 0 18px 44px rgba(0, 0, 0, 0.42);
}
.creds-title {
    margin: 0 0 6px;
    font-size: 16px;
    color: var(--text, #f4f4f6);
}
.creds-sub {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted, #8e9199);
    line-height: 1.55;
}
.creds-password-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 14px;
    padding: 10px 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
}
.creds-password {
    flex: 1;
    font-size: 14px;
    letter-spacing: 0.2px;
    cursor: pointer;
    word-break: break-all;
}
.creds-copy-btn {
    flex-shrink: 0;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: transparent;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
}
.creds-copy-btn:hover {
    color: var(--text, #f4f4f6);
    border-color: var(--stroke, #31343a);
}
.creds-meta {
    margin: 12px 0 0;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.creds-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
}
</style>
