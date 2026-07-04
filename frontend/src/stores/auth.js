import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref('')

  const isAuthenticated = computed(() => Boolean(user.value))

  function normalizeUser(payload) {
    if (!payload || typeof payload !== 'object') {
      return null
    }

    if (payload.user && typeof payload.user === 'object') {
      return payload.user
    }

    const hasFlatUser = payload.user_id || payload.username || payload.email || payload.role

    if (!hasFlatUser) {
      return null
    }

    return {
      user_id: payload.user_id,
      username: payload.username,
      email: payload.email,
      role: payload.role,
    }
  }

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

  async function registerDoctor(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/admin/doctors/register', form)
      user.value = data.user
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getAllUsers() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/admin/users')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load users.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateDoctor(userId, form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.put(
        `/api/admin/doctors/${userId}`,
        form
      )

      return data
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        'Failed to update doctor account.'

      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteDoctor(userId) {
    if (!confirm('Are you sure you want to delete this doctor account?')) {
      return
    }

    try {
      await api.delete(`/api/admin/doctors/${userId}`)
      await loadDoctors()
    } catch (error) {
      console.error(error)
    }
  }

  async function login(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/login', form)
      let sessionUser = normalizeUser(data)

      // Some backend responses only return a message after setting session.
      // Fallback to /api/me to fetch the authenticated user payload.
      if (!sessionUser) {
        const meResponse = await api.get('/api/me')
        sessionUser = normalizeUser(meResponse?.data)
      }

      if (!sessionUser) {
        throw new Error('Login response did not include user data.')
      }

      user.value = sessionUser
      return {
        ...data,
        user: sessionUser,
      }
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
      user.value = normalizeUser(data)
      return user.value
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
    registerDoctor,
    getAllUsers,
    updateDoctor,
    deleteDoctor
  }
})