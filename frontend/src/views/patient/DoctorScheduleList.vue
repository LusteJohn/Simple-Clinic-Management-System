<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/patient/SideBar.vue'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)

const doctorSchedules = ref([])
const loadingSchedules = ref(false)

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
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else>No doctor schedules found.</div>
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
</style>