/**
 * Maps system entity statuses and priorities to standard BaseBadge variants.
 */

export type BadgeVariant = 'brand' | 'success' | 'warning' | 'danger' | 'info' | 'neutral' | 'accent' | 'code';

export function getStatusBadgeVariant(status?: string | null): BadgeVariant {
    if (!status) return 'neutral';
    switch (status.toLowerCase()) {
        case 'activo':
        case 'disponible':
        case 'resuelto':
        case 'completado':
        case 'en_uso':
        case 'nuevo':
            return 'success';

        case 'pendiente':
        case 'en_proceso':
        case 'en_reparacion':
        case 'pospuesto':
        case 'usado':
            return 'warning';

        case 'critica':
        case 'inactivo':
        case 'cancelado':
        case 'fuera_de_servicio':
        case 'expirado':
            return 'danger';

        case 'abierto':
        case 'asignado':
        case 'predictivo':
            return 'info';

        case 'preventivo':
            return 'brand';

        default:
            return 'neutral';
    }
}

export function getPriorityBadgeVariant(priority?: string | null): BadgeVariant {
    if (!priority) return 'neutral';
    switch (priority.toLowerCase()) {
        case 'critica':
            return 'danger';
        case 'alta':
            return 'warning';
        case 'media':
            return 'info';
        case 'baja':
        default:
            return 'neutral';
    }
}
