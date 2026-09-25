/**
 * Contratos de tipo TypeScript para el Módulo de Categorías de Tickets
 * @see docs/ARQUITECTURA_Y_PATRONES.md (Regla 6)
 */

export interface CategoryItem {
    id: number;
    nombre: string;
    descripcion: string | null;
    icono: string;
    color: string;
    activo: boolean;
    total_tickets: number;
}

export interface CategoryKpis {
    total_activas: number;
    hardware_stat: string;
    red_stat: string;
}
