<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type {
    Product,
    Equipment,
    Department,
    Employee,
    InventoryMovement,
    StockLocation,
    InventoryPagination,
    InventoryKpis,
    InventoryFilters,
} from '@/Types/inventory';
import InventoryHeader from './InventoryHeader.vue';
import InventoryKpiGrid from './InventoryKpiGrid.vue';
import ArticulosTable from './ArticulosTable.vue';
import EquiposStockTab from './EquiposStockTab.vue';
import ViewEquipmentDetail from './ViewEquipmentDetail.vue';
import ViewItemDetail from './ViewItemDetail.vue';
import ViewStockDistribution from './ViewStockDistribution.vue';
import ViewItemHistory from './ViewItemHistory.vue';
import ViewNewItemForm from './ViewNewItemForm.vue';
import ViewEditEquipmentWizard from './ViewEditEquipmentWizard.vue';
import ModalAdjustStock from './ModalAdjustStock.vue';
import ModalLocationAssign from './ModalLocationAssign.vue';
import ModalTransferStock from './ModalTransferStock.vue';
import { useInventoryFilters } from '@/Composables/useInventoryFilters';

const props = defineProps<{
    products: Product[];
    pagination: InventoryPagination;
    kpis: InventoryKpis;
    filters: InventoryFilters;
    equipos: Equipment[];
    departamentos: Department[];
    empleados?: Employee[];
    movimientos?: InventoryMovement[];
    ubicaciones?: StockLocation[];
}>();

const { search, toggleLowStock } = useInventoryFilters(props.filters);
const currentView = ref<'list' | 'equipment-detail' | 'item-detail' | 'distribution' | 'history' | 'new-item' | 'edit-equipment'>('list');
const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const activeTab = ref<'articulos' | 'equipos'>(urlParams?.get('tab') === 'equipos' ? 'equipos' : 'articulos');
const selectedItem = ref<Product | null>(null);
const selectedItemForEdit = ref<Product | null>(null);
const selectedEquipment = ref<Equipment | null>(null);
const isAdjustOpen = ref(false);
const isLocationAssignOpen = ref(false);
const isTransferOpen = ref(false);

const openTransfer = (item?: Product) => {
    selectedItem.value = item || null;
    isTransferOpen.value = true;
};

const openItem = (item: Product) => {
    selectedItem.value = item;
    currentView.value = 'item-detail';
};

const openEquip = (eq: Equipment | null) => {
    selectedEquipment.value = eq;
    currentView.value = 'equipment-detail';
};

const openCreateEquipment = () => {
    selectedEquipment.value = null;
    currentView.value = 'edit-equipment';
};

const openAdjust = (item?: Product) => {
    selectedItem.value = item || null;
    isAdjustOpen.value = true;
};

const openReassign = (eq: Equipment) => {
    selectedEquipment.value = eq;
    isLocationAssignOpen.value = true;
};

const openCreateArticle = () => {
    selectedItemForEdit.value = null;
    currentView.value = 'new-item';
};

const openEditItem = (item: Product) => {
    selectedItemForEdit.value = item;
    currentView.value = 'new-item';
};

const handleCreateTicket = (eq?: Equipment | null) => {
    const equip = eq || selectedEquipment.value;
    if (equip?.id) {
        router.visit(`/soportes/crear?equipo_id=${equip.id}`);
    } else {
        router.visit('/soportes/crear');
    }
};

const handleCreateMaintenance = (eq?: Equipment | null) => {
    const equip = eq || selectedEquipment.value;
    if (equip?.id) {
        router.visit(`/mantenimientos/crear?equipo_id=${equip.id}`);
    } else {
        router.visit('/mantenimientos/crear');
    }
};
</script>

<template>
    <div class="inventory-wrapper">
        <template v-if="currentView === 'list'">
            <InventoryHeader
                @open-create-article="openCreateArticle"
                @open-create-equipment="openCreateEquipment"
                @open-adjust="openAdjust()"
                @open-transfer="openTransfer()"
            />
            <InventoryKpiGrid :kpis="kpis" />
            <div class="section-nav-strip">
                <button
                    class="section-tab-btn"
                    :class="{ active: activeTab === 'articulos' }"
                    type="button"
                    @click="activeTab = 'articulos'"
                >
                    <span>Artículos &amp; Repuestos</span>
                    <span class="tab-badge-pill">{{ products.length }}</span>
                </button>
                <button
                    class="section-tab-btn"
                    :class="{ active: activeTab === 'equipos' }"
                    type="button"
                    @click="activeTab = 'equipos'"
                >
                    <span>Equipos en Almacén (Disponibles)</span>
                    <span class="tab-badge-pill">{{ equipos.length }}</span>
                </button>
            </div>
            <ArticulosTable
                v-if="activeTab === 'articulos'"
                v-model:search="search"
                :products="products"
                :pagination="pagination"
                @view-item="openItem"
                @open-adjust="openAdjust"
            />
            <EquiposStockTab
                v-else
                v-model:search="search"
                :equipos="equipos"
                @open-create-equipment="openCreateEquipment"
                @view-equipment="openEquip"
            />
        </template>

        <ViewEquipmentDetail
            v-else-if="currentView === 'equipment-detail' && selectedEquipment"
            :equipment="selectedEquipment"
            :departments="departamentos"
            :employees="empleados || []"
            @back="currentView = 'list'"
            @edit="currentView = 'edit-equipment'"
            @open-reassign="openReassign"
            @create-ticket="handleCreateTicket"
            @new-maintenance="handleCreateMaintenance"
        />

        <ViewItemDetail
            v-else-if="currentView === 'item-detail' && selectedItem"
            :item="selectedItem"
            @back="currentView = 'list'"
            @view-distribution="currentView = 'distribution'"
            @view-history="currentView = 'history'"
            @open-adjust="openAdjust(selectedItem)"
            @edit="openEditItem(selectedItem)"
        />

        <ViewStockDistribution
            v-else-if="currentView === 'distribution' && selectedItem"
            :item="selectedItem"
            :departments="departamentos"
            :locations="ubicaciones || []"
            @back="currentView = 'item-detail'"
        />

        <ViewItemHistory
            v-else-if="currentView === 'history' && selectedItem"
            :item="selectedItem"
            :movimientos="movimientos || []"
            @back="currentView = 'item-detail'"
        />

        <ViewNewItemForm
            v-else-if="currentView === 'new-item'"
            :item="selectedItemForEdit"
            @back="currentView = 'list'"
            @saved="currentView = 'list'"
        />

        <ViewEditEquipmentWizard
            v-else-if="currentView === 'edit-equipment'"
            :equipment="selectedEquipment"
            :departamentos="departamentos"
            :empleados="empleados || []"
            @back="currentView = 'list'"
            @saved="currentView = 'list'"
        />

        <ModalAdjustStock
            :is-open="isAdjustOpen"
            :product="selectedItem"
            :products="products"
            @close="isAdjustOpen = false"
        />

        <ModalLocationAssign
            :is-open="isLocationAssignOpen"
            :equipment="selectedEquipment"
            :departamentos="departamentos"
            :empleados="empleados || []"
            @close="isLocationAssignOpen = false"
        />

        <ModalTransferStock
            :is-open="isTransferOpen"
            :products="products"
            :departamentos="departamentos"
            :preselected-item="selectedItem"
            @close="isTransferOpen = false"
        />
    </div>
</template>

<style scoped>
.inventory-wrapper {
    max-width: 1220px;
    margin: 0 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.section-nav-strip {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--card-radius);
    padding: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-tab-btn {
    height: 38px;
    padding: 0 16px;
    border-radius: 10px;
    border: var(--stroke-w) solid transparent;
    background: transparent;
    color: var(--text-muted);
    font-size: 13px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.section-tab-btn:hover {
    color: var(--text);
    background: var(--stroke-subtle);
}
.section-tab-btn.active {
    background: var(--bg-sub);
    border-color: var(--stroke);
    color: var(--text);
}
.tab-badge-pill {
    padding: 2px 8px;
    border-radius: 10px;
    background: var(--stroke-subtle);
    font-size: 11px;
    color: var(--text-muted);
}
.section-tab-btn.active .tab-badge-pill {
    background: rgba(var(--orange-rgb), 0.15);
    color: var(--orange);
}
</style>
