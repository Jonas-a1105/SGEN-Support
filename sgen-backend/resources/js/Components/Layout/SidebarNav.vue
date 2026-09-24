<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BaseAvatar } from '@/Components/UI';

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

const isOpen = ref(false);
const sidebarMode = ref<'hover' | 'manual'>('hover');

const userName = computed(() => page.props.auth?.user?.username || 'admin');
const userRole = computed(() => page.props.auth?.user?.rol || 'Administrador');

// Sincronización con la preferencia configurada en Configuración > Apariencia
const handleModeChange = (e: Event) => {
    const customEvent = e as CustomEvent<string>;
    if (customEvent.detail === 'manual' || customEvent.detail === 'hover') {
        sidebarMode.value = customEvent.detail;
    }
};

const handleStorageChange = (e: StorageEvent) => {
    if (e.key === 'sgen_sidebar_mode' && (e.newValue === 'manual' || e.newValue === 'hover')) {
        sidebarMode.value = e.newValue;
    }
};

onMounted(() => {
    const stored = localStorage.getItem('sgen_sidebar_mode');
    if (stored === 'manual' || stored === 'hover') {
        sidebarMode.value = stored;
    }
    window.addEventListener('sgen-sidebar-mode', handleModeChange);
    window.addEventListener('storage', handleStorageChange);
});

onUnmounted(() => {
    window.removeEventListener('sgen-sidebar-mode', handleModeChange);
    window.removeEventListener('storage', handleStorageChange);
});

// HOVER DINÁMICO (Activo únicamente si está configurado en modo hover)
const onMouseEnter = () => {
    if (sidebarMode.value === 'hover') {
        isOpen.value = true;
    }
};

const onMouseLeave = () => {
    if (sidebarMode.value === 'hover') {
        isOpen.value = false;
    }
};

// BOTÓN VERDE DE LA MUESCA: Alterna abrir y cerrar el menú
const toggleSidebar = (e: MouseEvent) => {
    e.stopPropagation();
    isOpen.value = !isOpen.value;
};

const navItems = computed(() => [
    {
        label: 'Panel',
        href: '/dashboard',
        icon: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        active: page.url.startsWith('/dashboard') || page.url === '/',
    },
    {
        label: 'Inventario',
        href: '/inventario',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        active: page.url.startsWith('/inventario') && !page.url.includes('tab=equipos'),
    },
    {
        label: 'Soportes',
        href: '/soportes',
        icon: 'M4 7h16v10H4z M7 7v10 M17 7v10 M8 11h8',
        active: page.url.startsWith('/soportes'),
    },
    {
        label: 'Mantenimientos',
        href: '/mantenimientos',
        icon: 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
        active: page.url.startsWith('/mantenimientos'),
    },
    {
        label: 'Reportes',
        href: '/reportes',
        icon: 'M21.21 15.89A10 10 0 1 1 8 2.83 M22 12A10 10 0 0 0 12 2v10z',
        active: page.url.startsWith('/reportes'),
    },
    {
        label: 'Equipos',
        href: '/equipos',
        icon: 'M2 3h20v14H2z M8 21h8 M12 17v4',
        active: page.url.startsWith('/equipos'),
    },
    {
        label: 'Departamentos',
        href: '/departamentos',
        icon: 'M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4',
        active: page.url.startsWith('/departamentos'),
    },
    {
        label: 'Personal',
        href: '/personal',
        icon: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z M12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z',
        active: page.url.startsWith('/personal'),
    },
    {
        label: 'Categorías',
        href: '/categorias',
        icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A1.994 1.994 0 0 1 3 12V7a4 4 0 0 1 4-4z',
        active: page.url.startsWith('/categorias'),
    },
    {
        label: 'Usuarios',
        href: '/usuarios',
        icon: 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z',
        active: page.url.startsWith('/usuarios'),
    },
    {
        label: 'Auditoría',
        href: '/auditoria',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        active: page.url.startsWith('/auditoria'),
    },
    {
        label: 'Configuración',
        href: '/configuracion',
        icon: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z',
        active: page.url.startsWith('/configuracion'),
    },
    {
        label: 'Acerca',
        href: '/acerca',
        icon: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z M12 16v-4 M12 8h.01',
        active: page.url.startsWith('/acerca'),
    },
]);
</script>

<template>
    <aside
        class="sidebar"
        :class="{ 'is-open': isOpen }"
        @mouseenter="onMouseEnter"
        @mouseleave="onMouseLeave"
    >
        <!-- MUESCA CON BOTÓN: Solo visible cuando el modo Manual / Fijo está seleccionado -->
        <Transition name="notch-fade">
            <div v-if="sidebarMode === 'manual'" class="notch-container">
                <svg class="notch-svg" viewBox="0 0 40 88" fill="none">
                    <path
                        d="M 0,0 L 0,8 C 0,18 7,26 16,26 A 18 18 0 0 1 16,62 C 7,62 0,70 0,80 L 0,88 L -8,88 L -8,0 Z"
                        class="notch-fill-path"
                    />
                    <path
                        d="M 0,0 L 0,8 C 0,18 7,26 16,26 A 18 18 0 0 1 16,62 C 7,62 0,70 0,80 L 0,88"
                        class="notch-stroke-path"
                        stroke-linecap="butt"
                    />
                </svg>

                <!-- BOTÓN VERDE QUE ABRE O CIERRA EL MENÚ CON CLIC -->
                <button
                    type="button"
                    class="notch-toggle-btn"
                    :title="isOpen ? 'Cerrar menú' : 'Abrir menú'"
                    :aria-label="isOpen ? 'Cerrar menú' : 'Abrir menú'"
                    @click="toggleSidebar"
                >
                    <svg
                        class="notch-arrow-icon"
                        :class="{ 'is-open': isOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- SECCIÓN PERFIL / USUARIO -->
        <div class="profile-section">
            <BaseAvatar :name="userName" size="md" />

            <div class="profile-info">
                <p class="profile-name">{{ userName }}</p>
                <p class="profile-role">{{ userRole }}</p>
            </div>
        </div>

        <!-- LISTA DE ENLACES PRINCIPALES -->
        <nav class="nav-menu">
            <Link
                v-for="item in navItems"
                :key="item.label"
                :href="item.href"
                class="nav-item"
                :class="{ active: item.active }"
            >
                <!-- ÚNICAMENTE el borde izquierdo indicador (sin ningún borde de recuadro) -->
                <span v-if="item.active" class="active-indicator"></span>
                <div class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path :d="item.icon" />
                    </svg>
                </div>
                <span class="nav-label">{{ item.label }}</span>
            </Link>
        </nav>

        <!-- PIE DE MENÚ: SALIR -->
        <div class="sidebar-footer">
            <Link
                href="/logout"
                method="post"
                as="button"
                class="nav-item logout-item"
                title="Cerrar Sesión"
            >
                <div class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                </div>
                <span class="nav-label">Salir</span>
            </Link>
        </div>
    </aside>
</template>

<style scoped>
.sidebar {
    position: fixed;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 58px;
    height: auto;
    max-height: calc(100vh - 36px);
    background: var(--sidebar);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 28px;
    transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 60;
    overflow: visible;
    display: flex;
    flex-direction: column;
    padding: 14px 8px 12px 8px;
    user-select: none;
    box-shadow: none !important;
}

.sidebar.is-open {
    width: 220px;
}

/* MUESCA CON CURVA ORGÁNICA */
.notch-fade-enter-active,
.notch-fade-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}

.notch-fade-enter-from,
.notch-fade-leave-to {
    opacity: 0;
    transform: translateX(-6px);
}

.notch-container {
    position: absolute;
    top: 14px;
    left: 100%;
    margin-left: -1px;
    width: 40px;
    height: 88px;
    z-index: 50;
    pointer-events: none;
}

.notch-svg {
    position: absolute;
    top: 0;
    left: 0;
    width: 40px;
    height: 88px;
    pointer-events: none;
}

.notch-fill-path {
    fill: var(--sidebar);
}

.notch-stroke-path {
    stroke: var(--stroke);
    stroke-width: var(--stroke-w, 1.2px);
}

.notch-toggle-btn {
    position: absolute;
    left: 4px;
    top: 32px;
    width: 24px;
    height: 24px;
    z-index: 60;
    background-color: var(--brand);
    color: #ffffff;
    border-radius: 9999px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    pointer-events: auto;
    box-shadow: none !important;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s;
}

.notch-toggle-btn:hover {
    background-color: var(--brand-hover);
    transform: scale(1.08);
}

.notch-toggle-btn:active {
    transform: scale(0.95);
}

.notch-arrow-icon {
    width: 12px;
    height: 12px;
    transition: transform 0.3s ease;
}

.notch-arrow-icon.is-open {
    transform: rotate(180deg);
}

/* SECCIÓN PERFIL DE USUARIO - Anclaje estático libre de bateo */
.profile-section {
    display: flex;
    align-items: center;
    height: 44px;
    margin-bottom: 6px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    overflow: hidden;
}

.profile-avatar {
    width: 36px;
    min-width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 9999px;
    background: #0f172a;
    border: 2px solid var(--brand);
    display: grid;
    place-items: center;
    color: #ffffff;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.04em;
    margin-left: 3px;
    box-shadow: none !important;
}

.profile-info {
    margin-left: 10px;
    overflow: hidden;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.12s ease 0s;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sidebar.is-open .profile-info {
    opacity: 1;
    pointer-events: auto;
    transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1) 0.08s;
}

.profile-name {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.profile-role {
    margin: 0;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
}

/* LISTA DE NAVEGACIÓN */
.nav-menu {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: none;
    padding: 2px 0;
    max-height: calc(100vh - 180px);
}

.nav-menu::-webkit-scrollbar {
    display: none;
}

.nav-item {
    position: relative;
    display: flex;
    align-items: center;
    height: 40px;
    padding: 0;
    border-radius: 12px;
    color: var(--text-muted);
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
    border: none !important;
    background: transparent;
    cursor: pointer;
    width: 100%;
    text-align: left;
    box-shadow: none !important;
}

.nav-item:hover {
    background: var(--stroke-subtle);
    color: var(--text);
}

/* LINK ACTIVO: Sin ningún borde de recuadro perimetral, solo fondo y su indicador izquierdo */
.nav-item.active {
    background: var(--stroke);
    color: var(--text);
    font-weight: 700;
    border: none !important;
}

.nav-item.active .nav-icon {
    color: var(--brand);
}

/* SOLO EL BORDE IZQUIERDO REDONDEADO INDICADOR */
.active-indicator {
    position: absolute;
    left: 0;
    top: 8px;
    bottom: 8px;
    width: 4px;
    background: var(--brand);
    border-radius: 0 4px 4px 0;
}

/* ÍCONO ANCLADO ESTÁTICO (Sin saltos ni desplazamientos horizontales) */
.nav-icon {
    width: 42px;
    min-width: 42px;
    height: 38px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    color: currentColor;
    transition: color 0.15s ease;
}

.nav-icon svg {
    width: 19px;
    height: 19px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.9;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ETIQUETA DE TEXTO - Fade suave de opacidad sin desplazamiento físico ni bateo */
.nav-label {
    margin-left: 8px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.12s ease 0s;
}

.sidebar.is-open .nav-label {
    opacity: 1;
    pointer-events: auto;
    transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1) 0.08s;
}

/* FOOTER */
.sidebar-footer {
    padding-top: 6px;
    margin-top: auto;
    border-top: var(--stroke-w) solid var(--stroke-subtle);
}

.logout-item:hover {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* OCULTAR EN PANTALLAS MÓVILES */
@media (max-width: 768px) {
    .sidebar {
        display: none !important;
    }
}
</style>
