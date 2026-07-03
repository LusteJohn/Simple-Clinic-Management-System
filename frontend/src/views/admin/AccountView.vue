<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)
const submitMessage = ref('')

const form = reactive({
  username: '',
  email: '',
  password: '',
})

onMounted(async () => {
  loadingSession.value = true

  const sessionUser = await auth.loadSession()

  if (!sessionUser) {
    router.push({ name: 'login' })
    return
  }

  loadingSession.value = false
})

async function submitForm() {
  submitMessage.value = ''

  try {
    await auth.registerDoctor(form)

    submitMessage.value = 'Doctor account created successfully.'

    form.username = ''
    form.email = ''
    form.password = ''
  } catch (error) {
    console.error(error)
    submitMessage.value = ''
  }
}

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

const hasUnsavedChanges = computed(() => {
  return (
    form.username.trim() !== '' ||
    form.email.trim() !== '' ||
    form.password.trim() !== ''
  )
})

onBeforeRouteLeave((to, from, next) => {
  if (!hasUnsavedChanges.value) {
    next()
    return
  }

  const leave = window.confirm(
    'You have not finished creating the doctor account. Are you sure you want to leave this page?'
  )

  if (leave) {
    next()
  } else {
    next(false)
  }
})

</script>

<template>
  <main class="register-page">
    <section class="register-card">
      <p class="eyebrow">Administrator Panel</p>

      <h1>Create Doctor Account</h1>

      <p class="lead" v-if="loadingSession">
        Loading session...
      </p>

      <template v-else>
        <p class="lead">
          Logged in as
          <strong>{{ auth.user?.username }}</strong>
          ({{ auth.user?.role }}).
        </p>

        <form class="register-form" @submit.prevent="submitForm">
          <label>
            Username
            <input
              v-model="form.username"
              type="text"
              autocomplete="username"
              required
            />
          </label>

          <label>
            Email
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
            />
          </label>

          <label>
            Password
            <input
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              minlength="8"
              required
            />
          </label>

          <label>
            Role
            <input
              type="text"
              value="doctor"
              readonly
              class="readonly-input"
            />
          </label>

          <p v-if="auth.error" class="error">
            {{ auth.error }}
          </p>

          <p v-if="submitMessage" class="success">
            {{ submitMessage }}
          </p>

          <button
            type="submit"
            :disabled="auth.loading"
          >
            {{ auth.loading ? 'Creating Doctor...' : 'Create Doctor Account' }}
          </button>

          <button
            type="button"
            class="dashboard-button"
            @click="router.push({ name: 'dashboardAdmin' })"
            >
            Back to Dashboard
          </button>
        </form>
      </template>
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
  width: min(100%, 500px);
  background: #fff;
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
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #2563eb;
}

.readonly-input {
  background: #f8fafc;
  color: #64748b;
  cursor: not-allowed;
}

button {
  border: none;
  border-radius: 12px;
  padding: 0.95rem 1rem;
  background: #0f172a;
  color: white;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}

button:hover:not(:disabled) {
  background: #1e293b;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.logout-button {
  background: #dc2626;
}

.logout-button:hover:not(:disabled) {
  background: #b91c1c;
}

.error {
  color: #dc2626;
  margin: 0;
}

.success {
  color: #15803d;
  margin: 0;
}
</style>