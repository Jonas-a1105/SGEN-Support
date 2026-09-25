<script setup lang="ts">
import { computed } from 'vue';
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

const emit = defineEmits<{
    (e: 'open-appearance'): void;
}>();

const operatorName = computed(() => {
    return page.props.auth?.user?.username || 'Operador';
});
</script>

<template>
    <section class="welcome-bar">
        <div class="welcome-content">
            <h2 class="welcome-title">¡Bienvenido de nuevo, {{ operatorName }}!</h2>
            <p class="welcome-desc">Aquí tienes el resumen operativo del día y el estado general de la infraestructura tecnológica.</p>
        </div>
        <button
            class="btn-appearance-trigger"
            type="button"
            title="Personalizar tema y apariencia"
            aria-label="Personalizar apariencia"
            @click="emit('open-appearance')"
        >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor" />
                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor" />
                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor" />
                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor" />
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z" />
            </svg>
            <span>Personalizar Vista</span>
        </button>
    </section>
</template>

<style scoped>
.welcome-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
    flex-wrap: wrap;
}

.welcome-content {
    flex: 1;
    min-width: 250px;
}

.welcome-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 var(--space-1);
    letter-spacing: -0.01em;
}

.welcome-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.45;
}

.btn-appearance-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: var(--panel-radius, 10px);
    border: var(--stroke-w, 1px) solid var(--stroke);
    background: var(--bg-card);
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-appearance-trigger:hover {
    background: var(--bg-sub);
    border-color: var(--primary);
    color: var(--primary);
}

@media (max-width: 480px) {
    .welcome-title {
        font-size: 17px;
    }
    .btn-appearance-trigger {
        width: 100%;
        justify-content: center;
    }
}
</style>
