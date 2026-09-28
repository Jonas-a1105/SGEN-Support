<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useIdempotencyKey } from '@/Composables/useIdempotencyKey';
import { BaseButton, BaseSignaturePad } from '@/Components/UI';
import { useToast } from '@/Composables/useToast';
import type { EquipmentCustody } from '@/Types/equipment';

/**
 * Cadena custodial patrimonial con firma probatoria: historial de eslabones
 * (cierres y aperturas) y la captura de conformidad del custodio vigente.
 */
const props = defineProps<{
    equipmentId: number;
    custodiaActual: EquipmentCustody | null;
    custodias: EquipmentCustody[];
}>();

const { addToast } = useToast();
const { idempotencyKey } = useIdempotencyKey();
const showSignModal = ref(false);
const signatureData = ref('');

function saveSignature(): void {
    if (!signatureData.value) {
        return;
    }

    router.post(
        `/equipos/${props.equipmentId}/custodia/firmar`,
        { firma_base64: signatureData.value },
        {
            preserveScroll: true,
            headers: { 'Idempotency-Key': idempotencyKey.value },
            onSuccess: () => {
                showSignModal.value = false;
                signatureData.value = '';
            },
            onError: () =>
                addToast({ type: 'error', title: 'No se pudo registrar la firma de custodia.' }),
        }
    );
}
</script>

<template>
    <div class="custody-tab">
        <div class="custody-head">
            <h3 class="custody-title">Custodia patrimonial</h3>
            <div v-if="custodiaActual" class="custody-actual">
                <div class="actual-data">
                    <strong>{{ custodiaActual.custodio }}</strong>
                    <span class="actual-meta">
                        desde {{ custodiaActual.fecha_inicio }} · asignado por {{ custodiaActual.asignado_por }}
                    </span>
                    <span v-if="custodiaActual.firmada" class="pill firmada">
                        Firmada ✓ {{ custodiaActual.firmado_en }}
                        <small v-if="custodiaActual.firma_hash" class="hash-chip" :title="custodiaActual.firma_hash">
                            SHA-256: {{ custodiaActual.firma_hash.substring(0, 12) }}… · IP {{ custodiaActual.firma_ip ?? 'N/D' }}
                        </small>
                    </span>
                    <span v-else class="pill pendiente">Pendiente de firma</span>
                </div>
                <BaseButton
                    v-if="!custodiaActual.firmada"
                    variant="primary"
                    @click="showSignModal = true"
                >
                    Firmar conformidad
                </BaseButton>
            </div>
            <p v-else class="custody-empty">
                Sin custodia vigente: reasigne el equipo para abrir un nuevo eslabón.
            </p>
        </div>

        <table class="custody-history">
            <thead>
                <tr>
                    <th>Custodio</th>
                    <th>Motivo</th>
                    <th>Inicio</th>
                    <th>Cierre</th>
                    <th>Firma</th>
                    <th>Asignado por</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="c in custodias" :key="c.id" :class="{ 'row-vigente': c.vigente }">
                    <td>{{ c.custodio }}</td>
                    <td class="mono">{{ c.motivo }}</td>
                    <td>{{ c.fecha_inicio ?? '—' }}</td>
                    <td>{{ c.fecha_fin ?? 'Vigente' }}</td>
                    <td>
                        <span v-if="c.firmada" class="pill firmada-pill">✓ {{ c.firmado_en }}</span>
                        <span v-else class="pill sinfirma">—</span>
                    </td>
                    <td>{{ c.asignado_por }}</td>
                </tr>
                <tr v-if="custodias.length === 0">
                    <td colspan="6" class="empty-cell">Sin historial de custodia todavía.</td>
                </tr>
            </tbody>
        </table>

        <!-- Modal firma -->
        <div v-if="showSignModal" class="modal-backdrop" @click.self="showSignModal = false">
            <div class="modal-card">
                <h4>Firma de conformidad del custodio</h4>
                <p class="modal-note">
                    La firma se archiva con hash SHA-256, IP y fecha exacta (valor probatorio).
                </p>
                <BaseSignaturePad @update="signatureData = $event" />
                <div class="modal-actions">
                    <BaseButton variant="secondary" @click="showSignModal = false">Cancelar</BaseButton>
                    <BaseButton variant="primary" :disabled="!signatureData" @click="saveSignature">
                        Registrar firma
                    </BaseButton>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custody-tab {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.custody-head {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.custody-title {
    margin: 0;
    font-size: 16px;
    color: var(--text, #f4f4f6);
}
.custody-actual {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px 16px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--card-radius, 14px);
}
.actual-data {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.actual-meta {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.custody-empty {
    color: var(--text-muted, #8e9199);
    font-size: 13px;
}
.pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    white-space: normal;
}
.pill.firmada,
.pill.firmada-pill {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: var(--stroke-w, 2px) solid rgba(16, 185, 129, 0.3);
}
.pill.pendiente {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border: var(--stroke-w, 2px) solid rgba(245, 158, 11, 0.3);
}
.pill.sinfirma {
    color: var(--text-dim, #60636d);
}
.hash-chip {
    font-family: var(--font-mono);
    font-size: 10px;
    opacity: 0.85;
}
.custody-history {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.custody-history th {
    text-align: left;
    padding: 8px 10px;
    font-size: 11px;
    letter-spacing: 0.05em;
    color: var(--text-muted, #8e9199);
    border-bottom: var(--stroke-w, 2px) solid var(--stroke, #31343a);
}
.custody-history td {
    padding: 9px 10px;
    border-bottom: 1px solid var(--stroke-subtle, #23252a);
    color: var(--text, #f4f4f6);
}
tr.row-vigente td {
    background: rgba(59, 130, 246, 0.06);
}
.mono {
    font-family: var(--font-mono);
    font-size: 12px;
}
.empty-cell {
    text-align: center;
    color: var(--text-muted, #8e9199);
    padding: 20px !important;
}
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: grid;
    place-items: center;
    z-index: 90;
    padding: 16px;
}
.modal-card {
    width: 100%;
    max-width: 520px;
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--radius-lg, 14px);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.modal-card h4 {
    margin: 0;
    font-size: 15px;
    color: var(--text, #f4f4f6);
}
.modal-note {
    margin: 0;
    font-size: 12px;
    color: var(--text-muted, #8e9199);
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
