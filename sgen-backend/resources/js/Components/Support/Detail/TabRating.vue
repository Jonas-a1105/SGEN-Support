<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { TicketRating } from '@/types/support';

interface Props {
    ticketId: number;
    rating: TicketRating | null;
}

const props = defineProps<Props>();

const currentScore = ref(props.rating?.score ?? 5);
const hoverScore = ref<number | null>(null);
const feedback = ref(props.rating?.comment ?? '');
const isSubmitting = ref(false);
const hasRated = ref(!!props.rating?.score);

const labels: Record<number, string> = {
    1: '1 estrella — Muy Insatisfecho',
    2: '2 estrellas — Poco Satisfecho',
    3: '3 estrellas — Regular',
    4: '4 estrellas — Satisfecho',
    5: '5 estrellas — Excelente Servicio',
};

const handleMouseEnter = (star: number) => {
    hoverScore.value = star;
};

const handleMouseLeave = () => {
    hoverScore.value = null;
};

const handleSelectStar = (star: number) => {
    currentScore.value = star;
};

const submitRating = () => {
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    router.post(
        `/soportes/${props.ticketId}/calificar`,
        {
            calificacion: currentScore.value,
            comentario: feedback.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                hasRated.value = true;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
};
</script>

<template>
    <div class="rating-tab-container">
        <div class="rating-center-card">
            <div class="rating-gold-circle">
                <svg viewBox="0 0 24 24">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>

            <div class="rating-text-header">
                <h2 class="rating-title">¿Cómo fue tu experiencia?</h2>
                <p class="rating-desc">Tu opinión es importante para nosotros y nos ayuda a mejorar constantemente.</p>
            </div>

            <div
                class="stars-row"
                role="radiogroup"
                aria-label="Calificación con estrellas"
                @mouseleave="handleMouseLeave"
            >
                <button
                    v-for="star in 5"
                    :key="star"
                    :class="['star-btn', { active: (hoverScore || currentScore) >= star }]"
                    type="button"
                    :aria-label="`${star} estrellas`"
                    @mouseenter="handleMouseEnter(star)"
                    @click="handleSelectStar(star)"
                >
                    <svg viewBox="0 0 24 24">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </button>
            </div>

            <span class="rating-label-feedback">
                {{ labels[hoverScore || currentScore] || 'Selecciona una calificación' }}
            </span>

            <textarea
                v-model="feedback"
                class="rating-textarea"
                placeholder="Cuéntanos qué podemos mejorar (opcional)..."
            ></textarea>

            <button
                class="btn-submit-rating"
                type="button"
                :disabled="isSubmitting"
                @click="submitRating"
            >
                {{ isSubmitting ? 'Guardando...' : hasRated ? 'Actualizar Valoración' : 'Enviar Valoración' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.rating-tab-container {
    padding: 30px 16px;
    display: flex;
    justify-content: center;
}
.rating-center-card {
    max-width: 540px;
    width: 100%;
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    background: var(--bg-card);
    padding: 34px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 16px;
    position: relative;
    margin-top: 24px;
}
.rating-gold-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(217, 119, 6, 0.15);
    border: var(--stroke-w) solid rgba(217, 119, 6, 0.35);
    color: var(--color-yellow, #d97706);
    display: grid;
    place-items: center;
    margin-top: -62px;
    margin-bottom: 4px;
}
.rating-gold-circle svg {
    width: 26px;
    height: 26px;
    fill: currentColor;
    stroke: none;
}
.rating-text-header {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.rating-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: var(--text);
    margin: 0;
}
.rating-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    max-width: 400px;
    line-height: 1.4;
}
.stars-row {
    display: flex;
    gap: 10px;
    cursor: pointer;
    margin: 6px 0;
}
.star-btn {
    background: transparent;
    border: none;
    color: var(--stroke);
    cursor: pointer;
    padding: 0;
    transition: transform 0.15s ease, color 0.15s ease;
}
.star-btn svg {
    width: 32px;
    height: 32px;
    fill: currentColor;
}
.star-btn.active,
.star-btn:hover {
    color: var(--color-yellow, #d97706);
    transform: scale(1.15);
}
.rating-label-feedback {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
}
.rating-textarea {
    width: 100%;
    min-height: 80px;
    padding: 12px 14px;
    border-radius: 12px;
    border: var(--stroke-w) solid var(--stroke);
    background: var(--bg-sub);
    color: var(--text);
    font-size: 13px;
    outline: none;
    resize: none;
    font-family: inherit;
}
.btn-submit-rating {
    width: 100%;
    height: 42px;
    background: var(--primary);
    border: none;
    border-radius: 12px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s ease;
}
.btn-submit-rating:hover {
    opacity: 0.9;
}
.btn-submit-rating:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
