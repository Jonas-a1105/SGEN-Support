<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseInput from '@/Components/UI/BaseInput.vue';
import { useToast } from '@/Composables/useToast';

interface ClaveGlobal {
    clave: string;
    valor: string;
    descripcion: string;
    actualizado_at: string;
    actualizado_por: string;
}

const props = defineProps<{ claves: ClaveGlobal[] }>();
const { addToast } = useToast();
const editando = ref<string | null>(null);
const valorEdicion = ref<string>('');

const editarClave = (clave: string, valor: string): void => {
    editando.value = clave;
    valorEdicion.value = valor;
};

const guardar = (): void => {
    if (editando.value === null) {
        return;
    }
    router.put('/configuracion/sistema', {
        clave: editando.value,
        valor: valorEdicion.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editando.value = null;
        },
    });
};

const cancelar = (): void => {
    editando.value = null;
};
</script>

<template>
    <AppLayout>
        <Head title="Configuración global" />

        <div class="config-global-page">
            <header class="config-head">
                <div>
                    <h1>Configuración global</h1>
                    <p class="subtitle">Parámetros vivos del sistema (seguridad, SLA, autocierre, notificaciones). Los cambios entran en vigor en la siguiente petición.</p>
                </div>
            </header>

            <BaseCard padding="lg">
                <table class="tabla-config">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Valor</th>
                            <th>Descripción</th>
                            <th>Última edición</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="clave in props.claves" :key="clave.clave">
                            <td class="clave-col mono">{{ clave.clave }}</td>
                            <td>
                                <template v-if="editando === clave.clave">
                                    <BaseInput v-model="valorEdicion" label="" placeholder="Nuevo valor" />
                                </template>
                                <template v-else>
                                    <span class="valor-captura">{{ clave.valor }}</span>
                                </template>
                            </td>
                            <td class="desc-col">{{ clave.descripcion }}</td>
                            <td class="actualizado-col">
                                {{ clave.actualizado_at }} <br>
                                <span class="by-col">por {{ clave.actualizado_por }}</span>
                            </td>
                            <td class="actions">
                                <template v-if="editando === clave.clave">
                                    <BaseButton size="sm" variant="primary" @click="guardar">Guardar</BaseButton>
                                    <BaseButton size="sm" variant="subtle" @click="cancelar">Cancelar</BaseButton>
                                </template>
                                <template v-else>
                                    <BaseButton size="sm" variant="outline" @click="editarClave(clave.clave, clave.valor)">
                                        Editar
                                    </BaseButton>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </BaseCard>
        </div>
    </AppLayout>
</template>

<style scoped>
.config-global-page { display: flex; flex-direction: column; gap: 20px; }
.config-head h1 { margin: 0; font-size: 22px; color: var(--text); }
.subtitle { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }
.tabla-config { width: 100%; border-collapse: collapse; font-size: 13px; }
.tabla-config th { text-align: left; font-size: 11px; letter-spacing: 0.04em; color: var(--text-muted); padding: 10px 12px; border-bottom: var(--stroke-w, 2px) solid var(--stroke, #31343a); }
.tabla-config td { padding: 10px 12px; border-bottom: 1px solid var(--stroke-subtle, #23252a); color: var(--text); vertical-align: top; }
.tabla-config td.clave-col { font-size: 12px; font-weight: 600; }
.tabla-config td.mono { font-family: monospace; }
.tabla-config td.desc-col { max-width: 260px; color: var(--text-muted); font-size: 12px; }
.tabla-config td.actualizado-col { font-size: 11px; color: var(--text-muted); }
.by-col { font-size: 10px; color: var(--text-dim); }
.tabla-config td.actions { display: flex; gap: 8px; justify-content: flex-end; }
.valor-captura { font-weight: 600; color: var(--color-accent, #10b981); }
</style>
