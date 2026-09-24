export interface Category {
    id: number;
    name: string;
}

export interface Department {
    id: number;
    name: string;
}

export type ViewMode = 'generator' | 'history';
export type ReportScope = 'soportes' | 'inventario' | 'mantenimiento' | 'rendimiento';
export type HistoryFilterFormat = 'all' | 'pdf' | 'excel' | 'today';

export interface ReportHistoryItem {
    id: number;
    title: string;
    scope: ReportScope;
    date: string;
    format: 'pdf' | 'excel';
    user: string;
    filters: string;
    url: string;
}

export interface TicketsFilters {
    fecha_inicio: string;
    fecha_fin: string;
    estado: string;
    categoria_id: string;
    prioridad: string;
}

export interface SelectOption {
    value: string;
    label: string;
}
