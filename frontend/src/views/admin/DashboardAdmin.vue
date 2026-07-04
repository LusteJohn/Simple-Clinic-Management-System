<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/admin/SideBar.vue'

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
  <div class="layout">
    <SideBar />

    <main class="content">
      <div class="dashboard-card">
        <p class="eyebrow">Admin</p>

        <h1>Dashboard</h1>

        <p
          v-if="loadingSession"
          class="lead"
        >
          Loading session...
        </p>

        <template v-else>
          <p class="lead">
            Welcome back,
            <strong>{{ auth.user?.username }}</strong>
          </p>

          <p class="lead">
            Role:
            <strong>{{ auth.user?.role }}</strong>
          </p>
        </template>
      </div>
    </main>
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f1f5f9;
}
.content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}
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

.register-doctor-button {
  margin-top: 1rem;
  border: 0;
  border-radius: 12px;
  padding: 0.95rem 1rem;
  background: #2563eb;
  color: white;
  font-weight: 700;
}

.logout-button:disabled {
  opacity: 0.7;
}
</style>
