<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SidebarNav from '@/Components/Layout/SidebarNav.vue';
import MobileBottomNav from '@/Components/Layout/MobileBottomNav.vue';
import Topbar from '@/Components/Layout/Topbar.vue';

defineProps<{ title?: string }>();

interface PageFlashProps {
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

const page = usePage<PageFlashProps>();
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
                <div v-if="page.props.flash?.success" class="alert-banner">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="alert-banner alert-error">
                    {{ page.props.flash.error }}
                </div>
                <slot />
            </div>
        </main>
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
    width: 100%;
    overflow-y: auto;
    padding: 24px 36px 48px;
}

.alert-banner {
    max-width: 1220px;
    margin: 0 auto 16px;
    padding: 12px 18px;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.12);
    border: var(--stroke-w) solid var(--green);
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    box-shadow: none !important;
}

.alert-error {
    background: rgba(220, 38, 38, 0.12);
    border-color: var(--red);
}

/* RESPONSIVE MÓVIL */
@media (max-width: 768px) {
    .main {
        margin-left: 0;
    }
    .content {
        padding: 16px 16px 84px 16px;
    }
}
</style>
