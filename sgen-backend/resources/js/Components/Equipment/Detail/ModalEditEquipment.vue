<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseModal, BaseButton, BaseInput } from '@/Components/UI';
import type { EquipmentDetail } from './types';

const props = defineProps<{
    isOpen: boolean;
    equipment: EquipmentDetail;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const editForm = ref({
    marca: props.equipment.brand ?? '',
    modelo: props.equipment.model ?? '',
    tipo: props.equipment.type,
    estado: props.equipment.rawStatus,
    numero_serie: props.equipment.serialNumber,
    procesador: props.equipment.processor ?? '',
    memoria_ram: props.equipment.ram ?? '',
    almacenamiento: props.equipment.storage ?? '',
    sistema_operativo: props.equipment.os ?? '',
    direccion_ip: props.equipment.ipAddress ?? '',
    proveedor: props.equipment.supplier ?? '',
    valor_compra: props.equipment.purchaseValue ?? '',
    fecha_compra: props.equipment.purchaseDate ?? '',
    garantia: props.equipment.warranty ?? '',
});

const isSubmitting = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            editForm.value = {
                marca: props.equipment.brand ?? '',
                modelo: props.equipment.model ?? '',
                tipo: props.equipment.type,
                estado: props.equipment.rawStatus,
                numero_serie: props.equipment.serialNumber,
                procesador: props.equipment.processor ?? '',
                memoria_ram: props.equipment.ram ?? '',
                almacenamiento: props.equipment.storage ?? '',
                sistema_operativo: props.equipment.os ?? '',
                direccion_ip: props.equipment.ipAddress ?? '',
                proveedor: props.equipment.supplier ?? '',
                valor_compra: props.equipment.purchaseValue ?? '',
                fecha_compra: props.equipment.purchaseDate ?? '',
                garantia: props.equipment.warranty ?? '',
            };
        }
    }
);

const handleSaveEdit = () => {
    isSubmitting.value = true;
    router.put(
        `/equipos/${props.equipment.id}`,
        editForm.value,
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                emit('close');
            },
        }
    );
};
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        title="Editar Ficha del Equipo"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="handleSaveEdit" class="modal-form-stack">
            <div class="form-grid-2">
                <BaseInput v-model="editForm.marca" label="Marca" placeholder="Ej. Dell, HP, Lenovo" />
                <BaseInput v-model="editForm.modelo" label="Modelo" placeholder="Ej. Latitude 5420" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.tipo" label="Tipo de Dispositivo" placeholder="Computadora, Laptop..." required />
                <BaseInput v-model="editForm.numero_serie" label="Número de Serie" placeholder="S/N" />
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Estado Operativo</label>
                    <select v-model="editForm.estado" class="form-control-select">
                        <option value="disponible">Disponible</option>
                        <option value="en_uso">En Uso</option>
                        <option value="en_reparacion">En Reparación</option>
                        <option value="fuera_de_servicio">Fuera de Servicio</option>
                    </select>
                </div>
                <BaseInput v-model="editForm.direccion_ip" label="Dirección IP" placeholder="192.168.1.X" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.procesador" label="Procesador (CPU)" placeholder="Intel Core i5..." />
                <BaseInput v-model="editForm.memoria_ram" label="Memoria RAM" placeholder="16 GB DDR4" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.almacenamiento" label="Almacenamiento" placeholder="512 GB NVMe SSD" />
                <BaseInput v-model="editForm.sistema_operativo" label="Sistema Operativo" placeholder="Windows 11 Pro" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.proveedor" label="Proveedor" placeholder="Nombre del proveedor" />
                <BaseInput v-model="editForm.valor_compra" type="number" step="0.01" label="Valor de Compra ($)" placeholder="0.00" />
            </div>

            <div class="form-grid-2">
                <BaseInput v-model="editForm.fecha_compra" type="date" label="Fecha de Compra" />
                <BaseInput v-model="editForm.garantia" type="date" label="Vencimiento de Garantía" />
            </div>

            <div class="modal-actions-bar">
                <BaseButton type="button" variant="subtle" size="md" @click="emit('close')">
                    Cancelar
                </BaseButton>
                <BaseButton type="submit" variant="primary" size="md" :disabled="isSubmitting">
                    Guardar Cambios
                </BaseButton>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.modal-form-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-control-select {
    width: 100%;
    height: 42px;
    padding: 0 var(--space-3);
    border-radius: var(--radius-sm);
    border: 1px solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
}

.form-control-select:focus {
    border-color: var(--brand);
}

.modal-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stroke-subtle);
}

@media (max-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>
