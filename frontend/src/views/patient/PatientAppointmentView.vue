<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/patient/SideBar.vue'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)
const appointments = ref([])
const loadingAppointments = ref(false)
const cancellingId = ref(null)
const cancelSuccess = ref('')
const cancelError = ref('')

async function loadAppointments() {
  loadingAppointments.value = true

  try {
    const data = await auth.getMyAppointments()
    appointments.value = data.appointments || []
  } catch (error) {
    console.error('Error loading appointments:', error)
  } finally {
    loadingAppointments.value = false
  }
}

function getStatusColor(status) {
  const colors = {
    'pending': '#f59e0b',
    'confirmed': '#3b82f6',
    'checked-in': '#8b5cf6',
    'completed': '#10b981',
    'cancelled': '#ef4444',
    'no-show': '#6b7280'
  }
  return colors[status] || '#6b7280'
}

function getStatusLabel(status) {
  const labels = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'checked-in': 'Checked In',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
    'no-show': 'No Show'
  }
  return labels[status] || status
}

async function cancelAppointment(appointmentId) {
  if (!confirm('Are you sure you want to cancel this appointment?')) {
    return
  }

  cancellingId.value = appointmentId
  cancelError.value = ''
  cancelSuccess.value = ''

  try {
    await auth.deleteAppointment(appointmentId)
    cancelSuccess.value = 'Appointment cancelled successfully!'
    
    setTimeout(() => {
      appointments.value = appointments.value.filter(apt => apt.appointment_id !== appointmentId)
      cancelSuccess.value = ''
    }, 2000)
  } catch (error) {
    cancelError.value = error.response?.data?.message || 'Failed to cancel appointment.'
    console.error('Error cancelling appointment:', error)
  } finally {
    cancellingId.value = null
  }
}

onMounted(async () => {
  loadingSession.value = true

  const session = await auth.loadSession()

  if (!session) {
    router.push({ name: 'login' })
    return
  }

  await loadAppointments()

  loadingSession.value = false
})

async function handleLogout() {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <div class="layout">
    <SideBar />
    <main class="appointments-page">
      <section class="appointments-card">
        <div class="header">
          <div>
            <p class="eyebrow">Patient Portal</p>
            <h1>My Appointments</h1>
            <p class="lead">
              Logged in as
              <strong>{{ auth.user?.username }}</strong>
            </p>
          </div>

          <button class="logout" @click="handleLogout">
            Logout
          </button>
        </div>

        <div v-if="loadingAppointments" class="loading">
          Loading your appointments...
        </div>

        <div v-else-if="appointments.length" class="table-wrap">
          <table class="appointments-table">
            <thead>
              <tr>
                <th>Doctor Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="appointment in appointments" :key="appointment.appointment_id">
                <td>{{ appointment.doctor_name }}</td>
                <td>{{ appointment.appointment_date }}</td>
                <td>{{ appointment.appointment_time }}</td>
                <td>
                  <span class="status-badge" :style="{ backgroundColor: getStatusColor(appointment.status) }">
                    {{ getStatusLabel(appointment.status) }}
                  </span>
                </td>
                <td class="reason">{{ appointment.reason_for_visit || '—' }}</td>
                <td>
                  <button
                    v-if="appointment.status === 'pending' || appointment.status === 'confirmed'"
                    class="cancel-btn"
                    @click="cancelAppointment(appointment.appointment_id)"
                    :disabled="cancellingId === appointment.appointment_id"
                  >
                    {{ cancellingId === appointment.appointment_id ? 'Cancelling...' : 'Cancel' }}
                  </button>
                  <span v-else class="no-action">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="empty-state">
          <p>You don't have any appointments yet.</p>
          <router-link to="/patient/schedules" class="book-link">
            Book an appointment
          </router-link>
        </div>

        <div v-if="cancelSuccess" class="success-message">
          {{ cancelSuccess }}
        </div>
        <div v-if="cancelError" class="error-message">
          {{ cancelError }}
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f1f5f9;
}

.appointments-page {
  flex: 1;
  padding: 40px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
  align-items: start;
}

.appointments-card {
  background: #fff;
  border-radius: 18px;
  padding: 32px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
  border: 1px solid #e2e8f0;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 35px;
}

.header h1 {
  margin: 10px 0 5px 0;
}

.eyebrow {
  color: #2563eb;
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .1em;
  margin: 0;
}

.lead {
  color: #64748b;
  margin: 0;
}

.logout {
  background: #dc2626;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: .2s;
}

.logout:hover {
  background: #b91c1c;
}

.loading {
  text-align: center;
  padding: 40px 20px;
  color: #64748b;
}

.table-wrap {
  overflow-x: auto;
}

.appointments-table {
  width: 100%;
  border-collapse: collapse;
}

.appointments-table th,
.appointments-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}

.appointments-table th {
  background: #f8fafc;
  color: #334155;
  font-weight: 600;
  font-size: .9rem;
  white-space: nowrap;
}

.appointments-table td {
  color: #0f172a;
  font-size: .95rem;
}

.reason {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  color: white;
  font-size: .85rem;
  font-weight: 600;
}

.cancel-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: .9rem;
  font-weight: 500;
  transition: .2s;
}

.cancel-btn:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.cancel-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.no-action {
  color: #cbd5e1;
  font-size: .9rem;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 20px;
  font-size: 1.1rem;
}

.book-link {
  display: inline-block;
  background: #2563eb;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: .2s;
}

.book-link:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

.success-message {
  margin-top: 20px;
  padding: 12px 14px;
  background: #ecfdf5;
  color: #166534;
  border-radius: 8px;
  font-weight: 500;
}

.error-message {
  margin-top: 20px;
  padding: 12px 14px;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 8px;
  font-weight: 500;
}
</style>