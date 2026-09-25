/**
 * Contratos de tipo TypeScript para el Módulo de Notificaciones
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export type NotificationType =
    | 'ticket_asignado'
    | 'ticket_vencimiento'
    | 'ticket_estado_cambiado'
    | 'mantenimiento_proximo'
    | 'inventario_bajo_stock'
    | 'sistema_general'
    | 'info'
    | 'warning'
    | 'success'
    | 'danger'
    | string;

export interface NotificationItem {
    id: number;
    usuario_id: number;
    tipo: NotificationType;
    titulo?: string | null;
    mensaje: string;
    enlace?: string | null;
    icono?: string | null;
    leido: boolean;
    read_at?: string | null;
    created_at?: string | null;
}
