/**
 * Contratos de tipo TypeScript para el Módulo de Auditoría y Bitácora
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export interface AuditKpis {
    active_sessions: number;
    avg_duration: string;
    total_logs: number;
}

export interface AuditSessionItem {
    id: number;
    user_id: number;
    username: string;
    avatar_initials: string;
    start_formatted: string;
    start_time: string;
    end_time: string | null;
    duration: string;
    status: string;
    is_active: boolean;
}

export interface BitacoraActionItem {
    id: number;
    usuario_id: number | null;
    username: string;
    accion: string;
    entidad: string;
    entidad_id: number | null;
    enlace_tipo: string | null;
    enlace_id: number | null;
    datos_anteriores: Record<string, unknown> | null;
    datos_nuevos: Record<string, unknown> | null;
    created_at: string;
    ip_address: string;
}

export interface AuditDateGroup {
    dateKey: string;
    displayDate: string;
    items: BitacoraActionItem[];
}
