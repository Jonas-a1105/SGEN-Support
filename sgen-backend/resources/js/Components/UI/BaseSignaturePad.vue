<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        height?: number;
        strokeColor?: string;
        lineWidth?: number;
        label?: string;
        helperText?: string;
    }>(),
    {
        modelValue: null,
        height: 180,
        strokeColor: '#1e293b',
        lineWidth: 2.5,
        label: 'Firma de Conformidad del Solicitante',
        helperText: 'Dibuje la firma con el dedo o puntero sobre la línea punteada.',
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', val: string | null): void;
    (e: 'change', val: string | null): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);
const isDrawing = ref(false);
const hasSignature = ref(false);
let ctx: CanvasRenderingContext2D | null = null;
let resizeObserver: ResizeObserver | null = null;

const setupCanvas = () => {
    const canvas = canvasRef.value;
    const container = containerRef.value;
    if (!canvas || !container) return;

    ctx = canvas.getContext('2d');
    if (!ctx) return;

    const dpr = window.devicePixelRatio || 1;
    const rect = container.getBoundingClientRect();
    const width = rect.width || 400;

    canvas.width = width * dpr;
    canvas.height = props.height * dpr;
    canvas.style.width = `${width}px`;
    canvas.style.height = `${props.height}px`;

    ctx.scale(dpr, dpr);
    ctx.strokeStyle = props.strokeColor;
    ctx.lineWidth = props.lineWidth;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    // Si ya había una firma previa en base64, renderizarla
    if (props.modelValue && props.modelValue.startsWith('data:image')) {
        const img = new Image();
        img.onload = () => {
            ctx?.drawImage(img, 0, 0, width, props.height);
            hasSignature.value = true;
        };
        img.src = props.modelValue;
    }
};

const getCanvasCoordinates = (e: MouseEvent | Touch): { x: number; y: number } => {
    const canvas = canvasRef.value;
    if (!canvas) return { x: 0, y: 0 };
    const rect = canvas.getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top,
    };
};

const startDrawing = (e: MouseEvent | TouchEvent) => {
    e.preventDefault();
    if (!ctx || !canvasRef.value) return;

    isDrawing.value = true;
    const point = 'touches' in e ? e.touches[0] : e;
    const { x, y } = getCanvasCoordinates(point);

    ctx.beginPath();
    ctx.moveTo(x, y);
};

const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value || !ctx || !canvasRef.value) return;
    e.preventDefault();

    const point = 'touches' in e ? e.touches[0] : e;
    const { x, y } = getCanvasCoordinates(point);

    ctx.lineTo(x, y);
    ctx.stroke();
    hasSignature.value = true;
};

const stopDrawing = () => {
    if (!isDrawing.value) return;
    isDrawing.value = false;

    if (canvasRef.value && hasSignature.value) {
        const dataUrl = canvasRef.value.toDataURL('image/png');
        emit('update:modelValue', dataUrl);
        emit('change', dataUrl);
    }
};

const clearSignature = () => {
    const canvas = canvasRef.value;
    if (!canvas || !ctx) return;

    const dpr = window.devicePixelRatio || 1;
    ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    hasSignature.value = false;
    emit('update:modelValue', null);
    emit('change', null);
};

onMounted(() => {
    setupCanvas();
    if (containerRef.value && typeof ResizeObserver !== 'undefined') {
        resizeObserver = new ResizeObserver(() => {
            // Evitar redimensionar si ya se está firmando
            if (!isDrawing.value) {
                setupCanvas();
            }
        });
        resizeObserver.observe(containerRef.value);
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (!newVal) {
            clearSignature();
        }
    }
);
</script>

<template>
    <div class="signature-pad-wrapper" ref="containerRef">
        <div class="signature-header">
            <label class="signature-label">{{ label }}</label>
            <button
                v-if="hasSignature"
                type="button"
                class="btn-clear-signature"
                @click="clearSignature"
                title="Borrar y firmar de nuevo"
            >
                <svg viewBox="0 0 24 24" class="clear-icon">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>Borrar firma</span>
            </button>
        </div>

        <div class="signature-canvas-box">
            <canvas
                ref="canvasRef"
                class="signature-canvas"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawing"
                @touchmove="draw"
                @touchend="stopDrawing"
            ></canvas>

            <div v-if="!hasSignature" class="signature-placeholder-guideline">
                <span class="guideline-text">Firme aquí</span>
                <div class="guideline-line"></div>
            </div>
        </div>

        <p v-if="helperText" class="signature-helper-text">
            {{ helperText }}
        </p>
    </div>
</template>

<style scoped>
.signature-pad-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

.signature-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.signature-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.btn-clear-signature {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: transparent;
    border: none;
    color: var(--color-red, #ef4444);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: background 0.15s ease;
}

.btn-clear-signature:hover {
    background: rgba(239, 68, 68, 0.1);
}

.clear-icon {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}

.signature-canvas-box {
    position: relative;
    border: 1.5px dashed var(--stroke, #cbd5e1);
    border-radius: var(--panel-radius, 10px);
    background: #ffffff;
    cursor: crosshair;
    overflow: hidden;
    touch-action: none;
    user-select: none;
}

.signature-canvas {
    display: block;
    width: 100%;
}

.signature-placeholder-guideline {
    position: absolute;
    bottom: 24px;
    left: 24px;
    right: 24px;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    gap: 4px;
    opacity: 0.45;
}

.guideline-text {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    font-weight: 700;
}

.guideline-line {
    width: 100%;
    height: 1px;
    border-bottom: 1.5px dotted #94a3b8;
}

.signature-helper-text {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}
</style>
