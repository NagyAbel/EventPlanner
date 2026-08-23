import { createRouter, createWebHistory } from 'vue-router'
import Home from '@/pages/Home.vue'
import UserAuth from '@/pages/UserAuth.vue'
import Profile from '@/pages/Profile.vue'
import EventView from '@/pages/EventView.vue'
import NotFound from '@/pages/NotFound.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      component: Home,
    },
    {
      path: '/auth',
      component: UserAuth,
      meta: {
        requiresGuest: true,
      },
    },
    {
      path: '/profile',
      component: Profile,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/events/:id',
      name: 'event-view',
      component: EventView,
    },
    {
      path: '/:pathMatch(.*)*',
      component: NotFound,
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Restore the Sanctum session on the first navigation.
  await auth.ensureInitialized()

  // Protect private routes from guests
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return {
      path: '/auth',
      query: { redirect: to.fullPath },
    }
  }

  // Prevent authenticated users from visiting guest-only routes
  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return {
      path: '/',
    }
  }

  return true
})

export default router