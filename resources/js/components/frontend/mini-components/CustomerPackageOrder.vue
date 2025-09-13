<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePackageOrders } from '@/composables/usePackageOrders'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const router = useRouter()
const { formatDate, formatAmount } = useCompanyFormatters()
const {
  packageOrders,
  packageDetails,
  isLoading,
  isLoadingDetails,
  error,
  detailsError,
  searchTerm,
  hasError,
  hasOrders,
  hasDetailsError,
  hasPackageDetails,
  paginationInfo,
  canGoPrev,
  canGoNext,
  fetchPackageOrders,
  fetchPackageDetails,
  searchOrders,
  goToPage,
  nextPage,
  prevPage,
  getStatusClass,
  clearPackageDetails,
  initializePackageOrders
} = usePackageOrders()

// Search functionality
const searchInput = ref('')
const searchDebounceTimer = ref(null)

// Modal functionality
const showDetailsModal = ref(false)

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

// View package details
const viewPackageDetails = async (packageOrder) => {
  try {
    await fetchPackageDetails(packageOrder.id)
    showDetailsModal.value = true
  } catch (error) {
    console.error('Failed to fetch package details:', error)
  }
}

// Close modal
const closeDetailsModal = () => {
  showDetailsModal.value = false
  clearPackageDetails()
}

// Initialize data on mount
onMounted(async () => {
  await initializePackageOrders()
})
</script>
<template>
    <div class="package-order package-order-wrapper d-flex justify-content-between align-items-center">
        <h4 class="package-order-title">{{ t('Package Order History') }}</h4>
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
                    :placeholder="t('Search by order ID, package name, branch, or status...')"
                >
            </div>
        </div>
    </div>
    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-4">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ t('Loading...') }}</span>
        </div>
        <p class="mt-2">{{ t('Loading package orders...') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="alert alert-danger" role="alert">
        <VIcon icon="tabler-alert-circle" size="20" />
        {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasOrders" class="text-center py-5">
        <VIcon icon="tabler-package-off" size="64" class="text-muted mb-3" />
        <h5 class="text-muted">{{ t('No package orders found') }}</h5>
        <p class="text-muted">
            {{ searchTerm ? t('Try adjusting your search criteria.') : t("You haven't purchased any packages yet.") }}
        </p>
    </div>

    <!-- Package Orders Table -->
    <div v-else class="package-order-list">
        <table class="table table-responsive">
            <thead>
                <tr class="table-header">
                    <th>{{ t('SN') }}</th>
                    <th>{{ t('Order ID') }}</th>
                    <th>{{ t('Package Name') }}</th>
                    <th>{{ t('End Date') }}</th>
                    <th>{{ t('Amount') }}</th>
                    <th>{{ t('Branch') }}</th>
                    <th>{{ t('Status') }}</th>
                    <th>{{ t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(order, index) in packageOrders" :key="order.id">
                    <td>{{ (paginationInfo.currentPage - 1) * paginationInfo.perPage + index + 1 }}</td>
                    <td>{{ order.order_id }}</td>
                    <td>{{ order.package_name }}</td>
                    <td>{{ formatDate(order.end_date) }}</td>
                    <td>{{ formatAmount(order.amount) }}</td>
                    <td>{{ order.branch }}</td>
                    <td>
                        <span class="status" :class="getStatusClass(order.status)">{{ order.status }}</span>
                    </td>
                    <td>
                        <a href="#" class="action-view" @click.prevent="viewPackageDetails(order)">
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
            <nav aria-label="Package orders pagination">
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

    <!-- Package Details Modal -->
    <div v-if="showDetailsModal" class="package-modal-overlay" @click="closeDetailsModal">
        <div class="package-modal-container" @click.stop>
            <div class="package-modal-content">
                <!-- Modal Header -->
                <div class="package-modal-header">
                    <div class="package-modal-title">
                        <VIcon icon="tabler-package" size="24" class="package-icon" />
                        <h4>{{ t('Package Details') }}</h4>
                    </div>
                    <button type="button" class="package-modal-close" @click="closeDetailsModal">
                        <VIcon icon="tabler-x" size="20" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="package-modal-body">
                    <!-- Loading State -->
                    <div v-if="isLoadingDetails" class="package-loading-state">
                        <div class="package-spinner">
                            <VIcon icon="tabler-loader-2" size="32" class="spinning" />
                        </div>
                        <p>{{ t('Loading package details...') }}</p>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="hasDetailsError" class="package-error-state">
                        <VIcon icon="tabler-alert-circle" size="48" class="error-icon" />
                        <h5>{{ t('Unable to load details') }}</h5>
                        <p>{{ detailsError }}</p>
                    </div>

                    <!-- Package Details -->
                    <div v-else-if="hasPackageDetails" class="package-details-content">
                        <!-- Package Summary Card -->
                        <div class="package-summary-card">
                            <div class="package-summary-header">
                                <div class="package-info">
                                    <h3 class="package-name">{{ packageDetails.package_name }}</h3>
                                    <div class="package-id">{{ t('Package Code') }}: {{ packageDetails.package_code }}</div>
                                    <div class="package-id">{{ t('Package Price') }}: {{ formatAmount(packageDetails.package_price) }}</div>
                                    <div class="package-id">{{ t('Duration') }}: {{ packageDetails.duration }} {{ packageDetails.duration_type }}</div>
                                    <div class="package-id">{{ t('Branch') }}: {{ packageDetails.branch }}</div>
                                    <div class="package-id">{{ t('Order ID') }}: {{ packageDetails.order_id }} </div>
                                    <div class="package-id">{{ t('Purchase Date') }}: {{ formatDate(packageDetails.start_date) }}</div>
                                    <div class="package-id">{{ t('Expire Date') }}: {{ formatDate(packageDetails.end_date) }}</div>
                                </div>
                                <div class="package-status-badge">
                                    <span class="status-indicator" :class="getStatusClass(packageDetails.status)">
                                        <VIcon icon="tabler-circle-filled" size="8" />
                                        {{ packageDetails.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <div class="package-services-section">
                            <div class="services-header">
                                <VIcon icon="tabler-list-details" />
                                <h5>{{ t('Included Services') }}</h5>
                            </div>
                            
                            <div class="services-grid" v-if="packageDetails.items && packageDetails.items.length > 0">
                                <div 
                                    v-for="(service, index) in packageDetails.items" 
                                    :key="service.id || index"
                                    class="service-card"
                                >
                                    <div class="service-header">
                                        <VIcon icon="tabler-scissors" size="18" class="service-icon" />
                                        <h6 class="service-name">{{ service.name }}</h6>
                                    </div>
                                    
                                    <div class="service-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">{{ t('Total') }}</span>
                                            <span class="stat-value total">{{ service.quantity }}</span>
                                        </div>
                                        <div class="stat-divider"></div>
                                        <div class="stat-item">
                                            <span class="stat-label">{{ t('Used') }}</span>
                                            <span class="stat-value used">{{ service.used }}</span>
                                        </div>
                                        <div class="stat-divider"></div>
                                        <div class="stat-item">
                                            <span class="stat-label">{{ t('Remaining') }}</span>
                                            <span class="stat-value remaining">{{ service.remaining }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="service-progress">
                                        <div class="progress-bar">
                                            <div 
                                                class="progress-fill" 
                                                :style="{ width: service.quantity > 0 ? (service.used / service.quantity * 100) + '%' : '0%' }"
                                            ></div>
                                        </div>
                                        <span class="progress-text">
                                            {{ service.quantity > 0 ? Math.round(service.used / service.quantity * 100) : 0 }}% {{ t('used') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="no-services-message">
                                <VIcon icon="tabler-info-circle" />
                                <span>{{ t('No services information available') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- No Details State -->
                    <div v-else class="package-empty-state">
                        <VIcon icon="tabler-package-off" size="64" class="empty-icon" />
                        <h5>{{ t('Package details not found') }}</h5>
                        <p>{{ t('Unable to load package details. Please try again later.') }}</p>
                    </div>

                    <!-- Usage Summary -->
                    <div class="package-usage-summary mt-4">
                        <h5>{{ t('Usage Summary') }}</h5>
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>{{ t('SN') }}</th>
                                    <th>{{ t('Service Item') }}</th>
                                    <th class="text-center">{{ t('Usage Date') }}</th>
                                    <th class="text-center">{{ t('Usage Time') }}</th>
                                    <th class="text-center">{{ t('Taken Qty') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(usage, index) in packageDetails.usages" :key="usage.id">
                                  <td>{{ index + 1 }}</td>
                                  <td>{{ usage.service_item }}</td>
                                  <td class="text-center">{{ formatDate(usage.usages_date) }}</td>
                                  <td class="text-center">{{ usage.usages_time }}</td>
                                  <td class="text-center">{{ usage.taken_qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="package-modal-footer">
                    <button color="error" variant="tonal"  class="package-close-btn" @click="closeDetailsModal">
                        <VIcon icon="tabler-x" size="16" />
                        {{ t('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Package Modal Overlay */
.package-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
}

.package-modal-container {
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  animation: modalSlideUp 0.3s ease-out;
}

.package-modal-content {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.package-modal-header {
  padding: 15px 20px;
  background: #fafafa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.package-modal-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.package-modal-title h4 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.package-modal-close {
  background: rgba(150, 150, 150, 0.2);
  border: none;
  border-radius: 4px;
  width: 30px;
  height: 30px;
  padding: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Modal Body */
.package-modal-body {
  padding: 32px;
  overflow-y: auto;
  flex: 1;
}

/* Loading State */
.package-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #666;
}

.package-spinner {
  margin-bottom: 16px;
}

.spinning {
  animation: spin 1s linear infinite;
  color: #667eea;
}

/* Error State */
.package-error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.error-icon {
  color: #e74c3c;
  margin-bottom: 16px;
}

.package-error-state h5 {
  color: #2c3e50;
  margin-bottom: 8px;
}

.package-error-state p {
  color: #666;
  margin: 0;
}

/* Package Summary Card */
.package-summary-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  border: 1px solid #e9ecef;
}

.package-summary-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.package-info h3 {
  color: #2c3e50;
  margin: 0 0 8px 0;
  font-size: 1.4rem;
  font-weight: 600;
}

.package-id {
  font-size: 16px;
}

.package-status-badge {
  flex-shrink: 0;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-indicator.status-active {
  background: #d4edda;
  color: #155724;
}

.status-indicator.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-indicator.status-cancel {
  background: #f8d7da;
  color: #721c24;
}


.summary-icon {
  color: #667eea;
  flex-shrink: 0;
  font-size: 24px;
}



/* Services Section */
.package-services-section {
  margin-top: 8px;
}

.services-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  color: #2c3e50;
}

.services-header h5 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.service-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.2s ease;
}

.service-card:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.service-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.service-icon {
  color: #667eea;
}

.service-name {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.service-stats {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-label {
  font-size: 0.75rem;
  color: #666;
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 1.2rem;
  font-weight: 700;
}

.stat-value.total {
  color: #3498db;
}

.stat-value.used {
  color: #e74c3c;
}

.stat-value.remaining {
  color: #27ae60;
}

.stat-divider {
  width: 1px;
  height: 30px;
  background: #e9ecef;
  margin: 0 12px;
}

.service-progress {
  display: flex;
  align-items: center;
  gap: 12px;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #667eea;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: #666;
  white-space: nowrap;
}

.no-services-message {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 40px;
  color: #666;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
}

/* Empty State */
.package-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  color: #bdc3c7;
  margin-bottom: 16px;
}

.package-empty-state h5 {
  color: #2c3e50;
  margin-bottom: 8px;
}

.package-empty-state p {
  color: #666;
  margin: 0;
}

/* Modal Footer */
.package-modal-footer {
  padding: 20px 32px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
}

.package-close-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #667eea;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.package-close-btn:hover {
  background: #5a6fd8;
  transform: translateY(-1px);
}

/* Animations */
@keyframes modalSlideUp {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .package-modal-overlay {
    padding: 10px;
  }
  
  .package-modal-header {
    padding: 20px 24px;
  }
  
  .package-modal-body {
    padding: 24px;
  }
  

  
  .services-grid {
    grid-template-columns: 1fr;
  }
  
  .service-stats {
    flex-direction: column;
    gap: 12px;
  }
  
  .stat-divider {
    width: 100%;
    height: 1px;
    margin: 0;
  }
}

@media (max-width: 480px) {
  .package-modal-header {
    padding: 16px 20px;
  }
  
  .package-modal-title h4 {
    font-size: 1.3rem;
  }
  
  .package-modal-body {
    padding: 20px;
  }
  
  .package-summary-card {
    padding: 20px;
  }
}
</style>
