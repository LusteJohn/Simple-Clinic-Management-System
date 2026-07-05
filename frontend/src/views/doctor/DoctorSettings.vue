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

          <button class="save" type="submit" :disabled="savingInfo">
            {{
              savingInfo
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