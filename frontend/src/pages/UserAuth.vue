<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t, te } = useI18n()
const router = useRouter()
const auth = useAuthStore()

type AuthMode = 'login' | 'signup'

const mode = ref<AuthMode>('login')
const feedback = ref('')
const feedbackType = ref<'error' | 'success'>('error')

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

function setMode(nextMode: AuthMode) {
  mode.value = nextMode
  feedback.value = ''
}

function getErrorMessage(error: unknown): string {
  if (typeof error === 'object' && error !== null) {
    const errObj = error as { response?: { data?: { code?: string; message?: string } }; code?: string; message?: string }
    
    // Extract backend error code or default error message
    const code = errObj.response?.data?.code || errObj.code
    if (code) {
      const translationKey = `auth.errors.${code}`
      if (te(translationKey)) {
        return t(translationKey)
      }
    }
    
    if (errObj.response?.data?.message) {
      return errObj.response.data.message
    }
    
    if (errObj.message) {
      return errObj.message
    }
  }
  
  return t('auth.errors.failed')
}

async function submitAuth() {
  feedback.value = ''
  feedbackType.value = 'error'

  if (mode.value === 'signup' && !form.name.trim()) {
    feedback.value = t('auth.errors.nameRequired')
    return
  }

  if (!form.email.trim() || !form.password.trim()) {
    feedback.value = t('auth.errors.fieldsRequired')
    return
  }

  if (
    mode.value === 'signup' &&
    form.password !== form.passwordConfirmation
  ) {
    feedback.value = t('auth.errors.passwordsMismatch')
    return
  }

  try {
    if (mode.value === 'signup') {
      await auth.signup(
        form.name,
        form.email,
        form.password,
        form.passwordConfirmation,
      )
      
      // Redirect to login tab after successful signup
      mode.value = 'login'
      form.password = ''
      form.passwordConfirmation = ''
      feedbackType.value = 'success'
      feedback.value = t('auth.signupSuccess')
    } else {
      await auth.login(
        form.email,
        form.password,
      )
      await router.push('/profile')
    }
  } catch (error) {
    feedbackType.value = 'error'
    feedback.value = getErrorMessage(error)
  }
}
</script>

<template>
  <section class="auth-page">
    <div class="auth-card">
      <header class="auth-header">
        <p class="kicker">{{ t('auth.kicker') }}</p>

        <h1>
          {{ mode === 'login' ? t('auth.login') : t('auth.signup') }}
        </h1>
      </header>

      <div
        class="auth-switch"
        role="tablist"
        :aria-label="t('auth.switchAria')"
      >
        <button
          type="button"
          class="switch-btn"
          :class="{ active: mode === 'login' }"
          role="tab"
          :aria-selected="mode === 'login'"
          @click="setMode('login')"
        >
          {{ t('auth.login') }}
        </button>

        <button
          type="button"
          class="switch-btn"
          :class="{ active: mode === 'signup' }"
          role="tab"
          :aria-selected="mode === 'signup'"
          @click="setMode('signup')"
        >
          {{ t('auth.signup') }}
        </button>
      </div>

      <form
        class="auth-form"
        @submit.prevent="submitAuth"
      >
        <label
          v-if="mode === 'signup'"
          class="auth-field"
        >
          <span>{{ t('auth.name') }}</span>

          <input
            v-model="form.name"
            type="text"
            maxlength="40"
            autocomplete="name"
            required
          />
        </label>

        <label class="auth-field">
          <span>{{ t('auth.email') }}</span>

          <input
            v-model="form.email"
            type="email"
            maxlength="80"
            autocomplete="email"
            required
          />
        </label>

        <label class="auth-field">
          <span>{{ t('auth.password') }}</span>

          <input
            v-model="form.password"
            type="password"
            maxlength="80"
            :autocomplete="
              mode === 'login'
                ? 'current-password'
                : 'new-password'
            "
            required
          />
        </label>

        <label
          v-if="mode === 'signup'"
          class="auth-field"
        >
          <span>{{ t('auth.confirmPassword') }}</span>

          <input
            v-model="form.passwordConfirmation"
            type="password"
            maxlength="80"
            autocomplete="new-password"
            required
          />
        </label>

        <button
          type="submit"
          class="submit-btn"
          :disabled="auth.loading"
        >
          {{
            auth.loading
              ? t('auth.pleaseWait')
              : mode === 'login'
                ? t('auth.login')
                : t('auth.signup')
          }}
        </button>
      </form>

      <p
        v-if="feedback"
        class="feedback"
        :class="feedbackType"
      >
        {{ feedback }}
      </p>
    </div>
  </section>
</template>

<style scoped>
.auth-page {
  width: 100%;
  box-sizing: border-box;
  padding: 1.2rem;
}

.auth-card {
  width: min(100%, 460px);
  margin: 1.25rem auto;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0.9rem;
  box-sizing: border-box;
}

.auth-header {
  margin-bottom: 0.9rem;
}

.kicker {
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
}

.auth-switch {
  display: flex;
  gap: 0.45rem;
  margin-bottom: 0.9rem;
}

.switch-btn {
  flex: 1;
  min-height: 2.4rem;
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 0.7rem;
  background: rgba(255, 255, 255, 0.04);
  color: var(--color-text);
  cursor: pointer;
  font-weight: 600;
}

.switch-btn.active {
  border-color: rgba(20, 184, 166, 0.8);
  background: rgba(20, 184, 166, 0.18);
}

.auth-form {
  display: grid;
  gap: 0.8rem;
}

.auth-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.auth-field span {
  color: var(--color-text-muted);
  font-size: 0.66rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  font-weight: 700;
}

.auth-field input {
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

.auth-field input:focus {
  border-color: rgba(20, 184, 166, 0.8);
}

.submit-btn {
  min-height: 2.6rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--color-primary);
  color: white;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.18s ease;
}

.submit-btn:hover {
  opacity: 0.96;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.feedback {
  margin: 0.8rem 0 0;
  font-size: 0.88rem;
  padding: 0.6rem;
  border-radius: 0.5rem;
}

.feedback.error {
  color: #f87171;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.feedback.success {
  color: #34d399;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

@media (max-width: 560px) {
  .auth-page {
    padding: 0.75rem;
  }
}
</style>