<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';
import type { InventoryHealthData } from '@/types/DashboardMetrics';

defineProps<{
    health: InventoryHealthData;
}>();

const emit = defineEmits<{
    (e: 'filter-inventory', status: string): void;
}>();
</script>

<template>
    <BaseCard class="inventory-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Salud del Inventario</h2>
        </div>
        <div class="inventory-grid">
            <div class="inv-item used" @click="emit('filter-inventory', 'en_uso')">
                <div class="inv-value">{{ health.used }}</div>
                <div class="inv-label">En uso</div>
            </div>
            <div class="inv-item repair" @click="emit('filter-inventory', 'en_reparacion')">
                <div class="inv-value">{{ health.repair }}</div>
                <div class="inv-label">Reparación</div>
            </div>
            <div class="inv-item available" @click="emit('filter-inventory', 'disponible')">
                <div class="inv-value">{{ health.available }}</div>
                <div class="inv-label">Disponible</div>
            </div>
            <div class="inv-item downstate" @click="emit('filter-inventory', 'fuera_de_servicio')">
                <div class="inv-value">{{ health.down }}</div>
                <div class="inv-label">Baja</div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.inventory-card {
    display: flex;
    flex-direction: column;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 18px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.inventory-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 16px 18px 18px;
}

.inv-item {
    border: none;
    border-left: 4px solid var(--stroke);
    border-radius: 4px 18px 18px 4px;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--stroke-subtle);
    cursor: pointer;
    transition: opacity var(--transition-normal), transform var(--transition-normal);
}

.inv-item:hover {
    transform: translateY(-2px);
    opacity: 0.94;
}

.inv-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
}

.inv-label {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
}

.inv-item.used {
    border-left-color: var(--blue);
    background: rgba(37, 99, 235, 0.08);
}

.inv-item.repair {
    border-left-color: var(--yellow);
    background: rgba(217, 119, 6, 0.08);
}

.inv-item.available {
    border-left-color: var(--green);
    background: rgba(22, 163, 74, 0.08);
}

.inv-item.downstate {
    border-left-color: var(--red);
    background: rgba(220, 38, 38, 0.08);
}

@media (max-width: 480px) {
    .inventory-grid {
        grid-template-columns: 1fr;
    }
}
</style>
