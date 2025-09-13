<script setup>
import { ref, onMounted } from 'vue'
import { useTransactionHistory } from '@/composables/useTransactionHistory'
import TransactionDetailsModal from './TransactionDetailsModal.vue'

const {
  transactions,
  isLoading,
  error,
  searchTerm,
  hasError,
  hasTransactions,
  paginationInfo,
  canGoPrev,
  canGoNext,
  searchTransactions,
  goToPage,
  nextPage,
  prevPage,
  getStatusClass,
  getTypeClass,
  formatAmount,
  initializeTransactionHistory
} = useTransactionHistory()

// Search functionality
const searchInput = ref('')
const searchDebounceTimer = ref(null)

const handleSearch = () => {
  // Clear previous timer
  if (searchDebounceTimer.value) {
    clearTimeout(searchDebounceTimer.value)
  }
  
  // Set new timer for debounced search
  searchDebounceTimer.value = setTimeout(() => {
    searchTransactions(searchInput.value.trim())
  }, 500)
}

// Modal state
const isModalOpen = ref(false)
const selectedTransactionId = ref(null)
const selectedSourceType = ref('booking')
const selectedTransactionType = ref('Service')

// View transaction details
const viewTransaction = (transaction) => {
  selectedTransactionId.value = transaction.id
  selectedSourceType.value = transaction.source_type
  selectedTransactionType.value = transaction.type
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  selectedTransactionId.value = null
  selectedSourceType.value = 'booking'
  selectedTransactionType.value = 'Service'
}

// Initialize data on mount
onMounted(async () => {
  await initializeTransactionHistory()
})
</script>

<template>
    <div class="package-order d-flex justify-content-between align-items-center customer-transaction-history-wrapper customer-product-order-wrapper">
        <h4 class="package-order-title">Transaction History</h4>
        <div class="search">
            <div class="form-group search-form">
                <div class="search-icon">
                    <VIcon icon="tabler-search" />
                </div>
                <input 
                    class="form-control" 
                    type="text" 
                    v-model="searchInput"
                    @input="handleSearch"
                    placeholder="Search by transaction ID, type, or status..."
                >
            </div>
        </div>
    </div>

    <!-- <div class="package-order d-flex justify-content-between align-items-center customer-product-order-wrapper">
        <h4 class="package-order-title">Product Order History</h4>
        <div class="search">
            <div class="form-group search-form">
                <div class="search-icon">
                    <VIcon icon="tabler-search" />
                </div>
                <input 
                    class="form-control" 
                    type="text" 
                    v-model="searchInput"
                    @input="handleSearch"
                    placeholder="Search by order ID, product name, or status..."
                >
            </div>
        </div>
    </div> -->


    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-4">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading transaction history...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="alert alert-danger" role="alert">
        <VIcon icon="tabler-alert-circle" size="20" />
        {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasTransactions" class="text-center py-5">
        <VIcon icon="tabler-receipt-off" size="64" class="text-muted mb-3" />
        <h5 class="text-muted">No transactions found</h5>
        <p class="text-muted">
            {{ searchTerm ? 'Try adjusting your search criteria.' : 'You haven\'t made any transactions yet.' }}
        </p>
    </div>

    <!-- Transaction History Table -->
    <div v-else class="package-order-list">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th>SN</th>
                    <th>Transaction ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(transaction, index) in transactions" :key="transaction.id">
                    <td>{{ (paginationInfo.currentPage - 1) * paginationInfo.perPage + index + 1 }}</td>
                    <td>{{ transaction.transaction_id }}</td>
                    <td>
                        <span class="transaction-type" :class="getTypeClass(transaction.type)">
                            {{ transaction.type }}
                        </span>
                    </td>
                    <td>
                        <span class="transaction-amount">
                            {{ formatAmount(transaction.amount) }}
                        </span>
                    </td>
                    <td>{{ transaction.date }}</td>
                    <td>{{ transaction.payment_method }}</td>
                    <td>
                        <span class="status" :class="getStatusClass(transaction.status)">{{ transaction.status }}</span>
                    </td>
                    <td>
                        <a href="#" class="action-view" @click.prevent="viewTransaction(transaction)">
                            <VIcon icon="tabler-eye" />
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="paginationInfo.totalPages > 1" class="pagination-wrapper d-flex justify-content-between align-items-center mt-4">
            <div class="pagination-info">
                <span class="text-muted">
                    Showing {{ paginationInfo.start }}-{{ paginationInfo.end }} of {{ paginationInfo.total }} results
                </span>
            </div>
            <nav aria-label="Transaction history pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !canGoPrev }">
                        <button class="page-link" @click="prevPage" :disabled="!canGoPrev">
                            <VIcon icon="tabler-chevron-left" size="16" />
                            Previous
                        </button>
                    </li>
                    
                    <template v-for="page in Math.min(paginationInfo.totalPages, 5)" :key="page">
                        <li class="page-item" :class="{ active: page === paginationInfo.currentPage }">
                            <button class="page-link" @click="goToPage(page)">
                                {{ page }}
                            </button>
                        </li>
                    </template>
                    
                    <li v-if="paginationInfo.totalPages > 5" class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    
                    <li class="page-item" :class="{ disabled: !canGoNext }">
                        <button class="page-link" @click="nextPage" :disabled="!canGoNext">
                            Next
                            <VIcon icon="tabler-chevron-right" size="16" />
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <TransactionDetailsModal
        :is-open="isModalOpen"
        :transaction-id="selectedTransactionId"
        :source-type="selectedSourceType"
        :transaction-type="selectedTransactionType"
        @close="closeModal"
    />
</template>

<style scoped>
.transaction-type {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.transaction-service {
  background-color: #e3f2fd;
  color: #1976d2;
}

.transaction-product {
  background-color: #f3e5f5;
  color: #7b1fa2;
}

.transaction-default {
  background-color: #f5f5f5;
  color: #616161;
}

.transaction-amount {
  font-weight: 600;
  color: #28a745;
}

.action-view {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 4px;
  background-color: #f8f9fa;
  color: #6c757d;
  text-decoration: none;
  transition: all 0.2s ease;
}

.action-view:hover {
  background-color: #e9ecef;
  color: #495057;
}
</style> 