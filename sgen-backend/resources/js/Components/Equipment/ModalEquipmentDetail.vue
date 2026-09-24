<script setup lang="ts">
import { computed } from 'vue';
import type { EquipmentItem } from '@/Composables/useEquipmentFilters';

const props = defineProps<{
    show: boolean;
    item: EquipmentItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const statusClass = computed(() => {
    if (!props.item) return '';
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
</script>

<template>
    <div v-if="show && item" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-head">
                <div class="head-code-title">
                    <span class="badge-code">{{ item.id }}</span>
                    <h3 class="modal-title">Ficha de Equipo</h3>
                </div>
                <button class="icon-close" @click="emit('close')" type="button">×</button>
            </div>

            <div class="detail-body">
                <div>
                    <span class="form-label">Equipo</span>
                    <h2 class="detail-name">{{ item.name }}</h2>
                </div>

                <div class="form-row-2">
                    <div>
                        <span class="form-label">Tipo</span>
                        <div class="detail-val">{{ item.type }}</div>
                    </div>
                    <div>
                        <span class="form-label">Estado</span>
                        <div>
                            <span class="status-pill" :class="statusClass">
                                {{ item.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="form-label">Ubicación Física</span>
                    <div class="detail-val">{{ item.dept }}</div>
                </div>

                <div v-if="item.assignedTo" class="form-row-2">
                    <div>
                        <span class="form-label">Usuario Asignado</span>
                        <div class="detail-val highlight">{{ item.assignedTo }}</div>
                    </div>
                    <div v-if="item.ipAddress">
                        <span class="form-label">Dirección IP</span>
                        <div class="detail-val code-font">{{ item.ipAddress }}</div>
                    </div>
                </div>

                <div v-if="item.serialNumber">
                    <span class="form-label">Número de Serie</span>
                    <div class="detail-val code-font">{{ item.serialNumber }}</div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-primary" @click="emit('close')" type="button">Cerrar Ficha</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: grid;
    place-items: center;
    z-index: 100;
    padding: 16px;
}

.modal {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    width: 100%;
    max-width: 440px;
    padding: 24px;
    box-shadow: none !important;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.head-code-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge-code {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.icon-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
}

.icon-close:hover {
    color: var(--text);
}

.detail-body {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.form-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-dim);
    display: block;
    margin-bottom: 3px;
}

.detail-name {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.detail-val {
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
}

.detail-val.highlight {
    color: var(--orange);
}

.detail-val.code-font {
    font-family: monospace;
    font-size: 12px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.status-pill.en-uso {
    background: rgba(37, 99, 235, 0.12);
    color: #3b82f6;
}

.status-pill.disponible {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.status-pill.reparacion {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.status-pill.baja {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 16px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.btn-primary {
    background: var(--orange);
    border: var(--stroke-w) solid var(--orange);
    color: #ffffff;
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: none !important;
}

.btn-primary:hover {
    opacity: 0.9;
}
</style>
