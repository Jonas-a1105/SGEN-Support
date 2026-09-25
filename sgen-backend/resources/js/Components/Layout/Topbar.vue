<script setup lang="ts">
import { onMounted } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import NotificationBellDropdown from '@/Components/Notifications/NotificationBellDropdown.vue';
import { IconSquare, IconPalette, IconSun, IconMoon } from '@tabler/icons-vue';

defineProps<{ title?: string }>();

const {
    theme,
    borderWidth,
    accentColor,
    toggleTheme,
    toggleBorderWidth,
    applyAccentColor,
    initTheme,
} = useTheme();

const onAccentInput = (e: Event) => {
    const target = e.target as HTMLInputElement | null;
    if (target) {
        applyAccentColor(target.value);
    }
};

onMounted(() => {
    initTheme();
});
</script>

<template>
    <header class="topbar">
        <div class="topbar-content">
            <h1 class="dash-title">{{ title || 'Inventario General' }}</h1>
            <div class="topbar-right">
                <div class="header-tools">
                    <NotificationBellDropdown />

                    <button class="border-control hide-mobile" type="button" @click="toggleBorderWidth" :title="'Alternar grosor de bordes (' + borderWidth + ')'">
                        <IconSquare :size="15" stroke-width="1.8" class="tool-icon" />
                        <span>Borde: {{ borderWidth }}</span>
                    </button>
                    <label class="color-control hide-mobile" title="Color de acento">
                        <IconPalette :size="15" stroke-width="1.8" class="tool-icon" />
                        <span>Acento</span>
                        <input type="color" :value="accentColor" @input="onAccentInput" />
                    </label>
                    <button class="theme-toggle" type="button" @click="toggleTheme" :title="'Alternar modo'">
                        <component :is="theme === 'dark' ? IconSun : IconMoon" :size="15" stroke-width="1.8" class="tool-icon" />
                        <span>{{ theme === 'dark' ? 'Claro' : 'Oscuro' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.topbar {
    width: 100%;
    height: 64px;
    padding: 0 36px;
    display: flex;
    align-items: center;
    border-bottom: var(--stroke-w) solid var(--stroke);
    background: var(--bg);
    z-index: 30;
    flex-shrink: 0;
    box-shadow: none !important;
}

.topbar-content {
    max-width: 1220px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dash-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}

.header-tools {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-control,
.theme-toggle,
.border-control {
    height: 34px;
    border: var(--stroke-w) solid var(--stroke);
    background: transparent;
    color: var(--text);
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 11px;
    cursor: pointer;
    font-size: 13px;
    white-space: nowrap;
    box-shadow: none !important;
}

.color-control:hover,
.theme-toggle:hover,
.border-control:hover {
    border-color: var(--stroke-hover);
}

.color-control input {
    width: 18px;
    height: 18px;
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 4px;
}

.tool-icon {
    color: var(--orange);
    font-size: 14px;
}

@media (max-width: 768px) {
    .topbar {
        padding: 0 16px;
        height: 56px;
    }
    .hide-mobile {
        display: none !important;
    }
}
</style>
