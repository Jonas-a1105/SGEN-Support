<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';

interface Props {
    isOpen?: boolean;
    show?: boolean;
    modelValue?: boolean;
    title?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: undefined,
    show: undefined,
    modelValue: undefined,
    title: '',
    maxWidth: 'md',
});

const isVisible = computed(() => {
    if (props.isOpen !== undefined) return props.isOpen;
    if (props.show !== undefined) return props.show;
    if (props.modelValue !== undefined) return props.modelValue;
    return false;
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:modelValue', value: boolean): void;
}>();

const handleClose = () => {
    emit('close');
    emit('update:modelValue', false);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isVisible.value) handleClose();
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="isVisible" class="base-modal-backdrop" @click.self="handleClose">
                <div :class="['base-modal-container', `base-modal-w-${maxWidth}`]">
                    <header class="base-modal-header">
                        <h3 class="base-modal-title">{{ title }}</h3>
                        <button type="button" class="base-modal-close-btn" @click="handleClose">✕</button>
                    </header>
                    <div class="base-modal-body">
                        <slot />
                    </div>
                    <footer v-if="$slots.footer" class="base-modal-footer">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.base-modal-backdrop {
    position: fixed; inset: 0; z-index: 70; display: flex; align-items: center;
    justify-content: center; padding: 16px; background: rgba(0, 0, 0, 0.75);
}
.base-modal-container {
    width: 100%; background: var(--bg-card); border-radius: var(--panel-radius);
    border: var(--stroke-w) solid var(--stroke); display: flex; flex-direction: column; overflow: hidden;
}
.base-modal-w-sm { max-width: 24rem; }
.base-modal-w-md { max-width: 32rem; }
.base-modal-w-lg { max-width: 42rem; }
.base-modal-w-xl { max-width: 48rem; }
.base-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px; border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}
.base-modal-title { margin: 0; font-size: 15px !important; font-weight: 700 !important; color: var(--text); }
.base-modal-close-btn {
    background: transparent; border: 1px solid var(--stroke); color: var(--text-muted);
    cursor: pointer; width: 28px; height: 28px; border-radius: 8px; display: grid; place-items: center;
}
.base-modal-close-btn:hover { color: var(--text); border-color: var(--stroke-hover); background: var(--stroke-subtle); }
.base-modal-body { padding: 22px; overflow-y: auto; max-height: calc(85vh - 120px); }
.base-modal-footer {
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
    padding: 14px 22px; background: var(--bg-sub); border-top: var(--stroke-w) solid var(--stroke-subtle);
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
