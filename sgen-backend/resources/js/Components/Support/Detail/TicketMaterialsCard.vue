<script setup lang="ts">
import type { ConsumedMaterial } from '@/types/support';

defineProps<{
    materials: ConsumedMaterial[];
}>();

const emit = defineEmits<{
    (e: 'open-add-material'): void;
}>();
</script>

<template>
    <section class="detail-panel">
        <div class="panel-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="6" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                    </svg>
                </div>
                <h2 class="panel-title">MATERIALES</h2>
            </div>
        </div>

        <div class="panel-body-pad">
            <!-- Si la lista está vacía -->
            <div v-if="materials.length === 0" class="materials-empty-state">
                <div class="materials-icon-circle" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="6" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                    </svg>
                </div>
                <span>No se han registrado consumos.</span>
                <button class="btn-add-material" type="button" @click="emit('open-add-material')">
                    <span>+</span> Registrar material o repuesto
                </button>
            </div>

            <!-- Si hay materiales -->
            <div v-else class="materials-list-box">
                <div v-for="mat in materials" :key="mat.id" class="material-item-row">
                    <div class="mat-info">
                        <strong class="mat-name">{{ mat.name }}</strong>
                        <span class="mat-code">{{ mat.code }} • {{ mat.date }}</span>
                    </div>
                    <div class="mat-qty-badge">
                        {{ mat.quantity }} ud
                    </div>
                </div>

                <button class="btn-add-material" type="button" @click="emit('open-add-material')">
                    <span>+</span> Registrar otro material
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.panel-header-row {
    padding: 18px 22px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.panel-title {
    margin: 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}
.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.panel-icon-pill svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.panel-body-pad {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.materials-empty-state {
    padding: 20px;
    background: var(--bg-sub);
    border: var(--stroke-w) dashed var(--stroke);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
    color: var(--text-muted);
    font-size: 12px;
}
.materials-icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bg-card);
    display: grid;
    place-items: center;
    color: var(--text-dim);
}
.materials-icon-circle svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-add-material {
    margin-top: 6px;
    background: transparent;
    border: var(--stroke-w) solid var(--stroke);
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: none !important;
    transition: all 0.2s ease;
}
.btn-add-material:hover {
    background: var(--bg-card);
    border-color: var(--primary);
}
.materials-list-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.material-item-row {
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mat-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.mat-name {
    font-size: 13px;
    color: var(--text);
}
.mat-code {
    font-size: 11px;
    color: var(--text-dim);
}
.mat-qty-badge {
    padding: 4px 10px;
    border-radius: 6px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
}
</style>
