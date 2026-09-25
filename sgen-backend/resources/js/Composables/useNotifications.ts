import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import type { NotificationItem } from '@/Types/notification';

/**
 * Composable useNotifications
 * Encapsula el estado reactivo, consumo de endpoints HTTP y ciclo de polling
 * del módulo de notificaciones. Desacopla la lógica de datos de la interfaz visual.
 *
 * Emplea estado compartido a nivel de módulo (singleton) y sensor de visibilidad (visibilitychange)
 * para suspender el polling cuando la pestaña está en segundo plano y reactivarlo al enfocar.
 */
const unreadCount = ref<number>(0);
const notifications = ref<NotificationItem[]>([]);
const isLoading = ref<boolean>(false);
let pollingTimer: ReturnType<typeof setInterval> | null = null;
let activeSubscribers = 0;

export function useNotifications(autoPoll = true, pollingIntervalMs = 45000) {
    const fetchUnreadCount = async (): Promise<void> => {
        try {
            const response = await axios.get<{ count: number }>('/notificaciones/count');
            unreadCount.value = response.data.count;
        } catch {
            // Silencioso ante expiración de sesión o error de red puntual
        }
    };

    const fetchNotifications = async (): Promise<void> => {
        isLoading.value = true;
        try {
            const response = await axios.get<NotificationItem[]>('/notificaciones');
            notifications.value = response.data;
        } catch {
            // Silencioso ante fallos temporales de red
        } finally {
            isLoading.value = false;
        }
    };

    const markAsRead = async (item: NotificationItem): Promise<void> => {
        if (item.leido) return;

        try {
            await axios.patch(`/notificaciones/${item.id}/read`);
            item.leido = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch {
            // Fallback silencioso
        }
    };

    const markAllAsRead = async (): Promise<void> => {
        try {
            await axios.post('/notificaciones/mark-all-read');
            unreadCount.value = 0;
            notifications.value.forEach((n) => {
                n.leido = true;
            });
        } catch {
            // Fallback silencioso
        }
    };

    const startPolling = (intervalMs = pollingIntervalMs): void => {
        stopPolling();
        pollingTimer = setInterval(fetchUnreadCount, intervalMs);
    };

    const stopPolling = (): void => {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    };

    const handleVisibilityChange = (): void => {
        if (document.visibilityState === 'visible') {
            fetchUnreadCount();
            startPolling();
        } else {
            stopPolling();
        }
    };

    const handleWindowFocus = (): void => {
        fetchUnreadCount();
    };

    onMounted(() => {
        activeSubscribers++;
        if (activeSubscribers === 1) {
            fetchUnreadCount();
            if (autoPoll) {
                startPolling();
            }
            if (typeof document !== 'undefined') {
                document.addEventListener('visibilitychange', handleVisibilityChange);
                window.addEventListener('focus', handleWindowFocus);
            }
        }
    });

    onUnmounted(() => {
        activeSubscribers = Math.max(0, activeSubscribers - 1);
        if (activeSubscribers === 0) {
            stopPolling();
            if (typeof document !== 'undefined') {
                document.removeEventListener('visibilitychange', handleVisibilityChange);
                window.removeEventListener('focus', handleWindowFocus);
            }
        }
    });

    return {
        unreadCount,
        notifications,
        isLoading,
        fetchUnreadCount,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        startPolling,
        stopPolling,
    };
}
