import { createRouter, createWebHistory } from 'vue-router'
import RegisterView from '@/views/auth/RegisterView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import DashboardAdmin from '@/views/admin/DashboardAdmin.vue'
import DashboardPatient from '@/views/patient/DashboardPatient.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/adminDashboard',
      name: 'adminDashboard',
      component: DashboardAdmin,
    },
    {
      path: '/patientDashboard',
      name: 'patientDashboard',
      component: DashboardPatient,
    },
  ],
})

export default router
