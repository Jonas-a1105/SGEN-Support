<script setup lang="ts">
import { computed } from 'vue';
import { BaseCard } from '@/Components/UI';

const props = defineProps<{
    tecnicoNombre: string | null;
    realizadoPor: string | null;
}>();

const technicianInitials = computed(() => {
    const name = props.tecnicoNombre || 'Tecnico';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
});
</script>

<template>
    <BaseCard class="maint-sidebar-card" padding="lg">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Técnico Responsable
        </h3>

        <div class="maint-tech-profile">
            <div class="tech-avatar-circle">
                {{ technicianInitials }}
            </div>
            <div class="tech-info-block">
                <strong class="tech-name">{{ tecnicoNombre || 'Sin asignar' }}</strong>
                <span class="tech-role-desc">Técnico de Soporte Asignado</span>
            </div>
        </div>

        <div v-if="realizadoPor" class="tech-third-party-row">
            <span class="field-label">Realizado por (Externo/Tercero):</span>
            <strong class="third-party-val">{{ realizadoPor }}</strong>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-sidebar-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.sidebar-card-title svg {
    width: 16px;
    height: 16px;
    color: var(--blue, #3b82f6);
}

.maint-tech-profile {
    display: flex;
    align-items: center;
    gap: 14px;
}

.tech-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--blue, #3b82f6);
    color: #ffffff;
    font-weight: 800;
    font-size: 15px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.tech-info-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.tech-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.tech-role-desc {
    font-size: 12px;
    color: var(--text-muted);
}

.tech-third-party-row {
    padding-top: 12px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
}

.field-label {
    color: var(--text-muted);
}

.third-party-val {
    color: var(--text);
    font-size: 13px;
}
</style>
