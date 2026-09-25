/**
 * Contratos de tipo TypeScript para el Módulo de Mantenimiento
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

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
    proximaFecha?: string | null;
    costo?: number;
    equipoCodigo?: string;
    equipoTipo?: string;
    tecnicoNombre?: string;
}

export interface EquipmentInfo {
    id: number;
    codigo: string;
    tipo: string;
    marca: string;
    modelo: string;
    serial: string;
    estado: string;
    departamento: string;
    departamentoId: number | null;
    custodio: string;
    custodioId: number | null;
}

export interface RelatedMaintenance {
    id: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    costo: number | null;
    descripcion: string;
    tecnicoNombre: string;
}

export interface MaintenanceDetail {
    id: number;
    equipoId: number;
    fecha: string;
    tipoMantenimiento: string;
    estado: string;
    descripcion: string;
    frecuencia: string;
    proximaFecha: string | null;
    costo: number | null;
    tecnicoId: number | null;
    tecnicoNombre: string | null;
    realizadoPor: string | null;
    observaciones: string | null;
    checklist: Array<string | { text?: string; checked?: boolean }> | null;
    duracion: number | null;
    createdAt: string | null;
    updatedAt: string | null;
    equipo: EquipmentInfo | null;
    historialEquipo: RelatedMaintenance[] | null;
    isOverdue: boolean;
}

export interface MaintenanceMaterialItem {
    id: number;
    item_id: number;
    item_codigo: string;
    item_nombre: string;
    cantidad: number;
    costo_unitario: number;
    costo_total: number;
    fecha: string;
}

export interface InventoryOptionItem {
    id: number;
    codigo: string;
    nombre: string;
    stock_actual: number;
    unidad_medida: string;
    valor_compra: number;
}

export interface MaintenanceFormOptions {
    equipments: Array<{ id: number; code: string; type: string; model: string; department: string }>;
    technicians: Array<{ id: number; name: string; email: string }>;
    inventory_items?: InventoryOptionItem[];
    types: Array<{ value: string; label: string }>;
    frequencies: Array<{ value: string; label: string }>;
}

export type MaintenanceFilterPill = 'todos' | 'pendiente' | 'en_proceso' | 'completado' | 'preventivo' | 'correctivo';
