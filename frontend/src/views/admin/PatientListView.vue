<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SideBar from '@/views/components/admin/SideBar.vue'

const auth = useAuthStore()
const router = useRouter()

const loadingSession = ref(true)

const patients = ref([])
const search = ref('')

async function loadPatients() {
    loadingSession.value = true

    try {
        const data = await auth.getAllPatients()
        patients.value = data.patients || []
    } catch (err) {
        console.error('Failed to load patients:', err)
    } finally {
        loadingSession.value = false
    }
}

const filteredPatients = computed(() => {
  return patients.value.filter(patient => {

    const keyword = search.value.toLowerCase()

    return (
      patient.firstname.toLowerCase().includes(keyword) ||
      patient.lastname.toLowerCase().includes(keyword) ||
      patient.gender.toLowerCase().includes(keyword)
    )
  })
})

onMounted(async () => {
    loadingSession.value = true

    const session = await auth.loadSession()

    if (!session) {
        router.push({ name: 'login' })
        return
    }

    await loadPatients()

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
        <main class="patients-page">

            <div class="page-header">
                <div>
                    <p class="eyebrow">Admin Panel</p>
                    <h1>Patient Management</h1>
                    <p class="subtitle">
                        View and manage all registered patients.
                    </p>
                    <p class="lead">
                        Logged in as
                        <strong>{{ auth.user?.username }}</strong>
                        ({{ auth.user?.role }})
                    </p>
                </div>

                <button class="refresh-btn" @click="loadPatients">
                    Refresh
                </button>
            </div>

            <div class="table-card">

                <div class="table-header">
                    <input v-model="search" type="text" placeholder="Search patient..." class="search-input" />
                </div>

                <div v-if="loadingSession" class="loading">
                    Loading patients...
                </div>

                <table v-else>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Gender</th>
                            <th>Date Registered</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-for="(patient, index) in filteredPatients" :key="patient.user_id">
                            <td>{{ index + 1 }}</td>

                            <td>
                                <div class="patient-info">

                                    <div class="avatar">
                                        {{ patient.firstname.charAt(0) }}
                                    </div>

                                    <div>
                                        <strong>
                                            {{ patient.lastname }},
                                            {{ patient.firstname }}
                                            {{ patient.middlename }}
                                            {{ patient.name_ext }}
                                        </strong>

                                        <small>User ID : {{ patient.user_id }}</small>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="badge" :class="patient.gender.toLowerCase()">
                                    {{ patient.gender }}
                                </span>
                            </td>

                            <td>
                                {{ new Date(patient.created_at).toLocaleDateString() }}
                            </td>

                            <td>

                                <button class="view-btn">
                                    View
                                </button>

                                <button class="edit-btn">
                                    Edit
                                </button>

                                <button class="delete-btn">
                                    Delete
                                </button>

                            </td>

                        </tr>

                        <tr v-if="filteredPatients.length === 0">
                            <td colspan="5" class="empty">
                                No patients found.
                            </td>
                        </tr>

                    </tbody>

                </table>

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
.patients-page{
    flex:1;
    padding:40px;
    background:#f8fafc;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.eyebrow{
    color:#2563eb;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.1em;
    font-weight:700;
}

.subtitle{
    color:#64748b;
    margin-top:5px;
}

.refresh-btn{
    padding:12px 22px;
    border:none;
    border-radius:10px;
    background:#2563eb;
    color:#fff;
    cursor:pointer;
    font-weight:600;
}

.table-card{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
}

.table-header{
    display:flex;
    justify-content:flex-end;
    margin-bottom:20px;
}

.search-input{
    width:300px;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}

table{
    width:100%;
    border-collapse:collapse;
}

thead{
    background:#f1f5f9;
}

th{
    padding:16px;
    text-align:left;
    font-size:.9rem;
    color:#475569;
}

td{
    padding:16px;
    border-bottom:1px solid #e2e8f0;
}

.patient-info{
    display:flex;
    gap:15px;
    align-items:center;
}

.avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
    font-size:1rem;
}

.patient-info small{
    display:block;
    color:#64748b;
    margin-top:3px;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:.8rem;
    font-weight:600;
}

.badge.male{
    background:#dbeafe;
    color:#1d4ed8;
}

.badge.female{
    background:#fce7f3;
    color:#be185d;
}

.view-btn,
.edit-btn,
.delete-btn{
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
    margin-right:6px;
    font-size:.85rem;
}

.view-btn{
    background:#2563eb;
    color:white;
}

.edit-btn{
    background:#16a34a;
    color:white;
}

.delete-btn{
    background:#dc2626;
    color:white;
}

.loading,
.empty{
    text-align:center;
    color:#64748b;
    padding:40px;
}

@media(max-width:900px){

table{
    display:block;
    overflow-x:auto;
}

.page-header{
    flex-direction:column;
    align-items:flex-start;
    gap:15px;
}

.search-input{
    width:100%;
}
}
</style>