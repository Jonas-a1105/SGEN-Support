<script setup lang="ts">
import type { FormEquipment } from './types';

defineProps<{
    isOpen: boolean;
    equipments: FormEquipment[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', equipment: FormEquipment): void;
}>();
</script>

<template>
    <div v-if="isOpen" class="tf-modal-backdrop" @click.self="emit('close')">
        <div class="tf-modal-box">
            <div class="tf-modal-head">
                <h3 class="tf-modal-title">Seleccionar Equipo</h3>
                <button type="button" class="tf-modal-close" @click="emit('close')">×</button>
            </div>
            <div class="tf-modal-body">
                <p class="tf-modal-desc">Se encontraron varios equipos coincidentes. Selecciona el activo correspondiente:</p>
                <div class="tf-modal-list">
                    <div
                        v-for="eq in equipments"
                        :key="eq.id"
                        class="tf-modal-item"
                        @click="emit('select', eq)"
                    >
                        <div>
                            <b class="tf-item-name">{{ eq.type }} - {{ eq.model }}</b>
                            <span class="tf-item-meta">Serial: {{ eq.serial || eq.code }} • {{ eq.department }} • {{ eq.assigned_to }}</span>
                        </div>
                        <span class="tf-item-btn">Seleccionar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tf-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: grid;
    place-items: center;
    z-index: 1000;
    padding: 16px;
}

.tf-modal-box {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 16px;
    max-width: 520px;
    width: 100%;
    overflow: hidden;
    box-shadow: none !important;
}

.tf-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.tf-modal-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}

.tf-modal-close {
    background: transparent;
    border: none;
    color: var(--text-muted, #8e9199);
    font-size: 20px;
    cursor: pointer;
}

.tf-modal-body {
    padding: 20px;
}

.tf-modal-desc {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin: 0 0 14px 0;
}

.tf-modal-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
}

.tf-modal-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
    cursor: pointer;
    transition: border-color 0.2s ease;
    box-shadow: none !important;
}

.tf-modal-item:hover {
    border-color: var(--orange, #2563eb);
}

.tf-item-name {
    font-size: 13px;
    color: var(--text, #f4f4f6);
    display: block;
}

.tf-item-meta {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.tf-item-btn {
    font-size: 11px;
    font-weight: 600;
    color: var(--orange, #2563eb);
    padding: 4px 8px;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 6px;
    white-space: nowrap;
}
</style>
