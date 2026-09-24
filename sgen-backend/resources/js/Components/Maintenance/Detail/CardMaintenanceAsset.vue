<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BaseBadge, BaseCard } from '@/Components/UI';
import type { EquipmentInfo } from './types';

defineProps<{
    equipo: EquipmentInfo | null;
}>();
</script>

<template>
    <BaseCard class="maint-section-card" padding="lg">
        <div class="maint-card-header">
            <div class="maint-card-header-left">
                <div class="maint-card-icon-wrap blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div>
                    <h2 class="maint-card-title">Información del Activo Intervenido</h2>
                    <p class="maint-card-sub">Detalles técnicos del equipo y su asignación actual</p>
                </div>
            </div>
            <Link
                v-if="equipo?.id"
                :href="`/equipos/${equipo.id}`"
                class="maint-link-btn"
            >
                <span>Ver Ficha de Equipo</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </Link>
        </div>

        <div v-if="equipo" class="maint-asset-banner">
            <div class="asset-banner-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                    <line x1="8" y1="4" x2="8" y2="20"></line>
                    <line x1="16" y1="4" x2="16" y2="20"></line>
                    <line x1="4" y1="8" x2="20" y2="8"></line>
                    <line x1="4" y1="16" x2="20" y2="16"></line>
                </svg>
            </div>
            <div class="asset-banner-info">
                <div class="asset-banner-title-row">
                    <h3 class="asset-title-text">
                        {{ equipo.marca }} {{ equipo.modelo }}
                    </h3>
                    <BaseBadge variant="code" size="md">{{ equipo.codigo }}</BaseBadge>
                    <span class="asset-type-badge">{{ equipo.tipo }}</span>
                </div>
                <div class="asset-banner-meta-row">
                    <span class="meta-item">
                        <strong>Serial:</strong> {{ equipo.serial }}
                    </span>
                    <span class="meta-divider">•</span>
                    <span class="meta-item">
                        <strong>Departamento:</strong>
                        <Link
                            v-if="equipo.departamentoId"
                            :href="`/departamentos/${equipo.departamentoId}`"
                            class="meta-link"
                        >
                            {{ equipo.departamento }}
                        </Link>
                        <span v-else>{{ equipo.departamento }}</span>
                    </span>
                    <span class="meta-divider">•</span>
                    <span class="meta-item">
                        <strong>Custodio:</strong>
                        <Link
                            v-if="equipo.custodioId"
                            :href="`/personal/${equipo.custodioId}`"
                            class="meta-link"
                        >
                            {{ equipo.custodio }}
                        </Link>
                        <span v-else>{{ equipo.custodio }}</span>
                    </span>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

<style scoped>
.maint-section-card {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius, 12px);
    box-shadow: none !important;
}

.maint-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.maint-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.maint-card-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.maint-card-icon-wrap.blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.maint-card-icon-wrap svg {
    width: 20px;
    height: 20px;
}

.maint-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.maint-card-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.maint-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--blue, #3b82f6);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(59, 130, 246, 0.08);
    transition: background 0.2s ease;
}

.maint-link-btn svg {
    width: 13px;
    height: 13px;
}

.maint-link-btn:hover {
    background: rgba(59, 130, 246, 0.16);
}

/* Asset Banner */
.maint-asset-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
}

.asset-banner-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--blue, #3b82f6);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.asset-banner-icon svg {
    width: 24px;
    height: 24px;
}

.asset-banner-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.asset-banner-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.asset-title-text {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.asset-type-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    color: var(--text-muted);
    text-transform: capitalize;
}

.asset-banner-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.meta-divider {
    color: var(--stroke);
}

.meta-link {
    color: var(--blue, #3b82f6);
    text-decoration: none;
    font-weight: 600;
}

.meta-link:hover {
    text-decoration: underline;
}
</style>
