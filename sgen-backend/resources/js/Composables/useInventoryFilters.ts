import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import type { InventoryFilters } from '@/Types/inventory';

export function useInventoryFilters(initialFilters: InventoryFilters = {}) {
    const search = ref(initialFilters.search || '');
    const categoria = ref(initialFilters.categoria || '');
    const lowStock = ref(!!initialFilters.low_stock);

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    const applyFilters = () => {
        const queryTerm = (search.value || '').trim();
        if (queryTerm) query.search = queryTerm;
        if (categoria.value) query.categoria = categoria.value;
        if (lowStock.value) query.low_stock = true;

        router.get('/inventario', query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    watch([search, categoria, lowStock], () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 300);
    });

    const resetFilters = () => {
        search.value = '';
        categoria.value = '';
        lowStock.value = false;
        applyFilters();
    };

    return {
        search,
        categoria,
        lowStock,
        resetFilters,
    };
}
