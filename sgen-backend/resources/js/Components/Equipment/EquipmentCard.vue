<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { EquipmentItem } from '@/Composables/useEquipmentFilters';

const props = defineProps<{
    item: EquipmentItem;
    isDense?: boolean;
}>();

const emit = defineEmits<{
    (e: 'view', item: EquipmentItem): void;
    (e: 'edit', item: EquipmentItem): void;
    (e: 'delete', item: EquipmentItem): void;
}>();

const statusClass = computed(() => {
    switch (props.item.status) {
        case 'En Uso':
            return 'en-uso';
        case 'Disponible':
            return 'disponible';
        case 'Reparación':
            return 'reparacion';
        default:
            return 'baja';
    }
});

const isPrinter = computed(() => props.item.type.toLowerCase() === 'impresora');
const isComputer = computed(() => props.item.type.toLowerCase() === 'computadora');
const isPhone = computed(() => props.item.type.toLowerCase() === 'teléfono' || props.item.type.toLowerCase() === 'telefono');
const isCamera = computed(() => props.item.type.toLowerCase() === 'cámara' || props.item.type.toLowerCase() === 'camara');
</script>

<template>
    <article class="device-card" :class="{ dense: isDense }">
        <div class="card-main-content">
            <div class="card-top-row">
                <div class="device-type-icon-box" aria-hidden="true">
                    <svg v-if="isPrinter" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <svg v-else-if="isComputer" viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <circle cx="12" cy="16" r="1.5"></circle>
                    </svg>
                    <svg v-else-if="isPhone" viewBox="0 0 24 24">
                        <rect x="5" y="2" width="14" height="20" rx="3"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                    <svg v-else-if="isCamera" viewBox="0 0 24 24">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                    <svg v-else viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>

                <Link
                    :href="`/equipos/${item.numericId}`"
                    class="arrow-ext-btn"
                    title="Abrir ficha detallada del equipo"
                >
                    <svg viewBox="0 0 24 24">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </Link>
            </div>

            <div class="card-info-block">
                <Link :href="`/equipos/${item.numericId}`" class="card-device-title-link">
                    <h2 class="card-device-title">{{ item.name }}</h2>
                </Link>
                <span class="card-sku-id">ID: {{ item.id }}</span>
            </div>

            <div class="card-meta-list">
                <div class="meta-item-row">
                    <span class="meta-key-label">Ubicación:</span>
                    <span class="meta-val-text" :title="item.dept">{{ item.deptShort }}</span>
                </div>
                <div class="meta-item-row">
                    <span class="meta-key-label">Tipo:</span>
                    <span class="meta-val-text">{{ item.type }}</span>
                </div>
            </div>
        </div>

        <div class="card-bottom-actions-row">
            <span class="status-pill" :class="statusClass">
                <svg v-if="statusClass === 'en-uso'" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <svg v-else-if="statusClass === 'disponible'" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <svg v-else-if="statusClass === 'reparacion'" viewBox="0 0 24 24">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
                <svg v-else viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span>{{ item.status }}</span>
            </span>

            <div class="card-actions-mini-group">
                <button
                    class="action-mini-btn view"
                    @click="emit('view', item)"
                    type="button"
                    title="Vista rápida"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
                <button
                    class="action-mini-btn edit"
                    @click="emit('edit', item)"
                    type="button"
                    title="Editar equipo"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
                <button
                    class="action-mini-btn delete"
                    @click="emit('delete', item)"
                    type="button"
                    title="Eliminar equipo"
                >
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
.device-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--card-radius);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color 0.2s ease, transform 0.2s ease;
    box-shadow: none !important;
}

.device-card:hover {
    border-color: var(--stroke-hover);
    transform: translateY(-2px);
}

.device-card.dense {
    padding: 12px;
}

.card-main-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.device-type-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    display: grid;
    place-items: center;
    color: var(--orange);
}

.device-type-icon-box svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.arrow-ext-btn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: transparent;
    border: var(--stroke-w) solid transparent;
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.arrow-ext-btn:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke);
    color: var(--text);
}

.arrow-ext-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.card-info-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.card-device-title-link {
    text-decoration: none;
    color: inherit;
    display: block;
    overflow: hidden;
}

.card-device-title-link:hover .card-device-title {
    color: var(--brand);
}

.card-device-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-sku-id {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
}

.card-meta-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: var(--bg-sub);
    border-radius: 8px;
    padding: 8px 10px;
}

.meta-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}

.meta-key-label {
    color: var(--text-dim);
}

.meta-val-text {
    color: var(--text);
    font-weight: 600;
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-bottom-actions-row {
    margin-top: 14px;
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    border: var(--stroke-w) solid transparent;
}

.status-pill svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

.status-pill.en-uso {
    background: rgba(37, 99, 235, 0.12);
    color: #3b82f6;
    border-color: rgba(37, 99, 235, 0.25);
}

.status-pill.disponible {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.25);
}

.status-pill.reparacion {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.25);
}

.status-pill.baja {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.25);
}

.card-actions-mini-group {
    display: flex;
    gap: 4px;
}

.action-mini-btn {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.action-mini-btn svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.action-mini-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
}

.action-mini-btn.delete:hover {
    color: var(--red);
    border-color: var(--red);
}
</style>
