export interface DashboardKpis {
    total_equipos: number;
    tickets_pendientes: number;
    tickets_en_proceso: number;
    tickets_resueltos: number;
    cambio_equipos: string;
    cambio_pendientes: string;
    cambio_proceso: string;
    cambio_resueltos: string;
}

export interface TicketVolumeData {
    year: number;
    months: string[];
    values: number[];
    total: number;
    available_years: number[];
}

export interface TopCategoryData {
    nombre: string;
    cantidad: number;
    porcentaje: number;
}

export interface InventoryHealthData {
    used: number;
    repair: number;
    available: number;
    down: number;
    operative_percentage: number;
}

export interface TechPerformanceItem {
    name: string;
    score: number;
    percentage: number;
}

export interface RecentActivityItem {
    id: number | string;
    title: string;
    time_ago: string;
    badge: string;
    type: string;
}

export interface StatusTicketItem {
    id: number;
    titulo: string;
    prioridad: string;
    created_at: string;
}

export interface MyWorkData {
    mis_pendientes: number;
    mis_en_proceso: number;
    mis_resueltos_mes: number;
    mis_equipos: { id: number; codigo: string; nombre: string }[];
    proximas_ordenes_mias: number;
}

export interface DashboardMetrics {
    kpis: DashboardKpis;
    ticket_volume: TicketVolumeData;
    top_category: TopCategoryData;
    inventory_health: InventoryHealthData;
    technicians: TechPerformanceItem[];
    recent_activity: RecentActivityItem[];
    tickets_by_status: {
        pending: StatusTicketItem[];
        in_process: StatusTicketItem[];
    };
    /** Vista por rol ("mi trabajo hoy): null para visitantes sin vínculo. */
    my_work?: MyWorkData | null;
}
