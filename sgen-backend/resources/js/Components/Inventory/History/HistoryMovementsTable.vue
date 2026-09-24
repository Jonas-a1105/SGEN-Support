<script setup lang="ts">
import type { InventoryMovement } from '@/Types/inventory';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

defineProps<{
    movements: InventoryMovement[];
}>();

const getBadgeVariant = (type: string): 'success' | 'danger' | 'info' | 'neutral' => {
    switch (type.toUpperCase()) {
        case 'ENTRADA': return 'success';
        case 'SALIDA': return 'danger';
        case 'TRANSFERENCIA': return 'info';
        default: return 'neutral';
    }
};

const getQtyClass = (type: string) => {
    switch (type.toUpperCase()) {
        case 'ENTRADA': return 'qty-text-green';
        case 'SALIDA': return 'qty-text-red';
        default: return 'qty-text-blue';
    }
};
</script>

<template>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>TIPO</th>
                    <th>CANTIDAD</th>
                    <th>MOTIVO</th>
                    <th>USUARIO</th>
                </tr>
            </thead>
            <tbody id="tbodyHistory">
                <tr v-for="mov in movements" :key="mov.id">
                    <td>{{ mov.created_at ? new Date(mov.created_at).toLocaleString() : 'Reciente' }}</td>
                    <td>
                        <BaseBadge :variant="getBadgeVariant(mov.tipo)">
                            {{ mov.tipo }}
                        </BaseBadge>
                    </td>
                    <td>
                        <strong :class="getQtyClass(mov.tipo)">
                            {{ mov.tipo.toUpperCase() === 'ENTRADA' ? '+' : (mov.tipo.toUpperCase() === 'SALIDA' ? '-' : '') }}{{ mov.cantidad }} uds
                        </strong>
                    </td>
                    <td>{{ mov.motivo || 'Sin motivo especificado' }}</td>
                    <td><BaseBadge variant="code">{{ mov.usuario_nombre || 'Sistema' }}</BaseBadge></td>
                </tr>
                <tr v-if="movements.length === 0">
                    <td colspan="5" class="history-empty-cell">
                        No hay movimientos registrados para este artículo.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.qty-text-green {
    color: var(--green);
}

.qty-text-red {
    color: var(--red);
}

.qty-text-blue {
    color: var(--blue);
}

.history-empty-cell {
    text-align: center;
    color: var(--text-muted);
    padding: var(--space-6);
}
</style>
