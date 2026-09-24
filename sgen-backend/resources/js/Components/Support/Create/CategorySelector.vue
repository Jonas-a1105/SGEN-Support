<script setup lang="ts">
import type { FormCategory } from './types';

defineProps<{
    modelValue: number;
    categories?: FormCategory[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', id: number): void;
}>();

function getCategoryIcon(catName: string): string {
    const name = catName.toLowerCase();
    if (name.includes('hardware') || name.includes('servidor')) return 'hardware';
    if (name.includes('software') || name.includes('sistema')) return 'software';
    if (name.includes('red') || name.includes('internet') || name.includes('wifi')) return 'network';
    if (name.includes('impresora') || name.includes('scanner')) return 'printer';
    if (name.includes('correo') || name.includes('email')) return 'email';
    return 'periferico';
}
</script>

<template>
    <div>
        <label class="tf-field-label">Categoría del Problema</label>
        <div class="tf-category-grid">
            <button
                v-for="cat in categories"
                :key="cat.id"
                type="button"
                class="tf-category-btn"
                :class="{ active: modelValue === cat.id }"
                @click="emit('update:modelValue', cat.id)"
            >
                <div class="tf-cat-icon">
                    <svg v-if="getCategoryIcon(cat.name) === 'hardware'" viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2" />
                        <rect x="9" y="9" width="6" height="6" />
                        <line x1="9" y1="1" x2="9" y2="4" />
                        <line x1="15" y1="1" x2="15" y2="4" />
                        <line x1="9" y1="20" x2="9" y2="23" />
                        <line x1="15" y1="20" x2="15" y2="23" />
                    </svg>
                    <svg v-else-if="getCategoryIcon(cat.name) === 'software'" viewBox="0 0 24 24">
                        <polyline points="16 18 22 12 16 6" />
                        <polyline points="8 6 2 12 8 18" />
                    </svg>
                    <svg v-else-if="getCategoryIcon(cat.name) === 'network'" viewBox="0 0 24 24">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                        <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                        <line x1="12" y1="20" x2="12.01" y2="20" />
                    </svg>
                    <svg v-else-if="getCategoryIcon(cat.name) === 'printer'" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" />
                    </svg>
                    <svg v-else-if="getCategoryIcon(cat.name) === 'email'" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                </div>
                <span>{{ cat.name }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.tf-field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #8e9199);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 8px;
}

.tf-category-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.tf-category-btn {
    height: 40px;
    padding: 0 10px;
    background: var(--bg-sub, #1e2024);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted, #8e9199);
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: none !important;
}

.tf-category-btn:hover {
    border-color: var(--stroke, #31343a);
    color: var(--text, #f4f4f6);
}

.tf-category-btn.active {
    background: rgba(37, 99, 235, 0.12);
    border-color: var(--orange, #2563eb);
    color: var(--text, #f4f4f6);
}

.tf-cat-icon {
    display: grid;
    place-items: center;
    color: currentColor;
    flex-shrink: 0;
}

.tf-cat-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
