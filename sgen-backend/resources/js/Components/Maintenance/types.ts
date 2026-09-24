export interface MaintenanceKpis {
    pending: number;
    inProcess: number;
    completed: number;
    upcoming: number;
    overdue: number;
    total: number;
}

export interface MaintenanceItem {
    id: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    frecuencia: string;
    proximaFecha?: string;
    costo?: number;
    equipoCodigo?: string;
    equipoTipo?: string;
    tecnicoNombre?: string;
}

export type MaintenanceFilterPill = 'todos' | 'pendiente' | 'en_proceso' | 'completado' | 'preventivo' | 'correctivo';
