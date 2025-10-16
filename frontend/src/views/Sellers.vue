<template>
    <div class="container">
        <button @click="router.back()" class="btn-back">
            <span class="back-icon">←</span>
            <span>Voltar</span>
        </button>

        <div class="header">
            <h1>Vendedores</h1>
            <button @click="openCreateModal" class="btn-new">+ Novo Vendedor</button>
        </div>

        <div class="table-container">
            <h2>Lista de Vendedores</h2>

            <!-- Loading Spinner -->
            <div v-if="loadingList" class="loading-container">
                <div class="spinner"></div>
                <p class="loading-text">Carregando vendedores...</p>
            </div>

            <!-- Tabela de Vendedores -->
            <div v-else>
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
                                <button @click="openResendModal(seller)" class="btn-small btn-secondary">Reenviar
                                    Email</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else>Nenhum vendedor cadastrado.</p>

                <!-- Paginação -->
                <Pagination v-if="sellers.length > 0" :meta="paginationMeta" @page-change="handlePageChange" />
            </div>
        </div>

        <!-- Modal de Criação de Vendedor -->
        <div v-if="showCreateModal" class="modal-overlay" @click="closeCreateModal">
            <div class="modal-content" @click.stop>
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

                    <div class="modal-actions">
                        <button type="button" @click="closeCreateModal" class="btn-cancel">Cancelar</button>
                        <button type="submit" :disabled="loading" class="btn-primary">
                            {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Reenvio de Email -->
        <div v-if="showResendModal" class="modal-overlay" @click="closeResendModal">
            <div class="modal-content" @click.stop>
                <h2>Reenviar Email de Comissão</h2>
                <p><strong>Vendedor:</strong> {{ selectedSeller?.name }}</p>
                <p><strong>Email:</strong> {{ selectedSeller?.email }}</p>

                <form @submit.prevent="handleResendEmail">
                    <div class="form-group">
                        <label>Data do Relatório:</label>
                        <input v-model="resendForm.date" type="date" :max="today" required />
                        <small>Selecione a data das vendas para reenviar o relatório</small>
                    </div>

                    <div class="success" v-if="resendSuccess">{{ resendSuccess }}</div>
                    <div class="error" v-if="resendError">{{ resendError }}</div>

                    <div class="modal-actions">
                        <button type="button" @click="closeResendModal" class="btn-cancel">Cancelar</button>
                        <button type="submit" :disabled="resendLoading" class="btn-primary">
                            {{ resendLoading ? 'Enviando...' : 'Reenviar Email' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { Seller, PaginationMeta } from '@/types'
import Pagination from '@/components/Pagination.vue'

const router = useRouter()
const sellers = ref<Seller[]>([])
const form = ref({ name: '', email: '' })
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

// Modal de reenvio
const showResendModal = ref(false)
const selectedSeller = ref<Seller | null>(null)
const resendForm = ref({ date: '' })
const resendLoading = ref(false)
const resendError = ref('')
const resendSuccess = ref('')

// Data máxima (hoje)
const today = computed(() => {
    const date = new Date()
    return date.toISOString().split('T')[0]
})

async function loadSellers(page: number = currentPage.value) {
    loadingList.value = true
    try {
        const response = await api.getSellers(page, perPage.value)
        sellers.value = response.data
        paginationMeta.value = response.meta
        currentPage.value = page
    } catch (err: any) {
        console.error('Erro ao carregar vendedores:', err)
    } finally {
        loadingList.value = false
    }
}

function handlePageChange(page: number) {
    loadSellers(page)
}

function openCreateModal() {
    form.value = { name: '', email: '' }
    error.value = ''
    showCreateModal.value = true
}

function closeCreateModal() {
    showCreateModal.value = false
    form.value = { name: '', email: '' }
    error.value = ''
}

async function handleCreate() {
    loading.value = true
    error.value = ''
    try {
        await api.createSeller(form.value)
        closeCreateModal()
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

function openResendModal(seller: Seller) {
    selectedSeller.value = seller
    resendForm.value.date = ''
    resendError.value = ''
    resendSuccess.value = ''
    showResendModal.value = true
}

function closeResendModal() {
    showResendModal.value = false
    selectedSeller.value = null
    resendForm.value.date = ''
    resendError.value = ''
    resendSuccess.value = ''
}

async function handleResendEmail() {
    if (!selectedSeller.value) return

    resendLoading.value = true
    resendError.value = ''
    resendSuccess.value = ''

    try {
        const response = await api.resendCommissionEmail(
            selectedSeller.value.id,
            resendForm.value.date
        )

        resendSuccess.value = response.message || 'Email reenviado com sucesso!'

        // Fechar modal após 2 segundos
        setTimeout(() => {
            closeResendModal()
        }, 2000)
    } catch (err: any) {
        resendError.value = err.response?.data?.message || 'Erro ao reenviar email'
    } finally {
        resendLoading.value = false
    }
}

onMounted(loadSellers)
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

.btn-small {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
    margin-right: 0.5rem;
    cursor: pointer;
    border: none;
    border-radius: 4px;
    background-color: #4CAF50;
    color: white;
    transition: background-color 0.3s;
}

.btn-small:hover {
    background-color: #45a049;
}

.btn-small.btn-secondary {
    background-color: #2196F3;
}

.btn-small.btn-secondary:hover {
    background-color: #0b7dda;
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

.modal-content p {
    margin: 0.5rem 0;
    color: #555;
}

.modal-content small {
    display: block;
    margin-top: 0.3rem;
    color: #666;
    font-size: 0.85rem;
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

.success {
    padding: 0.8rem;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    margin: 1rem 0;
}

.error {
    padding: 0.8rem;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    margin: 1rem 0;
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
