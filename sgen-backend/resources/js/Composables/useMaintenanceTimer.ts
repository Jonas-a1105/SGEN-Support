import { ref, computed, onMounted, onUnmounted, type Ref } from 'vue';

export interface MaintenanceTimerStatus {
    label: string;
    class: string;
    text: string;
}

export function useMaintenanceTimer(
    fecha: () => string | null | undefined,
    duracion: () => number | null | undefined,
    estado: () => string
) {
    const currentTime = ref(Date.now());
    let timerInterval: number | null = null;

    onMounted(() => {
        timerInterval = window.setInterval(() => {
            currentTime.value = Date.now();
        }, 1000);
    });

    onUnmounted(() => {
        if (timerInterval !== null) {
            clearInterval(timerInterval);
        }
    });

    const timerStatus = computed<MaintenanceTimerStatus | null>(() => {
        const rawFecha = fecha();
        if (!rawFecha) return null;
        const start = new Date(rawFecha).getTime();
        if (isNaN(start)) return null;

        const durationMin = duracion() || 60;
        const end = start + durationMin * 60 * 1000;
        const now = currentTime.value;
        const currentEstado = estado();

        if (currentEstado === 'completado') {
            return { label: 'Completado', class: 'timer-completed', text: 'Servicio finalizado' };
        }
        if (currentEstado === 'cancelado') {
            return { label: 'Cancelado', class: 'timer-cancelled', text: 'Cancelado' };
        }

        if (now < start) {
            const diffSec = Math.floor((start - now) / 1000);
            const hours = Math.floor(diffSec / 3600);
            const minutes = Math.floor((diffSec % 3600) / 60);
            const seconds = diffSec % 60;
            return {
                label: 'Programado',
                class: 'timer-scheduled',
                text: `Inicia en ${hours}h ${minutes}m ${seconds}s`,
            };
        }

        if (now >= start && now <= end) {
            const diffSec = Math.floor((end - now) / 1000);
            const hours = Math.floor(diffSec / 3600);
            const minutes = Math.floor((diffSec % 3600) / 60);
            const seconds = diffSec % 60;
            return {
                label: 'En curso',
                class: 'timer-running',
                text: `Tiempo restante: ${hours}h ${minutes}m ${seconds}s`,
            };
        }

        return {
            label: 'Vencido',
            class: 'timer-overdue',
            text: 'Plazo expirado',
        };
    });

    return {
        timerStatus,
    };
}
