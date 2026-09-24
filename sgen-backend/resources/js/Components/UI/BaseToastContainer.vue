<script setup lang="ts">
import { useToast, type ToastItem } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();
</script>

<template>
    <div class="toast-portal-container" aria-live="polite" aria-label="Notificaciones">
        <TransitionGroup name="toast-slide" tag="div" class="toast-stack">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="toast-card"
                :class="`toast-${toast.type}`"
                role="alert"
            >
                <div class="toast-icon-wrap">
                    <!-- Success Icon -->
                    <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>

                    <!-- Error Icon -->
                    <svg v-else-if="toast.type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>

                    <!-- Warning Icon -->
                    <svg v-else-if="toast.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>

                    <!-- Info Icon -->
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                </div>

                <div class="toast-content">
                    <strong class="toast-title">{{ toast.title }}</strong>
                    <p v-if="toast.message" class="toast-message">{{ toast.message }}</p>
                </div>

                <button
                    type="button"
                    class="toast-close-btn"
                    aria-label="Cerrar notificación"
                    @click="removeToast(toast.id)"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-portal-container {
    position: fixed;
    top: var(--space-5, 20px);
    right: var(--space-5, 20px);
    z-index: 9999;
    pointer-events: none;
    max-width: 400px;
    width: 100%;
}

.toast-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-3, 12px);
}

.toast-card {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: var(--space-3, 12px);
    padding: var(--space-4, 14px) var(--space-4, 16px);
    border-radius: var(--radius-md, 10px);
    background: var(--bg-card);
    border: 1px solid var(--stroke);
    box-shadow: none !important;
    transition: all var(--transition-fast, 0.2s ease);
}

.toast-icon-wrap {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-sm, 6px);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.toast-icon-wrap svg {
    width: 18px;
    height: 18px;
}

.toast-success {
    border-left: 4px solid var(--green, #10b981);
}

.toast-success .toast-icon-wrap {
    background: rgba(16, 185, 129, 0.12);
    color: var(--green, #10b981);
}

.toast-error {
    border-left: 4px solid var(--red, #ef4444);
}

.toast-error .toast-icon-wrap {
    background: rgba(239, 68, 68, 0.12);
    color: var(--red, #ef4444);
}

.toast-warning {
    border-left: 4px solid var(--yellow, #f59e0b);
}

.toast-warning .toast-icon-wrap {
    background: rgba(245, 158, 11, 0.12);
    color: var(--yellow, #f59e0b);
}

.toast-info {
    border-left: 4px solid var(--brand, #3b82f6);
}

.toast-info .toast-icon-wrap {
    background: rgba(59, 130, 246, 0.12);
    color: var(--brand, #3b82f6);
}

.toast-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.toast-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.toast-message {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

.toast-close-btn {
    background: transparent;
    border: none;
    color: var(--text-dim);
    cursor: pointer;
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm, 4px);
    transition: color var(--transition-fast, 0.15s ease);
}

.toast-close-btn svg {
    width: 14px;
    height: 14px;
}

.toast-close-btn:hover {
    color: var(--text);
}

/* Animations */
.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.25s ease;
}

.toast-slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.toast-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
