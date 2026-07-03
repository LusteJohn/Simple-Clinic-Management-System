<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const submitMessage = ref('')

const form = reactive({
  username: '',
  email: '',
  password: '',
})

async function submitForm() {
  submitMessage.value = ''

  try {
    await auth.register(form)
    submitMessage.value = 'Registration submitted successfully.'
    console.info('Registration completed for', form.email)
  } catch (error) {
    console.error('Registration failed', error)
    submitMessage.value = 'Registration could not be completed.'
  }
}
</script>

<template>
  <main class="register-page">
    <section class="register-card">
      <p class="eyebrow">Patient onboarding</p>
      <h1>Create your account</h1>
      <p class="lead">Vue Router loads the form, Axios sends the request, and Pinia keeps the response state.</p>

      <form class="register-form" @submit.prevent="submitForm">
        <label>
          Username
          <input v-model="form.username" type="text" autocomplete="username" required />
        </label>

        <label>
          Email
          <input v-model="form.email" type="email" autocomplete="email" required />
        </label>

        <label>
          Password
          <input v-model="form.password" type="password" autocomplete="new-password" minlength="8" required />
        </label>

        <p v-if="auth.error" class="error">{{ auth.error }}</p>
        <p v-if="submitMessage" class="success">{{ submitMessage }}</p>
        <p v-if="auth.user" class="success">Welcome, {{ auth.user.username }}. Registration complete.</p>

        <button :disabled="auth.loading" type="submit">
          {{ auth.loading ? 'Creating account...' : 'Register' }}
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