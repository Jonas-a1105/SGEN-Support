<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';
import { useToast } from '@/Composables/useToast';

interface RegistroTrashto {
    id: number;
    nombre: string;
    extra: string;
    deleted_at: string;
}

interface CatalogoTrasht {
    tipo: string;
    etiqueta: string;
    registros: RegistroTrashto[];
}

const props = defineProps<{ catalogos_activos: CatalogoTrasht[] }>();
const { addToast } = useToast();
const isProcessing = ref<number | null>(null);

const restore = (catalogo: CatalogoTrasht, id: number): void => {
    isProcessing.value = id;
    router.post(
        `/papelera/${catalogo.tipo}/${id}/restaurar`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isProcessing.value = null;
            },
        }
    );
};

const purge = (catalogo: CatalogoTrasht, id: number): void => {
    if (!confirm(`Purgar definitivamente ${catalogo.etiqueta} #${id}? Esta acción no se deshace.`)) {
        return;
    }

    isProcessing.value = id;
    router.delete(
        `/papelera/${catalogo.tipo}/${id}`,
        {
            preserveScroll: true,
            onFinish: () => {
                isProcessing.value = null;
            },
        }
    );
};

const totalRegistros = () => props.catalogos_activos.reduce((acc, catalogo) => acc + catalogo.registros.length, 0);
</script>

<template>
    <AppLayout>
        <Head title="Papelera · Restaurar o purgar" />

        <div class="papelera-page">
            <header class="papelera-header">
                <div>
                    <h1>Papelera universal</h1>
                    <p class="subtitle">Restaura lo retirado por error o purgado definitivo (solo administración).</p>
                </div>
                <BaseBadge variant="neutral" size="md">{{ totalRegistros() }} registros en papelera</BaseBadge>
            </header>

            <article
                v-for="catalogo in props.catalogos_activos"
                :key="catalogo.tipo"
                class="seccion-catalogo"
            >
                <BaseCard padding="lg">
                    <div class="seccion-head">
                        <h2>{{ catalogo.etiqueta }}</h2>
                        <span class="conteo">{{ catalogo.registros.length }} pendiente(s)</span>
                    </div>

                    <table class="tabla-papelera">
                        <thead>
                            <tr>
                                <th>Registro</th>
                                <th>Detalle</th>
                                <th>Retirado</th>
                                <th class="actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in catalogo.registros" :key="r.id">
                                <td class="mono">#{{ r.id }} · {{ r.nombre || 'registro' }}</td>
                                <td>{{ r.extra || '—' }}</td>
                                <td>{{ r.deleted_at }}</td>
                                <td class="actions">
                                    <BaseButton
                                        variant="outline"
                                        size="sm"
                                        :loading="isProcessing === r.id"
                                        @click="restore(catalogo, r.id)"
                                    >
                                        Restaurar
                                    </BaseButton>
                                    <BaseButton
                                        variant="danger"
                                        size="sm"
                                        :loading="isProcessing === r.id"
                                        @click="purge(catalogo, r.id)"
                                    >
                                        Purgar
                                    </BaseButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </BaseCard>
            </article>

            <p v-if="props.catalogos_activos.length === 0" class="papelera-vacia">
                La papelera está vacía. Nada pendiente de restauración.
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
.papelera-page { display: flex; flex-direction: column; gap: 20px; }
.papelera-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
.papelera-header h1 { margin: 0; font-size: 22px; color: var(--text); }
.subtitle { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }
.seccion-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.seccion-head h2 { margin: 0; font-size: 16px; color: var(--text); }
.conteo { font-size: 12px; color: var(--text-muted); }
.tabla-papelera { width: 100%; border-collapse: collapse; font-size: 13px; }
.tabla-papelera th { text-align: left; font-size: 11px; letter-spacing: 0.04em; color: var(--text-muted); padding: 8px 10px; border-bottom: var(--stroke-w, 2px) solid var(--stroke, #31343a); }
.tabla-papelera td { padding: 9px 10px; border-bottom: 1px solid var(--stroke-subtle, #23252a); color: var(--text); }
.tabla-papelera td.mono { font-family: var(--font-mono); font-size: 12px; }
.tabla-papelera td.actions { display: flex; gap: 8px; justify-content: flex-end; }
.papelera-vacia { padding: 60px 10px; text-align: center; color: var(--text-muted); border: 2px dashed var(--stroke-subtle); border-radius: 14px; }
</style>
