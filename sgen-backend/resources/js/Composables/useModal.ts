import { ref, readonly } from 'vue';

export function useModal<T = unknown>() {
    const isOpen = ref(false);
    const data = ref<T | null>(null);

    const open = (payload?: T) => {
        data.value = payload ?? null;
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
        data.value = null;
    };

    return {
        isOpen: readonly(isOpen),
        data: readonly(data),
        open,
        close,
    };
}
