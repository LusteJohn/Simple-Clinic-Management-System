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

const showCreateModal = ref(false)

const doctors = ref([])

function openCreateModal() {
    showCreateModal.value = true
}

function closeCreateModal() {
    showCreateModal.value = false

    form.username = ''
    form.email = ''
    form.password = ''
}

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
      <template v-if="loadingSession">
        <p class="lead">Loading session...</p>
      </template>

      <template v-else>
        <!-- Header -->
        <div class="page-header">
          <div>
            <p class="eyebrow">Administrator Panel</p>
            <h1>Doctor Management</h1>

            <p class="lead">
              Logged in as
              <strong>{{ auth.user?.username }}</strong>
              ({{ auth.user?.role }})
            </p>
          </div>

          <div class="header-actions">
            <button
              type="button"
              class="create-button"
              @click="openCreateModal"
            >
              + Create Doctor
            </button>

            <button
              type="button"
              class="dashboard-button"
              @click="router.push({ name: 'adminDashboard' })"
            >
              Back to Dashboard
            </button>
          </div>
        </div>

        <!-- Success/Error -->
        <p
          v-if="submitMessage"
          class="success"
        >
          {{ submitMessage }}
        </p>

        <p
          v-if="auth.error"
          class="error"
        >
          {{ auth.error }}
        </p>

        <!-- Doctor Table -->
        <div class="table-wrapper">
          <table class="doctor-table">
            <thead>
              <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th width="180">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="doctor in doctors"
                :key="doctor.user_id"
              >
                <td>{{ doctor.username }}</td>
                <td>{{ doctor.email }}</td>
                <td>{{ doctor.role }}</td>

                <td>
                  <span class="status active">
                    Active
                  </span>
                </td>

                <td>
                  <button
                    type="button"
                    class="action-button edit"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="action-button delete"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="!doctors.length">
                <td
                  colspan="5"
                  class="empty"
                >
                  No doctor accounts available.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Create Doctor Modal -->
        <div
          v-if="showCreateModal"
          class="modal-overlay"
        >
          <div class="modal-card">
            <div class="modal-header">
              <h2>Create Doctor Account</h2>

              <button
                type="button"
                class="close-button"
                @click="closeCreateModal"
              >
                ✕
              </button>
            </div>

            <form
              class="register-form"
              @submit.prevent="submitForm"
            >
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
                  value="Doctor"
                  readonly
                  class="readonly-input"
                />
              </label>

              <div class="modal-actions">
                <button
                  type="submit"
                  :disabled="auth.loading"
                >
                  {{ auth.loading ? 'Creating Doctor...' : 'Create Doctor' }}
                </button>

                <button
                  type="button"
                  class="cancel-button"
                  @click="closeCreateModal"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </template>
    </section>
  </main>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  padding: 2rem;
  background: linear-gradient(135deg, #f4f7fb 0%, #e6eefc 100%);
  font-family: Inter, system-ui, sans-serif;
}

.register-card {
  max-width: 1100px;
  margin: auto;
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
  margin-top: 0.75rem;
  color: #64748b;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  margin-bottom: 2rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.create-button,
.dashboard-button,
button[type="submit"] {
  border: none;
  border-radius: 10px;
  padding: 0.85rem 1.3rem;
  cursor: pointer;
  color: white;
  font-weight: 600;
  transition: .2s;
}

.create-button {
  background: #2563eb;
}

.create-button:hover {
  background: #1d4ed8;
}

.dashboard-button {
  background: #475569;
}

.dashboard-button:hover {
  background: #334155;
}

.table-wrapper {
  overflow-x: auto;
}

.doctor-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.doctor-table th {
  background: #f8fafc;
  color: #0f172a;
  text-align: left;
  padding: 1rem;
  border-bottom: 2px solid #e2e8f0;
}

.doctor-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.doctor-table tbody tr:hover {
  background: #f8fafc;
}

.empty {
  text-align: center;
  color: #64748b;
  padding: 2rem;
}

.status {
  display: inline-block;
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  font-size: .8rem;
  font-weight: 600;
}

.status.active {
  background: #dcfce7;
  color: #166534;
}

.action-button {
  border: none;
  border-radius: 8px;
  padding: .5rem .9rem;
  margin-right: .5rem;
  cursor: pointer;
  color: white;
  transition: .2s;
}

.action-button.edit {
  background: #f59e0b;
}

.action-button.edit:hover {
  background: #d97706;
}

.action-button.delete {
  background: #dc2626;
}

.action-button.delete:hover {
  background: #b91c1c;
}

/* ===========================
   Modal
=========================== */

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
  backdrop-filter: blur(4px);
}

.modal-card {
  width: min(100%, 500px);
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 25px 60px rgba(0,0,0,.25);
  animation: popup .25s ease;
}

@keyframes popup {
  from {
    transform: translateY(15px) scale(.98);
    opacity: 0;
  }

  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h2 {
  margin: 0;
}

.close-button {
  background: transparent;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: #64748b;
}

.close-button:hover {
  color: #dc2626;
}

.register-form {
  display: grid;
  gap: 1rem;
}

.register-form label {
  display: grid;
  gap: .4rem;
  font-weight: 600;
  color: #1e293b;
}

.register-form input {
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: .85rem 1rem;
  font: inherit;
}

.register-form input:focus {
  outline: none;
  border-color: #2563eb;
}

.readonly-input {
  background: #f8fafc;
  color: #64748b;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-button {
  background: #64748b;
  color: white;
  border: none;
  border-radius: 10px;
  padding: .85rem 1.3rem;
  cursor: pointer;
}

.cancel-button:hover {
  background: #475569;
}

button[type="submit"] {
  background: #0f172a;
}

button[type="submit"]:hover {
  background: #1e293b;
}

button:disabled {
  opacity: .7;
  cursor: not-allowed;
}

.error {
  color: #dc2626;
  margin-bottom: 1rem;
}

.success {
  color: #15803d;
  margin-bottom: 1rem;
}

/* ===========================
   Responsive
=========================== */

@media (max-width: 768px) {

  .page-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .create-button,
  .dashboard-button {
    flex: 1;
  }

  .doctor-table {
    font-size: .9rem;
  }

  .action-button {
    margin-bottom: .4rem;
  }

  .modal-card {
    width: 92%;
    padding: 1.5rem;
  }
}
</style>