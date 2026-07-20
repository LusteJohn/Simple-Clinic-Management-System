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

  async function getDoctorProfile() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/doctor/profile')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load doctor profile.'
      throw err
    } finally {
      loading.value = false
    }
  }

async function createDoctorProfile(form) {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.post('/api/doctor/profile', form)
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to create doctor profile.'
    throw err
  } finally {
    loading.value = false
  }
}

async function updateDoctorProfile(form) {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.put('/api/doctor/profile', form)
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to update doctor profile.'
    throw err
  } finally {
    loading.value = false
  }
}

async function getDoctorInfo() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/api/doctor/info')
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load doctor info.'
    throw err
  } finally {
    loading.value = false
  }
}

async function createDoctorInfo(form) {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.post('/api/doctor/info', form)
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to create doctor info.'
    throw err
  } finally {
    loading.value = false
  }
}

async function updateDoctorInfo(form) {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.put('/api/doctor/info', form)
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to update doctor info.'
    throw err
  } finally {
    loading.value = false
  }
}

async function deleteDoctorInfo() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.delete('/api/doctor/info')
    return data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to delete doctor info.'
    throw err
  } finally {
    loading.value = false
  }
}

  async function getAllPatients() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/admin/patients')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load patients.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getPatientProfile() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/patient/profile')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load patient profile.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createPatientProfile(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/patient/profile', form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to create patient profile.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePatientProfile(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.put('/api/patient/profile', form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to update patient profile.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deletePatientProfile() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.delete('/api/patient/profile')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to delete patient profile.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loadDoctorSchedule() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/doctor/schedule')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load doctor schedule.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createDoctorSchedule(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/doctor/schedule', form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to create doctor schedule.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateDoctorSchedule(scheduleId, form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.put(`/api/doctor/schedule/${scheduleId}`, form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to update doctor schedule.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteDoctorSchedule(scheduleId) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.delete(`/api/doctor/schedule/${scheduleId}`)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to delete doctor schedule.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loadDoctorScheduleList() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/doctor/schedules')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load doctor schedule list.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getMyAppointments() {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/api/patient/appointments')
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load appointments.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getAppointmentById(appointmentId) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get(`/api/patient/appointments/${appointmentId}`)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to load appointment.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createAppointment(form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/api/patient/appointments', form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to create appointment.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateAppointment(appointmentId, form) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.put(`/api/patient/appointments/${appointmentId}`, form)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to update appointment.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteAppointment(appointmentId) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.delete(`/api/patient/appointments/${appointmentId}`)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Unable to delete appointment.'
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
    deleteDoctor,
    getDoctorProfile,
    createDoctorProfile,
    updateDoctorProfile,
    getDoctorInfo,
    createDoctorInfo,
    updateDoctorInfo,
    deleteDoctorInfo,
    getPatientProfile,
    createPatientProfile,
    updatePatientProfile,
    deletePatientProfile,

    loadDoctorSchedule,
    createDoctorSchedule,
    updateDoctorSchedule,
    deleteDoctorSchedule,

    loadDoctorScheduleList,
    getAllPatients,
    getMyAppointments,
    getAppointmentById,
    createAppointment,
    updateAppointment,
    deleteAppointment,
  }
})