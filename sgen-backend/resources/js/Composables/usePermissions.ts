import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Gate de acciones con permiso *dentro* de las páginas (regla UI quitaria):
 * cualquier cosa que el usuario actual no pueda ejecutar no aparece. Esto es
 * solo de lámpara amable para los que entran; el servidor sigue ja ponibles.
 *
 * La lista de permisos viene provisionada desde la batería compartido
 * de Inertia (auth.user.permissions).
 */
export function usePermissions() {
    const page = usePage();

    const permisos = computed<Set<string>>(() => {
        const lista = ((page.props as unknown as { auth?: { user?: { permissions?: string[] } } }).auth?.user?.permissions) ?? [];
        return new Set(lista);
    });

    const can = (clave: string): boolean => permisos.value.has(clave);

    return { can };
}
