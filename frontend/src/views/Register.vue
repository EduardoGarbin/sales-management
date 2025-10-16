<template>
    <div class="auth-container">
        <div class="form-card">
            <h1>Cadastro</h1>
            <form @submit.prevent="handleRegister">
                <div class="form-group">
                    <label>Nome:</label>
                    <input v-model="name" type="text" required />
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input v-model="email" type="email" required />
                </div>
                <div class="form-group">
                    <label>Senha:</label>
                    <input v-model="password" type="password" required />
                </div>
                <div class="form-group">
                    <label>Confirmar Senha:</label>
                    <input v-model="password_confirmation" type="password" required />
                </div>
                <div class="error" v-if="error">{{ error }}</div>
                <button type="submit" :disabled="loading">
                    {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
                </button>
            </form>
            <p>Já tem conta? <router-link to="/login">Faça login</router-link></p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const error = ref('')

async function handleRegister() {
    loading.value = true
    error.value = ''
    try {
        await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        })
        router.push('/')
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Erro ao cadastrar'
    } finally {
        loading.value = false
    }
}
</script>
