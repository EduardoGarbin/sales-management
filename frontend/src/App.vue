<template>
    <div id="app">
        <nav v-if="authStore.isAuthenticated" class="navbar">
            <div class="nav-brand">
                <router-link to="/">Sales Management</router-link>
            </div>
            <div class="nav-links">
                <router-link to="/">Home</router-link>
                <router-link to="/sellers">Vendedores</router-link>
                <router-link to="/sales">Vendas</router-link>
                <button @click="handleLogout" class="btn-logout">Sair</button>
            </div>
        </nav>
        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>

<style>
@import './style.css';
</style>
