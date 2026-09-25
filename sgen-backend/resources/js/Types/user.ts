/**
 * Contratos de tipo TypeScript para el Módulo de Usuarios
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export interface UserItem {
    id: number;
    username: string;
    rol: string;
    rol_label: string;
    departamento_id: number | null;
    departamento_nombre: string;
    empleado_id: number | null;
    empleado_nombre: string | null;
    empleado_email: string | null;
    avatar_initials: string;
    is_verified_employee: boolean;
}

export interface UserKpis {
    total_users: number;
    admins_count: number;
    techs_count: number;
}

export interface UserFormData {
    id?: number;
    username: string;
    password?: string;
    rol: string;
    departamento_id: number | null;
    empleado_id: number | null;
}

export type UserRole = 'admin' | 'tecnico' | 'usuario';
