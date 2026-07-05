<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/doctor/SideBar.vue'
import api from '@/services/api'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)
const loadingProfile = ref(false)
const saving = ref(false)
const savingInfo = ref(false)

const profileExists = ref(false)
const doctorInfoExists = ref(false)
const success = ref('')
const infoSuccess = ref('')
const error = ref('')
const doctorInfoError = ref('')

const form = reactive({
  firstname: '',
  middlename: '',
  lastname: '',
  name_ext: '',
  gender: ''
})

const doctorInfoForm = reactive({
  specialization: '',
  license_number: '',
  years_of_experience: '',
  consultation_fees: ''
})

onMounted(async () => {
  loadingSession.value = true

  const session = await auth.loadSession()

  if (!session) {
    router.push({ name: 'login' })
    return
  }

  await loadProfile()
  await loadDoctorInfo()

  loadingSession.value = false
})

async function loadProfile() {
  loadingProfile.value = true

  try {
    const data = await auth.getDoctorProfile()

    if (data.doctor) {
      profileExists.value = true

      form.firstname = data.doctor.firstname
      form.middlename = data.doctor.middlename
      form.lastname = data.doctor.lastname
      form.name_ext = data.doctor.name_ext
      form.gender = data.doctor.gender
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
      await auth.updateDoctorProfile(form)

      success.value = 'Profile updated successfully.'
    } else {
      await auth.createDoctorProfile(form)

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

async function loadDoctorInfo() {
  try {
    const data = await auth.getDoctorInfo()

    if (data.doctor_info) {
      doctorInfoForm.specialization = data.doctor_info.specialization
      doctorInfoForm.license_number = data.doctor_info.license_number
      doctorInfoForm.years_of_experience = data.doctor_info.years_of_experience
      doctorInfoForm.consultation_fees = data.doctor_info.consultation_fees
      doctorInfoExists.value = true
    }
  } catch (err) {
    console.error('Error loading doctor info:', err)
  }
}

async function saveDoctorInfo() {
  savingInfo.value = true
  infoSuccess.value = ''
  doctorInfoError.value = ''

  try {
    if (doctorInfoExists.value) {
      await auth.updateDoctorInfo(doctorInfoForm)

      infoSuccess.value = 'Doctor info updated successfully.'
    } else {
      await auth.createDoctorInfo(doctorInfoForm)

      infoSuccess.value = 'Doctor info created successfully.'
    }
  } catch (err) {
    doctorInfoError.value =
      err.response?.data?.message ??
      'Unable to save doctor info.'
  } finally {
    savingInfo.value = false
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
            <p class="eyebrow">Doctor Portal</p>
            <h1>Doctor Settings</h1>

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

      <!-- Doctor Professional Information -->
      <section class="info-card">
        <div class="section-header">
          <h2>Professional Information</h2>
          <p>
            Provide your professional credentials and consultation details.
          </p>
        </div>

        <form class="profile-form" @submit.prevent="saveDoctorInfo">
          <div class="grid">

            <label>
              Specialization
              <input v-model="doctorInfoForm.specialization" type="text" placeholder="e.g. Internal Medicine"
                required />
            </label>

            <label>
              License Number
              <input v-model="doctorInfoForm.license_number" type="text" placeholder="PRC License Number" required />
            </label>

            <label>
              Years of Experience
              <input v-model="doctorInfoForm.years_of_experience" type="number" min="0" placeholder="0" required />
            </label>

            <label>
              Consultation Fee (₱)
              <input v-model="doctorInfoForm.consultation_fees" type="number" min="0" step="0.01" placeholder="500.00"
                required />
            </label>

          </div>

          <p v-if="infoSuccess" class="success">
            {{ infoSuccess }}
          </p>

          <p v-if="doctorInfoError" class="error">
            {{ doctorInfoError }}
          </p>

          <button class="save" :disabled="savingInfo">
            {{
              saveDoctorInfo
                ? 'Saving...'
                : doctorInfoExists
                  ? 'Update Professional Information'
                  : 'Save Professional Information'
            }}
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
  min-height: 100vh;
  background: #f8fafc;
  display: flex;
  justify-content: center;
  padding: 40px;
}

.settings-card {
  width: 850px;
  background: white;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, .08);
}

.info-card {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.section-header {
  margin-bottom: 1.5rem;
}

.section-header h2 {
  margin: 0;
  font-size: 1.35rem;
  color: #0f172a;
}

.section-header p {
  margin-top: .4rem;
  color: #64748b;
  font-size: .95rem;
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
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-weight: 600;
}

input,
select {
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-size: .95rem;
}

input:focus,
select:focus {
  outline: none;
  border-color: #2563eb;
}

.save {
  width: 220px;
  padding: 14px;
  border: none;
  border-radius: 12px;
  background: #2563eb;
  color: white;
  cursor: pointer;
}

.logout {
  background: #dc2626;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
}

.success {
  color: #15803d;
}

.error {
  color: #dc2626;
}
</style>