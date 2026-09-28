<script setup lang="ts">
import BaseCard from '@/Components/UI/BaseCard.vue';
import BaseBadge from '@/Components/UI/BaseBadge.vue';

interface ComentarioPublico {
    id: number;
    autor: string;
    mensaje: string;
    fecha: string;
}

interface TicketPortal {
    codigo: string;
    titulo: string;
    estado: string;
    prioridad: string;
    equipo: string;
    fecha_creacion: string;
    ultima_actualizacion: string;
    solucion: string | null;
}

defineProps<{ ticket: TicketPortal; comentarios_publicos: ComentarioPublico[] }>();

const estadoBadgeVariant = (estado: string): 'warning' | 'info' | 'success' | 'danger' | 'neutral' => {
    const mapa: Record<string, 'warning' | 'info' | 'success' | 'danger' | 'neutral'> = {
        pendiente: 'info',
        en_proceso: 'warning',
        en_espera: 'neutral',
        resuelto: 'success',
        cerrado: 'neutral',
        cancelado: 'danger',
    };
    return mapa[estado] ?? 'neutral';
};

const etiquetaPrioridad: Record<string, string> = {
    critica: 'Crítica',
    alta: 'Alta',
    media: 'Media',
    baja: 'Baja',
};
</script>

<template>
    <div class="portal-estado">
        <div class="portal-container">
            <header class="portal-hero">
                <div class="brand-pill">SGEN · Portal del cliente</div>
                <h1>{{ ticket.titulo }}</h1>
                <p class="sub">Referencia {{ ticket.codigo }} · seguimiento sin registro</p>
            </header>

            <BaseCard padding="lg">
                <div class="info-grid">
                    <div class="info-elem">
                        <span class="info-label">Estado actual</span>
                        <BaseBadge :variant="estadoBadgeVariant(ticket.estado)">{{ ticket.estado.replace(/_/g, ' ') }}</BaseBadge>
                    </div>
                    <div class="info-elem">
                        <span class="info-label">Prioridad</span>
                        <span class="info-value">{{ etiquetaPrioridad[ticket.prioridad] ?? ticket.prioridad }}</span>
                    </div>
                    <div class="info-elem">
                        <span class="info-label">Equipo / materia</span>
                        <span class="info-value">{{ ticket.equipo }}</span>
                    </div>
                    <div class="info-elem">
                        <span class="info-label">Registrado</span>
                        <span class="info-value">{{ ticket.fecha_creacion }}</span>
                    </div>
                    <div class="info-elem">
                        <span class="info-label">Última actualización</span>
                        <span class="info-value">{{ ticket.ultima_actualizacion }}</span>
                    </div>
                </div>

                <div v-if="ticket.solucion" class="solucion-spot">
                    <h4>Solución aplicada</h4>
                    <p>{{ ticket.solucion }}</p>
                </div>

                <div class="comentarios-trail">
                    <h4>Actualizaciones (públicas)</h4>
                    <ul v-if="comentarios_publicos.length > 0" class="trail-list">
                        <li v-for="c in comentarios_publicos" :key="c.id" class="trail-item">
                            <div class="trail-head">
                                <strong>{{ c.autor }}</strong>
                                <span class="trail-fecha">{{ c.fecha }}</span>
                            </div>
                            <p>{{ c.mensaje }}</p>
                        </li>
                    </ul>
                    <p v-else class="traillist-empty">Aún no hay mensajes públicos para este ticket. Te avisaremos por correo con cada movimiento.</p>
                </div>
            </BaseCard>

            <footer class="portal-footer">
                Si necesitas más detalles, contacta al personal de TI. El enlace es privado a esta conversación.
            </footer>
        </div>
    </div>
</template>

<style scoped>
.portal-estado { min-height: 100vh; background: radial-gradient(circle at top, rgba(61, 155, 255, 0.08), transparent 55%), #0c101d; padding: 48px 16px 32px; }
.portal-container { max-width: 720px; margin: 0 auto; }
.portal-hero { margin-bottom: 22px; }
.brand-pill { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 999px; color: #9ca3af; margin-bottom: 12px; }
.portal-hero h1 { margin: 0 0 4px; font-size: 22px; color: #f4f5f7; }
.portal-hero .sub { margin: 0; color: #7b8398; font-size: 13px; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin-bottom: 14px; }
.info-elem { display: flex; flex-direction: column; gap: 4px; }
.info-label { font-size: 11px; letter-spacing: 0.05em; color: #8b93a7; }
.info-value { font-size: 14px; color: #e3e7ee; font-weight: 600; }
.solucion-spot { margin: 14px 0; padding: 12px 14px; border-radius: 12px; background: rgba(16, 185, 129, 0.09); border: 1px solid rgba(16, 185, 129, 0.22); }
.solucion-spot h4 { margin: 0 0 6px; font-size: 13px; color: #34d399; }
.solucion-spot p { margin: 0; font-size: 13px; color: #d7d9e0; line-height: 1.6; }
.comentarios-trail h4 { margin: 10px 0 8px; font-size: 12px; letter-spacing: 0.05em; color: #8b93a7; }
.trail-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.trail-item { padding: 10px 12px; border-radius: 10px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.06); }
.trail-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; }
.trail-head strong { font-size: 13px; color: #e6e9ef; }
.trail-fecha { font-size: 11px; color: #7b8398; }
.trail-item p { margin: 0; font-size: 13px; color: #d7d9e0; line-height: 1.5; }
.traillist-empty { margin: 0; color: #7b8398; font-size: 13px; }
.portal-footer { margin-top: 12px; font-size: 11.5px; color: #7b8398; text-align: center; }
</style>
