<script setup>
import { ref, onMounted } from 'vue'
import { useServiceOrders } from '@/composables/useServiceOrders'
import ServiceOrderDetailsModal from './ServiceOrderDetailsModal.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatDate, formatAmount } = useCompanyFormatters()


const {
  serviceOrders,
  isLoading,
  error,
  searchTerm,
  hasError,
  hasOrders,
  paginationInfo,
  canGoPrev,
  canGoNext,
  fetchServiceOrders,
  searchOrders,
  goToPage,
  nextPage,
  prevPage,
  getStatusClass,
  fetchServiceOrderDetails,
  initializeServiceOrders
} = useServiceOrders()

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
const showDetailsModal = ref(false)
const selectedOrderDetails = ref({})
const isLoadingDetails = ref(false)

// View order details
const viewOrder = async (order) => {
  try {
    isLoadingDetails.value = true
    showDetailsModal.value = true
    selectedOrderDetails.value = {}
    
    // Fetch order details
    const details = await fetchServiceOrderDetails(order.id)
    selectedOrderDetails.value = details
    
  } catch (error) {
    console.error('Error fetching order details:', error)
    // Close modal on error
    showDetailsModal.value = false
  } finally {
    isLoadingDetails.value = false
  }
}

// Close modal
const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedOrderDetails.value = {}
  isLoadingDetails.value = false
}

// Initialize data on mount
onMounted(async () => {
  await initializeServiceOrders()
})
</script>
<template>
    <div class="package-order d-flex justify-content-between align-items-center customer-service-order-wrapper">
        <h4 class="package-order-title">{{ t('Service Booking History') }}</h4>
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
                    :placeholder="t('Search by order ID, service, branch, or status...')"
                >
            </div>
        </div>
    </div>
    

    <!-- Empty State -->
    <div v-if="!hasOrders" class="text-center py-5">
        <VIcon icon="tabler-clipboard-off" size="64" class="text-muted mb-3" />
        <h5 class="text-muted">{{ t('No service orders found') }}</h5>
        <p class="text-muted">
            {{ searchTerm ? t('Try adjusting your search criteria.') : t("You haven't placed any service orders yet.") }}
        </p>
    </div>

    <!-- Service Orders Table -->
    <div v-else class="package-order-list">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th>{{ t('SN') }}</th>
                    <th>{{ t('Booking ID') }}</th>
                    <th>{{ t('Branch') }}</th>
                    <th class="text-center">{{ t('Date') }}</th>
                    <th class="text-center">{{ t('Status') }}</th>
                    <th>{{ t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(order, index) in serviceOrders" :key="order.id">
                    <td>{{ (paginationInfo.currentPage - 1) * paginationInfo.perPage + index + 1 }}</td>
                    <td>{{ order.order_id }}</td>
                    <td>{{ order.branch }}</td>
                    <td class="text-center">{{ formatDate(order.date) }}</td>
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
            <nav aria-label="Service orders pagination">
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

    <!-- Service Order Details Modal -->
    <ServiceOrderDetailsModal
        :is-open="showDetailsModal"
        :order-details="selectedOrderDetails"
        :is-loading="isLoadingDetails"
        @close="closeDetailsModal"
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
