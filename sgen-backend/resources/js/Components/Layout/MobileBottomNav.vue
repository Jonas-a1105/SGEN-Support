<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

interface AuthUser {
    id: number;
    username: string;
    rol?: string;
}

interface PageProps {
    auth?: {
        user?: AuthUser;
    };
    [key: string]: unknown;
}

const page = usePage<PageProps>();
const isDrawerOpen = ref(false);

const primaryNav = computed(() => [
    {
        label: 'Panel',
        href: '/dashboard',
        icon: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        active: page.url.startsWith('/dashboard') || page.url === '/',
    },
    {
        label: 'Inventario',
        href: '/inventario',
        icon: 'M4 7h16v13H4z M8 7V4h8v3 M8 12h8 M8 16h5',
        active: page.url.startsWith('/inventario') && !page.url.includes('tab=equipos'),
    },
    {
        label: 'Soportes',
        href: '/soportes',
        icon: 'M4 7h16v10H4z M7 7v10 M17 7v10 M8 11h8',
        active: page.url.startsWith('/soportes'),
    },
    {
        label: 'Equipos',
        href: '/equipos',
        icon: 'M2 3h20v14H2z M8 21h8 M12 17v4',
        active: page.url.startsWith('/equipos'),
    },
]);

const drawerNav = computed(() => [
    {
        label: 'Departamentos',
        href: '/departamentos',
        icon: 'M3 21h18 M9 8h1 M9 12h1 M9 16h1 M14 8h1 M14 12h1 M14 16h1 M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16',
        active: page.url.startsWith('/departamentos'),
    },
    {
        label: 'Personal',
        href: '/personal',
        icon: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
        active: page.url.startsWith('/personal'),
    },
    {
        label: 'Categorías',
        href: '/categorias',
        icon: 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z M7 7h.01',
        active: page.url.startsWith('/categorias'),
    },
    {
        label: 'Usuarios',
        href: '/usuarios',
        icon: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
        active: page.url.startsWith('/usuarios'),
    },
    {
        label: 'Auditoría',
        href: '/auditoria',
        icon: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4',
        active: page.url.startsWith('/auditoria'),
    },
    {
        label: 'Configuración',
        href: '/configuracion',
        icon: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z',
        active: page.url.startsWith('/configuracion'),
    },
        {
            label: 'Acerca',
            href: '/acerca',
            icon: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z M12 16v-4 M12 8h.01',
            active: page.url.startsWith('/acerca'),
        },
        {
            label: 'Reportes',
            href: '/reportes',
            icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z',
            active: page.url.startsWith('/reportes'),
        },
        {
            label: 'Mantenimientos',
            href: '/mantenimientos',
            icon: 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
            active: page.url.startsWith('/mantenimientos'),
        },
    ]);

const closeDrawer = () => {
    isDrawerOpen.value = false;
};
</script>

<template>
    <div class="mobile-nav-root">
        <!-- BARRA PÍLDORA FLOTANTE INFERIOR -->
        <nav class="mobile-pill-bar" aria-label="Navegación móvil flotante">
            <Link
                v-for="item in primaryNav"
                :key="item.label"
                :href="item.href"
                class="pill-item"
                :class="{ active: item.active }"
                :title="item.label"
            >
                <div class="pill-icon">
                    <svg viewBox="0 0 24 24"><path :d="item.icon" /></svg>
                </div>
                <span class="pill-label">{{ item.label }}</span>
            </Link>

            <!-- BOTÓN MÁS / MENÚ COMPLETO -->
            <button
                type="button"
                class="pill-item btn-more"
                :class="{ active: isDrawerOpen }"
                title="Menú completo"
                @click="isDrawerOpen = !isDrawerOpen"
            >
                <div class="pill-icon">
                    <svg v-if="!isDrawerOpen" viewBox="0 0 24 24">
                        <circle cx="5" cy="12" r="1.8" fill="currentColor" />
                        <circle cx="12" cy="12" r="1.8" fill="currentColor" />
                        <circle cx="19" cy="12" r="1.8" fill="currentColor" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </div>
                <span class="pill-label">Más</span>
            </button>
        </nav>

        <!-- DRAWER / BOTTOM SHEET CON EL MENÚ COMPLETO -->
        <div v-if="isDrawerOpen" class="mobile-drawer-backdrop" @click="closeDrawer">
            <div class="mobile-drawer-sheet" @click.stop>
                <div class="drawer-header">
                    <div class="drawer-user-info">
                        <div class="drawer-avatar">
                            {{ (page.props.auth?.user?.username || 'OP').substring(0, 2).toUpperCase() }}
                        </div>
                        <div class="drawer-copy">
                            <b>{{ page.props.auth?.user?.username || 'Operaciones' }}</b>
                            <span>{{ page.props.auth?.user?.rol || 'Administrador' }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-drawer-close" title="Cerrar menú" @click="closeDrawer">
                        <svg viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <div class="drawer-grid-nav">
                    <span class="drawer-section-title">Módulos del Sistema</span>
                    <div class="drawer-links-grid">
                        <Link
                            v-for="item in drawerNav"
                            :key="item.label"
                            :href="item.href"
                            class="drawer-link-item"
                            :class="{ active: item.active }"
                            @click="closeDrawer"
                        >
                            <div class="drawer-link-icon">
                                <svg viewBox="0 0 24 24"><path :d="item.icon" /></svg>
                            </div>
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </div>

                <div class="drawer-footer">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="drawer-logout-btn"
                        @click="closeDrawer"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* OCULTO EN DESKTOP (> 768px) */
@media (min-width: 769px) {
    .mobile-nav-root {
        display: none !important;
    }
}

/* VISIBLE EN DISPOSITIVOS MÓVILES (<= 768px) */
@media (max-width: 768px) {
    .mobile-nav-root {
        display: block;
    }
}

/* BARRA PÍLDORA FLOTANTE INFERIOR */
.mobile-pill-bar {
    position: fixed;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 999;
    height: 56px;
    width: calc(100% - 28px);
    max-width: 380px;
    background: rgba(24, 25, 28, 0.95);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 0 6px;
    box-shadow: none !important;
}

.pill-item {
    flex: 1;
    height: 44px;
    border-radius: 9999px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    text-decoration: none;
    color: var(--text-muted, #8e9199);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.pill-item:hover {
    color: var(--text, #f4f4f6);
}

.pill-item.active {
    color: #ffffff;
    background: var(--stroke, #31343a);
}

.pill-icon {
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
}

.pill-icon svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pill-label {
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
}

/* DRAWER / BOTTOM SHEET */
.mobile-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.mobile-drawer-sheet {
    background: var(--bg-card, #17181a);
    border-top: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-left: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-right: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: 24px 24px 0 0;
    padding: 20px 20px 32px;
    max-height: 82vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
    box-shadow: none !important;
    animation: slideUp 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 14px;
    border-bottom: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.drawer-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.drawer-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #202227;
    border: var(--stroke-w, 2px) solid var(--orange, #74b300);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--text, #f4f4f6);
}

.drawer-copy b {
    display: block;
    font-size: 13px;
    color: var(--text, #f4f4f6);
}

.drawer-copy span {
    font-size: 11px;
    color: var(--text-muted, #8e9199);
}

.btn-drawer-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    color: var(--text-muted, #8e9199);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: none !important;
}

.btn-drawer-close svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2.2;
}

.drawer-section-title {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 10px;
}

.drawer-links-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.drawer-link-item {
    height: 44px;
    padding: 0 12px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-muted, #8e9199);
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.18s ease;
    box-shadow: none !important;
}

.drawer-link-item:hover,
.drawer-link-item.active {
    color: var(--text, #f4f4f6);
    border-color: var(--orange, #74b300);
}

.drawer-link-item.active {
    background: rgba(116, 179, 0, 0.12);
}

.drawer-link-icon {
    width: 18px;
    height: 18px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.drawer-link-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.drawer-footer {
    padding-top: 10px;
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
}

.drawer-logout-btn {
    width: 100%;
    height: 42px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.12);
    border: var(--stroke-w, 2px) solid rgba(239, 68, 68, 0.35);
    color: #ef4444;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: none !important;
}

.drawer-logout-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
