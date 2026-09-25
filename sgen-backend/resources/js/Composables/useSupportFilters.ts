import { ref, computed, type Ref } from 'vue';
import type { TicketListItem } from '@/Types/support';

export function useSupportFilters(tickets: Ref<TicketListItem[]> | { value: TicketListItem[] }, defaultFilter = 'all') {
    const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
    const queryEstado = urlParams?.get('estado');
    const initialFilter = queryEstado
        ? (queryEstado === 'proceso' || queryEstado === 'en_proceso' || queryEstado === 'process'
            ? 'process'
            : queryEstado === 'pendiente' || queryEstado === 'pending'
            ? 'pending'
            : queryEstado === 'resuelto' || queryEstado === 'resolved'
            ? 'resolved'
            : queryEstado === 'cerrado' || queryEstado === 'closed'
            ? 'closed'
            : queryEstado === 'en_espera' || queryEstado === 'waiting'
            ? 'waiting'
            : queryEstado)
        : defaultFilter;

    const searchQuery = ref(urlParams?.get('search') || '');
    const activeFilter = ref(initialFilter === 'todos' ? 'all' : initialFilter);
    const isCompact = ref(false);

    const filteredTickets = computed(() => {
        const list = tickets.value;
        const currentFilter = activeFilter.value;
        const query = searchQuery.value.toLowerCase().trim();

        return list.filter((ticket) => {
            // 1. Filtro de estado / asignación
            const status = ticket.status_variant || ticket.status;
            if (currentFilter === 'critical' && status !== 'critical') return false;
            if (currentFilter === 'process' && status !== 'process') return false;
            if (currentFilter === 'pending' && status !== 'pending') return false;
            if (currentFilter === 'resolved' && status !== 'resolved') return false;
            if (currentFilter === 'closed' && status !== 'closed' && ticket.status !== 'cerrado') return false;
            if (currentFilter === 'my' && !ticket.is_mine) return false;

            // 2. Filtro de búsqueda en vivo
            if (query !== '') {
                const codeMatch = `#t-${ticket.raw_id || ticket.id}`.toLowerCase().includes(query) || `${ticket.raw_id || ticket.id}`.toLowerCase().includes(query);
                const titleMatch = ticket.title?.toLowerCase().includes(query) ?? false;
                const reqMatch = ticket.requester?.toLowerCase().includes(query) ?? false;
                const deptMatch = (ticket.department || ticket.dept)?.toLowerCase().includes(query) ?? false;
                const techMatch = (ticket.tech_name || ticket.tech)?.toLowerCase().includes(query) ?? false;
                const catMatch = ticket.category?.toLowerCase().includes(query) ?? false;

                return codeMatch || titleMatch || reqMatch || deptMatch || techMatch || catMatch;
            }

            return true;
        });
    });

    const setFilter = (filter: string) => {
        activeFilter.value = filter;
    };

    const setSearch = (query: string) => {
        searchQuery.value = query;
    };

    const toggleCompact = () => {
        isCompact.value = !isCompact.value;
    };

    const filterByRequester = (name: string) => {
        searchQuery.value = name;
    };

    const filterByTech = (name: string) => {
        searchQuery.value = name;
    };

    return {
        searchQuery,
        activeFilter,
        isCompact,
        filteredTickets,
        setFilter,
        setSearch,
        toggleCompact,
        filterByRequester,
        filterByTech,
    };
}
