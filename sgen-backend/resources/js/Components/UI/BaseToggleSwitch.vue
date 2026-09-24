<script setup lang="ts">
interface Props {
    modelValue?: boolean;
    title?: string;
    label?: string;
}

withDefaults(defineProps<Props>(), {
    modelValue: false,
    title: 'Alternar',
    label: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'change', value: boolean): void;
}>();

const handleChange = (e: Event) => {
    const checked = (e.target as HTMLInputElement).checked;
    emit('update:modelValue', checked);
    emit('change', checked);
};
</script>

<template>
    <label class="base-toggle-switch" :title="title">
        <input
            type="checkbox"
            :checked="modelValue"
            @change="handleChange"
            class="switch-input"
        />
        <div class="switch-rail">
            <div class="switch-dot"></div>
        </div>
        <span v-if="label" class="switch-label">{{ label }}</span>
    </label>
</template>

<style scoped>
.base-toggle-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
}

.switch-input {
    display: none;
}

.switch-rail {
    width: 38px;
    height: 22px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 20px;
    position: relative;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.switch-dot {
    width: 14px;
    height: 14px;
    background: var(--text-muted);
    border-radius: 50%;
    position: absolute;
    top: 2px;
    left: 2px;
    transition: transform 0.2s ease, background 0.2s ease;
}

.switch-input:checked + .switch-rail {
    background: var(--brand, var(--blue, #2563eb));
    border-color: var(--brand, var(--blue, #2563eb));
}

.switch-input:checked + .switch-rail .switch-dot {
    transform: translateX(16px);
    background: #ffffff;
}

.switch-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
}

.base-toggle-switch:hover .switch-label {
    color: var(--text);
}
</style>
