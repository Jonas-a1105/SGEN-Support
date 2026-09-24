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

const operatorName = computed(() => {
    return page.props.auth?.user?.username || 'Operador';
});
</script>

<template>
    <section class="welcome-bar">
        <h2 class="welcome-title">¡Bienvenido de nuevo, {{ operatorName }}! 👋</h2>
        <p class="welcome-desc">Aquí tienes el resumen operativo del día y el estado general de las instalaciones deportivas.</p>
    </section>
</template>

<style scoped>
.welcome-bar {
    margin-bottom: var(--space-4);
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

@media (max-width: 480px) {
    .welcome-title {
        font-size: 17px;
    }
}
</style>
