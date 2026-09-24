<script setup lang="ts">
import { computed } from 'vue';
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';

interface Props {
    isOpen: boolean;
    title?: string;
    message?: string;
    itemName?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'info';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Confirmar Eliminación',
    message: '¿Estás seguro de que deseas realizar esta acción? Esta operación no se puede deshacer.',
    itemName: '',
    confirmLabel: 'Confirmar Eliminación',
    cancelLabel: 'Cancelar',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();

const variantIconColor = computed(() => {
    switch (props.variant) {
        case 'warning':
            return 'var(--yellow)';
        case 'info':
            return 'var(--blue)';
        case 'danger':
        default:
            return 'var(--red)';
    }
});

const variantBgColor = computed(() => {
    switch (props.variant) {
        case 'warning':
            return 'rgba(217, 119, 6, 0.12)';
        case 'info':
            return 'rgba(37, 99, 235, 0.12)';
        case 'danger':
        default:
            return 'rgba(220, 38, 38, 0.12)';
    }
});
</script>

<template>
    <BaseModal
        :is-open="isOpen"
        :title="title"
        max-width="sm"
        @close="emit('close')"
    >
        <div class="confirm-body">
            <div class="confirm-icon-box" :class="variant">
                <!-- Danger Icon (Trash) -->
                <svg v-if="variant === 'danger'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>

                <!-- Warning Icon (Alert Triangle) -->
                <svg v-else-if="variant === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>

                <!-- Info Icon (Help Circle) -->
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>

            <div class="confirm-content">
                <p class="confirm-message">
                    <span v-if="itemName">
                        ¿Estás seguro de que deseas eliminar <strong class="item-highlight">{{ itemName }}</strong>?
                    </span>
                    <span v-else>
                        {{ message }}
                    </span>
                </p>
                <p v-if="itemName && message" class="confirm-submessage">
                    {{ message }}
                </p>
            </div>
        </div>

        <template #footer>
            <BaseButton
                variant="secondary"
                size="md"
                :disabled="loading"
                @click="emit('close')"
            >
                {{ cancelLabel }}
            </BaseButton>
            <BaseButton
                :variant="variant === 'danger' ? 'danger' : 'primary'"
                size="md"
                :loading="loading"
                @click="emit('confirm')"
            >
                {{ confirmLabel }}
            </BaseButton>
        </template>
    </BaseModal>
</template>

<style scoped>
.confirm-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 16px;
    padding: 8px 0;
}

.confirm-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 9999px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.confirm-icon-box.danger {
    background: rgba(220, 38, 38, 0.12);
    color: var(--red);
    border: var(--stroke-w) solid rgba(220, 38, 38, 0.25);
}

.confirm-icon-box.warning {
    background: rgba(217, 119, 6, 0.12);
    color: var(--yellow);
    border: var(--stroke-w) solid rgba(217, 119, 6, 0.25);
}

.confirm-icon-box.info {
    background: rgba(37, 99, 235, 0.12);
    color: var(--blue);
    border: var(--stroke-w) solid rgba(37, 99, 235, 0.25);
}

.confirm-icon-box svg {
    width: 24px;
    height: 24px;
}

.confirm-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.confirm-message {
    margin: 0;
    font-size: 14px;
    color: var(--text);
    line-height: 1.5;
}

.item-highlight {
    color: var(--text);
    font-weight: 700;
}

.confirm-submessage {
    margin: 0;
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.4;
}
</style>
