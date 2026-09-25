export interface Product {
    id: number;
    sku: string;
    name: string;
    category: string;
    description?: string | null;
    brand?: string | null;
    model?: string | null;
    unit_of_measure?: string | null;
    current_stock: number;
    minimum_stock: number;
    is_low_stock: boolean;
    purchase_price: number;
    location?: string | null;
    /** Metadatos de adquisición gestionados por el formulario (aún no persistidos por el backend). */
    vendor?: string | null;
    purchase_date?: string | null;
    warranty_expiration?: string | null;
}

export interface Equipment {
    id: number;
    codigo_inventario: string;
    tipo_equipo?: string | null;
    tipo?: string | null;
    marca: string;
    modelo: string;
    numero_serie?: string | null;
    estado: 'Disponible' | 'En uso' | 'Reparación' | 'Baja' | string;
    departamento_id?: number | null;
    empleado_id?: number | null;
    departamento_nombre?: string | null;
    empleado_nombre?: string | null;
    empleado_apellido?: string | null;
    ubicacion_fisica?: string | null;
    ip_address?: string | null;
    mac_address?: string | null;
    sistema_operativo?: string | null;
    ram?: string | null;
    almacenamiento?: string | null;
    procesador?: string | null;
    fecha_adquisicion?: string | null;
    garantia_expira?: string | null;
    proveedor?: string | null;
    numero_factura?: string | null;
    orden_compra?: string | null;
    precio_compra?: number | string | null;
    notas?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface Department {
    id: number;
    nombre: string;
    codigo?: string | null;
    descripcion?: string | null;
}

export interface Employee {
    id: number;
    nombre: string;
    apellido?: string | null;
    departamento_id?: number | null;
    cargo?: string | null;
    email?: string | null;
    extension?: string | null;
}

export interface InventoryMovement {
    id: number;
    item_id?: number;
    producto_id?: number;
    tipo_movimiento?: string;
    tipo: 'ENTRADA' | 'SALIDA' | 'AJUSTE' | 'TRANSFERENCIA' | 'CONSUMO' | 'BAJA' | string;
    cantidad: number;
    motivo: string;
    referencia_id?: number | null;
    referencia_tipo?: string | null;
    usuario_id?: number | null;
    usuario_nombre?: string | null;
    origen?: string | null;
    destino?: string | null;
    origen_departamento_nombre?: string | null;
    destino_departamento_nombre?: string | null;
    fecha?: string | null;
    created_at?: string | null;
}

export interface StockLocation {
    id: number;
    nombre: string;
    tipo?: string | null;
    departamento_id?: number | null;
    departamento_nombre?: string | null;
    capacidad_maxima?: number | null;
    stock_actual?: number | null;
    estado?: string | null;
}

export interface InventoryPagination {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
}

export interface InventoryKpis {
    total_items: number;
    total_units: number;
    low_stock_count: number;
    total_valuation: number;
}

export interface InventoryFilters {
    search?: string;
    categoria?: string;
    low_stock?: boolean;
}

export interface CreateProductPayload {
    sku?: string;
    name: string;
    category: string;
    unit_of_measure?: string;
    brand?: string;
    model?: string;
    current_stock: number;
    minimum_stock: number;
    purchase_price: number;
    location?: string;
    vendor?: string;
    purchase_date?: string;
    warranty_exp?: string;
    notes?: string;
}

export interface CreateEquipmentPayload {
    codigo_inventario: string;
    numero_serie: string;
    tipo_equipo: string;
    marca: string;
    modelo: string;
    estado: string;
    procesador?: string;
    ram?: string;
    almacenamiento?: string;
    sistema_operativo?: string;
    departamento_id?: number | null;
    empleado_id?: number | null;
    precio_compra?: number | null;
    proveedor?: string;
    fecha_adquisicion?: string;
    garantia_expira?: string;
    notas?: string;
}

export interface TransferStockPayload {
    item_id: number;
    origen_id?: number | null;
    destino_id: number;
    cantidad: number;
    motivo?: string;
}

export interface AdjustStockPayload {
    product_id: number;
    type: 'ENTRADA' | 'SALIDA' | 'AJUSTE' | 'BAJA' | 'CONSUMO' | 'TRANSFERENCIA' | string;
    quantity: number;
    reason: string;
}

export interface ReassignEquipmentPayload {
    equipo_id: number;
    departamento_id?: number | null;
    empleado_id?: number | null;
    ubicacion_fisica?: string | null;
}
