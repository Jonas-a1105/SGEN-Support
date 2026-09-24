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

export interface DepartmentOption {
    id: number;
    nombre: string;
}

export interface EmployeeOption {
    id: number;
    nombre: string;
    cargo: string;
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

export type EquipmentTabKey = 'specs' | 'purchase' | 'support' | 'maintenance';
