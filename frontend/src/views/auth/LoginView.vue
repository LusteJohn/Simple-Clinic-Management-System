<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const submitMessage = ref('')

const form = reactive({
  identifier: '',
  password: '',
})

async function submitForm() {
  submitMessage.value = ''

  try {
    const loginResult = await auth.login(form)
    submitMessage.value = 'Login successful.'
    console.info('Login completed for', form.identifier)

    const role = loginResult?.user?.role || auth.user?.role

    if (!role) {
      throw new Error('Login succeeded but no user role was returned.')
    }

    const roleRoutes = {
      admin: 'adminDashboard',
      patient: 'patientDashboard',
      doctor: 'doctorDashboard',
      staff: 'patientDashboard',
    }

    await router.push({ name: roleRoutes[role] || 'patientDashboard' })
  } catch (error) {
    console.error('Login failed', error)
    submitMessage.value = 'Login could not be completed.'
  }
}
</script>

<template>
  <main class="register-page">
    <section class="register-card">
      <p class="eyebrow">Users access</p>
      <h1>Login to your account</h1>
      <p class="lead">Use either your username or email address, then enter your password.</p>

      <form class="register-form" @submit.prevent="submitForm">
        <label>
          Username or Email
          <input v-model="form.identifier" type="text" autocomplete="username email" required />
        </label>

        <label>
          Password
          <input v-model="form.password" type="password" autocomplete="current-password" required />
        </label>

        <p v-if="auth.error" class="error">{{ auth.error }}</p>
        <p v-if="submitMessage" class="success">{{ submitMessage }}</p>
        <p v-if="auth.user" class="success">Welcome back, {{ auth.user.username }}.</p>

        <button :disabled="auth.loading" type="submit">
          {{ auth.loading ? 'Signing in...' : 'Login' }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem;
  background: linear-gradient(135deg, #f4f7fb 0%, #e6eefc 100%);
  font-family: Inter, system-ui, sans-serif;
}

.register-card {
  width: min(100%, 480px);
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
}

.eyebrow {
  margin: 0 0 0.5rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: #2563eb;
}

h1 {
  margin: 0;
  font-size: 2rem;
  color: #0f172a;
}

.lead {
  margin: 0.75rem 0 1.5rem;
  color: #475569;
}

.register-form {
  display: grid;
  gap: 1rem;
}

label {
  display: grid;
  gap: 0.4rem;
  font-weight: 600;
  color: #1e293b;
}

input {
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 0.9rem 1rem;
  font: inherit;
}

button {
  border: 0;
  border-radius: 12px;
  padding: 0.95rem 1rem;
  background: #0f172a;
  color: white;
  font-weight: 700;
}

button:disabled {
  opacity: 0.7;
}

.error {
  margin: 0;
  color: #b91c1c;
}

.success {
  margin: 0;
  color: #166534;
}
</style>