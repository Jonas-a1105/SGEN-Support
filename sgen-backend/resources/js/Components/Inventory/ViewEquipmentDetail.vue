<script setup lang="ts">
import { ref } from 'vue';
import type { Equipment, Department, Employee } from '@/Types/inventory';
import EquipmentHeroCard from './Detail/EquipmentHeroCard.vue';
import EquipmentLocationCard from './Detail/EquipmentLocationCard.vue';
import EquipmentContextMenu from './Detail/EquipmentContextMenu.vue';
import EquipmentSpecsGrid from './Detail/EquipmentSpecsGrid.vue';
import EquipmentAcquisitionPane from './Detail/EquipmentAcquisitionPane.vue';
import EquipmentTicketsPane from './Detail/EquipmentTicketsPane.vue';
import EquipmentMaintPane from './Detail/EquipmentMaintPane.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';

const props = defineProps<{
    equipment: Equipment;
    departments: Department[];
    employees: Employee[];
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'edit', eq: Equipment): void;
    (e: 'open-reassign', eq: Equipment): void;
    (e: 'view-history', eq: Equipment): void;
    (e: 'create-ticket', eq: Equipment): void;
}>();

const activeSubtab = ref<'specs' | 'acq' | 'tickets' | 'maint'>('specs');
const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const handleDuplicate = () => {
    isDropdownOpen.value = false;
    emit('edit', {
        ...props.equipment,
        id: 0,
        codigo_inventario: `${props.equipment.codigo_inventario}-COPIA`,
    });
};
</script>

<template>
    <section class="inventory-wrapper detail-view-container" id="viewEquipmentDetail">
        <div class="detail-top-nav">
            <button class="back-link-btn" id="btnBackFromEquipmentDetail" type="button" @click="emit('back')">
                <span>←</span>
                <span>Volver a Inventario (Stock)</span>
            </button>
            <div class="header-actions-row">
                <BaseButton
                    variant="outline"
                    id="btnEditFromDetail"
                    type="button"
                    @click="emit('edit', equipment)"
                >
                    <svg viewBox="0 0 24 24" class="svg-icon-standard">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    <span>Editar</span>
                </BaseButton>

                <EquipmentContextMenu
                    :is-open="isDropdownOpen"
                    @toggle="toggleDropdown"
                    @history="isDropdownOpen = false; emit('view-history', equipment)"
                    @print="isDropdownOpen = false; window.print()"
                    @duplicate="handleDuplicate"
                />
            </div>
        </div>

        <!-- PESTAÑAS INTERNAS DEL EQUIPO -->
        <div class="detail-tabs-pillbar">
            <button class="sub-tab-btn" :class="{ active: activeSubtab === 'specs' }" type="button" @click="activeSubtab = 'specs'">
                <span>⚙</span>
                <span>Especificaciones</span>
            </button>
            <button class="sub-tab-btn" :class="{ active: activeSubtab === 'acq' }" type="button" @click="activeSubtab = 'acq'">
                <span>📄</span>
                <span>Adquisición</span>
            </button>
            <button class="sub-tab-btn" :class="{ active: activeSubtab === 'tickets' }" type="button" @click="activeSubtab = 'tickets'">
                <span>💬</span>
                <span>Soportes</span>
            </button>
            <button class="sub-tab-btn" :class="{ active: activeSubtab === 'maint' }" type="button" @click="activeSubtab = 'maint'">
                <span>🛠</span>
                <span>Mantenimiento</span>
            </button>
        </div>

        <!-- CONTENIDO DETALLE DE EQUIPO -->
        <div class="detail-layout-grid">
            <div class="detail-column-stack">
                <EquipmentHeroCard :equipment="equipment" />
                <EquipmentLocationCard :equipment="equipment" @change-location="emit('open-reassign', equipment)" />
            </div>

            <div class="detail-column-stack">
                <EquipmentSpecsGrid v-show="activeSubtab === 'specs'" :equipment="equipment" />
                <EquipmentAcquisitionPane v-show="activeSubtab === 'acq'" :equipment="equipment" />
                <EquipmentTicketsPane v-show="activeSubtab === 'tickets'" @create-ticket="emit('create-ticket', equipment)" />
                <EquipmentMaintPane v-show="activeSubtab === 'maint'" @new-maintenance="emit('create-ticket', equipment)" />
            </div>
        </div>
    </section>
</template>

<style scoped>
.svg-icon-standard {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.detail-column-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}
</style>
