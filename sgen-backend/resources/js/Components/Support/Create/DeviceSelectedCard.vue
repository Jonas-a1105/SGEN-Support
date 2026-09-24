<script setup lang="ts">
import type { FormEquipment } from './types';

defineProps<{
    equipment: FormEquipment;
}>();

const emit = defineEmits<{
    (e: 'clear'): void;
}>();
</script>

<template>
    <div class="tf-device-card">
        <div class="tf-device-header">
            <span class="tf-device-badge">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                Equipo Vinculado
            </span>
            <button
                type="button"
                class="tf-device-close"
                title="Desvincular equipo"
                @click="emit('clear')"
            >
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
            </button>
        </div>
        <div class="tf-device-body">
            <div class="tf-device-icon">
                <svg v-if="equipment.type.toLowerCase().includes('laptop')" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="12" rx="2" />
                    <line x1="2" y1="20" x2="22" y2="20" />
                </svg>
                <svg v-else-if="equipment.type.toLowerCase().includes('impresora')" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                <svg v-else-if="equipment.type.toLowerCase().includes('servidor')" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="8" rx="2" />
                    <rect x="2" y="14" width="20" height="8" rx="2" />
                    <line x1="6" y1="6" x2="6.01" y2="6" />
                    <line x1="6" y1="18" x2="6.01" y2="18" />
                </svg>
                <svg v-else viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
            </div>
            <h3 class="tf-device-name">{{ equipment.model }}</h3>
            <p class="tf-device-code">S/N: {{ equipment.serial || equipment.code }}</p>

            <div class="tf-device-details">
                <div class="tf-device-detail-item">
                    <div class="tf-detail-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M9 3v18M15 3v18M3 9h18M3 15h18" /></svg>
                    </div>
                    <div>
                        <span class="tf-device-detail-label">Departamento</span>
                        <p class="tf-device-detail-value">{{ equipment.department }}</p>
                    </div>
                </div>
                <div class="tf-device-detail-item">
                    <div class="tf-detail-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg>
                    </div>
                    <div>
                        <span class="tf-device-detail-label">Asignado a</span>
                        <p class="tf-device-detail-value">{{ equipment.assigned_to }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tf-device-card {
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: none !important;
}

.tf-device-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.tf-device-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #10b981;
}

.tf-device-badge svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.5;
}

.tf-device-close {
    background: transparent;
    border: none;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    display: grid;
    place-items: center;
    padding: 4px;
}

.tf-device-close svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-device-body {
    padding: 18px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.tf-device-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(37, 99, 235, 0.12);
    border: var(--stroke-w, 2px) solid rgba(37, 99, 235, 0.3);
    color: var(--orange, #2563eb);
    display: grid;
    place-items: center;
    margin-bottom: 12px;
}

.tf-device-icon svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.tf-device-name {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0 0 4px 0;
}

.tf-device-code {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin: 0 0 16px 0;
}

.tf-device-details {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-top: 14px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    text-align: left;
}

.tf-device-detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tf-detail-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--bg-card, #17181a);
    display: grid;
    place-items: center;
    color: var(--text-muted, #8e9199);
    flex-shrink: 0;
}

.tf-detail-icon svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.tf-device-detail-label {
    display: block;
    font-size: 10px;
    color: var(--text-dim, #60636d);
    text-transform: uppercase;
}

.tf-device-detail-value {
    font-size: 12px;
    font-weight: 600;
    color: var(--text, #f4f4f6);
    margin: 0;
}
</style>
