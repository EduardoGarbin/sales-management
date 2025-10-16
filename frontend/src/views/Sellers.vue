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
                            <button @click="openResendModal(seller)" class="btn-small btn-secondary">Reenviar
                                Email</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else>Nenhum vendedor cadastrado.</p>
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
import type { Seller } from '@/types'

const router = useRouter()
const sellers = ref<Seller[]>([])
const form = ref({ name: '', email: '' })
const loading = ref(false)
const error = ref('')

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
</style>
