<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/doctor/SideBar.vue'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)
const loadingSchedule = ref(false)
const saving = ref(false)
const savingSchedule = ref(false)

const scheduleExists = ref(false)
const success = ref('')
const error = ref('')

const scheduleForm = reactive({
    day_of_week: '',
    start_time: '',
    end_time: '',
    slot_duration_min: ''
})

onMounted(async () => {
    loadingSession.value = true

    const session = await auth.loadSession()

    if (!session) {
        router.push({ name: 'login' })
        return
    }

    await loadSchedule()

    loadingSession.value = false
})

async function loadSchedule() {
    loadingSchedule.value = true

    try {
        const data = await auth.loadDoctorSchedule()

        if (data.doctor_schedule) {
            scheduleExists.value = true

            scheduleForm.day_of_week = data.doctor_schedule.day_of_week
            scheduleForm.start_time = data.doctor_schedule.start_time
            scheduleForm.end_time = data.doctor_schedule.end_time
            scheduleForm.slot_duration_min = data.doctor_schedule.slot_duration_min
        }
    } catch (err) {
        error.value = 'Failed to load schedule.'
    } finally {
        loadingSchedule.value = false
    }
}

async function saveSchedule() {
    savingSchedule.value = true
    error.value = ''
    success.value = ''

    try {
        if (scheduleExists.value) {
            await auth.updateDoctorSchedule(scheduleForm)
            success.value = 'Schedule updated successfully.'
        } else {
            await auth.createDoctorSchedule(scheduleForm)
            scheduleExists.value = true
            success.value = 'Schedule created successfully.'
        }
    } catch (err) {
        error.value = 'Failed to save schedule.'
    } finally {
        savingSchedule.value = false
    }
}

async function deleteSchedule() {
    savingSchedule.value = true
    error.value = ''
    success.value = ''

    try {
        await auth.deleteDoctorSchedule()
        scheduleExists.value = false
        scheduleForm.day_of_week = ''
        scheduleForm.start_time = ''
        scheduleForm.end_time = ''
        scheduleForm.slot_duration_min = ''
        success.value = 'Schedule deleted successfully.'
    } catch (err) {
        error.value = 'Failed to delete schedule.'
    } finally {
        savingSchedule.value = false
    }
}

async function handleLogout() {
    await auth.logout()
    router.push({ name: 'login' })
}

</script>
<template>
    <div class="layout">
        <SideBar />

        <main class="schedule-page">
            <section class="schedule-card">

                <div class="header">
                    <div>
                        <p class="eyebrow">Doctor Portal</p>
                        <h1>Clinic Schedule</h1>
                        <p class="lead">
                            Configure your available consultation schedule.
                        </p>
                        <p class="lead">
                            Logged in as
                            <strong>{{ auth.user?.username }}</strong>
                        </p>
                    </div>
                </div>

                <div v-if="loadingSession || loadingSchedule" class="loading">
                    Loading schedule...
                </div>

                <form v-else class="schedule-form" @submit.prevent="saveSchedule">

                    <div class="grid">

                        <label>
                            Day of Week
                            <select v-model="scheduleForm.day_of_week" required>
                                <option disabled value="">Select Day</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="7">Sunday</option>
                            </select>
                        </label>

                        <label>
                            Appointment Duration
                            <input type="number" min="5" v-model.number="scheduleForm.slot_duration_min"
                                placeholder="30" required />
                        </label>

                        <label>
                            Start Time
                            <input type="time" v-model="scheduleForm.start_time" required />
                        </label>

                        <label>
                            End Time
                            <input type="time" v-model="scheduleForm.end_time" required />
                        </label>

                    </div>

                    <div class="alert success" v-if="success">
                        {{ success }}
                    </div>

                    <div class="alert error" v-if="error">
                        {{ error }}
                    </div>

                    <div class="actions">

                        <button class="primary-btn" type="submit" :disabled="savingSchedule">
                            {{
                                savingSchedule
                                    ? 'Saving...'
                                    : scheduleExists
                                        ? 'Update Schedule'
                            : 'Create Schedule'
                            }}
                        </button>

                        <button v-if="scheduleExists" class="danger-btn" type="button" @click="deleteSchedule"
                            :disabled="savingSchedule">
                            Delete Schedule
                        </button>

                    </div>

                </form>

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

.schedule-page {
  flex: 1;
  padding: 40px;
  background: #f8fafc;
}

.schedule-card {
  max-width: 900px;
  margin: auto;
  background: #fff;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 15px 40px rgba(0,0,0,.08);
}

.header {
  margin-bottom: 35px;
}

.eyebrow {
  color: #2563eb;
  text-transform: uppercase;
  letter-spacing: .12em;
  font-size: .8rem;
  font-weight: 700;
}

.header h1 {
  margin-top: 8px;
  font-size: 2rem;
  color: #0f172a;
}

.lead {
  margin-top: 8px;
  color: #64748b;
}

.schedule-form {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2,1fr);
  gap: 22px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-weight: 600;
  color: #334155;
}

input,
select {
  padding: 13px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  font-size: .95rem;
  transition: .2s;
}

input:focus,
select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37,99,235,.12);
}

.actions {
  display: flex;
  gap: 15px;
}

.primary-btn {
  background: #2563eb;
  color: white;
  border: none;
  padding: 14px 26px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: .2s;
}

.primary-btn:hover {
  background: #1d4ed8;
}

.primary-btn:disabled {
  opacity: .7;
  cursor: not-allowed;
}

.danger-btn {
  background: #dc2626;
  color: white;
  border: none;
  padding: 14px 26px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: .2s;
}

.danger-btn:hover {
  background: #b91c1c;
}

.danger-btn:disabled {
  opacity: .7;
}

.alert {
  padding: 14px;
  border-radius: 10px;
  font-weight: 600;
}

.success {
  background: #ecfdf5;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.loading {
  text-align: center;
  color: #64748b;
  padding: 40px;
}

@media (max-width: 768px) {

  .schedule-page {
    padding: 20px;
  }

  .schedule-card {
    padding: 25px;
  }

  .grid {
    grid-template-columns: 1fr;
  }

  .actions {
    flex-direction: column;
  }

  .primary-btn,
  .danger-btn {
    width: 100%;
  }

}
</style>