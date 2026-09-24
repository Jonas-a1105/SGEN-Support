export interface AssignedEquipment {
    id: number;
    codigo: string;
    numeroSerie: string;
    nombre: string;
    tipo: string;
    marca: string;
    modelo: string;
    estado: string;
    ubicacion: string;
}

export interface EmployeeTicket {
    id: number;
    titulo: string;
    descripcion: string;
    estado: string;
    prioridad: string;
    fecha: string | null;
}

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeDetail {
    id: number;
    formattedId: string;
    nombre: string;
    apellido: string;
    fullName: string;
    email: string;
    cedula: string | null;
    cargo: string | null;
    telefono: string | null;
    departamentoId: number | null;
    departamentoNombre: string | null;
    rol: string;
    usuarioId: number | null;
    username: string | null;
    initials: string;
    tint: string;
    equipos: AssignedEquipment[];
    tickets: EmployeeTicket[];
    departamentos: DepartmentOption[];
    equiposCount: number;
    ticketsCount: number;
    resolvedTicketsCount: number;
}

export type EmployeeTabKey = 'overview' | 'assets' | 'tickets';
