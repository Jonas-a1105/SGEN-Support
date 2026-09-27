<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{ message?: string }>();
</script>

<template>
  <AppLayout title="Error">
    <Head title="Error" />
    <div class="error-page">
      <div class="error-card">
        <div class="error-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <h1>Ocurrió un problema</h1>
        <p class="error-message">
          {{ message ?? 'No se pudo completar la operación. Inténtalo de nuevo.' }}
        </p>
        <Link href="/dashboard" class="error-link">
          Volver al inicio
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.error-page {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.error-card {
  width: 100%;
  max-width: 480px;
  border: var(--stroke-w) solid var(--stroke);
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  text-align: center;
}
.error-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto var(--space-4);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(var(--red-rgb, 220,38,38), 0.1);
  color: var(--color-danger);
}
.error-icon svg {
  width: 24px;
  height: 24px;
}
.error-message {
  color: var(--text-muted);
  margin-bottom: var(--space-5);
}
.error-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  background: var(--color-primary);
  color: #fff;
  border-radius: var(--radius-md);
  text-decoration: none;
  font-weight: var(--weight-semibold);
}
.error-link:hover {
  background: var(--color-primary-hover);
}
</style>
