<template>
    <div class="container">
        <button @click="router.push('/')" class="btn-back">← Voltar</button>
        <h1>Vendedores</h1>

        <div class="form-card">
            <h2>Novo Vendedor</h2>
            <form @submit.prevent="handleCreate">
                <div class="form-group">
                    <label>Nome:</label>
                    <input v-model="form.name" type="text" required />
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input v-model="form.email" type="email" required />
                </div>
                <div class="error" v-if="error">{{ error }}</div>
                <button type="submit" :disabled="loading">Cadastrar</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Lista de Vendedores</h2>
            <table v-if="sellers.length">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Taxa Comissão</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="seller in sellers" :key="seller.id">
                        <td>{{ seller.id }}</td>
                        <td>{{ seller.name }}</td>
                        <td>{{ seller.email }}</td>
                        <td>{{ seller.commission_rate || 8.5 }}%</td>
                        <td>
                            <button @click="viewSales(seller.id)" class="btn-small">Ver Vendas</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else>Nenhum vendedor cadastrado.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { Seller } from '@/types'

const router = useRouter()
const sellers = ref<Seller[]>([])
const form = ref({ name: '', email: '' })
const loading = ref(false)
const error = ref('')

async function loadSellers() {
    try {
        const response = await api.getSellers()
        sellers.value = response.data
    } catch (err: any) {
        console.error('Erro ao carregar vendedores:', err)
    }
}

async function handleCreate() {
    loading.value = true
    error.value = ''
    try {
        await api.createSeller(form.value)
        form.value = { name: '', email: '' }
        await loadSellers()
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Erro ao cadastrar vendedor'
    } finally {
        loading.value = false
    }
}

function viewSales(sellerId: number) {
    router.push(`/sales?seller=${sellerId}`)
}

onMounted(loadSellers)
</script>
