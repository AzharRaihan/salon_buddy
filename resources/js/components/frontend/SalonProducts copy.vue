<template>
  <section :class="[sectionClass, 'animate-out']">
    <div class="container">
      <div class="row" v-if="showHeader">
        <div class="col-12">
          <div class="section-header text-center">
            <h6 class="heading-mini-title" xyz="fade down-2 duration-8">{{ t('Products') }}</h6>
            <h2 class="section-title" xyz="fade down-1 duration-8 delay-2">{{ t('Salon Quality Products') }}</h2>
          </div>
        </div>
      </div>
      
      <!-- Product Header (search + results count) - Only show in page mode -->
      <div v-if="showProductControls" class="d-flex justify-content-between align-items-center product-header">
        <div class="search" xyz="fade left-2 duration-8">
          <div class="search-inner">
            <input 
              type="text" 
              :placeholder="t('Search')" 
              class="form-control"
              v-model="searchQuery"
              @input="onSearch"
            >
            <VIcon size="22" icon="tabler-search" />
          </div>
        </div>
        <div class="count" xyz="fade right-2 duration-8 delay-2">
          <p>{{ getPaginationText() }}</p>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="isLoading" class="row g-4">
        <div 
          v-for="i in 8" 
          :key="`loading-${i}`"
          class="col-xl-3 col-lg-4 col-md-6 col-sm-6"
        >
          <div class="product-card h-100 loading-skeleton" xyz="fade up-2 duration-8">
            <div class="product-image skeleton-image"></div>
            <div class="product-content">
              <div class="skeleton-title"></div>
              <div class="d-flex justify-content-between">
                <div class="skeleton-price"></div>
                <div class="skeleton-cart"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else class="row g-4">
        <div 
          v-for="(product, index) in displayedProducts" 
          :key="product.id"
          class="col-xl-3 col-lg-4 col-md-6 col-sm-6"
        >
          <div 
            class="product-card h-100"
            xyz="fade left-2 stagger-1 duration-10"
            :style="{ '--xyz-stagger': index * 0.1 + 's' }"
          >
            <div class="product-image" xyz="fade up-2 duration-8">
              <img :src="product.photo_url" :alt="product.name" class="img-fluid">
            </div>
            <div class="product-content" xyz="fade up-1 duration-6 delay-2">
              <h5 class="product-name">{{ product.name }}</h5>
              <div class="d-flex justify-content-between">
                <div class="product-price">
                  <span class="current-price">{{ formatAmount(product.sale_price) }}</span>
                </div>
                <div class="add-to-cart" @click="addToCart(product)" style="cursor: pointer;">
                  <VIcon size="22" icon="tabler-shopping-cart" />
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- View All Button (Homepage) -->
        <div v-if="showViewAllButton" class="col-12 text-center mt-5">
          <div xyz="fade up-2 duration-8 delay-4">
            <BookingSamllBtn :link="'/frontend/product'" :text="t('View All Products')" />
          </div>
        </div>

        <!-- Pagination (Product Page) -->
        <div v-if="showPagination && paginationData" class="col-12 text-center mt-0">
          <div class="pagination-wrapper" xyz="fade up-2 duration-8 delay-3">
            <div class="pagination-inner d-flex justify-content-center align-items-center">
              <div class="pagination-item d-flex justify-content-center align-items-center">
                <a 
                  v-for="page in paginationPages" 
                  :key="page"
                  href="#" 
                  @click.prevent="goToPage(page)"
                  :class="{ active: page === currentPage }"
                  xyz="fade scale-0.8 duration-6"
                >
                  {{ page }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import BookingSamllBtn from './mini-components/BookingSamllBtn.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()

// Props
const props = defineProps({
  mode: {
    type: String,
    default: 'homepage', // 'homepage' or 'page'
    validator: (value) => ['homepage', 'page'].includes(value)
  },
  sectionClass: {
    type: String,
    default: 'salon-products default-section-padding'
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  itemLimit: {
    type: Number,
    default: 8
  },
  buttonText: {
    type: String,
    default: 'Load More'
  }
})

// Stores
const cartStore = useShoppingCartStore()

// Reactive data
const salonProducts = ref([])
const paginationData = ref(null)
const currentPage = ref(1)
const searchQuery = ref('')
const isLoading = ref(false)
let searchTimeout = null

// Computed properties
const isHomepage = computed(() => props.mode === 'homepage')
const showViewAllButton = computed(() => isHomepage.value)
const showPagination = computed(() => !isHomepage.value)
const showProductControls = computed(() => !isHomepage.value)

const displayedProducts = computed(() => {
  if (isHomepage.value) {
    // Homepage → show only first 8 products
    return salonProducts.value.slice(0, props.itemLimit)
  } else {
    // Product page → use paginated data directly
    return salonProducts.value
  }
})

const paginationPages = computed(() => {
  if (!paginationData.value) return []
  
  const totalPages = paginationData.value.last_page
  const current = currentPage.value
  const pages = []
  
  // Simple pagination logic - show up to 5 pages around current
  const start = Math.max(1, current - 2)
  const end = Math.min(totalPages, current + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  // Add ellipsis and last page if needed
  if (end < totalPages) {
    if (end < totalPages - 1) {
      pages.push('...')
    }
    pages.push(totalPages)
  }
  
  return pages
})

// Methods
const fetchProducts = async () => {
  try {
    isLoading.value = true
    const response = await $api('/get-products')
    if (response.success) {
      salonProducts.value = response.data || []
    }
  } catch (error) {
    console.error('Error fetching products:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchProductsPaginated = async (page = 1, search = '') => {
  try {
    isLoading.value = true
    const response = await $api(`/get-products-paginated?page=${page}&per_page=${props.itemLimit}&search=${search}`)
    
    if (response.success) {
      // Add fade out effect before updating
      salonProducts.value = []
      
      // Small delay for smooth transition
      setTimeout(() => {
        salonProducts.value = response.data.data
        paginationData.value = response.data
        currentPage.value = page
      }, 150)
    }
  } catch (error) {
    console.error('Error fetching paginated products:', error)
  } finally {
    isLoading.value = false
  }
}

const goToPage = (page) => {
  if (page === '...' || page === currentPage.value) return
  fetchProductsPaginated(page, searchQuery.value)
}

const onSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchProductsPaginated(1, searchQuery.value)
  }, 300)
}

const getPaginationText = () => {
  if (!paginationData.value) return ''
  const { from, to, total } = paginationData.value
  return `Showing ${from}-${to} of ${total} results`
}

const addToCart = (product) => {
  // Add a small bounce animation effect
  const cartButton = event.target.closest('.add-to-cart')
  if (cartButton) {
    cartButton.style.transform = 'scale(0.8)'
    setTimeout(() => {
      cartButton.style.transform = 'scale(1.1)'
      setTimeout(() => {
        cartButton.style.transform = 'scale(1)'
      }, 150)
    }, 100)
  }
  
  cartStore.addItem({
    id: product.id,
    name: product.name,
    price: product.sale_price,
    quantity: 1,
    image: product.photo_url,
    type: 'Product',
    description: product.description
  })
  
  // Optional: Show toast notification
  toast('Product added to cart!', { type: 'success' })
}

onMounted(async () => {
  if (isHomepage.value) {
    await cartStore.initializeTaxSettings()
    await fetchProducts()
  } else {
    await fetchProductsPaginated(1)
  }
})
</script>

<style scoped>


.salon-products {
  background-image: url('../../../../public/assets/images/default-images/Product.png');
  background-repeat: no-repeat;
}
/* Product header styles */
.salon-products .product-header {
  margin-bottom: 30px;
}

.salon-products .product-header .search-inner {
  position: relative;
}

.salon-products .product-header .search-inner input {
  border-radius: 8px;
  border: 1px solid #E0E0E0;
  background: white;
  width: 100%;
  height: 40px;
  font-size: 16px;
  transition: 0.3s;
  transform: translateX(0);
  min-width: 300px;
}

.salon-products .product-header .search-inner input::placeholder {
  transition: 0.3s;
  transform: translateX(0);
}

.salon-products .product-header .search-inner input:focus {
  box-shadow: none;
}

.salon-products .product-header .search-inner input:focus::placeholder {
  padding-left: 10px;
  transform: translateX(10px);
  transition: 0.3s;
}

.salon-products .product-header .search-inner .v-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--title-color);
}

/* Product page specific styles */
.salon-products.salon-products-2 .product-card {
  border: none !important;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

/* Pagination styles */
.pagination-wrapper {
  margin-top: 40px;
}

.pagination-inner {
  gap: 10px;
}

.pagination-item a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  border: 1px solid #E0E0E0;
  background: white;
  color: var(--title-color);
  text-decoration: none;
  transition: all 0.3s ease;
  font-weight: 500;
}

.pagination-item a:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.pagination-item a.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.pagination-item a:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Enhanced product card animations */
.product-card {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateZ(0);
  position: relative;
  overflow: hidden;
}

.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.product-card:hover::before {
  left: 100%;
}

.product-card:hover {
  transform: translateY(-12px) scale(1.03);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-card:hover .product-image img {
  transform: rotate(2deg) scale(1.08);
}

.product-image img {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.add-to-cart {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.add-to-cart:hover {
  transform: scale(1.15) rotate(5deg);
  color: var(--primary-color);
}

.add-to-cart::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(var(--primary-color-rgb, 0, 123, 0.1));
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.3s ease;
}

.add-to-cart:hover::after {
  width: 40px;
  height: 40px;
}

/* Search input animation enhancement */
.search-inner input:focus {
  box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb, 0, 123, 0.1));
  border-color: var(--primary-color);
  transform: translateY(-1px);
}

/* CSS Animation Fallbacks */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Main section animation */
.animate-in {
  animation: fadeInUp 0.8s ease-out;
}

/* Staggered product card animations */
.product-card {
  animation: fadeInLeft 0.6s ease-out;
  animation-fill-mode: both;
}

.product-card:nth-child(1) { animation-delay: 0.1s; }
.product-card:nth-child(2) { animation-delay: 0.2s; }
.product-card:nth-child(3) { animation-delay: 0.3s; }
.product-card:nth-child(4) { animation-delay: 0.4s; }
.product-card:nth-child(5) { animation-delay: 0.5s; }
.product-card:nth-child(6) { animation-delay: 0.6s; }
.product-card:nth-child(7) { animation-delay: 0.7s; }
.product-card:nth-child(8) { animation-delay: 0.8s; }

/* Header animations */
.section-header .heading-mini-title {
  animation: fadeInDown 0.8s ease-out 0.2s both;
}

.section-header .section-title {
  animation: fadeInDown 0.8s ease-out 0.4s both;
}

/* Search and controls animations */
.search {
  animation: fadeInLeft 0.8s ease-out 0.6s both;
}

.count {
  animation: fadeInRight 0.8s ease-out 0.8s both;
}

/* View all button animation */
.showViewAllButton {
  animation: fadeInUp 0.8s ease-out 1s both;
}

/* Pagination animation */
.pagination-wrapper {
  animation: fadeInUp 0.8s ease-out 0.8s both;
}

/* Loading skeleton styles */
.loading-skeleton {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.skeleton-image {
  height: 200px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-title {
  height: 20px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  margin-bottom: 15px;
  border-radius: 4px;
}

.skeleton-price {
  height: 16px;
  width: 60px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-cart {
  height: 16px;
  width: 16px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 50%;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style> 