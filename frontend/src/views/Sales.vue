<template>
    <div class="container">
        <button @click="router.back()" class="btn-back">
            <span class="back-icon">←</span>
            <span>Voltar</span>
        </button>

        <div class="header">
            <h1>Vendas</h1>
            <button @click="openCreateModal" class="btn-new">+ Nova Venda</button>
        </div>

        <div class="table-container">
            <h2>Lista de Vendas</h2>

            <!-- Loading Spinner -->
            <div v-if="loadingList" class="loading-container">
                <div class="spinner"></div>
                <p class="loading-text">Carregando vendas...</p>
            </div>

            <!-- Tabela de Vendas -->
            <div v-else>
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

                <!-- Paginação -->
                <Pagination v-if="sales.length > 0" :meta="paginationMeta" @page-change="handlePageChange" />
            </div>
        </div>

        <!-- Modal de Criação de Venda -->
        <div v-if="showCreateModal" class="modal-overlay" @click="closeCreateModal">
            <div class="modal-content" @click.stop>
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
                        <input v-model="form.amount" type="number" step="0.01" min="0.01" max="99999999.99" required />
                        <small class="field-hint">Valor máximo: R$ 99.999.999,99</small>
                    </div>
                    <div class="form-group">
                        <label>Data:</label>
                        <input v-model="form.sale_date" type="date" :max="maxDate" required />
                        <small class="field-hint">A data não pode ser no futuro</small>
                    </div>

                    <div class="error" v-if="error">{{ error }}</div>

                    <div class="modal-actions">
                        <button type="button" @click="closeCreateModal" class="btn-cancel">Cancelar</button>
                        <button type="submit" :disabled="loading" class="btn-primary">
                            {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import type { Sale, Seller, PaginationMeta } from '@/types'
import Pagination from '@/components/Pagination.vue'

const route = useRoute()
const router = useRouter()
const sales = ref<Sale[]>([])
const sellers = ref<Seller[]>([])
const form = ref({ seller_id: '', amount: '', sale_date: '' })
const loading = ref(false)
const loadingList = ref(false)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const paginationMeta = ref<PaginationMeta>({
    current_page: 1,
    from: null,
    last_page: 1,
    per_page: 15,
    to: null,
    total: 0
})

// Modal de criação
const showCreateModal = ref(false)

// Data máxima (hoje) para validação do campo de data
const maxDate = computed(() => {
    const date = new Date()
    return date.toISOString().split('T')[0]
})

async function loadSales(page: number = currentPage.value) {
    loadingList.value = true
    try {
        const sellerId = route.query.seller
        let response

        if (sellerId) {
            response = await api.getSalesBySeller(Number(sellerId), page, perPage.value)
        } else {
            response = await api.getSales(page, perPage.value)
        }

        sales.value = response.data
        paginationMeta.value = response.meta
        currentPage.value = page
    } catch (err: any) {
        console.error('Erro ao carregar vendas:', err)
    } finally {
        loadingList.value = false
    }
}

function handlePageChange(page: number) {
    loadSales(page)
}

function openCreateModal() {
    form.value = { seller_id: '', amount: '', sale_date: '' }
    error.value = ''
    showCreateModal.value = true
}

function closeCreateModal() {
    showCreateModal.value = false
    form.value = { seller_id: '', amount: '', sale_date: '' }
    error.value = ''
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
        closeCreateModal()
        await loadSales()
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Erro ao cadastrar venda'
    } finally {
        loading.value = false
    }
}

function formatDate(date: string) {
    // Parse manual para evitar problemas de timezone
    // Date string vem como 'YYYY-MM-DD' do backend
    const [year, month, day] = date.split('-')
    return new Date(Number(year), Number(month) - 1, Number(day)).toLocaleDateString('pt-BR')
}

onMounted(() => {
    loadSellers()
    loadSales()
})
</script>

<style scoped>
/* Botão Voltar */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    margin-bottom: 1.5rem;
    background-color: transparent;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #555;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-back:hover {
    background-color: #f5f5f5;
    border-color: #2196F3;
    color: #2196F3;
}

.back-icon {
    font-size: 1.2rem;
    font-weight: bold;
    transition: transform 0.3s;
}

.btn-back:hover .back-icon {
    transform: translateX(-3px);
}

/* Header */
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.header h1 {
    margin: 0;
    flex: 1;
}

.btn-new {
    padding: 0.75rem 1.5rem;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.3s;
    white-space: nowrap;
}

.btn-new:hover {
    background-color: #45a049;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content h2 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #2196F3;
    padding-bottom: 0.5rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-cancel {
    padding: 0.6rem 1.2rem;
    border: 1px solid #ccc;
    background-color: #f5f5f5;
    color: #333;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-cancel:hover {
    background-color: #e0e0e0;
}

.btn-primary {
    padding: 0.6rem 1.2rem;
    border: none;
    background-color: #2196F3;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0b7dda;
}

.btn-primary:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.error {
    padding: 0.8rem;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    margin: 1rem 0;
}

.field-hint {
    display: block;
    margin-top: 0.3rem;
    color: #666;
    font-size: 0.85rem;
    font-style: italic;
}

/* Loading Spinner */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    min-height: 200px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2196F3;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.loading-text {
    margin-top: 1rem;
    color: #666;
    font-size: 1rem;
    font-weight: 500;
}
</style>
