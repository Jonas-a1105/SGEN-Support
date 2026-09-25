/**
 * Contratos de tipo TypeScript para el Módulo de Empleados / Personal
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export interface EmployeeItem {
    numericId: number;
    id: string;
    fullName: string;
    firstName: string;
    lastName: string;
    position: string;
    dept: string;
    departmentId?: number | null;
    email: string;
    idDoc: string;
    userAccount?: string | null;
    initials: string;
    tint: string;
    role: string;
}

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

export interface EmployeeDepartmentOption {
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
    departamentos: EmployeeDepartmentOption[];
    equiposCount: number;
    ticketsCount: number;
    resolvedTicketsCount: number;
}

export interface EmployeeLookup {
    id: number;
    nombre: string;
    apellido: string;
    departamento_id: number | null;
}

export interface EmployeeKpis {
    totalEmp: number;
    activeUsers: number;
    noUsers: number;
}

export type EmployeeTabKey = 'overview' | 'assets' | 'tickets';
