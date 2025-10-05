<script setup>
import { onMounted } from 'vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import ServiceCard from '@/components/frontend/ServiceCard.vue'
import CategoryFilter from '@/components/frontend/CategoryFilter.vue'
import { useServiceManagement } from '@/composables/useServiceManagement'
import { useServiceApi } from '@/composables/useServiceApi'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
// Define page meta
definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

// Use composables
const {
  serviceCategories,
  loading: apiLoading,
  error,
  fetchServiceCategories,
  fetchServicesPaginated
} = useServiceApi()

const {
  allServices,
  selectedCategories,
  sortBy,
  currentPage,
  perPage,
  loading,
  totalPages,
  totalResults,
  sortOptions,
  paginatedServices,
  handleCategoryChange: handleCategoryChangeBase,
  handleSortChange: handleSortChangeBase,
  goToPage: goToPageBase,
  resetFilters: resetFiltersBase,
  initializeServices: initializeServicesBase
} = useServiceManagement()

// Initialize services data
const initializeServices = async () => {
  await initializeServicesBase(fetchServicesPaginated)
}

// Event handlers
const handleBookService = (service) => {
  console.log('Booking service:', service)
  // Navigate to booking page or show booking modal
  // You can implement this based on your routing system
}

const handleImageError = (service) => {
  console.log('Image error for service:', service)
  // Handle image error if needed - no infinite loop risk as ServiceCard handles fallback
}

// Event handlers for filtering
const handleCategoryChange = async (categoryId) => {
  await handleCategoryChangeBase(categoryId, fetchServicesPaginated)
}

const handleSortChange = async (newSortBy) => {
  await handleSortChangeBase(newSortBy, fetchServicesPaginated)
}

const goToPage = async (page) => {
  await goToPageBase(page, fetchServicesPaginated)
}

const resetFilters = async () => {
  await resetFiltersBase(fetchServicesPaginated)
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchServiceCategories(),
    initializeServices()
  ])
})

// IRUL ID Encryption
function encryptId(id) {
  return btoa(id) // Base64 encode
}
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="t('Service')" :breadcrumb="t('Service')" />
    
    <!-- Service Section -->
    <section class="service-wrapper default-section-padding-t">
      <div class="container">
        <div class="service-wrap-inner">
          <div></div>
          <div class="filter-and-result-header d-flex justify-content-between align-items-center">
            <div class="sort-by">
              <div class="sort-by-inner d-flex align-items-center">
                <h4>{{ t('Sort By') }}</h4>
                <div>
                  <AppAutocomplete 
                    class="form-control custom-autocomplete" 
                    :items="sortOptions" 
                    v-model="sortBy"
                    @update:model-value="handleSortChange"
                  />
                </div>
              </div>
            </div>
            <div class="result-count">
              <p><span class="number-count">{{ totalResults }}</span> {{ t('Results Found') }}</p>
            </div>
          </div>
        </div>
        
        <div class="service-wrap-inner">
          <!-- Category Filter -->
          <CategoryFilter 
            :categories="serviceCategories"
            :selected-categories="selectedCategories"
            :title="t('All Categories')"
            @category-change="handleCategoryChange"
            @clear-filters="resetFilters"
          />
          
          <!-- Service Cards -->
          <div class="service-card">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">{{ t('Loading...') }}</span>
              </div>
            </div>
            
            <!-- Services Grid -->
            <div v-else class="row">
              <div 
                class="col-lg-4 col-md-6 col-sm-6 col-12 mb-3" 
                v-for="service in paginatedServices" 
                :key="service.id"
              >
                <ServiceCard 
                  :service="service"
                  :booking-link="`/appointment-service?service_id=${encryptId(service.id)}`"
                  @book-service="handleBookService"
                  @image-error="handleImageError"
                />
              </div>
            </div>
            
            <!-- No Results -->
            <div v-if="!loading && paginatedServices.length == 0" class="text-center py-4">
              <p class="text-muted">{{ t('No services found matching your criteria.') }}</p>
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div class="service-wrap-inner" v-if="totalPages > 1">
          <div></div>
          <div class="pagination-wrapper">
            <div class="pagination-inner d-flex justify-content-center align-items-center">
              <div class="pagination-item d-flex justify-content-center align-items-center">
                <!-- Previous Button -->
                <a 
                  href="#" 
                  @click.prevent="goToPage(currentPage - 1)"
                  v-if="currentPage > 1"
                  class="pagination-btn"
                >
                <VIcon icon="tabler-arrow-narrow-left"/>

                </a>
                
                <!-- Page Numbers -->
                <a 
                  href="#" 
                  v-for="page in Math.min(totalPages, 5)" 
                  :key="page"
                  @click.prevent="goToPage(page)"
                  :class="{ active: currentPage == page }"
                >
                  {{ page }}
                </a>
                
                <!-- Show dots if there are more pages -->
                <span v-if="totalPages > 5">...</span>
                
                <!-- Last page -->
                <a 
                  href="#" 
                  v-if="totalPages > 5"
                  @click.prevent="goToPage(totalPages)"
                  :class="{ active: currentPage == totalPages }"
                >
                  {{ totalPages }}
                </a>
                
                <!-- Next Button -->
                <a 
                  href="#" 
                  @click.prevent="goToPage(currentPage + 1)"
                  v-if="currentPage < totalPages"
                  class="pagination-btn"
                >
                  <VIcon icon="tabler-arrow-narrow-right"/>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Service Section End -->
  </div>
</template>

<style scoped>
/* Category Filter Styles */
.custom-checkbox input[type="checkbox"]:checked + label .checkmark {
  background-color: var(--primary-bg-color);
  border-color: var(--primary-bg-color);
}

/* Pagination Styles */
.pagination-item a.active {
  background-color: var(--primary-bg-color);
  color: white;
}

.pagination-btn {
  padding: 8px 12px;
  margin: 0 2px;
  text-decoration: none;
  border: 1px solid #ddd;
  color: #333;
  transition: all 0.3s ease;
}

.pagination-btn:hover {
  background-color: var(--primary-bg-color);
  color: white;
  border-color: var(--primary-bg-color);
}

/* Loading Styles */
.spinner-border {
  color: var(--primary-bg-color);
}

/* Sort By Styles */
.sort-by-inner {
  gap: 1rem;
}

.sort-by h4 {
  margin-bottom: 0;
  font-weight: 600;
  color: #333;
}

/* Result Count Styles */
.result-count .number-count {
  font-weight: 600;
  color: var(--primary-bg-color);
}

/* Service Grid Styles */
.service-card .row {
  margin: 0 -15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .filter-and-result-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start !important;
  }
  
  .sort-by-inner {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>