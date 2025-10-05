<template>
  <div :class="sectionClass">
    <div class="container">
      <!-- Search Header -->
      <div v-if="showHeader" class="row">
        <div class="col-12">
          <div class="product-header">
            <h2 class="title">{{ t('Search Results') }}</h2>
            <p class="subtitle" v-if="searchSummary">{{ searchSummary }}</p>
            
            <!-- Search Input -->
            <div class="search-inner">
              <input 
                type="text" 
                :placeholder="t('Search products, services...')" 
                class="form-control"
                v-model="searchQuery"
                @keydown="handleSearchInput"
              />
              <VIcon icon="tabler-search" @click="performSearch" />
            </div>

            <!-- Search Filters -->
            <div class="search-filters mt-3">
              <div class="filter-buttons">
                <button 
                  class="btn btn-sm"
                  :class="searchType === 'all' ? 'btn-primary-2 common-animation-button' : 'btn-outline-primary-2 common-animation-button'"
                  @click="setSearchType('all')"
                >
                  {{ t('All') }}
                </button>
                <button 
                  class="btn btn-sm"
                  :class="searchType === 'products' ? 'btn-primary-2 common-animation-button' : 'btn-outline-primary-2 common-animation-button'"
                  @click="setSearchType('products')"
                >
                  {{ t('Products') }}
                </button>
                <button 
                  class="btn btn-sm"
                  :class="searchType === 'services' ? 'btn-primary-2 common-animation-button' : 'btn-outline-primary-2 common-animation-button'"
                  @click="setSearchType('services')"
                >
                  {{ t('Services') }}
                </button>
                <button 
                  class="btn btn-sm"
                  :class="searchType === 'packages' ? 'btn-primary-2 common-animation-button' : 'btn-outline-primary-2 common-animation-button'"
                  @click="setSearchType('packages')"
                >
                  {{ t('Packages') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="row">
        <div class="col-12 text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ t('Loading...') }}</span>
          </div>
          <p class="mt-3">{{ t('Searching...') }}</p>
        </div>
      </div>

      <!-- No Results -->
      <div v-else-if="!hasResults && searchQuery" class="row">
        <div class="col-12 text-center py-5">
          <VIcon size="64" icon="tabler-search-off" class="text-muted mb-3" />
          <h4 class="text-muted">{{ t('No results found') }}</h4>
          <p class="text-muted">{{ t('Try searching with different keywords') }}</p>
        </div>
      </div>

      <!-- Search Results -->
      <div v-else-if="hasResults" class="row">
        <!-- Products Section -->
        <div v-if="filteredResults.products.length > 0" class="col-12 mb-5">
          <div class="section-header mb-4">
            <h3 class="section-title">{{ t('Products') }} ({{ filteredResults.products.length }})</h3>
          </div>
          <div class="row">
            <div 
              v-for="product in filteredResults.products" 
              :key="`product-${product.id}`"
              class="col-lg-3 col-md-4 col-sm-6 mb-4"
            >
              <div class="product-card">
                <div class="product-content">
                  <div class="product-image light-shadow-wrap">
                    <img :src="product.photo_url || '/assets/images/system-config/default-picture.png'" :alt="product.name" />
                    <div class="light-shadow"></div>
                  </div>
                  <h4 class="product-name">{{ product.name }}</h4>
                  <p class="product-description" v-if="product.description">
                    {{ truncateText(product.description, 100) }}
                  </p>
                  <div class="product-price d-flex justify-content-between align-items-center">
                    <span class="price">{{ formatAmount(product.sale_price) }}</span>
                    <span class="type-badge product-badge">{{ (product.type) }}</span>
                  </div>
                  <div class="product-actions">
                    <button 
                      class="btn common-animation-button add-to-cart w-100"
                      @click="addToCart(product, 'Product')"
                    >
                      {{ t('Add to Cart') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Services Section -->
        <div v-if="filteredResults.services.length > 0" class="col-12 mb-5">
          <div class="section-header mb-4">
            <h3 class="section-title">{{ t('Services') }} ({{ filteredResults.services.length }})</h3>
          </div>
          <div class="row">
            <div 
              v-for="service in filteredResults.services" 
              :key="`service-${service.id}`"
              class="col-lg-3 col-md-4 col-sm-6 mb-4"
            >
              <div class="service-card">
                <div class="service-content">
                  <div class="service-image">
                    <img :src="service.category_image || '/assets/images/system-config/default-picture.png'" :alt="service.name" />
                  </div>
                  <h4 class="service-name">{{ service.name }}</h4>

                  <p class="service-info" v-if="parseFloat(service.duration) > 0">
                    <VIcon icon="tabler-clock" size="20" />
                    <span class="duration">
                      {{ parseFloat(service.duration) > 1 ? service.duration + ' ' + service.duration_type + 's' : service.duration + ' ' + service.duration_type }}
                    </span>
                  </p>
                  <p class="service-info">
                    <VIcon icon="tabler-users" size="20" />
                    <span class="duration staff-assigned" v-if="service.staff_assigned > 0">
                      {{ service.staff_assigned }} Staffs
                    </span>
                    <span class="duration staff-assigned" v-else>
                      N/A
                    </span>
                  </p>
                  <p class="service-info">
                    <VIcon icon="tabler-coin" size="20" />
                    <span class="duration">{{ (service.price) }}</span>
                  </p>
                  <div class="service-rating" v-if="service.rating">
                    <div class="rating-stars">
                      <VIcon 
                        v-for="i in 5" 
                        :key="i"
                        icon="tabler-star-filled" 
                        :class="i <= service.rating ? 'text-warning' : 'text-muted'"
                      />
                    </div>
                    <span class="rating-text">({{ service.reviews }} {{ t('reviews') }})</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <BookNowBtn2 :link="`/appointment-service?service_id=${encryptId(service.id)}`" :text="t('Book Now')" />
                    <span class="type-badge service-badge">{{ (service.type) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Packages Section -->
        <div v-if="filteredResults.packages.length > 0" class="col-12 mb-5">
          <div class="section-header mb-4">
            <h3 class="section-title">{{ t('Packages') }} ({{ filteredResults.packages.length }})</h3>
          </div>
          <div class="row">
            <div 
              v-for="packageItem in filteredResults.packages" 
              :key="`package-${packageItem.id}`"
              class="col-lg-3 col-md-4 col-sm-6 mb-4"
            >
              <div class="package-card">
                <div class="package-content">
                  <div class="package-image light-shadow-wrap">
                    <img :src="packageItem.photo_url || '/assets/images/system-config/default-picture.png'" :alt="packageItem.name" />
                    <div class="light-shadow"></div>
                  </div>
                  <h4 class="package-name">{{ packageItem.name }}</h4>
                  <div class="media">
                    <VIcon icon="tabler-clock" size="24" class="serviceInfoicon me-2" />
                    <div class="media-body" v-if="parseFloat(packageItem.duration) > 0">
                      <p class="mb-0">{{ parseFloat(packageItem.duration) > 1 ? packageItem.duration + ' ' + packageItem.duration_type + 's' : packageItem.duration + ' ' + packageItem.duration_type }}</p>
                    </div>
                  </div>
                  <div class="media mb-3 justify-content-between">
                    <div class="media-body d-flex align-items-center">
                      <VIcon icon="tabler-premium-rights" size="24" class="serviceInfoicon me-2" />
                      <span class="mb-0">
                        {{ formatAmount(packageItem.sale_price) }}
                      </span>
                    </div>
                    <span class="type-badge package-badge">{{ (packageItem.type) }}</span>
                  </div>
                  <div class="attached-services">
                    <ul class="package-details">
                      <li v-for="detail in packageItem.item_details" :key="detail.id">
                        <div v-tooltip="detail.items.name + ' (' + detail.quantity + ' x)'">
                          <span>
                            <VIcon icon="tabler-check" size="24" class="serviceInfoicon" />
                          </span>
                          <span class="pe-2">
                            {{ detail.items.name }} ({{ detail.quantity }} x)
                          </span>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="package-actions">
                    <button 
                      class="btn common-animation-button add-to-cart w-100"
                      @click="addToCart(packageItem, 'Package')"
                    >
                      {{ t('Add to Cart') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More Button -->
        <div v-if="hasMorePages" class="col-12 text-center mt-4">
          <button 
            class="btn btn-outline-primary"
            @click="loadMore"
            :disabled="isLoading"
          >
            <VIcon icon="tabler-loader" v-if="isLoading" class="me-2" />
            {{ t('Load More') }}
          </button>
        </div>
      </div>

      <!-- Initial State -->
      <div v-else-if="!searchQuery" class="row">
        <div class="col-12 text-center py-5">
          <VIcon size="64" icon="tabler-search" class="text-muted mb-3" />
          <h4 class="text-muted">{{ t('Start searching...') }}</h4>
          <p class="text-muted">{{ t('Enter keywords to find products, services, or packages') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useSearch } from '@/composables/useSearch'
import { useShoppingCartStore } from '@/stores/shoppingCart'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { toast } from 'vue3-toastify'
import BookNowBtn2 from './mini-components/BookNowBtn2.vue'

const { t } = useI18n()
const route = useRoute()
const { formatAmount } = useCompanyFormatters()
const cartStore = useShoppingCartStore()
// IRUL ID Encryption
function encryptId(id) {
  return btoa(id) // Base64 encode
}
// Props
const props = defineProps({
  mode: {
    type: String,
    default: 'component' // 'component' or 'page'
  },
  itemLimit: {
    type: Number,
    default: 12
  },
  sectionClass: {
    type: String,
    default: 'search-results default-section-padding'
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  initialQuery: {
    type: String,
    default: ''
  }
})

// Search composable
const {
  searchQuery,
  searchResults,
  isLoading,
  searchType,
  currentPage,
  totalPages,
  totalResults,
  hasResults,
  hasMorePages,
  searchSummary,
  performSearch: performSearchComposable,
  clearSearch,
  loadMore: loadMoreComposable
} = useSearch()

// Filtered results by type
const filteredResults = computed(() => {
  if (!searchResults.value.length) {
    return { products: [], services: [], packages: [] }
  }

  const results = { products: [], services: [], packages: [] }
  
  searchResults.value.forEach(item => {
    if (item.type === 'Product') {
      results.products.push(item)
    } else if (item.type === 'Service') {
      results.services.push(item)
    } else if (item.type === 'Package') {
      results.packages.push(item)
    }
  })

  return results
})

// Handle search input
const handleSearchInput = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    performSearch()
  }
}

// Perform search
const performSearch = () => {
  if (searchQuery.value && searchQuery.value.trim()) {
    performSearchComposable(searchQuery.value.trim(), searchType.value)
  }
}

// Set search type
const setSearchType = (type) => {
  searchType.value = type
  if (searchQuery.value && searchQuery.value.trim()) {
    performSearch()
  }
}

// Load more results
const loadMore = () => {
  loadMoreComposable()
}

// Add item to cart
const addToCart = (item, type) => {
  const cartItem = {
    id: item.id,
    name: item.name,
    price: item.sale_price || item.price,
    image: item.photo_url || item.category_image,
    type: type,
    quantity: 1
  }
  
  cartStore.addItem(cartItem)
  
  toast(t('Item added to cart'), {
    type: 'success',
    position: 'top-right',
    autoClose: 2000
  })
}

// Truncate text
const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Initialize search from route query or initialQuery prop
onMounted(() => {
  const queryToUse = route.query.q || props.initialQuery
  if (queryToUse) {
    searchQuery.value = queryToUse
    performSearchComposable(queryToUse, searchType.value)
  }
})

// Watch for route changes
watch(() => route.query.q, (newQuery) => {
  if (newQuery && newQuery !== searchQuery.value) {
    searchQuery.value = newQuery
    performSearchComposable(newQuery, searchType.value)
  }
})

// Watch for initialQuery prop changes
watch(() => props.initialQuery, (newQuery) => {
  if (newQuery && newQuery !== searchQuery.value) {
    searchQuery.value = newQuery
    performSearchComposable(newQuery, searchType.value)
  }
})
</script>

<style scoped>
.search-results {
  padding: 60px 0;
}

.product-header {
  text-align: center;
  margin-bottom: 40px;
}

.product-header .title {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--title-color);
  margin-bottom: 10px;
}

.product-header .subtitle {
  color: var(--text-color);
  margin-bottom: 30px;
}

.search-inner {
  position: relative;
  max-width: 500px;
  margin: 0 auto;
}

.search-inner input {
  border-radius: 25px;
  border: 2px solid #E0E0E0;
  background: white;
  width: 100%;
  height: 50px;
  font-size: 16px;
  padding: 0 60px 0 20px;
  transition: all 0.3s ease;
}

.search-inner input:focus {
  border-color: var(--primary-bg-color);
  box-shadow: 0 0 0 0.2rem rgba(var(--primary-bg-color-rgb), 0.25);
  outline: none;
}

.search-inner .v-icon {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--primary-bg-color);
  cursor: pointer;
  font-size: 20px;
  transition: all 0.3s ease;
}

.search-inner .v-icon:hover {
  color: var(--title-color);
}

.search-filters {
  display: flex;
  justify-content: center;
}

.filter-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}


.filter-buttons .btn {
  border-radius: 20px;
  padding: 8px 20px;
  font-weight: 500;
  font-size: 14px !important;
  transition: all 0.3s ease;
}

.section-header {
  text-align: center;
}

.section-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--title-color);
  margin-bottom: 0;
}

/* Card Styles */
.product-card,
.service-card,
.package-card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  height: 100%;
  overflow: hidden;
}

.product-card:hover,
.service-card:hover,
.package-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.service-image {
  margin-bottom: 20px;
}
.service-image  img {
  max-width: 65px;
  max-height: 65px;
  object-fit: cover;
}

.product-image,
.package-image {
  position: relative;
  /* height: 200px; */
  overflow: hidden;
}

.product-image img,
.package-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}
.package-image img {
  border-radius: 8px;
  margin-bottom: 20px;
}
.product-overlay,
.service-overlay,
.package-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: all 0.3s ease;
}

.product-card:hover .product-overlay,
.service-card:hover .service-overlay,
.package-card:hover .package-overlay {
  opacity: 1;
}

.product-content,
.service-content,
.package-content {
  padding: 20px;
}

.product-card .product-name {
  margin-top: 20px;
}

.product-name,
.service-name,
.package-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--title-color);
  margin-bottom: 10px;
  line-height: 1.3;
}

.product-description,
.package-description {
  color: var(--text-color);
  font-size: 0.9rem;
  margin-bottom: 15px;
  line-height: 1.4;
}

.product-price .price,
.package-price .price {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--primary-bg-color);
}

.service-rating {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 15px;
}

.rating-stars {
  display: flex;
  gap: 2px;
}

.rating-text {
  font-size: 0.8rem;
  color: var(--text-color);
}

.product-actions,
.service-actions,
.package-actions {
  margin-top: 15px;
}

/* Loading and Empty States */
.spinner-border {
  width: 3rem;
  height: 3rem;
}

.type-badge {
  font-size: 12px;
  font-weight: 500;
  padding: 4px 8px;
  border-radius: 12px;
}
.service-badge {
  color: rgba(255, 121, 0, 1);
  background: #ffefe1;
}
.product-badge {
  /* color should be green */
  color: #7367f0;
  background: #f1f0ff;
}
.package-badge {
  /* should be primary */
  color: #F7A34C;
  background: #FEF0E2;
}


.add-to-cart {
  color: var(--color-white);
  text-transform: capitalize;
  text-align: center;
  justify-content: center;
  height: 48px;
}

.add-to-cart::before {
  background-color: var(--primary-bg-hover-color);
  color: var(--color-white);
}
.add-to-cart::after {
  border: 1px solid var(--primary-bg-color);
  color: var(--color-white);
  background-color: var(--primary-bg-color);
}
.add-to-cart:hover {
  color: var(--color-white);
}


.btn-primary-2 {
  color: var(--color-white);
  text-transform: capitalize;
  text-align: center;
  justify-content: center;
}

.btn-primary-2::before {
  background-color: var(--primary-bg-hover-color);
}
.btn-primary-2::after {
  background-color: var(--primary-bg-color);
}
.btn-primary-2:hover {
  color: var(--color-white);
}
.btn-primary-2:hover::before {
  width: 100%;
}

.btn-outline-primary-2 {
  color: var(--primary-bg-color);
  text-transform: capitalize;
  text-align: center;
  justify-content: center;
}

.btn-outline-primary-2::before {
  background-color: var(--primary-bg-color);
  color: var(--color-white);
}
.btn-outline-primary-2::after {
  color: var(--primary-bg-color);
  background-color: rgb(235, 235, 235);
}
.btn-outline-primary-2:hover {
  color: var(--color-white);
}


/* Responsive */
@media (max-width: 768px) {
  .search-results {
    padding: 40px 0;
  }
  
  .product-header .title {
    font-size: 2rem;
  }
  
  .filter-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .filter-buttons .btn {
    width: 200px;
  }
}

.package-details {
  list-style: none;
}
.attached-services {
  margin-top: 20px;
}
</style>