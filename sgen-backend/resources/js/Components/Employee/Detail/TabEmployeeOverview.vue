<script setup lang="ts">
import type { EmployeeDetail } from './types';

defineProps<{
    employee: EmployeeDetail;
}>();
</script>

<template>
    <div class="tab-panel-overview">
        <div class="overview-section">
            <h4 class="overview-subheading">Resumen del Perfil</h4>
            <div class="overview-fields-grid">
                <div class="ov-field-card">
                    <span class="ov-label">Nombre Completo</span>
                    <strong class="ov-value">{{ employee.fullName }}</strong>
                </div>
                <div class="ov-field-card">
                    <span class="ov-label">Cédula de Identidad</span>
                    <strong class="ov-value mono">{{ employee.cedula || 'No registrada' }}</strong>
                </div>
                <div class="ov-field-card">
                    <span class="ov-label">Cargo Laboral</span>
                    <strong class="ov-value">{{ employee.cargo || 'Personal General' }}</strong>
                </div>
                <div class="ov-field-card">
                    <span class="ov-label">Departamento</span>
                    <strong class="ov-value">{{ employee.departamentoNombre || 'Sin departamento' }}</strong>
                </div>
                <div class="ov-field-card">
                    <span class="ov-label">Rol en la Plataforma</span>
                    <strong class="ov-value capitalize">{{ employee.rol }}</strong>
                </div>
                <div class="ov-field-card">
                    <span class="ov-label">Cuenta de Usuario</span>
                    <strong class="ov-value mono">{{ employee.username ? `@${employee.username}` : 'No asignada' }}</strong>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tab-panel-overview {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.overview-subheading {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 16px 0;
}

.overview-fields-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.ov-field-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px 16px;
    border-radius: 8px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
}

.ov-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.ov-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.ov-value.mono {
    font-family: monospace;
    color: var(--blue, #3b82f6);
}

.capitalize {
    text-transform: capitalize;
}
</style>
