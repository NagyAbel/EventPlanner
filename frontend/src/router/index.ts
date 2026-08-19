import { createRouter, createWebHistory } from 'vue-router'
import Home from '@/pages/Home.vue'
import UserAuth from '@/pages/UserAuth.vue'
import UserSettings from '@/pages/UserSettings.vue'
import NotFound from '@/pages/NotFound.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: Home
    },
    {
      path: '/auth',
      component: UserAuth
    },
    {
      path: '/profile',
      component: UserSettings
    },
    {
     path: '/:pathMatch(.*)*',
     component: NotFound
    }

  ],
})

export default router
