import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref('')

  const isAuthenticated = computed(() => Boolean(user.value))

  async function register(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/register', form)
      user.value = data.user
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function login(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/login', form)
      user.value = data.user
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loadSession() {
    try {
      const { data } = await api.get('/api/me')
      user.value = data.user
      return data.user
    } catch {
      user.value = null
      return null
    }
  }

  async function logout() {
    loading.value = true
    error.value = ''

    try {
      await api.post('/api/logout')
      user.value = null
    } catch (err) {
      error.value = err.response?.data?.message || 'Logout failed.'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    loadSession,
    logout,
  }
})