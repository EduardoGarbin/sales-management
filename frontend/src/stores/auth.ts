import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { User, LoginRequest, RegisterRequest } from '@/types'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('token'))
    const isAuthenticated = ref<boolean>(!!token.value)

    async function login(credentials: LoginRequest) {
        const response = await api.login(credentials)
        token.value = response.token
        user.value = response.user
        isAuthenticated.value = true
        localStorage.setItem('token', response.token)
    }

    async function register(data: RegisterRequest) {
        const response = await api.register(data)
        token.value = response.token
        user.value = response.user
        isAuthenticated.value = true
        localStorage.setItem('token', response.token)
    }

    async function logout() {
        await api.logout()
        token.value = null
        user.value = null
        isAuthenticated.value = false
        localStorage.removeItem('token')
    }

    async function fetchUser() {
        if (token.value) {
            const response = await api.getMe()
            user.value = response.data
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser
    }
})
