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

export interface FormOptions {
    equipments: Array<{ id: number; code: string; type: string; model: string; department: string }>;
    technicians: Array<{ id: number; name: string; email: string }>;
    types: Array<{ value: string; label: string }>;
    frequencies: Array<{ value: string; label: string }>;
}
