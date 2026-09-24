<script setup lang="ts">
defineProps<{
    fullName: string;
    email: string;
    position: string;
    idDoc: string;
    deptName: string;
    initials: string;
    isVerifiedUser: boolean;
}>();
</script>

<template>
    <div class="preview-wrapper-col">
        <div class="preview-header-bar">
            <span class="preview-note-text">Registra el talento humano de tu organización.</span>
            <span class="badge-code">VISTA PREVIA</span>
        </div>

        <div class="preview-card-box">
            <div class="preview-avatar-badge-wrap">
                <div class="preview-avatar-main">
                    {{ initials }}
                </div>
                <div v-if="isVerifiedUser" class="preview-avatar-badge-verified" title="Usuario vinculado">
                    <svg viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>

            <h2 class="preview-name-display">{{ fullName }}</h2>
            <div class="preview-email-display">{{ email }}</div>
            <div class="preview-job-tag">{{ position }}</div>

            <div class="preview-details-subcard">
                <div class="preview-key-val-row">
                    <span class="preview-key-label">ID:</span>
                    <span class="preview-val-text">{{ idDoc || '---' }}</span>
                </div>
                <div class="preview-key-val-row">
                    <span class="preview-key-label">Depto:</span>
                    <span class="preview-val-text">{{ deptName }}</span>
                </div>
            </div>
        </div>

        <div class="preview-footer-note">
            La vista previa se actualiza en tiempo real.
        </div>
    </div>
</template>

<style scoped>
.preview-wrapper-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.preview-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-note-text {
    font-size: 11px;
    color: var(--text-dim);
}

.badge-code {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 6px;
    background: var(--stroke-subtle);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}

.preview-card-box {
    background: var(--bg-card);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: var(--panel-radius);
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: none !important;
}

.preview-avatar-badge-wrap {
    position: relative;
    margin-bottom: 14px;
}

.preview-avatar-main {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.15);
    border: var(--stroke-w) solid var(--blue);
    display: grid;
    place-items: center;
    font-size: 20px;
    font-weight: 700;
    color: #3b82f6;
}

.preview-avatar-badge-verified {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--green);
    border: 2px solid var(--bg-card);
    display: grid;
    place-items: center;
    color: #ffffff;
}

.preview-avatar-badge-verified svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 3;
}

.preview-name-display {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px;
}

.preview-email-display {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.preview-job-tag {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    font-size: 11px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: 0.04em;
    margin-bottom: 16px;
}

.preview-details-subcard {
    width: 100%;
    background: var(--bg-sub);
    border: var(--stroke-w) solid var(--stroke);
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preview-key-val-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.preview-key-label {
    color: var(--text-dim);
}

.preview-val-text {
    color: var(--text);
    font-weight: 600;
}

.preview-footer-note {
    font-size: 11px;
    color: var(--text-dim);
    text-align: center;
}
</style>
