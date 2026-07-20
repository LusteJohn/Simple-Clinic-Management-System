<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/patient/SideBar.vue'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)

const doctorSchedules = ref([])
const loadingSchedules = ref(false)
const showBookingModal = ref(false)
const bookingLoading = ref(false)
const bookingSuccess = ref('')
const bookingError = ref('')

const selectedSchedule = ref(null)

const appointmentForm = reactive({
  appointment_date: '',
  appointment_time: '',
  reason_for_visit: ''
})

async function loadDoctorSchedules() {
  loadingSchedules.value = true

  try {
    const data = await auth.loadDoctorScheduleList()
    doctorSchedules.value = data.schedules || data.doctorSchedules || []
  } catch (error) {
    console.error('Error loading doctor schedules:', error)
  } finally {
    loadingSchedules.value = false
  }
}

function openBookingModal(schedule) {
  selectedSchedule.value = schedule
  appointmentForm.appointment_date = ''
  appointmentForm.appointment_time = ''
  appointmentForm.reason_for_visit = ''
  bookingSuccess.value = ''
  bookingError.value = ''
  showBookingModal.value = true
}

function closeBookingModal() {
  showBookingModal.value = false
  selectedSchedule.value = null
  bookingSuccess.value = ''
  bookingError.value = ''
}

function getDayName(dayOfWeek) {
  // dayOfWeek is 1-7 (1=Monday, 7=Sunday)
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  return days[dayOfWeek] || 'Unknown'
}

function validateDateForSchedule(dateString) {
  // Check if the selected date matches the doctor's day_of_week
  if (!dateString || !selectedSchedule.value) return true
  
  const date = new Date(dateString)
  // JavaScript getDay(): 0=Sunday, 1=Monday, ..., 6=Saturday
  // Convert to PHP format: 1=Monday, 7=Sunday
  let jsDay = date.getDay()
  let phpDay = jsDay === 0 ? 7 : jsDay
  
  return parseInt(phpDay) === parseInt(selectedSchedule.value.day_of_week)
}

async function submitAppointment() {
  bookingError.value = ''
  bookingSuccess.value = ''

  if (!appointmentForm.appointment_date || !appointmentForm.appointment_time) {
    bookingError.value = 'Please select both date and time.'
    return
  }

  // Validate date matches doctor's working day
  if (!validateDateForSchedule(appointmentForm.appointment_date)) {
    const dayName = getDayName(selectedSchedule.value.day_of_week)
    bookingError.value = `This doctor only works on ${dayName}s. Please select a ${dayName}.`
    return
  }

  bookingLoading.value = true

  try {
    await auth.createAppointment({
      doctor_id: selectedSchedule.value.doctor_id,
      appointment_date: appointmentForm.appointment_date,
      appointment_time: appointmentForm.appointment_time,
      reason_for_visit: appointmentForm.reason_for_visit || null
    })

    bookingSuccess.value = 'Appointment booked successfully!'
    setTimeout(() => {
      closeBookingModal()
    }, 2000)
  } catch (error) {
    bookingError.value = error.response?.data?.message || 'Failed to book appointment.'
    console.error('Error booking appointment:', error)
  } finally {
    bookingLoading.value = false
  }
}

onMounted(async () => {
    loadingSession.value = true

    const session = await auth.loadSession()

    if (!session) {
        router.push({ name: 'login' })
        return
    }

    await loadDoctorSchedules()

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
        <main class="settings-page">
            <section class="settings-card">
                <div class="header">
                    <div>
                        <p class="eyebrow">Patient Portal</p>

                        <h1>Doctor Schedule List</h1>

                        <p class="lead">
                            Logged in as
                            <strong>{{ auth.user?.username }}</strong>
                        </p>
                    </div>

                    <button class="logout" @click="handleLogout">
                        Logout
                    </button>
                </div>

                <div v-if="loadingSchedules">
                    Loading doctor schedules...
                </div>

                <div v-else-if="doctorSchedules.length" class="table-wrap">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Doctor Name</th>
                                <th>Day of Week</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Slot Duration (min)</th>
                                <th>Specialization</th>
                                <th>Consultation Fees</th>
                                <th>Years of Experience</th>
                                <th>Is Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="schedule in doctorSchedules" :key="schedule.schedule_id">
                                <td>{{ schedule.doctor_name }}</td>
                                <td>{{ schedule.day_of_week }}</td>
                                <td>{{ schedule.start_time }}</td>
                                <td>{{ schedule.end_time }}</td>
                                <td>{{ schedule.slot_duration_min }}</td>
                                <td>{{ schedule.specialization }}</td>
                                <td>{{ schedule.consultation_fees }}</td>
                                <td>{{ schedule.years_of_experience }}</td>
                                <td>{{ schedule.is_active === 1 || schedule.is_active === '1' || schedule.is_active === 'active' ? 'Yes' : 'No' }}</td>
                                <td>
                                  <button
                                    v-if="schedule.is_active === 1 || schedule.is_active === '1' || schedule.is_active === 'active'"
                                    class="book-btn"
                                    @click="openBookingModal(schedule)"
                                  >
                                    Book Appointment
                                  </button>
                                  <span v-else class="unavailable">Unavailable</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else>No doctor schedules found.</div>
            </section>
        </main>

        <!-- Booking Modal -->
        <div v-if="showBookingModal" class="modal-overlay" @click="closeBookingModal">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Book Appointment with {{ selectedSchedule?.doctor_name }}</h2>
              <button class="modal-close" @click="closeBookingModal">✕</button>
            </div>

            <form class="modal-form" @submit.prevent="submitAppointment">
              <div class="form-info">
                <p><strong>Available:</strong> {{ getDayName(selectedSchedule?.day_of_week) }}s, {{ selectedSchedule?.start_time }} - {{ selectedSchedule?.end_time }}</p>
              </div>

              <div class="form-group">
                <label>Appointment Date</label>
                <input
                  v-model="appointmentForm.appointment_date"
                  type="date"
                  required
                  @change="bookingError = ''"
                >
                <small v-if="appointmentForm.appointment_date && !validateDateForSchedule(appointmentForm.appointment_date)" class="date-warning">
                  ⚠️ Please select a {{ getDayName(selectedSchedule?.day_of_week) }}
                </small>
              </div>

              <div class="form-group">
                <label>Appointment Time</label>
                <input
                  v-model="appointmentForm.appointment_time"
                  type="time"
                  required
                >
              </div>

              <div class="form-group">
                <label>Reason for Visit (Optional)</label>
                <textarea
                  v-model="appointmentForm.reason_for_visit"
                  rows="4"
                  placeholder="Describe your reason for the appointment"
                />
              </div>

              <p v-if="bookingSuccess" class="success-message">{{ bookingSuccess }}</p>
              <p v-if="bookingError" class="error-message">{{ bookingError }}</p>

              <div class="modal-footer">
                <button
                  type="button"
                  class="cancel-btn"
                  @click="closeBookingModal"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="submit-btn"
                  :disabled="bookingLoading"
                >
                  {{ bookingLoading ? 'Booking...' : 'Book Appointment' }}
                </button>
              </div>
            </form>
          </div>
        </div>
    </div>
</template>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f1f5f9;
}

.settings-page {
  flex: 1;
  padding: 40px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
  gap: 28px;
  align-items: start;
}

.settings-card {
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

.eyebrow {
  color: #2563eb;
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .1em;
}

.lead {
  color: #64748b;
}

.logout {
  background: #dc2626;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
}

.table-wrap {
  overflow-x: auto;
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
}

.schedule-table th,
.schedule-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
  white-space: nowrap;
}

.schedule-table th {
  color: #334155;
  font-size: .9rem;
}

.schedule-table td {
  color: #0f172a;
}

.book-btn {
  background: #2563eb;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: .9rem;
  font-weight: 500;
  transition: .2s;
}

.book-btn:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

.unavailable {
  color: #94a3b8;
  font-size: .9rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: grid;
  place-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 18px;
  padding: 32px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.15);
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.4rem;
  color: #0f172a;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #94a3b8;
  transition: .2s;
  padding: 0;
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
}

.modal-close:hover {
  color: #0f172a;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-info {
  background: #eff6ff;
  border-left: 4px solid #2563eb;
  padding: 12px 14px;
  border-radius: 6px;
  margin-bottom: 8px;
}

.form-info p {
  margin: 0;
  color: #1e40af;
  font-size: .95rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #0f172a;
  font-size: .95rem;
}

.form-group input,
.form-group textarea {
  padding: 12px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-family: inherit;
  font-size: .95rem;
  transition: .2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.date-warning {
  color: #ea580c;
  font-size: .85rem;
  margin-top: -4px;
}

.success-message {
  padding: 12px 14px;
  background: #ecfdf5;
  color: #166534;
  border-radius: 8px;
  margin: 0;
  font-weight: 500;
}

.error-message {
  padding: 12px 14px;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 8px;
  margin: 0;
  font-weight: 500;
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.cancel-btn,
.submit-btn {
  padding: 12px 22px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: .2s;
  font-size: .95rem;
}

.cancel-btn {
  background: #f1f5f9;
  color: #0f172a;
  border: 1px solid #d1d5db;
}

.cancel-btn:hover {
  background: #e2e8f0;
}

.submit-btn {
  background: #2563eb;
  color: white;
}

.submit-btn:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>