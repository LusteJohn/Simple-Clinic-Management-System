<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/patient/SideBar.vue'
import api from '@/services/api'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)
const loadingProfile = ref(false)
const saving = ref(false)

const profileExists = ref(false)
const success = ref('')
const error = ref('')

const form = reactive({
  firstname: '',
  middlename: '',
  lastname: '',
  name_ext: '',
  gender: ''
})

onMounted(async () => {
  loadingSession.value = true

  const session = await auth.loadSession()

  if (!session) {
    router.push({ name: 'login' })
    return
  }

  await loadPatientProfile()

  loadingSession.value = false
})

async function loadPatientProfile() {
  loadingProfile.value = true

  try {
    const data = await auth.getPatientProfile()

    if (data.patient) {
      profileExists.value = true

      form.firstname = data.patient.firstname
      form.middlename = data.patient.middlename
      form.lastname = data.patient.lastname
      form.name_ext = data.patient.name_ext
      form.gender = data.patient.gender
    }
  } catch (err) {
    profileExists.value = false
  } finally {
    loadingProfile.value = false
  }
}

async function saveProfile() {
  saving.value = true
  success.value = ''
  error.value = ''

  try {
    if (profileExists.value) {
      await auth.updatePatientProfile(form)

      success.value = 'Profile updated successfully.'
    } else {
      await auth.createPatientProfile(form)

      success.value = 'Profile created successfully.'
      profileExists.value = true
    }
  } catch (err) {
    error.value =
      err.response?.data?.message ??
      'Unable to save profile.'
  } finally {
    saving.value = false
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
    <main class="settings-page">
      <section class="settings-card">

        <div class="header">
          <div>
            <p class="eyebrow">Patient Portal</p>
            <h1>Patient Settings</h1>

            <p class="lead">
              Logged in as
              <strong>{{ auth.user?.username }}</strong>
            </p>
          </div>

          <button class="logout" @click="handleLogout">
            Logout
          </button>
        </div>

        <div v-if="loadingSession || loadingProfile">
          Loading...
        </div>

        <form v-else class="profile-form" @submit.prevent="saveProfile">
          <div class="grid">

            <label>
              First Name
              <input v-model="form.firstname" required>
            </label>

            <label>
              Middle Name
              <input v-model="form.middlename">
            </label>

            <label>
              Last Name
              <input v-model="form.lastname" required>
            </label>

            <label>
              Name Extension
              <input v-model="form.name_ext" placeholder="Jr., Sr., III">
            </label>

            <label>
              Gender
              <select v-model="form.gender">
                <option value="">Select Gender</option>
                <option>Male</option>
                <option>Female</option>
              </select>
            </label>

          </div>

          <p v-if="success" class="success">
            {{ success }}
          </p>

          <p v-if="error" class="error">
            {{ error }}
          </p>

          <button class="save" :disabled="saving">
            {{ saving ? 'Saving...' : profileExists ? 'Update Profile' : 'Create Profile' }}
          </button>
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

.settings-page {
  flex: 1;
  padding: 40px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
  gap: 28px;
  align-items: start;
}

.settings-card,
.info-card {
  background: #fff;
  border-radius: 18px;
  padding: 32px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
  border: 1px solid #e2e8f0;
}

.section-header {
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f172a;
}

.section-header p {
  margin-top: 6px;
  color: #64748b;
  line-height: 1.6;
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

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 22px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-weight: 600;
}

input,
select {
  width: 100%;
  margin-top: 6px;
  padding: 13px 15px;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  background: #fff;
  transition: .25s;
}

input:focus,
select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37,99,235,.12);
}

.save {
  align-self: flex-start;
  padding: 13px 24px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg,#2563eb,#1d4ed8);
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: .25s;
}

.save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(37,99,235,.25);
}

.save:disabled {
  opacity: .7;
  cursor: not-allowed;
}

.logout {
  background: #dc2626;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
}

.success,
.error {
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 500;
}

.success {
  background: #ecfdf5;
  color: #166534;
}

.error {
  background: #fef2f2;
  color: #b91c1c;
}
</style>