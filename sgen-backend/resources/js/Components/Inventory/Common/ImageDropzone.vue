<script setup lang="ts">
import { ref } from 'vue';

withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        compact?: boolean;
    }>(),
    {
        title: 'Subir Imagen Principal',
        subtitle: 'PNG, JPG hasta 5MB',
        compact: false,
    }
);

const emit = defineEmits<{
    (e: 'file-selected', file: File): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => {
    fileInput.value?.click();
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        emit('file-selected', target.files[0]);
    }
};
</script>

<template>
    <div
        class="upload-drop-placeholder"
        :class="{ 'upload-drop-compact': compact }"
        role="button"
        tabindex="0"
        @click="triggerFileInput"
        @keydown.enter="triggerFileInput"
    >
        <input
            ref="fileInput"
            type="file"
            accept="image/png, image/jpeg, image/webp"
            class="upload-drop-input-hidden"
            @change="onFileChange"
        />
        <svg viewBox="0 0 24 24" class="upload-drop-icon">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="17 8 12 3 7 8" />
            <line x1="12" y1="3" x2="12" y2="15" />
        </svg>
        <div class="upload-drop-copy">
            <strong class="upload-drop-title">{{ title }}</strong>
            <span v-if="subtitle" class="upload-drop-subtitle">{{ subtitle }}</span>
        </div>
    </div>
</template>

<style scoped>
.upload-drop-placeholder {
    border: var(--stroke-w) dashed var(--stroke);
    border-radius: var(--radius-md);
    background: var(--bg-card);
    padding: var(--space-6) var(--space-4);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.upload-drop-placeholder:hover {
    border-color: var(--orange);
    background: var(--stroke-subtle);
}

.upload-drop-compact {
    padding: var(--space-3) var(--space-2) !important;
}

.upload-drop-input-hidden {
    display: none;
}

.upload-drop-icon {
    width: 28px;
    height: 28px;
    stroke: var(--text-muted);
    fill: none;
    stroke-width: 2;
}

.upload-drop-copy {
    text-align: center;
}

.upload-drop-title {
    font-size: 13px;
    display: block;
    color: var(--text);
}

.upload-drop-subtitle {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
    display: block;
}
</style>
