<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import type { BadgeVariant } from '@/Utils/badgeVariants';
import type { EquipmentItem } from '@/Composables/useEquipmentFilters';

const props = defineProps<{
    show: boolean;
    item: EquipmentItem | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const badgeVariant = computed<BadgeVariant>(() => {
    if (!props.item) return 'neutral';
    switch (props.item.status) {
        case 'En Uso': return 'info';
        case 'Disponible': return 'success';
        case 'Reparación': return 'warning';
        default: return 'danger';
    }
});
</script>

<template>
    <BaseModal
        :is-open="show && !!item"
        :title="item ? `Ficha de Equipo #${item.id}` : 'Ficha de Equipo'"
        max-width="md"
        @close="emit('close')"
    >
        <div v-if="item" class="detail-body">
            <div>
                <span class="detail-label">Equipo</span>
                <h2 class="detail-name">{{ item.name }}</h2>
            </div>

            <div class="form-row-2">
                <div>
                    <span class="detail-label">Tipo</span>
                    <div class="detail-val">{{ item.type }}</div>
                </div>
                <div>
                    <span class="detail-label">Estado</span>
                    <div>
                        <BaseBadge :variant="badgeVariant" size="sm">
                            {{ item.status }}
                        </BaseBadge>
                    </div>
                </div>
            </div>

            <div>
                <span class="detail-label">Ubicación Física</span>
                <div class="detail-val">{{ item.dept }}</div>
            </div>

            <div v-if="item.assignedTo" class="form-row-2">
                <div>
                    <span class="detail-label">Usuario Asignado</span>
                    <div class="detail-val highlight">{{ item.assignedTo }}</div>
                </div>
                <div v-if="item.ipAddress">
                    <span class="detail-label">Dirección IP</span>
                    <div class="detail-val code-font">{{ item.ipAddress }}</div>
                </div>
            </div>

            <div v-if="item.serialNumber">
                <span class="detail-label">Número de Serie</span>
                <div class="detail-val code-font">{{ item.serialNumber }}</div>
            </div>

            <div class="form-actions-row">
                <button class="btn-cancel" @click="emit('close')" type="button">Cerrar</button>
                <Link :href="`/equipos/${item.numericId}`" class="btn-submit">
                    Ver Ficha Completa &rarr;
                </Link>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.detail-body {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.detail-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.detail-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.detail-val {
    font-size: 14px;
    color: var(--text);
    background: var(--bg-sub);
    padding: 8px 12px;
    border-radius: 8px;
    border: var(--stroke-w) solid var(--stroke);
}

.detail-val.highlight {
    color: var(--brand);
    font-weight: 600;
}

.detail-val.code-font {
    font-family: var(--font-mono, monospace);
}
</style>
