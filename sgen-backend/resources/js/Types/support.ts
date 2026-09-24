export interface SupportKpis {
    critical_pending: number;
    general_queue: number;
    in_process: number;
    my_assignments: number;
    total_tickets: number;
    resolved_tickets: number;
}

export interface TicketListItem {
    id: string; // e.g. "T-96"
    raw_id: number;
    title: string;
    category: string;
    requester: string;
    dept: string;
    tech: string;
    tech_init: string;
    date: string;
    comments: number;
    status: 'resolved' | 'process' | 'pending' | 'critical' | string;
    priority: 'baja' | 'media' | 'alta' | 'critica' | string;
}

export interface TicketAsset {
    id: number;
    serial: string;
    type: string;
    model: string;
    department: string;
    assigned_to: string;
}

export interface TicketComment {
    id: number;
    comment: string;
    is_internal: boolean;
    time_ago: string;
    date: string;
    author: string;
    initial: string;
}

export interface TicketAttachment {
    id: number;
    name: string;
    size: string;
    mime: string;
    url: string;
    date: string;
}

export interface ConsumedMaterial {
    id: number;
    item_name: string;
    code: string;
    quantity: number;
    date: string;
}

export interface TicketRating {
    score: number;
    comment: string;
    date: string | null;
}

export interface LogEntry {
    id: number;
    title: string;
    body: string;
    date: string;
    author: string;
}

export interface TicketDetailData {
    id: number;
    code: string;
    title: string;
    description: string;
    status: string;
    status_label: string;
    priority: string;
    priority_label: string;
    category: string;
    department: string;
    requester: string;
    requester_dept: string;
    tech_id: number;
    tech_name: string;
    tech_initial: string;
    report_date: string;
    close_date: string;
    attention_time: string;
    sla_on_time: boolean;
}

export interface TicketDetail {
    ticket: TicketDetailData;
    asset: TicketAsset;
    comments: TicketComment[];
    attachments: TicketAttachment[];
    materials: ConsumedMaterial[];
    rating: TicketRating;
    log_entries: LogEntry[];
}

export interface TechnicianOption {
    id: number;
    name: string;
    email: string;
    initial: string;
    specialty: string;
    active_tickets: number;
}

export interface EquipmentOption {
    id: number;
    code: string;
    serial: string;
    type: string;
    model: string;
    department: string;
    assigned_to: string;
}

export interface CategoryOption {
    id: number;
    name: string;
}

export interface DepartmentOption {
    id: number;
    name: string;
}

export interface InventoryItemOption {
    id: number;
    code: string;
    name: string;
    stock: number;
}

export interface SupportFormOptions {
    technicians: TechnicianOption[];
    equipments: EquipmentOption[];
    categories: CategoryOption[];
    departments: DepartmentOption[];
    inventory_items: InventoryItemOption[];
}
