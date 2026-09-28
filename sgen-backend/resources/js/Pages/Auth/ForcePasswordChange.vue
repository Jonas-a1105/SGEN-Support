<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import BaseButton from '@/Components/UI/BaseButton.vue';
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseInput from '@/Components/UI/BaseInput.vue';

const form = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const showPasswordHelp = ref(true);

const submit = () => {
    form.post('/configuracion/password', {
        onSuccess: () => {
            // El middleware ya no encontrará la bandera y permitirá navegar.
            window.location.href = '/inventario';
        },
    });
};
</script>

<template>
    <Head title="Establecer contraseña" />
    <div class="force-page">
        <BaseCard class="force-card">
            <h1 class="force-title">Establece tu nueva contraseña</h1>
            <p class="force-subtitle">
                Tu cuenta fue creada o restablecida por administración con una clave temporal.
                Por seguridad, define una contraseña personal antes de usar el sistema.
            </p>

            <div v-if="showPasswordHelp" class="force-rules">
                Mínimo 10 caracteres, con mayúsculas, minúsculas y números. No debe aparecer en filtraciones conocidas.
            </div>

            <form @submit.prevent="submit" class="force-form">
                <BaseInput
                    v-model="form.current_password"
                    type="password"
                    label="Contraseña temporal"
                    placeholder="La que te entregó el administrador"
                    :error="form.errors.current_password"
                    required
                />
                <BaseInput
                    v-model="form.new_password"
                    type="password"
                    label="Nueva contraseña"
                    :error="form.errors.new_password"
                    required
                />
                <BaseInput
                    v-model="form.new_password_confirmation"
                    type="password"
                    label="Confirmar nueva contraseña"
                    required
                />
                <BaseButton type="submit" variant="primary" :loading="form.processing" class="w-full">
                    Guardar contraseña y continuar
                </BaseButton>

                <p class="force-alt">
                    ¿No eras tú en esta cuenta?
                    <Link href="/logout" method="post" as="button" type="button" class="force-salir">Cerrar sesión y volver al login</Link>
                </p>
            </form>
        </BaseCard>
    </div>
</template>

<style scoped>
.force-page {
    min-height: 100vh;
    display: grid;
    place-items: center;
    background: var(--bg);
    padding: 24px;
}
.force-card {
    max-width: 440px;
    width: 100%;
    padding: 28px;
}
.force-title {
    margin: 0 0 6px;
    font-size: 19px;
    color: var(--text);
}
.force-subtitle {
    margin: 0 0 16px;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
}
.force-rules {
    font-size: 12px;
    color: var(--text-muted);
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke-subtle);
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 16px;
}
.force-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.w-full {
    width: 100%;
}
.force-alt {
    margin: 0;
    font-size: 12px;
    color: var(--text-muted);
    text-align: center;
    padding-top: 8px;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
.force-salir {
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    font-size: inherit;
    font-weight: 600;
    color: inherit;
    cursor: pointer;
    text-decoration: underline;
}
</style>
