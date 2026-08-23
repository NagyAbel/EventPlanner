import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { apiRequest, authRequest } from '@/services/api'

export interface User {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  async function fetchUser() {
    try {
      const response = await apiRequest<{ user: User }>('/api/user')
      user.value = response.user
    } catch {
      user.value = null
    } finally {
      initialized.value = true
    }
  }

  async function ensureInitialized() {
    if (!initialized.value) {
      await fetchUser()
    }
  }

  async function login(email: string, password: string) {
    loading.value = true
    try {
      await authRequest('/api/user/auth', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      })
      await fetchUser()
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      // Use authRequest for CSRF protection
      await authRequest('/api/user/logout', { method: 'POST' })
    } catch {
      // Ignore errors on logout (e.g. session already invalidated)
    } finally {
      user.value = null
      initialized.value = true
      loading.value = false
    }
  }

  async function updateName(name: string) {
    loading.value = true
    try {
      // Switched to authRequest for state mutation
      const response = await authRequest<{ user: User }>('/api/user/name', {
        method: 'POST',
        body: JSON.stringify({ name }),
      })
      user.value = response.user
    } finally {
      loading.value = false
    }
  }
  async function signup(name: string,email: string,password: string,passwordConfirmation: string,) {
    loading.value = true
    try {
      await authRequest('/api/user/signup', {
        method: 'POST',
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation,
        }),
      })

      await fetchUser()
    } finally {
      loading.value = false
    }
  }

  // Single global event listener
  function handleUnauthorized() {
    user.value = null
    initialized.value = true
  }

  window.addEventListener('auth:unauthorized', handleUnauthorized)

  return {
    user,
    loading,
    initialized,
    isAuthenticated,
    fetchUser,
    ensureInitialized,
    login,
    logout,
    signup,
    updateName,
  }
})