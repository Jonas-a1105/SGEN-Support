export interface FormEquipment {
    id: number;
    code: string | null;
    serial: string | null;
    type: string;
    model: string;
    department: string;
    assigned_to: string;
}

export interface FormCategory {
    id: number;
    name: string;
}

export interface FormTechnician {
    id: number;
    name: string;
    email: string;
    initial: string;
    specialty: string;
    active_tickets: number;
}

export type TicketPriority = 'baja' | 'media' | 'alta' | 'critica';
