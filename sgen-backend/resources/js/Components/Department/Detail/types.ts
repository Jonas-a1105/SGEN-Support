export interface DepartmentEmployee {
    id: number;
    nombre: string;
    apellido: string;
    fullName: string;
    cedula: string;
    cargo: string;
    email: string;
    telefono: string;
    rol: string;
    initials: string;
}

export interface DepartmentEquipment {
    id: number;
    codigo: string;
    numeroSerie: string;
    nombre: string;
    tipo: string;
    marca: string;
    modelo: string;
    estado: string;
    ubicacion: string;
    custodio: string;
}

export interface DepartmentConsumable {
    id: number;
    codigo: string;
    nombre: string;
    categoria: string;
    unidad_medida: string;
    stock: number;
    stock_minimo: number;
    is_low_stock: boolean;
    valor_unitario: number;
    valor_total: number;
}

export interface EmployeeCandidate {
    id: number;
    nombre: string;
    cargo: string;
    departamento_id: number | null;
}

export interface EquipmentCandidate {
    id: number;
    codigo: string;
    nombre: string;
    estado: string;
}

export interface DepartmentDetail {
    id: number;
    code: string;
    nombre: string;
    ubicacion: string | null;
    jefeAreaNombre: string | null;
    jefeAreaId: number | null;
    jefeEmail: string | null;
    jefeTelefono: string | null;
    jefeCargo: string | null;
    jefeInitials: string;
    descripcion: string | null;
    equiposCount: number;
    empleadosCount: number;
    inventoryPercent: number;
    ticketsCount: number;
    consumablesCount: number;
    consumablesValue: number;
    color: string;
    empleados: DepartmentEmployee[];
    equipos: DepartmentEquipment[];
    consumibles: DepartmentConsumable[];
    candidatosEmpleados: EmployeeCandidate[];
    candidatosEquipos: EquipmentCandidate[];
    createdAt: string | null;
}

export type DepartmentTabKey = 'overview' | 'employees' | 'assets' | 'inventory';
