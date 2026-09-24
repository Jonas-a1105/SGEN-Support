<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/UI/BaseModal.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import type { TicketDetail, TicketAsset, SupportFormOptions } from '@/types/support';

interface Props {
    isOpen: boolean;
    ticket: TicketDetail;
    asset: TicketAsset;
    technicians: SupportFormOptions['technicians'];
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'close'): void }>();

const searchTech = ref('');
const selectedTechId = ref<number>(props.ticket.tech_id);
const isSubmitting = ref(false);

const filteredTechnicians = computed(() => {
    if (!props.technicians) return [];
    if (!searchTech.value.trim()) return props.technicians;
    const q = searchTech.value.toLowerCase().trim();
    return props.technicians.filter(
        (t) => t.name.toLowerCase().includes(q) || t.specialty.toLowerCase().includes(q)
    );
});

const handleConfirm = () => {
    if (!selectedTechId.value || isSubmitting.value) return;

    isSubmitting.value = true;
    router.put(
        `/soportes/${props.ticket.id}/asignar-tecnico`,
        {
            empleado_id: selectedTechId.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                emit('close');
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
};
</script>

<template>
    <BaseModal :is-open="isOpen" title="Asignar Técnico" max-width="xl" @close="emit('close')">
        <div class="modal-assign-grid">
            <!-- DETALLES DEL SOPORTE (COLUMNA IZQUIERDA) -->
            <div class="assign-left-col">
                <span class="data-kicker">Detalles del Soporte</span>

                <div class="assign-item-sub">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <div>
                        <span class="sub-label">Equipo</span>
                        <strong class="sub-val">{{ asset.type }} {{ asset.serial }}</strong>
                    </div>
                </div>

                <div class="assign-item-sub">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <div>
                        <span class="sub-label">Ubicación</span>
                        <strong class="sub-val">{{ asset.department }}</strong>
                    </div>
                </div>

                <div class="assign-item-sub">
                    <svg viewBox="0 0 24 24">
                        <path d="m4 6 7-1 7 7-6 6-7-7z"></path>
                        <circle cx="8.5" cy="8.5" r="1" fill="currentColor"></circle>
                    </svg>
                    <div>
                        <span class="sub-label">Categoría</span>
                        <strong class="sub-val">{{ ticket.category }}</strong>
                    </div>
                </div>

                <div class="priority-row">
                    <span class="sub-label">Prioridad</span>
                    <span class="priority-pill">{{ ticket.priority.toUpperCase() }}</span>
                </div>

                <div class="assign-item-sub">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 14 14"></polyline>
                    </svg>
                    <div>
                        <span class="sub-label">Tiempo Transcurrido</span>
                        <strong class="sub-val">{{ ticket.attention_time || 'Reciente' }}</strong>
                    </div>
                </div>
            </div>

            <!-- SELECTOR DE TÉCNICO CON BUSCADOR (COLUMNA DERECHA) -->
            <div class="assign-right-col">
                <div class="tech-search-box">
                    <input
                        v-model="searchTech"
                        type="text"
                        class="search-tech-input"
                        placeholder="Buscar técnico por nombre o especialidad..."
                    />
                    <svg viewBox="0 0 24 24" class="search-svg">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <div class="tech-select-list">
                    <div
                        v-for="tech in filteredTechnicians"
                        :key="tech.id"
                        :class="['tech-select-option', { selected: selectedTechId === tech.id }]"
                        @click="selectedTechId = tech.id"
                    >
                        <div class="tech-opt-left">
                            <div class="avatar-user">{{ tech.initial }}</div>
                            <div class="tech-meta">
                                <strong class="tech-name">{{ tech.name }}</strong>
                                <span class="tech-sub">{{ tech.specialty }} • {{ tech.active_tickets }} tickets</span>
                            </div>
                        </div>
                        <span v-if="tech.active_tickets === 0" class="tech-status-available">
                            ● DISPONIBLE
                        </span>
                        <span v-else class="tech-status-busy">
                            OCUPADO
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-actions-bar">
            <BaseButton variant="secondary" type="button" @click="emit('close')">
                Cancelar
            </BaseButton>
            <BaseButton
                variant="primary"
                type="button"
                :disabled="isSubmitting || !selectedTechId"
                @click="handleConfirm"
            >
                {{ isSubmitting ? 'Guardando...' : 'Confirmar Asignación' }}
            </BaseButton>
        </div>
    </BaseModal>
</template>

<style scoped>
.modal-assign-grid {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 18px;
}
.assign-left-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-right: 14px;
    border-right: var(--stroke-w) solid var(--stroke-subtle);
}
.data-kicker {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    font-weight: 700;
}
.assign-item-sub {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 12px;
}
.assign-item-sub svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    color: var(--text-muted);
    flex-shrink: 0;
    margin-top: 2px;
}
.sub-label {
    font-size: 11px;
    color: var(--text-muted);
    display: block;
}
.sub-val {
    font-size: 13px;
    color: var(--text);
}
.priority-row {
    margin-top: 6px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
    padding-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.priority-pill {
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(37, 99, 235, 0.15);
    color: var(--color-blue, #2563eb);
    font-size: 11px;
    font-weight: 700;
}
.assign-right-col {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tech-search-box {
    position: relative;
}
.search-tech-input {
    width: 100%;
    height: 38px;
    padding: 8px 12px 8px 34px;
    border-radius: 10px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}
.search-tech-input:focus {
    border-color: var(--primary);
}
.search-svg {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    width: 15px;
    height: 15px;
    stroke: var(--text-muted);
    fill: none;
    stroke-width: 2;
    pointer-events: none;
}
.tech-select-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 240px;
    overflow-y: auto;
}
.tech-select-option {
    padding: 10px 12px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.18s ease;
}
.tech-select-option:hover,
.tech-select-option.selected {
    border-color: var(--primary);
    background: rgba(79, 70, 229, 0.12);
}
.tech-opt-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.avatar-user {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 13px;
    font-weight: 700 !important;
    flex-shrink: 0;
}
.tech-meta {
    display: flex;
    flex-direction: column;
}
.tech-name {
    font-size: 13px;
    color: var(--text);
}
.tech-sub {
    font-size: 11px;
    color: var(--text-muted);
}
.tech-status-available {
    font-size: 11px;
    color: var(--color-green, #16a34a);
    font-weight: 700;
}
.tech-status-busy {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
}
.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
@media (max-width: 640px) {
    .modal-assign-grid {
        grid-template-columns: 1fr;
    }
    .assign-left-col {
        border-right: none;
        border-bottom: var(--stroke-w) solid var(--stroke-subtle);
        padding-bottom: 12px;
        padding-right: 0;
    }
}
</style>
