<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';

interface Props {
    isOpen: boolean;
    title?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: false,
    title: '',
    maxWidth: 'md',
});

const emit = defineEmits<{ (e: 'close'): void }>();

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.isOpen) emit('close');
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="isOpen" class="modal-backdrop" @click.self="emit('close')">
                <div :class="['modal-container', `max-w-${maxWidth}`]">
                    <header class="modal-header">
                        <h3 class="modal-title">{{ title }}</h3>
                        <button type="button" class="modal-close-btn" @click="emit('close')">✕</button>
                    </header>
                    <div class="modal-body">
                        <slot />
                    </div>
                    <footer v-if="$slots.footer" class="modal-footer">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed; inset: 0; z-index: 70; display: flex; align-items: center;
    justify-content: center; padding: 16px; background: rgba(0, 0, 0, 0.75);
}
.modal-container {
    width: 100%; background: var(--bg-card); border-radius: var(--panel-radius);
    border: var(--stroke-w) solid var(--stroke); display: flex; flex-direction: column; overflow: hidden;
}
.max-w-sm { max-width: 24rem; }
.max-w-md { max-width: 32rem; }
.max-w-lg { max-width: 42rem; }
.max-w-xl { max-width: 48rem; }
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px; border-bottom: var(--stroke-w) solid var(--stroke-subtle);
}
.modal-title { margin: 0; font-size: 15px !important; font-weight: 700 !important; color: var(--text); }
.modal-close-btn {
    background: transparent; border: 1px solid var(--stroke); color: var(--text-muted);
    cursor: pointer; width: 28px; height: 28px; border-radius: 8px; display: grid; place-items: center;
}
.modal-close-btn:hover { color: var(--text); border-color: var(--stroke-hover); background: var(--stroke-subtle); }
.modal-body { padding: 22px; overflow-y: auto; max-height: calc(85vh - 120px); }
.modal-footer {
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
    padding: 14px 22px; background: var(--bg-sub); border-top: var(--stroke-w) solid var(--stroke-subtle);
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
