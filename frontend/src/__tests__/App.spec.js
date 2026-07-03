import { describe, it, expect } from 'vitest'

import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import App from '../App.vue'
import RegisterView from '../views/auth/RegisterView.vue'
import AccountView from '../views/admin/AccountView.vue'

describe('App', () => {
  it('renders the routed shell', async () => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/register',
          component: RegisterView,
        },
        {
          path: '/register-doctor',
          component: AccountView,
        }
      ],
    })

    router.push('/register')
    await router.isReady()

    const wrapper = mount(App, {
      global: {
        plugins: [router, createPinia()],
      },
    })

    expect(wrapper.text()).toContain('Create your account')
  })
})
