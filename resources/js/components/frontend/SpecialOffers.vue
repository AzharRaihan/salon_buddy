<template>
  <section class="special-offers-section" :class="sectionClass">
    <div class="container-fluid">
      <div class="row" v-if="showHeader">
        <div class="col-12">
          <div class="section-header text-center">
            <h6 class="heading-mini-title">{{ t('Package') }}</h6>
            <h2 class="section-title">{{ t('Special Offer Packages') }}</h2>
          </div>
        </div>
      </div>
      <!-- New Design -->
      <section class="about-area-one" id="ourServices">
        <div class="container-fluid">
          <div class="row" v-if="displayedOffers.length > 0">
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-4 pd-0" v-for="offer in displayedOffers" 
            :key="offer.id">
              <div class="about-list-right">
                <figure>
                  <img :src="offer.image || '../../@pos/assets/images/homepage/service.png'" :alt="offer.name" class="img-fluid">
                  <span class="save-badge" v-if="offer.discount">{{ formatAmount(offer.saveAmount) }} {{ t('OFF') }}</span>
                </figure>
                <div class="content">
                  <h4 v-tooltip="offer.name">{{ offer.name }}</h4>
                  <div class="serviceInfoMain">
                    <div class="media">
                      <VIcon icon="tabler-clock" size="24" class="serviceInfoicon" />
                      <div class="media-body" v-if="parseFloat(offer.duration) > 0">
                        <p class="serviceInfoValue">{{ parseFloat(offer.duration) > 1 ? offer.duration + ' ' + offer.duration_type + 's' : offer.duration + ' ' + offer.duration_type }}</p>
                      </div>
                    </div>
                    <div class="media mb-3">
                      <VIcon icon="tabler-premium-rights" size="24" class="serviceInfoicon" />
                      <div class="media-body">
                        <p class="serviceInfoValue">
                          {{ formatAmount(offer.packagePrice) }}
                          <del class="regular-price">{{ formatAmount(offer.regularPrice) }}</del>
                        </p>
                      </div>
                    </div>
                    <div class="attached-services">
                      <ul>
                        <li v-for="service in offer.services" :key="service.name">
                          <div v-tooltip="service.name + ' (' + service.quantity + ' x)'">
                            <span>
                              <VIcon icon="tabler-check" size="24" class="serviceInfoicon" />
                            </span>
                            <span class="pe-2">
                              {{ service.name }} ({{ service.quantity }} x)
                            </span>
                          </div>
                        </li>
                      </ul>
                    </div>
                    <a href="javascript:void(0)" class="booknowBtn btn3" @click="addToCart(offer)"><span>{{ t('Buy Now') }}</span></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="displayedOffers.length == 0">
            <div class="text-center">
              <h5>{{ t('No package found') }}</h5>
              <VIcon size="45" icon="tabler-filter-search" />
            </div>
          </div>
        </div>
      </section>


      <div class="row">
        <!-- View All Button (Homepage) -->
        <div v-if="showViewAllButton && displayedOffers.length > 0" class="col-12 text-center mt-5">
          <BookingSamllBtn :link="'/package'" :text="'All Packages'" />
        </div>

        <!-- Pagination (Package Page) -->
        <div v-if="showPagination && paginationData && displayedOffers.length > 0" class="col-12 text-center mt-0">
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
                  &laquo;
                </a>
                
                <!-- Page Numbers -->
                <a 
                  href="#" 
                  v-for="page in Math.min(paginationData.last_page, 5)" 
                  :key="page"
                  @click.prevent="goToPage(page)"
                  :class="{ active: currentPage == page }"
                >
                  {{ page }}
                </a>
                
                <!-- Show dots if there are more pages -->
                <span v-if="paginationData.last_page > 5">...</span>
                
                <!-- Last page -->
                <a 
                  href="#" 
                  v-if="paginationData.last_page > 5"
                  @click.prevent="goToPage(paginationData.last_page)"
                  :class="{ active: currentPage == paginationData.last_page }"
                >
                  {{ paginationData.last_page }}
                </a>
                
                <!-- Next Button -->
                <a 
                  href="#" 
                  @click.prevent="goToPage(currentPage + 1)"
                  v-if="currentPage < paginationData.last_page"
                  class="pagination-btn"
                >
                  &raquo;
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
  import { toast } from 'vue3-toastify';
  import BookingSamllBtn from './mini-components/BookingSamllBtn.vue'
  import { useI18n } from 'vue-i18n';
  import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
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
      default: 'special-offers default-section-padding-b'
    },
    showHeader: {
      type: Boolean,
      default: true
    },
    itemLimit: {
      type: Number,
      default: 9
    },
    buttonText: {
      type: String,
      default: 'Buy This Package'
    }
  })

  // Stores
  const cartStore = useShoppingCartStore()

  // Reactive data
  const specialOffers = ref([])
  const paginationData = ref(null)
  const currentPage = ref(1)

  // Computed properties
  const isHomepage = computed(() => props.mode === 'homepage')
  const showViewAllButton = computed(() => isHomepage.value)
  const showPagination = computed(() => !isHomepage.value)

  const displayedOffers = computed(() => {
    if (isHomepage.value) {
      return specialOffers.value.slice(0, props.itemLimit)
    }
    return specialOffers.value
  })


  // Methods
  const fetchPackages = async () => {
    try {
      const response = await $api('/get-package-type-item-list-frontend')
      if (response.success) {
        transformApiData(response.data)
      }
    } catch (error) {
      console.error('Error fetching packages:', error)
    }
  }

  const fetchPackagesPaginated = async (page = 1) => {
    try {
      const response = await $api(`/get-package-type-item-list-paginated?page=${page}&per_page=6`)
      if (response.success) {
        transformApiData(response.data.data)
        paginationData.value = response.data
        currentPage.value = page
      }
    } catch (error) {
      console.error('Error fetching paginated packages:', error)
    }
  }

  const transformApiData = (data) => {
    specialOffers.value = data.map(item => {
      // Calculate regular price (sum of item_details price * quantity)
      const regularPrice = item.item_details?.reduce((total, detail) => {
        return total + Number(detail.price * detail.quantity)
      }, 0) || 0

      // Calculate package price (sum of total_price)
      const packagePrice = item.item_details?.reduce((total, detail) => {
        return total + Number(detail.total_price)
      }, 0) || 0

      // Calculate savings amount
      const saveAmount = regularPrice - packagePrice

      return {
        id: item.id,
        name: item.name,
        description: item.description,
        image: item.photo_url,
        duration: item.duration,
        duration_type: item.duration_type,
        services: item.item_details?.map(detail => ({
          name: detail.items.name,
          quantity: detail.quantity
        })) || [],
        regularPrice: regularPrice,
        packagePrice: packagePrice,
        saveAmount: saveAmount,
        discount: item.discount_percentage || calculateDiscount(regularPrice, packagePrice)
      }
    })
  }

  const calculateDiscount = (regular, sales) => {
    if (!regular || !sales) return 0
    return Math.round(((regular - sales) / regular) * 100)
  }

  const goToPage = (page) => {
    if (page === currentPage.value) return
    fetchPackagesPaginated(page)
  }

  const addToCart = (offer) => {
    cartStore.addItem({
      id: offer.id,
      name: offer.name,
      price: offer.packagePrice,
      quantity: 1,
      image: offer.image,
      type: 'package',
      services: offer.services,
      description: offer.description
    })
    
    // Optional: Show toast notification
    toast('Package added to cart!', { type: 'success' })
  }

  onMounted(async () => {
    if (isHomepage.value) {
      await fetchPackages()
    } else {
      await fetchPackagesPaginated(1)
    }
  })
</script>

<style scoped>
/* Pagination styles */
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
</style> 