<script setup lang="ts">
defineProps<{
    displayName: string;
    handle: string;
    roleLabel: string;
    deptName: string;
    initials: string;
    isVerified: boolean;
}>();
</script>

<template>
    <div class="preview-wrapper-col">
        <div class="preview-header-bar">
            <span class="badge-preview-tag">VISTA PREVIA</span>
        </div>

        <div class="live-credential-card">
            <div class="credential-top-banner"></div>

            <div class="credential-avatar-wrapper">
                <div class="credential-avatar-circle">
                    {{ initials }}
                </div>
                <div v-if="isVerified" class="badge-credential-verified" title="Empleado verificado">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                </div>
            </div>

            <div class="credential-body-content">
                <h2 class="credential-user-name">{{ displayName }}</h2>
                <div class="credential-user-handle">{{ handle }}</div>

                <div class="credential-pills-row">
                    <span class="cred-role-pill">{{ roleLabel }}</span>
                    <span class="cred-dept-pill">{{ deptName }}</span>
                </div>

                <div class="credential-meta-box">
                    <div class="meta-row">
                        <span class="meta-lbl">Estado de Acceso:</span>
                        <span class="meta-val-active">
                            <span class="meta-dot"></span>
                            Habilitado
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-lbl">Tipo de Credencial:</span>
                        <span class="meta-val">Digital Corporativa</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.preview-wrapper-col {
    position: sticky;
    top: 20px;
}
.preview-header-bar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 8px;
}
.badge-preview-tag {
    font-size: 10px;
    font-weight: 700 !important;
    letter-spacing: 0.05em;
    color: #4f46e5;
    background: rgba(79, 70, 229, 0.15);
    border: var(--stroke-w, 2px) solid rgba(79, 70, 229, 0.3);
    padding: 2px 8px;
    border-radius: 6px;
}
.live-credential-card {
    background: var(--bg-card, #17181a);
    border: var(--stroke-w, 2px) solid var(--stroke, #31343a);
    border-radius: var(--panel-radius, 18px);
    overflow: hidden;
    box-shadow: none !important;
}
.credential-top-banner {
    height: 70px;
    background: #4f46e5;
}
.credential-avatar-wrapper {
    position: relative;
    margin-top: -35px;
    padding-left: 20px;
    display: inline-block;
}
.credential-avatar-circle {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    background: #1e2024;
    border: var(--stroke-w, 2px) solid #ffffff;
    display: grid;
    place-items: center;
    font-size: 22px;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    box-shadow: none !important;
}
.badge-credential-verified {
    position: absolute;
    bottom: -2px;
    right: -4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid var(--bg-card, #17181a);
    display: grid;
    place-items: center;
    color: #ffffff;
}
.badge-credential-verified svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    fill: none;
    stroke-width: 3;
}
.credential-body-content {
    padding: 16px 20px 24px;
}
.credential-user-name {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: var(--text, #f4f4f6);
    margin: 0;
}
.credential-user-handle {
    font-size: 12px;
    color: var(--text-muted, #8e9199);
    margin-bottom: 12px;
}
.credential-pills-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.cred-role-pill {
    padding: 3px 10px;
    border-radius: 8px;
    background: rgba(79, 70, 229, 0.15);
    color: #4f46e5;
    border: var(--stroke-w, 2px) solid rgba(79, 70, 229, 0.3);
    font-size: 10px;
    font-weight: 700 !important;
}
.cred-dept-pill {
    padding: 3px 10px;
    border-radius: 8px;
    background: var(--bg-sub, #1e2024);
    color: var(--text-muted, #8e9199);
    border: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    font-size: 10px;
    font-weight: 600 !important;
}
.credential-meta-box {
    border-top: var(--stroke-w, 2px) solid var(--stroke-subtle, #23252a);
    padding-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
}
.meta-lbl {
    color: var(--text-muted, #8e9199);
}
.meta-val {
    color: var(--text, #f4f4f6);
}
.meta-val-active {
    color: #10b981;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 600 !important;
}
.meta-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}
</style>
