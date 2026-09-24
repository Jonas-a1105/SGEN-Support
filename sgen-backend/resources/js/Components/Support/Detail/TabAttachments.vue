<script setup lang="ts">
import { ref } from 'vue';
import type { TicketAttachment } from '@/types/support';

interface Props {
    attachments: TicketAttachment[];
}

const props = defineProps<Props>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const handleFiles = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    // In a real app we can upload via Inertia/axios. For now, add preview or notify
    const file = input.files[0];
    props.attachments.push({
        id: Date.now(),
        name: file.name,
        size: `${Math.round(file.size / 1024)} KB`,
        mime: file.type || 'application/octet-stream',
        url: URL.createObjectURL(file),
        date: new Date().toLocaleDateString('es-ES'),
    });
};

const handleDrop = (e: DragEvent) => {
    isDragOver.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        const file = e.dataTransfer.files[0];
        props.attachments.push({
            id: Date.now(),
            name: file.name,
            size: `${Math.round(file.size / 1024)} KB`,
            mime: file.type || 'application/octet-stream',
            url: URL.createObjectURL(file),
            date: new Date().toLocaleDateString('es-ES'),
        });
    }
};
</script>

<template>
    <section class="detail-panel">
        <div class="panel-header-row">
            <div class="panel-title-group">
                <div class="panel-icon-pill" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                    </svg>
                </div>
                <h2 class="panel-title">EVIDENCIAS ADJUNTAS</h2>
            </div>
            <button class="btn-upload" type="button" @click="triggerFileInput">
                <span>+</span> Subir Archivo
            </button>
            <input
                ref="fileInputRef"
                type="file"
                multiple
                class="hidden-file-input"
                @change="handleFiles"
            />
        </div>

        <div class="panel-body-pad">
            <div
                :class="['dropzone-container', { dragover: isDragOver }]"
                @dragover.prevent="isDragOver = true"
                @dragleave="isDragOver = false"
                @drop.prevent="handleDrop"
                @click="triggerFileInput"
            >
                <div class="dropzone-icon-circle">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
                <div class="dropzone-title">Arrastra archivos aquí</div>
                <div class="dropzone-desc">Soporta imágenes (PNG, JPG) y documentos PDF hasta 10MB.</div>
                <span class="dropzone-link">O selecciona desde tu ordenador</span>
            </div>

            <!-- GRILLA DE ARCHIVOS -->
            <div v-if="attachments.length > 0" class="attachments-grid">
                <div v-for="att in attachments" :key="att.id" class="attachment-item-card">
                    <div class="att-file-info">
                        <div class="att-icon">
                            <svg viewBox="0 0 24 24" class="file-svg">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <div class="att-meta">
                            <span class="att-name" :title="att.name">{{ att.name }}</span>
                            <span class="att-size">{{ att.size }} • {{ att.date }}</span>
                        </div>
                    </div>

                    <a :href="att.url" target="_blank" download class="att-download-btn" title="Descargar archivo">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-card);
    border-radius: var(--panel-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.panel-header-row {
    padding: 18px 22px;
    border-bottom: var(--stroke-w) solid var(--stroke-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.panel-title {
    margin: 0;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--text);
    letter-spacing: -0.01em;
}
.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.panel-icon-pill svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.btn-upload {
    height: 36px;
    padding: 0 14px;
    background: var(--primary);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: opacity 0.2s ease;
}
.btn-upload:hover {
    opacity: 0.9;
}
.hidden-file-input {
    display: none;
}
.panel-body-pad {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.dropzone-container {
    border: var(--stroke-w) dashed var(--stroke);
    border-radius: var(--panel-radius);
    padding: 48px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: var(--bg-card);
    cursor: pointer;
    transition: all 0.2s ease;
}
.dropzone-container:hover,
.dropzone-container.dragover {
    border-color: var(--primary);
    background: var(--stroke-subtle);
}
.dropzone-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    margin-bottom: 14px;
}
.dropzone-icon-circle svg {
    width: 30px;
    height: 30px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}
.dropzone-title {
    font-size: 15px;
    font-weight: 700 !important;
    color: var(--text);
    margin-bottom: 4px;
}
.dropzone-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.dropzone-link {
    color: var(--primary);
    font-size: 13px;
    border-bottom: 1.5px solid var(--primary);
    padding-bottom: 1px;
    cursor: pointer;
    font-weight: 600;
}
.attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
}
.attachment-item-card {
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.att-file-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.att-icon {
    color: var(--primary);
    flex-shrink: 0;
}
.file-svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.att-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.att-name {
    font-size: 13px;
    color: var(--text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
}
.att-size {
    font-size: 11px;
    color: var(--text-muted);
}
.att-download-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1px solid var(--stroke);
    background: transparent;
    color: var(--text-muted);
    display: grid;
    place-items: center;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.18s ease;
    flex-shrink: 0;
}
.att-download-btn:hover {
    color: var(--text);
    border-color: var(--stroke-hover);
    background: var(--stroke-subtle);
}
.att-download-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
</style>
