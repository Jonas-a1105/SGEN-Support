<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{ message?: string }>();
</script>

<template>
    <div class="min-h-screen d-flex align-items-center justify-content-center bg-light">
        <Head title="Error" />
        <div class="card shadow-sm border-0 rounded-4 p-5 text-center" style="max-width: 480px;">
            <div class="display-6 text-danger mb-3">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
            <h1 class="h4 mb-3">Ocurrió un problema</h1>
            <p class="text-secondary mb-4">
                {{ message ?? 'No se pudo completar la operación. Inténtalo de nuevo.' }}
            </p>
            <Link href="/dashboard" class="btn btn-primary px-4">
                <i class="bi bi-house me-2"></i>Volver al inicio
            </Link>
        </div>
    </div>
</template>
