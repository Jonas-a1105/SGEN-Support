<script setup lang="ts">
import BasePagination from '@/Components/UI/BasePagination.vue';

withDefaults(
    defineProps<{
        showingCount?: number;
        totalCount: number;
        itemLabel?: string;
        currentPage: number;
        totalPages: number;
        perPage?: number;
        perPageOptions?: number[];
    }>(),
    {
        itemLabel: 'items',
        perPage: 5,
        perPageOptions: () => [5, 10, 20, 50],
    }
);

const emit = defineEmits<{
    (e: 'change-page', page: number): void;
    (e: 'change-per-page', perPage: number): void;
}>();
</script>

<template>
    <div class="table-pagination-footer-wrapper">
        <BasePagination
            :current-page="currentPage"
            :total-pages="totalPages"
            :total-items="totalCount"
            :per-page="perPage"
            :per-page-options="perPageOptions"
            :item-label="itemLabel"
            @page-change="emit('change-page', $event)"
            @per-page-change="emit('change-per-page', $event)"
        />
    </div>
</template>

<style scoped>
.table-pagination-footer-wrapper {
    width: 100%;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}
</style>
