<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import profileIcon from '@/assets/profile.svg'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

function goToDashboard() {
  router.push('/profile')
}

function goToHome() {
  router.push('/')
}
</script>

<template>
  <header class="app-header">
    <div class="header-container">
      <!-- Left: Logo / Brand Title -->
      <div class="header-left">
        <button
          class="brand-button"
          type="button"
          :aria-label="t('header.goToHomeAria')"
          @click="goToHome"
        >
          <span class="brand-title">{{ t("header.title") }}</span>
        </button>
      </div>

      <!-- Right: User Dashboard Control -->
      <div class="header-right">
        <button
          class="dashboard-pill"
          type="button"
          :aria-label="t('header.openDashboardAria')"
          @click="goToDashboard"
        >
          <div class="avatar-wrapper">
            <img
              class="profile-icon"
              :src="profileIcon"
              :alt="t('header.userProfileAlt')"
            />
          </div>
          <div class="user-meta">
            <span class="dashboard-label">Dashboard</span>
            <span class="user-name">{{ auth.user?.name ?? t('header.guest') }}</span>
          </div>
        </button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
  height: 64px;
  background-color: var(--color-secondary, rgba(20, 24, 33, 0.85));
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.header-container {
  max-width: 1280px;
  height: 100%;
  margin: 0 auto;
  padding: 0 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
}

/* Brand Link */
.brand-button {
  background: transparent;
  border: none;
  padding: 0.25rem 0.5rem;
  margin: 0;
  cursor: pointer;
  border-radius: 0.5rem;
  transition: opacity 0.2s ease;
}

.brand-button:hover {
  opacity: 0.85;
}

.brand-button:focus-visible {
  outline: 2px solid #14b8a6;
  outline-offset: 2px;
}

.brand-title {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #ffffff;
}

/* Dashboard User Pill */
.dashboard-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.35rem 0.85rem 0.35rem 0.4rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.dashboard-pill:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(20, 184, 166, 0.5);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}



.dashboard-pill:focus-visible {
  outline: 2px solid #14b8a6;
  outline-offset: 2px;
}

/* Avatar Circle */
.avatar-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(20, 184, 166, 0.2);
  border: 1px solid rgba(20, 184, 166, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.profile-icon {
  width: 18px;
  height: 18px;
  filter: brightness(0) invert(1);
  object-fit: contain;
}

/* User Metadata Labels */
.user-meta {
  display: flex;
  flex-direction: column;
  text-align: left;
  line-height: 1.15;
}

.dashboard-label {
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #14b8a6;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #ffffff;
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>