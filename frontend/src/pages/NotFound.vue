<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const { t } = useI18n()
const router = useRouter()

function goHome() {
  router.push('/')
}

function goBack() {
  router.back()
}
</script>

<template>
  <main class="not-found-page">
    <div class="not-found-card">
      <div class="badge">404</div>

      <h1>{{ t('notFound.title') }}</h1>
      <p class="description">{{ t('notFound.description') }}</p>

      <div class="actions">
        <button
          type="button"
          class="btn btn-primary"
          @click="goHome"
        >
          {{ t('notFound.goHome') }}
        </button>

        <button
          type="button"
          class="btn btn-secondary"
          @click="goBack"
        >
          {{ t('notFound.goBack') }}
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
.not-found-page {
  width: 100%;
  min-height: calc(100vh - 100px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  box-sizing: border-box;
}

.not-found-card {
  width: min(100%, 460px);
  padding: 2.5rem 2rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 1rem;
  box-sizing: border-box;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35rem 0.85rem;
  border-radius: 2rem;
  background: rgba(20, 184, 166, 0.15);
  border: 1px solid rgba(20, 184, 166, 0.4);
  color: var(--color-primary, #14b8a6);
  font-size: 0.85rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  margin-bottom: 1.25rem;
}

h1 {
  margin: 0;
  font-size: clamp(1.5rem, 3vw, 2rem);
  line-height: 1.2;
  color: var(--color-text);
  font-weight: 700;
}

.description {
  margin: 0.75rem 0 1.75rem;
  color: var(--color-text-muted);
  font-size: 0.95rem;
  line-height: 1.5;
}

.actions {
  display: flex;
  gap: 0.75rem;
  width: 100%;
}

.btn {
  flex: 1;
  min-height: 2.6rem;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font: inherit;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  border: none;
  background: var(--color-primary, #14b8a6);
  color: #ffffff;
}

.btn-primary:hover {
  opacity: 0.92;
  transform: translateY(-1px);
}

.btn-secondary {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.04);
  color: var(--color-text);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.25);
  transform: translateY(-1px);
}

.btn:active {
  transform: translateY(0);
}

.btn:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.8);
  outline-offset: 2px;
}

@media (max-width: 480px) {
  .actions {
    flex-direction: column;
  }
}
</style>