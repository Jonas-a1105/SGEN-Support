<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BasePageHeader, BaseButton } from '@/Components/UI';
</script>

<template>
    <BasePageHeader
        title="Gestión de Soportes"
        subtitle="Control y seguimiento de tickets"
    >
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V9z" />
                <path d="M10 7v10M14 7v10" />
            </svg>
        </template>

        <template #actions>
            <Link href="/soportes/crear" class="link-no-style">
                <BaseButton variant="primary">
                    + Nuevo Ticket
                </BaseButton>
            </Link>
        </template>
    </BasePageHeader>
</template>

<style scoped>
.link-no-style {
    text-decoration: none;
}
</style>
