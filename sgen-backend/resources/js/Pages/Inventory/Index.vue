<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import InventoryDashboard from '@/Components/Inventory/InventoryDashboard.vue';
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

defineProps<{
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
</script>

<template>
    <AppLayout title="Inventario General">
        <InventoryDashboard
            :products="products"
            :pagination="pagination"
            :kpis="kpis"
            :filters="filters"
            :equipos="equipos"
            :departamentos="departamentos"
            :empleados="empleados"
            :movimientos="movimientos"
            :ubicaciones="ubicaciones"
        />
    </AppLayout>
</template>
