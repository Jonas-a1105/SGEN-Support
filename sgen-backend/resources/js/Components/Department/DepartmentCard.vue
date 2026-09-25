<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import BaseDropdown from '@/Components/UI/BaseDropdown.vue';
import type { DepartmentItem } from '@/Composables/useDepartmentFilters';

const props = defineProps<{
    department: DepartmentItem;
    isDense?: boolean;
}>();

const emit = defineEmits<{
    (e: 'edit', department: DepartmentItem): void;
    (e: 'delete', department: DepartmentItem): void;
}>();

const navigateToDetail = () => {
    router.visit(`/departamentos/${props.department.numericId}`);
};

// Deterministic color class
const colorClass = computed(() => {
    const c = props.department.color;
    if (c === '#f97316') return 'accent-orange';
    if (c === '#ec4899') return 'accent-pink';
    if (c === '#10b981') return 'accent-emerald';
    if (c === '#06b6d4') return 'accent-cyan';
    if (c === '#8b5cf6') return 'accent-purple';
    if (c === '#f59e0b') return 'accent-amber';
    if (c === '#f43f5e') return 'accent-rose';
    return 'accent-indigo';
});

// Stepped progress width class (0 to 100 in steps of 5)
const progressWidthClass = computed(() => {
    const pct = Math.min(100, Math.max(0, props.department.inventoryPercent));
    const rounded = Math.round(pct / 5) * 5;
    return `pct-${rounded}`;
});
</script>

<template>
    <article
        class="department-card"
        :class="[colorClass, { dense: isDense }]"
        role="button"
        tabindex="0"
        @click="navigateToDetail"
        @keydown.enter="navigateToDetail"
    >
        <div class="card-top-accent-bar"></div>

        <div class="card-content-stack">
            <div class="card-top-meta-row">
                <div class="dept-icon-square" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="2"></line>
                        <line x1="15" y1="22" x2="15" y2="2"></line>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <line x1="8" y1="14" x2="16" y2="14"></line>
                    </svg>
                </div>

                <div class="dropdown-anchor" @click.stop>
                    <BaseDropdown align="right">
                        <template #trigger="{ toggle }">
                            <button
                                class="btn-card-menu"
                                @click.stop="toggle"
                                type="button"
                                title="Opciones del departamento"
                            >
                                ···
                            </button>
                        </template>
                        <template #content="{ close }">
                            <Link :href="`/departamentos/${department.numericId}`" class="card-menu-item" @click.stop="close">
                                <svg viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>Ver Detalle</span>
                            </Link>
                            <button class="card-menu-item" @click.stop="() => { close(); emit('edit', department); }" type="button">
                                <svg viewBox="0 0 24 24">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Editar</span>
                            </button>
                            <button class="card-menu-item delete" @click.stop="() => { close(); emit('delete', department); }" type="button">
                                <svg viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                <span>Eliminar</span>
                            </button>
                        </template>
                    </BaseDropdown>
                </div>
            </div>

            <div class="card-title-group">
                <Link :href="`/departamentos/${department.numericId}`" class="dept-card-title-link" @click.stop>
                    <h2 class="dept-card-title">{{ department.name }}</h2>
                </Link>
                <span class="dept-card-code">{{ department.code }}</span>
            </div>

            <p class="dept-card-desc">{{ department.desc }}</p>

            <div class="card-metrics-row">
                <div class="metric-badge-box">
                    <div class="metric-badge-head">
                        <svg viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>EQUIPOS</span>
                    </div>
                    <div class="metric-badge-value">{{ department.equipos }}</div>
                </div>

                <div class="metric-badge-box">
                    <div class="metric-badge-head">
                        <svg viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                        </svg>
                        <span>EMPLEADOS</span>
                    </div>
                    <div class="metric-badge-value">{{ department.empleados }}</div>
                </div>
            </div>
        </div>

        <div class="card-progress-footer">
            <div class="progress-legend-row">
                <span>Inventario Asignado</span>
                <span class="progress-legend-percent">{{ department.inventoryPercent }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" :class="progressWidthClass"></div>
            </div>
        </div>
    </article>
</template>

<style scoped>
.department-card {
    position: relative;
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--card-radius);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    transition: border-color 0.2s ease, transform 0.2s ease;
    box-shadow: none !important;
    cursor: pointer;
}

.department-card:hover {
    border-color: var(--stroke-hover);
    transform: translateY(-2px);
}

.department-card.dense {
    padding: 12px;
}

.card-top-accent-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.card-content-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-top-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dept-icon-square {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    border: var(--stroke-w) solid transparent;
}

.dept-icon-square svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.btn-card-menu {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: transparent;
    border: var(--stroke-w) solid transparent;
    color: var(--text-muted);
    font-size: 16px;
    font-weight: 700;
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}

.btn-card-menu:hover {
    background: var(--stroke-subtle);
    border-color: var(--stroke);
    color: var(--text);
}

.card-menu-item {
    background: transparent;
    border: none;
    color: var(--text);
    font-size: 12px;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: none !important;
    text-decoration: none;
    width: 100%;
    transition: background 0.15s ease;
}

.card-menu-item svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.card-menu-item:hover {
    background: var(--stroke-subtle);
}

.card-menu-item.delete {
    color: var(--red);
}

.card-menu-item.delete:hover {
    background: rgba(239, 68, 68, 0.1);
}

.card-title-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.dept-card-title-link {
    text-decoration: none;
    color: inherit;
    display: block;
    overflow: hidden;
}

.dept-card-title-link:hover .dept-card-title {
    color: var(--brand);
}

.dept-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dept-card-code {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 700;
}

.dept-card-desc {
    font-size: 12px;
    color: var(--text-dim);
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-metrics-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.metric-badge-box {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: 10px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.metric-badge-head {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-dim);
    letter-spacing: 0.04em;
}

.metric-badge-head svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.metric-badge-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
}

.card-progress-footer {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.progress-legend-row {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-muted);
}

.progress-legend-percent {
    font-weight: 700;
    color: var(--text);
}

.progress-track {
    width: 100%;
    height: 5px;
    background: var(--stroke-subtle);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 4px;
}

/* PALETA SEMÁNTICA POR CLASE */
.accent-indigo .card-top-accent-bar { background: #4f46e5; }
.accent-indigo .dept-icon-square { background: rgba(79, 70, 229, 0.12); color: #4f46e5; }
.accent-indigo .progress-fill { background: #4f46e5; }

.accent-orange .card-top-accent-bar { background: #f97316; }
.accent-orange .dept-icon-square { background: rgba(249, 115, 22, 0.12); color: #f97316; }
.accent-orange .progress-fill { background: #f97316; }

.accent-pink .card-top-accent-bar { background: #ec4899; }
.accent-pink .dept-icon-square { background: rgba(236, 72, 153, 0.12); color: #ec4899; }
.accent-pink .progress-fill { background: #ec4899; }

.accent-emerald .card-top-accent-bar { background: #10b981; }
.accent-emerald .dept-icon-square { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.accent-emerald .progress-fill { background: #10b981; }

.accent-cyan .card-top-accent-bar { background: #06b6d4; }
.accent-cyan .dept-icon-square { background: rgba(6, 182, 212, 0.12); color: #06b6d4; }
.accent-cyan .progress-fill { background: #06b6d4; }

.accent-purple .card-top-accent-bar { background: #8b5cf6; }
.accent-purple .dept-icon-square { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
.accent-purple .progress-fill { background: #8b5cf6; }

.accent-amber .card-top-accent-bar { background: #f59e0b; }
.accent-amber .dept-icon-square { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.accent-amber .progress-fill { background: #f59e0b; }

.accent-rose .card-top-accent-bar { background: #f43f5e; }
.accent-rose .dept-icon-square { background: rgba(244, 63, 94, 0.12); color: #f43f5e; }
.accent-rose .progress-fill { background: #f43f5e; }

/* ANCHOS DE PROGRESO */
.pct-0 { width: 0%; }
.pct-5 { width: 5%; }
.pct-10 { width: 10%; }
.pct-15 { width: 15%; }
.pct-20 { width: 20%; }
.pct-25 { width: 25%; }
.pct-30 { width: 30%; }
.pct-35 { width: 35%; }
.pct-40 { width: 40%; }
.pct-45 { width: 45%; }
.pct-50 { width: 50%; }
.pct-55 { width: 55%; }
.pct-60 { width: 60%; }
.pct-65 { width: 65%; }
.pct-70 { width: 70%; }
.pct-75 { width: 75%; }
.pct-80 { width: 80%; }
.pct-85 { width: 85%; }
.pct-90 { width: 90%; }
.pct-95 { width: 95%; }
.pct-100 { width: 100%; }
</style>
