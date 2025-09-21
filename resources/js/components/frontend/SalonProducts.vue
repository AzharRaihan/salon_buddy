<template>
  <section :class="sectionClass">
    <div class="container">
      <div class="row" v-if="showHeader">
        <div class="col-12">
          <div class="section-header text-center">
            <h6 class="heading-mini-title">{{ t('Products') }}</h6>
            <h2 class="section-title">{{ t('Salon Quality Products') }}</h2>
          </div>
        </div>
      </div>
      
      <!-- Product Header (search + results count) - Only show in page mode -->
      <div v-if="showProductControls" class="d-flex justify-content-between align-items-center product-header">
        <div class="search">
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
        <div class="count">
          <p>{{ getPaginationText() }}</p>
        </div>
      </div>
      
      <div ref="productSection">
        <transition-group name="fade-up" tag="div" class="row g-4" >
          <div 
            v-for="(product, index) in displayedProducts" 
            :key="product.id"
            v-show="visible"
            class="col-xl-3 col-lg-4 col-md-6 col-sm-6"
            :style="{ transitionDelay: (index * 0.4) + 's' }"
          >
            <div class="product-card h-100">
              <div class="product-image">
                <img :src="product.photo_url" :alt="product.name" class="img-fluid">
                <div class="light-shadow"></div>
              </div>
              <div class="product-content">
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
          <div v-if="showViewAllButton" class="col-12 text-center mt-5" v-show="visible" :style="{ transitionDelay: (8 * 0.4) + 's' }">
            <BookingSamllBtn  :link="'/frontend/product'" :text="t('View All Products')" />
          </div>

          <!-- Pagination (Product Page) -->
          <div v-if="showPagination && paginationData" class="col-12 text-center mt-0">
            <div class="pagination-wrapper">
              <div class="pagination-inner d-flex justify-content-center align-items-center">
                <div class="pagination-item d-flex justify-content-center align-items-center">
                  <a 
                    v-for="page in paginationPages" 
                    :key="page"
                    href="#" 
                    @click.prevent="goToPage(page)"
                    :class="{ active: page === currentPage }"
                  >
                    {{ page }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </transition-group>
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

// Animation
const visible = ref(false)
const productSection = ref(null)
onMounted(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          visible.value = true
          observer.disconnect()
        }
      },
      { threshold: 0.2 }
    )
    observer.observe(productSection.value)
  })
// Animation End

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
      salonProducts.value = response.data.data
      paginationData.value = response.data
      currentPage.value = page
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
/* .pagination-wrapper {
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
}

.pagination-item a.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.pagination-item a:disabled {
  opacity: 0.5;
  cursor: not-allowed;
} */
</style> 