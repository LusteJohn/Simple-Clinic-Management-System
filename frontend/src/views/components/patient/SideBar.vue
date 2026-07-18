<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const menus = computed(() => [
  {
    label: 'Dashboard',
    route: 'patientDashboard',
    icon: '',
  },
  {
    label: 'Patient Profile',
    route: 'patientProfile',
    icon: '',
  },
  {
    label: 'Doctor Schedule List',
    route: 'doctorScheduleList',
    icon: '',
  },
])

async function logout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <aside class="sidebar">
    <div>
      <div class="brand">
        <div class="brand-logo">
        </div>

        <div>
          <h2>ClinicSys</h2>
          <p>Patient Portal</p>
        </div>
      </div>

      <nav class="navigation">
        <button
          v-for="menu in menus"
          :key="menu.route"
          class="nav-item"
          :class="{ active: route.name === menu.route }"
          @click="router.push({ name: menu.route })"
        >
          <span class="icon">
            {{ menu.icon }}
          </span>

          <span>
            {{ menu.label }}
          </span>
        </button>
      </nav>
    </div>

    <div class="profile-section">
      <div class="profile-card">
        <div class="avatar">
          {{ auth.user?.username?.charAt(0).toUpperCase() }}
        </div>

        <div class="profile-info">
          <strong>{{ auth.user?.username }}</strong>
          <small>{{ auth.user?.role }}</small>
        </div>
      </div>

      <button
        class="logout-btn"
        @click="logout"
      >
        Logout
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 280px;
  min-height: 100vh;
  background: #6896c4;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 28px;
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 42px;
}

.brand-logo {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  display: grid;
  place-items: center;
  font-size: 1.5rem;
}

.brand h2 {
  margin: 0;
  font-size: 1.25rem;
}

.brand p {
  margin: 4px 0 0;
  color: #94a3b8;
  font-size: .85rem;
}

.navigation {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  width: 100%;
  border: none;
  background: transparent;
  color: #cbd5e1;
  padding: 14px 16px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: .95rem;
  cursor: pointer;
  transition: all .25s ease;
}

.nav-item:hover {
  background: rgba(255,255,255,.08);
  color: white;
}

.nav-item.active {
  background: #2563eb;
  color: white;
  box-shadow: 0 10px 24px rgba(37,99,235,.35);
}

.icon {
  width: 24px;
  text-align: center;
  font-size: 1.1rem;
}

.profile-section {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.profile-card {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 18px;
  padding: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #2563eb;
  display: grid;
  place-items: center;
  font-weight: bold;
  font-size: 1.2rem;
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.profile-info strong {
  font-size: .95rem;
}

.profile-info small {
  color: #94a3b8;
  text-transform: capitalize;
}

.logout-btn {
  border: none;
  background: #dc2626;
  color: white;
  padding: 14px;
  border-radius: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: .25s;
}

.logout-btn:hover {
  background: #b91c1c;
}
</style>