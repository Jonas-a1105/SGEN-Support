<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarNav from '@/Components/Layout/SidebarNav.vue';
import MobileBottomNav from '@/Components/Layout/MobileBottomNav.vue';
import Topbar from '@/Components/Layout/Topbar.vue';
import BaseToastContainer from '@/Components/UI/BaseToastContainer.vue';
import { useToast } from '@/Composables/useToast';

defineProps<{ title?: string }>();

interface PageFlashProps {
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

const page = usePage<PageFlashProps>();
const toast = useToast();

watch(
    () => page.props.flash?.success,
    (msg) => {
        if (msg) toast.success('Éxito', msg);
    },
    { immediate: true }
);

watch(
    () => page.props.flash?.error,
    (msg) => {
        if (msg) toast.error('Error', msg);
    },
    { immediate: true }
);
</script>

<template>
    <div class="app">
        <!-- NAVEGACIÓN DESKTOP -->
        <SidebarNav />

        <!-- NAVEGACIÓN MÓVIL PÍLDORA FLOTANTE -->
        <MobileBottomNav />

        <main class="main">
            <Topbar :title="title" />
            <div class="content">
                <div class="app-container">
                    <slot />
                </div>
            </div>
        </main>

        <!-- GLOBAL TOAST NOTIFICATIONS PORTAL -->
        <BaseToastContainer />
    </div>
</template>

<style scoped>
.app {
    height: 100vh;
    display: flex;
    overflow: hidden;
    background: var(--bg);
    color: var(--text);
}

.main {
    flex: 1;
    height: 100vh;
    margin-left: 108px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: margin-left 0.2s ease;
}

.content {
    flex: 1;
    overflow-y: auto;
    background: var(--bg);
    padding: 24px 36px 48px;
}

.app-container {
    max-width: 1220px;
    width: 100%;
    margin: 0 auto;
}

@media (max-width: 1024px) {
    .main {
        margin-left: 0;
        padding-bottom: 80px;
    }
}

@media (max-width: 768px) {
    .content {
        padding: 16px 16px 80px;
    }
}
</style>
