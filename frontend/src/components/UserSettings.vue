<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import profile from '@/assets/profile.svg'

const { t } = useI18n()
const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const savingName = ref(false)

watch(
  () => auth.user?.name,
  (newName) => {
    if (newName !== undefined && !savingName.value) {
      name.value = newName
    }
  },
  { immediate: true },
)

async function saveName() {
  if (!auth.user || savingName.value) {
    return
  }

  const newName = name.value.trim()

  if (!newName) {
    name.value = auth.user.name
    return
  }

  if (newName === auth.user.name) {
    return
  }

  savingName.value = true

  try {
    await auth.updateName(newName)
  } catch (error) {
    console.error('Name update failed:', error)
    name.value = auth.user.name
  } finally {
    savingName.value = false
  }
}

async function logout() {
  try {
    await auth.logout()
    await router.push('/auth')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>

<template>
  <section class="user-settings">
    <div v-if="auth.user" class="settings-header">
      <div class="profile-avatar">
        <img :src="profile" :alt="t('userSettings.avatarAlt')" />
      </div>

      <div class="profile-meta">
        <p class="profile-kicker">{{ t('userSettings.accountDetails') }}</p>
        <h1>{{ auth.user.name }}</h1>
      </div>
    </div>

    <div v-if="auth.user" class="profile-grid">
      <label class="profile-field">
        <span>{{ t('userSettings.name') }}</span>

        <input
          v-model="name"
          type="text"
          maxlength="40"
          :disabled="savingName"
          @keydown.enter="saveName"
          @blur="saveName"
        />

        <small v-if="savingName" class="saving-text">
          {{ t('userSettings.saving') }}
        </small>
      </label>

      <label class="profile-field profile-field--full">
        <span>{{ t('userSettings.email') }}</span>

        <input
          :value="auth.user.email"
          type="email"
          disabled
        />
      </label>
    </div>

    <button
      type="button"
      class="logout-button"
      :disabled="auth.loading"
      @click="logout"
    >
      {{ auth.loading ? t('userSettings.loggingOut') : t('userSettings.logout') }}
    </button>
  </section>
</template>

<style scoped>
.user-settings {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0.9rem;
}

.settings-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  min-width: 0;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  min-width: 48px;
  min-height: 48px;
  border-radius: 50%;
  background-color: var(--color-primary);
  display: grid;
  place-items: center;
  overflow: hidden;
  flex-shrink: 0;
}

.profile-avatar img {
  width: 20px;
  height: 20px;
  object-fit: contain;
  filter: brightness(0) invert(1);
}

.profile-meta {
  min-width: 0;
  flex: 1;
}

.profile-kicker {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

h1 {
  margin: 0.15rem 0 0;
  font-size: clamp(1.1rem, 1.8vw, 1.5rem);
  line-height: 1.2;
  color: var(--color-text);
  overflow-wrap: anywhere;
  word-break: break-word;
}

.profile-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.8rem;
}

.profile-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  color: var(--color-text);
  font-size: 0.78rem;
  font-weight: 600;
}

.profile-field--full {
  grid-column: 1;
}

.profile-field span {
  color: var(--color-text-muted);
  font-size: 0.66rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

input {
  width: 100%;
  box-sizing: border-box;
  min-height: 2.45rem;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.96);
  color: var(--color-text-dark);
  padding: 0.7rem 0.75rem;
  font: inherit;
  outline: none;
}

input:focus {
  border-color: rgba(20, 184, 166, 0.8);
  outline: none;
}

input:disabled {
  background: rgba(148, 163, 184, 0.08);
  color: var(--color-text-muted);
  cursor: not-allowed;
}

.saving-text {
  color: var(--color-primary);
  font-size: 0.75rem;
  margin-top: 0.2rem;
}

.logout-button {
  margin-top: 1rem;
  width: 100%;
  min-height: 2.6rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--color-primary);
  color: white;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.18s ease;
}

.logout-button:hover {
  opacity: 0.96;
}

.logout-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 560px) {
  .user-settings {
    padding: 0.75rem;
  }
}
</style>