<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BaseButton } from '@/Components/UI';
import type { TicketAttachment } from '@/Types/support';

interface Props {
    ticketId: number;
    attachments: TicketAttachment[];
}

const props = defineProps<Props>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref<string | null>(null);

const triggerFileInput = () => {
    if (isUploading.value) return;
    fileInputRef.value?.click();
};

const uploadFile = (file: File) => {
    errorMessage.value = null;

    if (file.size > 25 * 1024 * 1024) {
        errorMessage.value = 'El archivo supera el límite máximo permitido de 25 MB.';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    isUploading.value = true;
    uploadProgress.value = 0;

    router.post(`/soportes/${props.ticketId}/archivos`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onProgress: (progress) => {
            if (progress.percentage) {
                uploadProgress.value = progress.percentage;
            }
        },
        onSuccess: () => {
            isUploading.value = false;
            uploadProgress.value = 0;
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
        onError: (errors) => {
            isUploading.value = false;
            uploadProgress.value = 0;
            errorMessage.value = (Object.values(errors)[0] as string) || 'Error al subir el archivo.';
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
    });
};

const handleFiles = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    uploadFile(input.files[0]);
};

const handleDrop = (e: DragEvent) => {
    isDragOver.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        uploadFile(e.dataTransfer.files[0]);
    }
};

const deleteFile = (attachmentId: number) => {
    if (!confirm('¿Estás seguro de eliminar este archivo adjunto? Esta acción es irreversible.')) {
        return;
    }
    router.delete(`/soportes/archivos/${attachmentId}`, {
        preserveScroll: true,
    });
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
                <div>
                    <h2 class="panel-title">EVIDENCIAS Y ARCHIVOS ADJUNTOS</h2>
                    <span class="panel-subtitle">Almacenamiento seguro con checksum SHA-256 de integridad</span>
                </div>
            </div>
            <BaseButton
                variant="primary"
                size="sm"
                type="button"
                :disabled="isUploading"
                @click="triggerFileInput"
            >
                <span>{{ isUploading ? 'Subiendo...' : '+ Subir Archivo' }}</span>
            </BaseButton>
            <input
                ref="fileInputRef"
                type="file"
                class="hidden-file-input"
                @change="handleFiles"
            />
        </div>

        <div class="panel-body-pad">
            <!-- ALERTA DE ERROR -->
            <div v-if="errorMessage" class="upload-error-box">
                <svg viewBox="0 0 24 24" class="error-svg">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ errorMessage }}</span>
                <button type="button" class="error-close-btn" @click="errorMessage = null">✕</button>
            </div>

            <!-- PROGRESO DE SUBIDA -->
            <div v-if="isUploading" class="upload-progress-box">
                <div class="progress-info-row">
                    <span class="progress-label">Subiendo archivo al servidor...</span>
                    <span class="progress-val">{{ uploadProgress }}%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" :style="{ width: `${uploadProgress}%` }"></div>
                </div>
            </div>

            <!-- DROPZONE -->
            <div
                :class="['dropzone-container', { dragover: isDragOver, disabled: isUploading }]"
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
                <div class="dropzone-title">Arrastra archivos aquí o haz clic para examinar</div>
                <div class="dropzone-desc">Soporta documentos PDF, imágenes (PNG, JPG, WEBP), ZIP y texto hasta 25 MB.</div>
                <span class="dropzone-link">Seleccionar desde tu equipo</span>
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
                            <span v-if="att.checksum" class="att-checksum" :title="`Integridad SHA-256: ${att.checksum}`">
                                SHA: {{ att.checksum.substring(0, 10) }}...
                            </span>
                        </div>
                    </div>

                    <div class="att-actions">
                        <a :href="att.url" class="att-action-btn" title="Descargar archivo">
                            <svg viewBox="0 0 24 24">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </a>

                        <button
                            type="button"
                            class="att-action-btn delete"
                            title="Eliminar archivo"
                            @click="deleteFile(att.id)"
                        >
                            <svg viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="empty-attachments-note">
                No hay archivos adjuntos en este ticket. Sube capturas o documentos de respaldo.
            </div>
        </div>
    </section>
</template>

<style scoped>
.detail-panel {
    border: var(--stroke-w, 1px) solid var(--stroke, #2d3139);
    background: var(--bg-card, #14161a);
    border-radius: var(--panel-radius, 12px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.panel-header-row {
    padding: 16px 20px;
    border-bottom: var(--stroke-w, 1px) solid var(--stroke-subtle, #1e2229);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.panel-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--text, #f0f2f5);
    letter-spacing: -0.01em;
}

.panel-subtitle {
    font-size: 11px;
    color: var(--text-muted, #8b92a0);
    display: block;
    margin-top: 2px;
}

.panel-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary, #6366f1);
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

.hidden-file-input {
    display: none;
}

.panel-body-pad {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.upload-error-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 8px;
    color: #ef4444;
    font-size: 13px;
}

.error-svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
}

.error-close-btn {
    margin-left: auto;
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 14px;
}

.upload-progress-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 12px 14px;
    background: var(--bg-sub, #1a1d24);
    border: 1px solid var(--stroke, #2d3139);
    border-radius: 8px;
}

.progress-info-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-muted, #8b92a0);
}

.progress-val {
    font-weight: 700;
    color: var(--primary, #6366f1);
}

.progress-bar-bg {
    height: 6px;
    background: var(--stroke, #2d3139);
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: var(--primary, #6366f1);
    transition: width 0.2s ease;
}

.dropzone-container {
    border: var(--stroke-w, 2px) dashed var(--stroke, #2d3139);
    border-radius: var(--panel-radius, 12px);
    padding: 36px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: var(--bg-card, #14161a);
    cursor: pointer;
    transition: all 0.2s ease;
}

.dropzone-container:hover,
.dropzone-container.dragover {
    border-color: var(--primary, #6366f1);
    background: rgba(99, 102, 241, 0.04);
}

.dropzone-container.disabled {
    pointer-events: none;
    opacity: 0.6;
}

.dropzone-icon-circle {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary, #6366f1);
    display: grid;
    place-items: center;
    margin-bottom: 12px;
}

.dropzone-icon-circle svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.8;
}

.dropzone-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text, #f0f2f5);
    margin-bottom: 4px;
}

.dropzone-desc {
    font-size: 12px;
    color: var(--text-muted, #8b92a0);
    margin-bottom: 10px;
}

.dropzone-link {
    color: var(--primary, #6366f1);
    font-size: 12px;
    border-bottom: 1px solid var(--primary, #6366f1);
    padding-bottom: 1px;
    font-weight: 600;
}

.attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
}

.attachment-item-card {
    border: var(--stroke-w, 1px) solid var(--stroke, #2d3139);
    background: var(--bg-sub, #1a1d24);
    border-radius: 10px;
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
    color: var(--primary, #6366f1);
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
    gap: 2px;
}

.att-name {
    font-size: 13px;
    color: var(--text, #f0f2f5);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
}

.att-size {
    font-size: 11px;
    color: var(--text-muted, #8b92a0);
}

.att-checksum {
    font-size: 10px;
    font-family: monospace;
    color: var(--text-muted, #6b7280);
    background: rgba(255, 255, 255, 0.05);
    padding: 1px 4px;
    border-radius: 4px;
    width: fit-content;
}

.att-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.att-action-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid var(--stroke, #2d3139);
    background: transparent;
    color: var(--text-muted, #8b92a0);
    display: grid;
    place-items: center;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.18s ease;
}

.att-action-btn:hover {
    color: var(--text, #f0f2f5);
    border-color: var(--primary, #6366f1);
    background: var(--stroke-subtle, #1e2229);
}

.att-action-btn.delete:hover {
    color: #ef4444;
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.att-action-btn svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.empty-attachments-note {
    text-align: center;
    padding: 24px;
    font-size: 13px;
    color: var(--text-muted, #8b92a0);
}
</style>
