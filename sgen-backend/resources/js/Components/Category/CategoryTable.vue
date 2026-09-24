<script setup lang="ts">
import type { CategoryItem } from '@/Composables/useCategoryFilters';

defineProps<{
    categories: CategoryItem[];
}>();

const emit = defineEmits<{
    (e: 'edit', category: CategoryItem): void;
    (e: 'delete', category: CategoryItem): void;
}>();

function getColorClass(color: string): string {
    const map: Record<string, string> = {
        '#dc3545': 'tone-red',
        '#ffc107': 'tone-yellow',
        '#6c757d': 'tone-gray',
        '#198754': 'tone-green',
        '#0d6efd': 'tone-blue',
        '#8b5cf6': 'tone-purple',
    };
    return map[color.toLowerCase()] || 'tone-blue';
}
</script>

<template>
    <div class="categories-table-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="th-icon">Icono</th>
                        <th class="th-name">Nombre</th>
                        <th class="th-desc">Descripción</th>
                        <th class="th-color">Color</th>
                        <th class="th-tickets">Tickets</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cat in categories" :key="cat.id" class="table-row">
                        <td class="td-icon">
                            <div class="cat-icon-bubble" :class="getColorClass(cat.color)">
                                <svg v-if="cat.icono === 'hardware'" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" /><rect x="9" y="9" width="6" height="6" /><line x1="9" y1="1" x2="9" y2="4" /><line x1="15" y1="1" x2="15" y2="4" /><line x1="9" y1="20" x2="9" y2="23" /><line x1="15" y1="20" x2="15" y2="23" /><line x1="20" y1="9" x2="23" y2="9" /><line x1="20" y1="15" x2="23" y2="15" /><line x1="1" y1="9" x2="4" y2="9" /><line x1="1" y1="15" x2="4" y2="15" /></svg>
                                <svg v-else-if="cat.icono === 'printer'" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect x="6" y="14" width="12" height="8" /></svg>
                                <svg v-else-if="cat.icono === 'wifi'" viewBox="0 0 24 24"><path d="M5 12.55a11 11 0 0 1 14.08 0" /><path d="M1.42 9a16 16 0 0 1 21.16 0" /><path d="M8.53 16.11a6 6 0 0 1 6.95 0" /><line x1="12" y1="20" x2="12.01" y2="20" /></svg>
                                <svg v-else-if="cat.icono === 'mouse'" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="7" /><line x1="12" y1="6" x2="12" y2="10" /></svg>
                                <svg v-else-if="cat.icono === 'window'" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" /><line x1="3" y1="9" x2="21" y2="9" /><line x1="9" y1="21" x2="9" y2="9" /></svg>
                                <svg v-else viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                            </div>
                        </td>
                        <td class="td-name">
                            <span class="cat-name-text">{{ cat.nombre }}</span>
                        </td>
                        <td class="td-desc">
                            <span class="cat-desc-text">{{ cat.descripcion || 'Sin descripción' }}</span>
                        </td>
                        <td class="td-color">
                            <div class="color-badge" :class="getColorClass(cat.color)">
                                <span class="color-dot"></span>
                                <span class="color-code">{{ cat.color }}</span>
                            </div>
                        </td>
                        <td class="td-tickets">
                            <span class="tickets-pill">{{ cat.total_tickets }} tickets</span>
                        </td>
                        <td class="td-actions">
                            <div class="actions-group">
                                <button class="btn-action edit" type="button" title="Editar categoría" @click="emit('edit', cat)">
                                    <svg viewBox="0 0 24 24"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" /></svg>
                                </button>
                                <button class="btn-action delete" type="button" title="Eliminar categoría" @click="emit('delete', cat)">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="empty-cell">
                            <div class="empty-state-box">
                                <div class="empty-icon-circle">
                                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
                                </div>
                                <strong class="empty-title">No se encontraron categorías</strong>
                                <span class="empty-subtitle">Prueba con otros términos de búsqueda.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.categories-table-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
    overflow: hidden;
    box-shadow: none !important;
}
.table-responsive {
    overflow-x: auto;
}
.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
}
.custom-table thead th {
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    font-size: 11px;
    letter-spacing: 0.05em;
    padding: 12px 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke, #31343a);
}
.custom-table tbody td {
    padding: 14px 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    vertical-align: middle;
}
.custom-table tbody tr:last-child td {
    border-bottom: none;
}
.th-icon { width: 70px; }
.th-name { width: 200px; }
.th-color { width: 140px; }
.th-tickets { width: 110px; }
.th-actions { width: 120px; text-align: right; }
.td-actions { text-align: right; }

.cat-icon-bubble {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    background: var(--bg-sub, #1e2024);
    box-shadow: none !important;
}
.cat-icon-bubble svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tone-red { color: #dc3545; border-color: rgba(220, 53, 69, 0.3); background: rgba(220, 53, 69, 0.08); }
.tone-yellow { color: #ffc107; border-color: rgba(255, 193, 7, 0.3); background: rgba(255, 193, 7, 0.08); }
.tone-gray { color: #8e9199; border-color: rgba(142, 145, 153, 0.3); background: rgba(142, 145, 153, 0.08); }
.tone-green { color: #198754; border-color: rgba(25, 135, 84, 0.3); background: rgba(25, 135, 84, 0.08); }
.tone-blue { color: #0d6efd; border-color: rgba(13, 110, 253, 0.3); background: rgba(13, 110, 253, 0.08); }
.tone-purple { color: #8b5cf6; border-color: rgba(139, 92, 246, 0.3); background: rgba(139, 92, 246, 0.08); }

.cat-name-text {
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
}
.cat-desc-text {
    color: var(--text-muted, #8e9199);
    font-size: 12px;
}
.color-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-family: monospace;
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    box-shadow: none !important;
}
.color-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}
.tickets-pill {
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}
.actions-group {
    display: inline-flex;
    gap: 6px;
}
.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: none !important;
}
.btn-action svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-action.edit:hover {
    color: var(--text, #f4f4f6);
    border-color: #0d6efd;
}
.btn-action.delete:hover {
    color: #ef4444;
    border-color: #ef4444;
}
.empty-cell {
    padding: 48px 20px;
    text-align: center;
}
.empty-state-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.empty-icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    display: grid;
    place-items: center;
    color: var(--text-muted, #8e9199);
}
.empty-icon-circle svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.empty-title {
    font-size: 14px;
    color: var(--text, #f4f4f6);
}
.empty-subtitle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
</style>
