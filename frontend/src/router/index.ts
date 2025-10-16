import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'Login',
            component: () => import('@/views/Login.vue')
        },
        {
            path: '/register',
            name: 'Register',
            component: () => import('@/views/Register.vue')
        },
        {
            path: '/',
            name: 'Home',
            component: () => import('@/views/Home.vue'),
            meta: { requiresAuth: true }
        },
        {
            path: '/sellers',
            name: 'Sellers',
            component: () => import('@/views/Sellers.vue'),
            meta: { requiresAuth: true }
        },
        {
            path: '/sales',
            name: 'Sales',
            component: () => import('@/views/Sales.vue'),
            meta: { requiresAuth: true }
        }
    ]
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if ((to.name === 'Login' || to.name === 'Register') && authStore.isAuthenticated) {
        next('/')
    } else {
        next()
    }
})

export default router
