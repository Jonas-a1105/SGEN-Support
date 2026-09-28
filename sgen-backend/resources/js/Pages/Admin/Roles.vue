<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

interface RolItem {
    id: number;
    nombre: string;
    es_sistema: boolean;
    permisos: string[];
    asignados: number;
}

const props = defineProps<{
    roles: RolItem[];
    permisos_disponibles: string[];
}>();

const rolActivo = ref<string>(props.roles[0]?.nombre ?? '');

const rolActual = computed(() => props.roles.find((r) => r.nombre === rolActivo.value) ?? null);

const permisosMarcados = reactive<Record<string, boolean>>({});

sincronizarConRol();

function sincronizarConRol(): void {
    for (const permiso of props.permisos_disponibles) {
        permisosMarcados[permiso] = rolActual.value ? rolActual.value.permisos.includes(permiso) : false;
    }
}

function seleccionarRol(nombre: string): void {
    rolActivo.value = nombre;
    sincronizarConRol();
}

function permisosAgrupados() {
    const grupos: Record<string, string[]> = {};
    for (const permiso of props.permisos_disponibles) {
        const [modulo] = permiso.split('.');
        (grupos[modulo] ??= []).push(permiso);
    }
    return grupos;
}

const enviando = ref(false);

const guardar = (): void => {
    if (! rolActual.value) {
        return;
    }
    enviando.value = true;
    router.put(`/roles/${rolActual.value.id}`, {
        permisos: Object.keys(permisosMarcados).filter((clave) => permisosMarcados[clave]),
    }, {
        preserveScroll: true,
        onFinish: () => {
            enviando.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Roles y permisos" />

        <div class="roles-page">
            <header class="roles-head">
                <div>
                    <h1>Roles y permisos</h1>
                    <p class="subtitle">Matriz editable con bloqueo del rol de sistema (admin).</p>
                </div>
            </header>

            <div class="roles-layout">
                <BaseCard padding="lg" class="roles-list-card">
                    <h3 class="card-title">Roles</h3>
                    <div class="roles-list">
                        <button
                            v-for="rol in props.roles"
                            :key="rol.id"
                            type="button"
                            class="rol-item"
                            :class="{ active: rolActivo === rol.nombre, bloqueado: rol.es_sistema }"
                            @click="seleccionarRol(rol.nombre)"
                        >
                            <span class="rol-nombre">{{ rol.nombre }}</span>
                            <span class="rol-meta">{{ rol.asignados }} usuarios</span>
                            <BaseBadge v-if="rol.es_sistema" variant="warning" size="sm">sistema</BaseBadge>
                        </button>
                    </div>
                </BaseCard>

                <BaseCard padding="lg" class="roles-matrix-card">
                    <div class="matrix-head">
                        <h3 class="card-title">
                            Permisos del rol <strong>{{ rolActivo }}</strong>
                        </h3>
                        <BaseButton
                            type="button"
                            variant="primary"
                            size="md"
                            :disabled="!rolActual || rolActual.es_sistema || enviando"
                            @click="guardar"
                        >
                            Aplicar matriz
                        </BaseButton>
                    </div>

                    <p v-if="rolActual?.es_sistema" class="matrix-warn">
                        Está bloqueado: no se puede degradar la administración del núcleo.
                    </p>

                    <div class="matrix-grid">
                        <section v-for="(permisos, modulo) in permisosAgrupados()" :key="modulo" class="modulo-block">
                            <h4>{{ modulo }}</h4>
                            <label v-for="permiso in permisos" :key="permiso" class="permiso-row">
                                <input
                                    type="checkbox"
                                    v-model="permisosMarcados[permiso]"
                                    :disabled="rolActual?.es_sistema"
                                />
                                <span class="permiso-label">{{ permiso }}</span>
                            </label>
                        </section>
                    </div>
                </BaseCard>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.roles-page { display: flex; flex-direction: column; gap: 20px; }
.roles-head h1 { margin: 0; font-size: 22px; color: var(--text); }
.subtitle { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }
.roles-layout { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
.card-title { margin: 0 0 12px; font-size: 15px; color: var(--text); }
.roles-list { display: flex; flex-direction: column; gap: 6px; }
.rol-item { display: flex; align-items: center; gap: 10px; justify-content: space-between; padding: 10px 12px; border-radius: 10px; background: transparent; border: 2px solid transparent; cursor: pointer; text-align: left; box-shadow: none !important; }
.rol-item:hover { background: var(--bg-sub); border-color: var(--stroke); }
.rol-item.active { background: var(--bg-sub); border-color: var(--stroke); }
.rol-item.bloqueado { opacity: 0.7; }
.rol-nombre { font-weight: 600; font-size: 13px; color: var(--text); }
.rol-meta { font-size: 11px; color: var(--text-muted); }
.matrix-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.matrix-head .card-title { margin: 0; }
.matrix-warn { margin: 0 0 14px; font-size: 12.5px; color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: var(--stroke-w, 2px) solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 10px 12px; }
.matrix-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 18px; }
.modulo-block h4 { margin: 0 0 8px; font-size: 12px; font-weight: var(--weight-semibold); letter-spacing: 0.04em; color: var(--text-muted); }
.permiso-row { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: var(--text); margin-bottom: 6px; cursor: pointer; }
.permiso-row input { margin-top: 2px; }
.permiso-label { font-family: var(--font-mono); font-size: 11.5px; }
</style>
