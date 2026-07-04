<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const loadingSession = ref(true)

onMounted(async () => {
  loadingSession.value = true
  const sessionUser = await auth.loadSession()

  if (!sessionUser) {
    await router.push({ name: 'login' })
  }

  loadingSession.value = false
})

async function handleLogout() {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <main class="dashboard-page">
    <section class="dashboard-card">
      <p class="eyebrow">Doctor</p>
      <h1>Doctor Dashboard</h1>
      <p v-if="loadingSession" class="lead">Loading session...</p>
      <template v-else>
        <p v-if="auth.user" class="lead">Logged in as {{ auth.user.username }} with role {{ auth.user.role }}.</p>
        <p v-else class="lead">No active session found.</p>
        <button class="logout-button" :disabled="auth.loading" @click="handleLogout">
          {{ auth.loading ? 'Signing out...' : 'Logout' }}
        </button>
      </template>
    </section>
  </main>
</template>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  font-family: Inter, system-ui, sans-serif;
}

.dashboard-card {
  width: min(100%, 560px);
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
  margin: 0.75rem 0 0;
  color: #475569;
}

.logout-button {
  margin-top: 1.25rem;
  border: 0;
  border-radius: 12px;
  padding: 0.95rem 1rem;
  background: #0f172a;
  color: white;
  font-weight: 700;
}

.logout-button:disabled {
  opacity: 0.7;
}
</style>
