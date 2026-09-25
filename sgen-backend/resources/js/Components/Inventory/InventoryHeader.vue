<script setup lang="ts">
import { BasePageHeader, BaseButton } from '@/Components/UI';

const emit = defineEmits<{
    (e: 'open-create-article'): void;
    (e: 'open-create-equipment'): void;
    (e: 'open-adjust'): void;
    (e: 'open-transfer'): void;
}>();
</script>

<template>
    <BasePageHeader
        title="Inventario General"
        subtitle="Gestión centralizada de recursos"
    >
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
        </template>

        <template #actions>
            <BaseButton
                variant="outline"
                id="btnOpenTransfer"
                type="button"
                @click="emit('open-transfer')"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-btn-icon">
                    <polyline points="17 1 21 5 17 9"></polyline>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                    <polyline points="7 23 3 19 7 15"></polyline>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                </svg>
                <span>Transferir Stock</span>
            </BaseButton>

            <BaseButton
                variant="outline"
                id="btnOpenNewItem"
                type="button"
                @click="emit('open-create-article')"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-btn-icon">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                    <line x1="12" y1="22.08" x2="12" y2="12" />
                </svg>
                <span>Nuevo Artículo</span>
            </BaseButton>
            <BaseButton
                variant="primary"
                id="btnOpenNewEquipment"
                type="button"
                @click="emit('open-create-equipment')"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-btn-icon">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
                <span>Nuevo Equipo</span>
            </BaseButton>
        </template>
    </BasePageHeader>
</template>

<style scoped>
.header-btn-icon {
    width: 15px;
    height: 15px;
    margin-right: 6px;
}
</style>
