export interface Equipment {
    id: number;
    code: string;
    type: string;
    model: string;
    department: string;
}

export interface Technician {
    id: number;
    name: string;
    email: string;
}

export interface FormOption {
    value: string;
    label: string;
}

export interface ChecklistTask {
    id: number;
    text: string;
    done: boolean;
}

export interface MaintenanceFormData {
    equipo_id: string;
    fecha: string;
    tipo_mantenimiento: string;
    estado: string;
    descripcion: string;
    frecuencia: string;
    proxima_fecha: string;
    costo: string;
    tecnico_id: string;
    observaciones: string;
    duracion: number;
}
