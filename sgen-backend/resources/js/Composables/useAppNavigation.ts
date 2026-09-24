import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export interface NavItemConfig {
    id: string;
    label: string;
    href: string;
    icon: string;
    isActive: (url: string) => boolean;
}

export interface ComputedNavItem {
    id: string;
    label: string;
    href: string;
    icon: string;
    active: boolean;
}

export const NAV_ITEMS_REGISTRY: NavItemConfig[] = [
    {
        id: 'panel',
        label: 'Panel',
        href: '/dashboard',
        icon: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        isActive: (url) => url.startsWith('/dashboard') || url === '/',
    },
    {
        id: 'inventario',
        label: 'Inventario',
        href: '/inventario',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        isActive: (url) => url.startsWith('/inventario') && !url.includes('tab=equipos'),
    },
    {
        id: 'soportes',
        label: 'Soportes',
        href: '/soportes',
        icon: 'M4 7h16v10H4z M7 7v10 M17 7v10 M8 11h8',
        isActive: (url) => url.startsWith('/soportes'),
    },
    {
        id: 'mantenimientos',
        label: 'Mantenimientos',
        href: '/mantenimientos',
        icon: 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
        isActive: (url) => url.startsWith('/mantenimientos'),
    },
    {
        id: 'reportes',
        label: 'Reportes',
        href: '/reportes',
        icon: 'M21.21 15.89A10 10 0 1 1 8 2.83 M22 12A10 10 0 0 0 12 2v10z',
        isActive: (url) => url.startsWith('/reportes'),
    },
    {
        id: 'equipos',
        label: 'Equipos',
        href: '/equipos',
        icon: 'M2 3h20v14H2z M8 21h8 M12 17v4',
        isActive: (url) => url.startsWith('/equipos'),
    },
    {
        id: 'departamentos',
        label: 'Departamentos',
        href: '/departamentos',
        icon: 'M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4',
        isActive: (url) => url.startsWith('/departamentos'),
    },
    {
        id: 'personal',
        label: 'Personal',
        href: '/personal',
        icon: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z M12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z',
        isActive: (url) => url.startsWith('/personal'),
    },
    {
        id: 'categorias',
        label: 'Categorías',
        href: '/categorias',
        icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A1.994 1.994 0 0 1 3 12V7a4 4 0 0 1 4-4z',
        isActive: (url) => url.startsWith('/categorias'),
    },
    {
        id: 'usuarios',
        label: 'Usuarios',
        href: '/usuarios',
        icon: 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z',
        isActive: (url) => url.startsWith('/usuarios'),
    },
    {
        id: 'auditoria',
        label: 'Auditoría',
        href: '/auditoria',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        isActive: (url) => url.startsWith('/auditoria'),
    },
    {
        id: 'configuracion',
        label: 'Configuración',
        href: '/configuracion',
        icon: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z',
        isActive: (url) => url.startsWith('/configuracion'),
    },
    {
        id: 'acerca',
        label: 'Acerca',
        href: '/acerca',
        icon: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z M12 16v-4 M12 8h.01',
        isActive: (url) => url.startsWith('/acerca'),
    },
];

export function useAppNavigation() {
    const page = usePage();

    const currentUrl = computed(() => page.url);

    const navItems = computed<ComputedNavItem[]>(() =>
        NAV_ITEMS_REGISTRY.map((item) => ({
            id: item.id,
            label: item.label,
            href: item.href,
            icon: item.icon,
            active: item.isActive(currentUrl.value),
        }))
    );

    const primaryMobileNav = computed<ComputedNavItem[]>(() => {
        const primaryIds = ['panel', 'inventario', 'soportes', 'equipos'];
        return navItems.value.filter((item) => primaryIds.includes(item.id));
    });

    const drawerMobileNav = computed<ComputedNavItem[]>(() => {
        const primaryIds = ['panel', 'inventario', 'soportes', 'equipos'];
        return navItems.value.filter((item) => !primaryIds.includes(item.id));
    });

    return {
        currentUrl,
        navItems,
        primaryMobileNav,
        drawerMobileNav,
    };
}
