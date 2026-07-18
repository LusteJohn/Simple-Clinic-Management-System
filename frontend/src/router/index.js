import { createRouter, createWebHistory } from 'vue-router'
import RegisterView from '@/views/auth/RegisterView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import DashboardAdmin from '@/views/admin/DashboardAdmin.vue'
import DashboardPatient from '@/views/patient/DashboardPatient.vue'
import AccountView from '@/views/admin/AccountView.vue'
import DashboardDoctor from '@/views/doctor/DashboardDoctor.vue'
import DoctorSettings from '@/views/doctor/DoctorSettings.vue'
import PatientSettings from '@/views/patient/PatientSettings.vue'
import DoctorScheduleView from '@/views/doctor/DoctorScheduleView.vue'
import PatientsListView from '@/views/admin/PatientListView.vue'
import DoctorScheduleList from '@/views/patient/DoctorScheduleList.vue'

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
    {
      path: '/doctorDashboard',
      name: 'doctorDashboard',
      component: DashboardDoctor,
    },
    {
      path: '/doctor-profile',
      name: 'doctorProfile',
      component: DoctorSettings,
    },
  
    {
      path: '/patient-profile',
      name: 'patientProfile',
      component: PatientSettings,
    },
    {
      path: '/register-doctor',
      name: 'registerDoctor',
      component: AccountView,
    },
    {
      path: '/update-doctor/:userId',
      name: 'updateDoctor',
      component: AccountView,
    },
    {
      path: '/doctor-schedule',
      name: 'doctorSchedule',
      component: DoctorScheduleView,
    },
    {
      path: '/patients-list',
      name: 'patientsList',
      component: PatientsListView,
    },
    {
      path: '/doctor-schedule-list',
      name: 'doctorScheduleList',
      component: DoctorScheduleList,
    },
  ],
})

export default router
