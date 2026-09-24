import { ref, computed, type Ref } from 'vue';

export function useClientPagination<T>(items: Ref<T[]>, initialPerPage = 10) {
    const perPage = ref(initialPerPage);
    const currentPage = ref(1);

    const totalPages = computed(() => Math.ceil(items.value.length / perPage.value) || 1);

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;
        return items.value.slice(start, start + perPage.value);
    });

    const setPage = (page: number) => {
        currentPage.value = Math.max(1, Math.min(page, totalPages.value));
    };

    const setPerPage = (val: number) => {
        perPage.value = val;
        currentPage.value = 1;
    };

    return {
        perPage,
        currentPage,
        totalPages,
        paginatedItems,
        setPage,
        setPerPage,
    };
}
