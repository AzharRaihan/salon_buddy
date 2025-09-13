<template>
  <section class="special-offers-section" :class="sectionClass">
    <div class="row" v-if="showHeader">
      <div class="col-12">
        <div class="section-header text-center">
          <h6 class="heading-mini-title">{{ t('Package') }}</h6>
          <h2 class="section-title">{{ t('Special Offer Packages') }}</h2>
        </div>
      </div>
    </div>
    
    <div class="container-fluid">

      <div class="special-offers-swiper-wrap">
        <div class="swiper special-offers-swiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide" v-for="offer in displayedOffers" 
            :key="offer.id">
              <div class="offer-card h-100">
                <div class="offer-content">
                  <div class="package-wrapper">
                    <div class="offer-image">
                      <img :src="offer.image || '../../@pos/assets/images/homepage/service.png'" :alt="offer.name" class="img-fluid">
                      <div class="offer-badge" v-if="offer.discount">
                        {{ formatAmount(offer.saveAmount) }} {{ t('OFF') }}
                      </div>
                    </div>
                    <div class="package-content">
                      <h4 class="offer-name">{{ offer.name }}</h4>
                      <div class="offer-description" v-if="offer.description" v-html="offer.description"></div>
                      <div class="offer-services">
                        <ul class="services-list">
                          <li v-for="service in offer.services" :key="service.name">
                            <span>
                              {{ service.name }}
                            </span>
                            <span>
                              ({{ service.quantity }}x)
                            </span>
                          </li>
                        </ul>
                        <div class="offer-pricing">
                          <div class="pricing-row">
                            <p class="label pe-1">{{ t('Price') }}</p>
                            <p class="package-price">
                              {{ formatAmount(offer.packagePrice) }}

                              <del class="regular-price">{{ formatAmount(offer.regularPrice) }}</del>
                            </p>
                          </div>
                          <div class="pricing-row" v-if="parseFloat(offer.duration) > 0">
                            <p class="label pe-1">{{ t('Duration') }} </p>
                            <p>
                              {{ parseFloat(offer.duration) > 1 ? offer.duration + ' ' + offer.duration_type + 's' : offer.duration + ' ' + offer.duration_type }}
                            </p>
                          </div>
                          <div class="d-flex">
                            <BookNowBtn :link="'#'" :text="t('Buy Now')" @click="addToCart(offer)" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Navigation Buttons -->
        <div class="swiper-button-next" v-if="specialOffers.length > 0">
          <VIcon size="22" icon="tabler-arrow-narrow-right" />
        </div>
        <div class="swiper-button-prev" v-if="specialOffers.length > 0">
          <VIcon size="22" icon="tabler-arrow-narrow-left" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { toast } from 'vue3-toastify';
import { Swiper } from 'swiper'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import BookNowBtn from './mini-components/BookNowBtn.vue'

import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()


// Initialize swiper
let swiperInstance = null;
const initSwiper = () => {
  if (swiperInstance) {
    swiperInstance.destroy(true, true) // cleanup old instance if exists
  }

  swiperInstance = new Swiper('.special-offers-swiper', {
    modules: [Navigation, Pagination, Autoplay],
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: false,
    },
    speed: 3000,
    breakpoints: {
      576: {
        slidesPerView: 2,
        spaceBetween: 15,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      1200: {
        slidesPerView: 3.5,
        spaceBetween: 15,
      },
      1367: {
        slidesPerView: 3.5,
        spaceBetween: 30,
      },
      1400: {
        slidesPerView: 'auto',
        spaceBetween: 30,
      }
    }
  })
}

// Component lifecycle
onMounted(async () => {
  await fetchPackages()
  await fetchPackagesPaginated(1)
  
  // Initialize swiper after data is loaded
  setTimeout(() => {
    initSwiper()
  }, 100)
})





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
    default: 6
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

const paginationPages = computed(() => {
  if (!paginationData.value) return []
  
  const totalPages = paginationData.value.last_page
  const current = currentPage.value
  const pages = []
  
  // Simple pagination logic - show up to 10 pages
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
const fetchPackages = async () => {
  try {
    const response = await $api('/get-package-type-item-list')
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
        name: detail.item.name,
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
  if (page === '...' || page === currentPage.value) return
  fetchPackagesPaginated(page)
}

const addToCart = (offer) => {
  cartStore.addItem({
    id: offer.id,
    name: offer.name,
    price: offer.packagePrice,
    quantity: 1,
    image: offer.image,
    type: 'Package',
    services: offer.services,
    description: offer.description
  })

  toast('Package added to cart!', {
    type: 'success',
  })
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
.special-offers-swiper .swiper-slide {
  width: auto;
  flex-shrink: 0;
  margin-right: 30px;
}
</style> 