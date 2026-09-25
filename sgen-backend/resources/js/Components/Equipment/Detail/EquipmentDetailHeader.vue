<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseButton } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    equipment: EquipmentDetail;
}>();

const emit = defineEmits<{
    (e: 'edit'): void;
}>();

const getStatusBadgeVariant = (rawStatus: string): 'success' | 'info' | 'warning' | 'danger' | 'neutral' => {
    switch (rawStatus.toLowerCase()) {
        case 'disponible':
        case 'nuevo':
        case 'en_uso':
            return 'success';
        case 'en_reparacion':
        case 'usado':
        case 'en_reserva':
            return 'warning';
        case 'fuera_de_servicio':
            return 'danger';
        default:
            return 'neutral';
    }
};

const handleDownloadActa = () => {
    window.open(`/equipos/${props.equipment.id}/acta-pdf`, '_blank');
};
</script>

<template>
    <header class="equip-page-header">
        <div class="equip-header-left">
            <Link href="/equipos" class="equip-back-link">
                <svg class="header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver a Equipos</span>
            </Link>

            <div class="equip-title-row">
                <div class="equip-icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="equip-title-text-group">
                    <div class="equip-headline-tags">
                        <h1 class="equip-main-title">{{ equipment.name }}</h1>
                        <BaseBadge variant="code" size="md">{{ equipment.inventoryCode }}</BaseBadge>
                        <BaseBadge :variant="getStatusBadgeVariant(equipment.rawStatus)" size="md" dot>
                            {{ equipment.status }}
                        </BaseBadge>
                    </div>
                    <div class="equip-meta-info">
                        <span class="equip-type-pill">{{ equipment.type }}</span>
                        <span v-if="equipment.serialNumber" class="equip-serial-pill">
                            S/N: {{ equipment.serialNumber }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="equip-header-actions">
            <BaseButton variant="subtle" size="md" @click="handleDownloadActa">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Acta de Custodia (PDF)</span>
            </BaseButton>

            <BaseButton variant="subtle" size="md" @click="emit('edit')">
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Editar Equipo</span>
            </BaseButton>
            <BaseButton
                variant="primary"
                size="md"
                :href="`/soportes/crear?equipo_id=${equipment.id}`"
            >
                <svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Crear Ticket</span>
            </BaseButton>
        </div>
    </header>
</template>

<style scoped>
.equip-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.equip-header-left {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.equip-back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.equip-back-link:hover {
    color: var(--brand);
}

.header-icon-svg {
    width: 16px;
    height: 16px;
}

.equip-title-row {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.equip-icon-box {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-md);
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    flex-shrink: 0;
}

.equip-icon-box svg {
    width: 26px;
    height: 26px;
}

.equip-title-text-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.equip-headline-tags {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.equip-main-title {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text);
    margin: 0;
}

.equip-meta-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: 13px;
    color: var(--text-muted);
}

.equip-type-pill {
    font-weight: 600;
    color: var(--brand);
}

.equip-serial-pill {
    font-family: var(--font-mono);
    color: var(--text-dim);
}

.equip-header-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.btn-icon-svg {
    width: 16px;
    height: 16px;
}
</style>
