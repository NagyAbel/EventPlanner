<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

type AuthMode = 'login' | 'signup'

const mode = ref<AuthMode>('login')
const feedback = ref('')

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

async function submitAuth() {
  feedback.value = ''

  if (mode.value === 'signup' && !form.name.trim()) {
    feedback.value = 'Name is required.'
    return
  }

  if (!form.email.trim() || !form.password.trim()) {
    feedback.value = 'Email and password are required.'
    return
  }

  if (
    mode.value === 'signup' &&
    form.password !== form.passwordConfirmation
  ) {
    feedback.value = 'Passwords do not match.'
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
    } else {
      await auth.login(
        form.email,
        form.password,
      )
    }

    await router.push('/profile')
  } catch (error) {
    feedback.value =
      error instanceof Error
        ? error.message
        : 'Authentication failed.'
  }
}
</script>

<template>
  <section class="auth-page">
    <div class="auth-card">
      <header class="auth-header">
        <p class="kicker">Account</p>

        <h1>
          {{ mode === 'login' ? 'Login' : t('signup.title', 'Sign Up') }}
        </h1>
      </header>

      <div
        class="auth-switch"
        role="tablist"
        aria-label="Switch auth mode"
      >
        <button
          type="button"
          class="switch-btn"
          :class="{ active: mode === 'login' }"
          role="tab"
          :aria-selected="mode === 'login'"
          @click="setMode('login')"
        >
          Login
        </button>

        <button
          type="button"
          class="switch-btn"
          :class="{ active: mode === 'signup' }"
          role="tab"
          :aria-selected="mode === 'signup'"
          @click="setMode('signup')"
        >
          {{ t('signup.title', 'Sign Up') }}
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
          <span>Name</span>

          <input
            v-model="form.name"
            type="text"
            maxlength="40"
            autocomplete="name"
            required
          />
        </label>

        <label class="auth-field">
          <span>{{ t('signup.email', 'Email') }}</span>

          <input
            v-model="form.email"
            type="email"
            maxlength="80"
            autocomplete="email"
            required
          />
        </label>

        <label class="auth-field">
          <span>{{ t('signup.password', 'Password') }}</span>

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
          <span>Confirm Password</span>

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
              ? 'Please wait...'
              : mode === 'login'
                ? 'Login'
                : t('signup.button', 'Sign Up')
          }}
        </button>
      </form>

      <p
        v-if="feedback"
        class="feedback"
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
  color: var(--color-text);
  font-size: 0.88rem;
}

@media (max-width: 560px) {
  .auth-page {
    padding: 0.75rem;
  }
}
</style>