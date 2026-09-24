import { ref, computed, type Ref } from 'vue';

export interface CategoryItem {
    id: number;
    nombre: string;
    descripcion: string | null;
    icono: string;
    color: string;
    activo: boolean;
    total_tickets: number;
}

export function useCategoryFilters(categories: Ref<CategoryItem[]>) {
    const search = ref('');

    const filteredCategories = computed(() => {
        const query = search.value.trim().toLowerCase();
        if (!query) {
            return categories.value;
        }

        return categories.value.filter((cat) => {
            const nameMatch = cat.nombre.toLowerCase().includes(query);
            const descMatch = (cat.descripcion || '').toLowerCase().includes(query);
            return nameMatch || descMatch;
        });
    });

    return {
        search,
        filteredCategories,
    };
}
