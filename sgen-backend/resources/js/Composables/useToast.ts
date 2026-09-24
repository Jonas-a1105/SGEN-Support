import { ref, readonly } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface ToastItem {
    id: string;
    type: ToastType;
    title: string;
    message?: string;
    duration?: number;
}

const toasts = ref<ToastItem[]>([]);
let counter = 0;

export function useToast() {
    const addToast = (payload: {
        type?: ToastType;
        title: string;
        message?: string;
        duration?: number;
    }): string => {
        const id = `toast-${Date.now()}-${++counter}`;
        const duration = payload.duration ?? 4000;

        const newToast: ToastItem = {
            id,
            type: payload.type ?? 'info',
            title: payload.title,
            message: payload.message,
            duration,
        };

        toasts.value.push(newToast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    };

    const removeToast = (id: string): void => {
        const idx = toasts.value.findIndex(t => t.id === id);
        if (idx !== -1) {
            toasts.value.splice(idx, 1);
        }
    };

    const success = (title: string, message?: string, duration?: number): string => {
        return addToast({ type: 'success', title, message, duration });
    };

    const error = (title: string, message?: string, duration?: number): string => {
        return addToast({ type: 'error', title, message, duration: duration ?? 5000 });
    };

    const warning = (title: string, message?: string, duration?: number): string => {
        return addToast({ type: 'warning', title, message, duration });
    };

    const info = (title: string, message?: string, duration?: number): string => {
        return addToast({ type: 'info', title, message, duration });
    };

    return {
        toasts: readonly(toasts),
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    };
}
