<template>
    <div v-if="meta.last_page > 1" class="pagination">
        <button @click="goToPage(1)" :disabled="meta.current_page === 1" class="pagination-btn">
            &laquo; Primeira
        </button>

        <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="pagination-btn">
            &lsaquo; Anterior
        </button>

        <div class="pagination-info">
            Página {{ meta.current_page }} de {{ meta.last_page }}
            <span class="pagination-total">({{ meta.total }} {{ meta.total === 1 ? 'item' : 'itens' }})</span>
        </div>

        <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
            class="pagination-btn">
            Próxima &rsaquo;
        </button>

        <button @click="goToPage(meta.last_page)" :disabled="meta.current_page === meta.last_page"
            class="pagination-btn">
            Última &raquo;
        </button>
    </div>
</template>

<script setup lang="ts">
import type { PaginationMeta } from '@/types'

interface Props {
    meta: PaginationMeta
}

const props = defineProps<Props>()
const emit = defineEmits<{
    'page-change': [page: number]
}>()

function goToPage(page: number) {
    if (page >= 1 && page <= props.meta.last_page && page !== props.meta.current_page) {
        emit('page-change', page)
    }
}
</script>

<style scoped>
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 1.5rem 0;
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background-color: white;
    color: #333;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.pagination-btn:hover:not(:disabled) {
    background-color: #2196F3;
    color: white;
    border-color: #2196F3;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5;
}

.pagination-info {
    padding: 0.5rem 1rem;
    color: #555;
    font-weight: 500;
    font-size: 0.9rem;
}

.pagination-total {
    color: #888;
    font-weight: normal;
    font-size: 0.85rem;
    margin-left: 0.3rem;
}
</style>
