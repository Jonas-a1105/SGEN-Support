import { onMounted, onUnmounted, type Ref } from 'vue';

/**
 * Invokes callback when a pointer or touch event occurs outside the target element.
 */
export function useClickOutside(
    elementRef: Ref<HTMLElement | null | undefined>,
    callback: (event: MouseEvent | TouchEvent) => void
): void {
    const listener = (event: MouseEvent | TouchEvent) => {
        const el = elementRef.value;
        if (!el) return;
        const target = event.target as Node | null;
        if (!target) return;

        // If target is inside the element, do nothing
        if (el.contains(target)) return;

        callback(event);
    };

    onMounted(() => {
        window.addEventListener('pointerdown', listener, { passive: true });
    });

    onUnmounted(() => {
        window.removeEventListener('pointerdown', listener);
    });
}
