<script setup>
import { ref, onMounted } from 'vue'
import { useProductOrders } from '@/composables/useProductOrders'
import ProductOrderDetailsModal from './ProductOrderDetailsModal.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatDate, formatAmount } = useCompanyFormatters()


const {
  productOrders,
  isLoading,
  error,
  searchTerm,
  hasError,
  hasOrders,
  paginationInfo,
  canGoPrev,
  canGoNext,
  fetchProductOrders,
  searchOrders,
  goToPage,
  nextPage,
  prevPage,
  getStatusClass
} = useProductOrders()

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
    searchOrders(searchInput.value.trim())
  }, 500)
}

// Modal functionality
const isModalOpen = ref(false)
const selectedOrderId = ref(null)

// View order details
const viewOrder = (order) => {
  selectedOrderId.value = order.id
  isModalOpen.value = true
}

// Close modal
const closeModal = () => {
  isModalOpen.value = false
  selectedOrderId.value = null
}

// Initialize data on mount
onMounted(async () => {
  await fetchProductOrders()
})
</script>
<template>
    <div class="package-order d-flex justify-content-between align-items-center customer-product-order-wrapper">
        <h4 class="package-order-title">{{ t('Product Order History') }}</h4>
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
                    :placeholder="t('Search by order ID, product name, or status...')"
                >
            </div>
        </div>
    </div>


    <!-- Empty State -->
    <div v-if="!hasOrders" class="text-center py-5">
        <VIcon icon="tabler-shopping-cart-off" size="64" class="text-muted mb-3" />
        <h5 class="text-muted">{{ t('No product orders found') }}</h5>
        <p class="text-muted">
            {{ searchTerm ? t('Try adjusting your search criteria.') : t("You haven't ordered any products yet.") }}
        </p>
    </div>

    <!-- Product Orders Table -->
    <div v-else class="package-order-list">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th>{{ t('SN') }}</th>
                    <th>{{ t('Order ID') }}</th>
                    <th class="text-center">{{ t('Date') }}</th>
                    <th class="text-center">{{ t('Amount') }}</th>
                    <th class="text-center">{{ t('Status') }}</th>
                    <th class="text-center">{{ t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(order, index) in productOrders" :key="order.id">
                    <td>{{ index + 1 }}</td>
                    <td>{{ order.order_id }}</td>
                    <td class="text-center">{{ formatDate(order.date) }}</td>
                    <td class="text-center">{{ formatAmount(order.total_amount) }}</td>
                    <td class="text-center">
                        <span class="status" :class="getStatusClass(order.status)">{{ order.status }}</span>
                    </td>
                    <td class="text-center">
                        <a href="#" class="action-view" @click.prevent="viewOrder(order)">
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
                    {{ t('Showing') }} {{ paginationInfo.start }}-{{ paginationInfo.end }} {{ t('of') }} {{ paginationInfo.total }} {{ t('results') }}
                </span>
            </div>
            <nav aria-label="Product orders pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !canGoPrev }">
                        <button class="page-link" @click="prevPage" :disabled="!canGoPrev">
                            <VIcon icon="tabler-chevron-left" size="16" />
                        </button>
                    </li>
                    
                    <template v-for="page in Math.min(paginationInfo.totalPages, 5)" :key="page">
                        <li class="page-item" :class="{ active: page === paginationInfo.currentPage }">
                            <button class="page-link pagi-number" @click="goToPage(page)">
                                {{ page }}
                            </button>
                        </li>
                    </template>
                    
                    <li v-if="paginationInfo.totalPages > 5" class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    
                    <li class="page-item" :class="{ disabled: !canGoNext }">
                        <button class="page-link" @click="nextPage" :disabled="!canGoNext">
                            <VIcon icon="tabler-chevron-right" size="16" />
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Product Order Details Modal -->
    <ProductOrderDetailsModal 
        :is-open="isModalOpen" 
        :order-id="selectedOrderId" 
        @close="closeModal" 
    />
</template>

<style scoped>
.status {
    padding: 5px 10px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 500;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.status-accepted {
    background-color: #2196F32a;
    color: #2196F3;
}
.status-pending {
    background-color: #ffc1072a;
    color: #FFC107;
}
.status-rejected {
    background-color: #f443362a;
    color: #F44336;
}
.status-completed {
    background-color: #0dca002a;
    color: #4CAF50;
}
</style>
