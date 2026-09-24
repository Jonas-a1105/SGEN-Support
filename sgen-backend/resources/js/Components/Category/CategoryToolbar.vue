<script setup lang="ts">
defineProps<{
    showingCount: number;
    totalCount: number;
}>();

const search = defineModel<string>('search', { default: '' });
</script>

<template>
    <section class="toolbar-card" aria-label="Herramientas y filtros de categorías">
        <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            <input
                v-model="search"
                type="text"
                class="search-input"
                placeholder="Buscar categoría por nombre o descripción..."
                autocomplete="off"
            />
        </div>
        <div class="table-info-counter">
            Mostrando <strong>{{ showingCount }}</strong> de <strong>{{ totalCount }}</strong> categorías
        </div>
    </section>
</template>

<style scoped>
.toolbar-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: none !important;
}
.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 10px;
    padding: 8px 14px;
    flex: 1;
    min-width: 260px;
    max-width: 500px;
    box-shadow: none !important;
}
.search-box svg {
    width: 16px;
    height: 16px;
    stroke: var(--text-muted, #8e9199);
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}
.search-input {
    background: transparent;
    border: none;
    outline: none;
    color: var(--text, #f4f4f6);
    font-size: 13px;
    width: 100%;
}
.search-input::placeholder {
    color: var(--text-dim, #60636d);
}
.table-info-counter {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.table-info-counter strong {
    color: var(--text, #f4f4f6);
}
</style>
