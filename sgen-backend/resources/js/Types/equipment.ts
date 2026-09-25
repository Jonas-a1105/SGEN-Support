/**
 * Contratos de tipo TypeScript para el Módulo de Equipos Tecnológicos
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeOption {
    id: number;
    nombre?: string;
    nombre_completo?: string;
    cargo?: string | null;
}

export interface EquipmentTicket {
    id: number;
    titulo: string;
    descripcion: string;
    estado: string;
    prioridad: string;
    fecha: string | null;
}

export interface EquipmentMaintenance {
    id: number;
    tipo: string;
    estado: string;
    descripcion: string;
    costo: number;
    realizadoPor: string;
    fecha: string | null;
    proximaFecha: string | null;
}

export interface EquipmentItem {
    id: string;
    numericId: number;
    name: string;
    type: string;
    dept: string;
    deptShort: string;
    status: string;
    rawStatus: string;
    serialNumber?: string | null;
    assignedTo?: string | null;
    location?: string | null;
    ipAddress?: string | null;
    departmentId?: number | null;
}

export interface EquipmentKpis {
    totalActivos: number;
    operativos: number;
    enReparacion: number;
    fueraServicio: number;
    enUso?: number;
    disponibles?: number;
}

export interface EquipmentDetail {
    id: number;
    inventoryCode: string;
    serialNumber: string;
    name: string;
    type: string;
    brand: string | null;
    model: string | null;
    status: string;
    rawStatus: string;
    departmentId: number | null;
    departmentName: string | null;
    employeeId: number | null;
    employeeName: string | null;
    physicalLocation: string | null;
    processor: string | null;
    ram: string | null;
    storage: string | null;
    os: string | null;
    ipAddress: string | null;
    driver: string | null;
    toner: string | null;
    purchaseDate: string | null;
    supplier: string | null;
    warranty: string | null;
    purchaseValue: number | null;
    warrantyPercent: number;
    warrantyStatus: 'active' | 'warning' | 'expired';
    warrantyRemaining: string | null;
    tickets: EquipmentTicket[];
    maintenances: EquipmentMaintenance[];
    departamentos: DepartmentOption[];
    empleados: EmployeeOption[];
}

export interface EquipmentFilterOptions {
    departments: DepartmentOption[];
    employees: EmployeeOption[];
}

export interface EquipmentFormPayload {
    nombre?: string;
    id?: string;
    codigo_inventario?: string;
    tipo: string;
    estado: string;
    departamento_id?: number | null;
    empleado_id?: number | null;
    numero_serie?: string | null;
    marca?: string | null;
    modelo?: string | null;
    procesador?: string | null;
    memoria_ram?: string | null;
    almacenamiento?: string | null;
    sistema_operativo?: string | null;
    direccion_ip?: string | null;
    ubicacion_fisica?: string | null;
    valor_compra?: number | null;
    proveedor?: string | null;
}

export type EquipmentTabKey = 'specs' | 'purchase' | 'support' | 'maintenance';
