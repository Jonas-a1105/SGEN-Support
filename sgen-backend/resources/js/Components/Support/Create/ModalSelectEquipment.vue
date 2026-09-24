<script setup lang="ts">
import BaseModal from '@/Components/UI/BaseModal.vue';
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
    <BaseModal
        :is-open="isOpen"
        title="Seleccionar Equipo"
        max-width="md"
        @close="emit('close')"
    >
        <div class="tf-modal-body">
            <p class="tf-modal-desc">Se encontraron varios equipos coincidentes. Selecciona el activo correspondiente:</p>
            <div class="tf-modal-list">
                <div
                    v-for="eq in equipments"
                    :key="eq.id"
                    class="tf-modal-item"
                    @click="emit('select', eq)"
                    role="button"
                    tabindex="0"
                >
                    <div>
                        <b class="tf-item-name">{{ eq.type }} - {{ eq.model }}</b>
                        <span class="tf-item-meta">Serial: {{ eq.serial || eq.code }} • {{ eq.department }} • {{ eq.assigned_to }}</span>
                    </div>
                    <span class="tf-item-btn">Seleccionar</span>
                </div>
                <div v-if="equipments.length === 0" class="empty-notice">
                    No se encontraron equipos.
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.tf-modal-body {
    display: flex;
    flex-direction: column;
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
    max-height: 320px;
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
    color: var(--blue, #2563eb);
    padding: 4px 8px;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 6px;
    white-space: nowrap;
}

.empty-notice {
    text-align: center;
    padding: 20px;
    font-size: 13px;
    color: var(--text-muted);
}
</style>
