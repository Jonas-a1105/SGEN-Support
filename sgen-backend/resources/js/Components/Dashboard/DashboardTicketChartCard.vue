<script setup lang="ts">
import { computed } from 'vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseCombobox, { type ComboboxOption } from '@/Components/UI/BaseCombobox.vue';
import TicketVolumeSvgChart from './TicketVolumeSvgChart.vue';
import type { TicketVolumeData } from '@/types/DashboardMetrics';

const props = defineProps<{
    data: TicketVolumeData;
}>();

const emit = defineEmits<{
    (e: 'change-year', year: number): void;
    (e: 'point-clicked', point: { month: string; val: number; year: number }): void;
}>();

const yearOptions = computed<ComboboxOption[]>(() =>
    (props.data.available_years || []).map((yr) => ({
        value: yr,
        label: String(yr),
    }))
);
</script>

<template>
    <BaseCard class="chart-card" padding="none">
        <div class="panel-head">
            <h2 class="panel-title">Volumen de Tickets Mensual</h2>
            <div class="select-wrap">
                <BaseCombobox
                    :model-value="data.year"
                    :options="yearOptions"
                    :searchable="false"
                    aria-label="Seleccionar año"
                    @update:model-value="(val) => { if (val != null) emit('change-year', Number(val)); }"
                />
            </div>
        </div>

        <div class="chart-area">
            <TicketVolumeSvgChart
                :data="data"
                @point-clicked="(pt) => emit('point-clicked', pt)"
            />
        </div>

        <div class="chart-footer">
            <span>Frecuencia mensual sincronizada</span>
            <span class="chart-total">Total anual: {{ data.total }}</span>
        </div>
    </BaseCard>
</template>

<style scoped>
.chart-card {
    min-height: 310px;
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

.select-wrap {
    min-width: 110px;
}

.chart-area {
    padding: 12px 18px 8px;
    position: relative;
    flex: 1;
}

.chart-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px 16px;
    font-size: 13px;
    color: var(--text-muted);
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.chart-total {
    color: var(--text);
    font-size: 13px;
    font-weight: 700;
}
</style>
