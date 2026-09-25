<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useClickOutside } from '@/Composables/useClickOutside';

interface Props {
    align?: 'left' | 'right';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    align: 'right',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'open'): void;
    (e: 'close'): void;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
let lastToggleTimestamp = 0;

const toggle = () => {
    if (props.disabled) return;
    const now = Date.now();
    if (now - lastToggleTimestamp < 200) return;
    lastToggleTimestamp = now;

    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        emit('open');
    } else {
        emit('close');
    }
};

const close = () => {
    if (isOpen.value) {
        isOpen.value = false;
        emit('close');
    }
};

const open = () => {
    if (props.disabled) return;
    if (!isOpen.value) {
        isOpen.value = true;
        emit('open');
    }
};

useClickOutside(dropdownRef, () => {
    close();
});

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isOpen.value) {
        close();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

defineExpose({
    isOpen,
    toggle,
    close,
    open,
});
</script>

<template>
    <div ref="dropdownRef" class="base-dropdown-container">
        <!-- Trigger slot -->
        <div class="dropdown-trigger-wrap" @click.stop="toggle">
            <slot name="trigger" :is-open="isOpen" :toggle="toggle" :close="close" />
        </div>

        <!-- Popover floating content -->
        <Transition name="dropdown-pop">
            <div
                v-if="isOpen"
                class="dropdown-popover"
                :class="`align-${align}`"
                role="menu"
            >
                <slot name="content" :close="close" />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.base-dropdown-container {
    position: relative;
    display: inline-block;
}

.dropdown-trigger-wrap {
    display: inline-flex;
    cursor: pointer;
}

.dropdown-popover {
    position: absolute;
    top: calc(100% + 6px);
    z-index: 1050;
    min-width: 180px;
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-md, 8px);
    padding: var(--space-2, 6px);
    box-shadow: none !important;
}

.align-right {
    right: 0;
    left: auto;
}

.align-left {
    left: 0;
    right: auto;
}

/* Transitions */
.dropdown-pop-enter-active,
.dropdown-pop-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-pop-enter-from {
    opacity: 0;
    transform: translateY(-6px) scale(0.98);
}

.dropdown-pop-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.98);
}
</style>
