<template>
    <div class="container">
        <button @click="router.push('/')" class="btn-back">← Voltar</button>
        <h1>Vendas</h1>

        <div class="form-card">
            <h2>Nova Venda</h2>
            <form @submit.prevent="handleCreate">
                <div class="form-group">
                    <label>Vendedor:</label>
                    <select v-model="form.seller_id" required>
                        <option value="">Selecione um vendedor</option>
                        <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                            {{ seller.name }}
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor:</label>
                    <input v-model="form.amount" type="number" step="0.01" required />
                </div>
                <div class="form-group">
                    <label>Data:</label>
                    <input v-model="form.sale_date" type="date" required />
                </div>
                <div class="error" v-if="error">{{ error }}</div>
                <button type="submit" :disabled="loading">Cadastrar</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Lista de Vendas</h2>
            <table v-if="sales.length">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendedor</th>
                        <th>Valor</th>
                        <th>Comissão</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales" :key="sale.id">
                        <td>{{ sale.id }}</td>
                        <td>{{ sale.seller.name }}</td>
                        <td>R$ {{ sale.amount }}</td>
                        <td>R$ {{ sale.commission }}</td>
                        <td>{{ formatDate(sale.sale_date) }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-else>Nenhuma venda cadastrada.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import type { Sale, Seller } from '@/types'

const route = useRoute()
const router = useRouter()
const sales = ref<Sale[]>([])
const sellers = ref<Seller[]>([])
const form = ref({ seller_id: '', amount: '', sale_date: '' })
const loading = ref(false)
const error = ref('')

async function loadSales() {
    try {
        const sellerId = route.query.seller
        if (sellerId) {
            const response = await api.getSalesBySeller(Number(sellerId))
            sales.value = response.data
        } else {
            const response = await api.getSales()
            sales.value = response.data
        }
    } catch (err: any) {
        console.error('Erro ao carregar vendas:', err)
    }
}

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
        await api.createSale({
            seller_id: Number(form.value.seller_id),
            amount: Number(form.value.amount),
            sale_date: form.value.sale_date
        })
        form.value = { seller_id: '', amount: '', sale_date: '' }
        await loadSales()
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Erro ao cadastrar venda'
    } finally {
        loading.value = false
    }
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('pt-BR')
}

onMounted(() => {
    loadSellers()
    loadSales()
})
</script>
