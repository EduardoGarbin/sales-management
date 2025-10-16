<template>
    <div class="auth-container">
        <div class="form-card">
            <h1>Login</h1>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label>Email:</label>
                    <input v-model="email" type="email" required />
                </div>
                <div class="form-group">
                    <label>Senha:</label>
                    <input v-model="password" type="password" required />
                </div>
                <div class="error" v-if="error">{{ error }}</div>
                <button type="submit" :disabled="loading">
                    {{ loading ? 'Entrando...' : 'Entrar' }}
                </button>
            </form>
            <p>Não tem conta? <router-link to="/register">Cadastre-se</router-link></p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
    loading.value = true
    error.value = ''
    try {
        await authStore.login({ email: email.value, password: password.value })
        router.push('/')
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Erro ao fazer login'
    } finally {
        loading.value = false
    }
}
</script>
