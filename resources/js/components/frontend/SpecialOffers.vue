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
          <div class="row">
            <div class="col-md-6 col-xl-4 pd-0" v-for="offer in displayedOffers" 
            :key="offer.id">
              <div class="about-list-right">
                <figure>
                  <img :src="offer.image || '../../@pos/assets/images/homepage/service.png'" :alt="offer.name" class="img-fluid">
                  <span class="save-badge" v-if="offer.discount">{{ formatAmount(offer.saveAmount) }} {{ t('OFF') }}</span>
                </figure>
                <div class="content">
                  <h4>{{ offer.name }}</h4>
                  <div class="serviceInfoMain">
                    <div class="media">
                      <VIcon icon="tabler-clock" size="24" class="serviceInfoicon" />
                      <div class="media-body" v-if="parseFloat(offer.duration) > 0">
                        <p class="serviceInfoHeading">{{ t('Duration') }}:</p>
                        <p class="serviceInfoValue ps-1">{{ parseFloat(offer.duration) > 1 ? offer.duration + ' ' + offer.duration_type + 's' : offer.duration + ' ' + offer.duration_type }}</p>
                      </div>
                    </div>
                    <div class="media mb-3">
                      <VIcon icon="tabler-premium-rights" size="24" class="serviceInfoicon" />
                      <div class="media-body">
                        <p class="serviceInfoHeading">{{ t('Price') }}:</p>
                        <p class="serviceInfoValue ps-1">
                          {{ formatAmount(offer.packagePrice) }}
                          <del class="regular-price">{{ formatAmount(offer.regularPrice) }}</del>
                        </p>
                      </div>
                    </div>
                    <div class="attached-services">
                      <ul>
                        <li v-for="service in offer.services" :key="service.name">
                          <span>
                            <VIcon icon="tabler-check" size="24" class="serviceInfoicon" />
                          </span>
                          <span class="pe-2">
                            {{ service.name }} ({{ service.quantity }} x)
                          </span>
                        </li>
                      </ul>
                    </div>
                    <a href="javascript:void(0)" class="booknowBtn btn3" @click="addToCart(offer)"><span>{{ t('Buy Now') }}</span></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <div class="row">
        <!-- View All Button (Homepage) -->
        <div v-if="showViewAllButton" class="col-12 text-center mt-5">
          <BookingSamllBtn :link="'/package'" :text="'All Packages'" />
        </div>

        <!-- Pagination (Package Page) -->
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
.save-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: linear-gradient(135deg, var(--primary-bg-color), var(--primary-bg-color));
  color: var(--color-white);
  padding: 8px 15px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  z-index: 2;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.included-service-name {
  font-weight: 400;
  font-size: 16px;
}
.attached-services {
  background: #F9F9F9;
  border-radius: 12px;
  padding: 10px;
}
.attached-services ul {
  padding: 0;
  margin: 0;
}
.attached-services ul li {
  list-style: none;
  display: flex;
  align-items: center;
}
.about-area-one .about-list-right {
  display: flex;
  width: 100%;
  float: left;
}

.about-area-one .about-list-right figure {
  width: 50%;
  float: left;
  position: relative;
  z-index: 9;
  overflow: hidden;
  padding-top: 50%;
  margin: 0;
}
.about-area-one .about-list-right figure:before {
  content: '';
  position: absolute;
  left: 100%;
  right: 0;
  bottom: 50%;
  top: 50%;
  background: rgba(0, 0, 0, 0.5);
  transition: all 0.5s;
  z-index: 99;
}
.about-area-one .about-list-right figure:after {
  content: '';
  position: absolute;
  top: 42%;
  right: -19px;
  transform: rotate(45deg);
  background: #fff;
  width: 35px;
  height: 35px;
  z-index: 999;
}
.about-area-one .about-list-right figure img {
  transition: all 0.8s;
  position: absolute;
  top: 0;
  height: 100%;
  width: auto;
  max-width: initial;
  left: 50%;
  transform: translateX(-50%);
}
.about-area-one .about-list-right .content {
  background: #fff;
  width: 50%;
  display: flex;
  position: relative;
  z-index: 1;
  padding-left: 20px;
  padding-top: 20px;
  padding-bottom: 20px;
  flex-direction: column;
  justify-content: center;
  min-height: 100%;
}
.about-area-one .about-list-right .content h4 {
  font-size: 28px;
  font-weight: 600;
  margin: 0 0 10px;
  display: block;
  line-height: 35px;
}
.media {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.serviceInfoMain .serviceInfoicon {
  color: var(--primary-bg-hover-color);
  margin-right: 15px;
}
.media-body {
  display: flex;
  justify-content: start;
  align-items: center;
}
.serviceInfoMain .serviceInfoHeading {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 0;
  line-height: 1;
  text-align: left;
}
.about-area-one .about-list-right .content p {
  color: #797979;
}
.serviceInfoMain .serviceInfoValue {
  font-size: 16px;
  text-align: left;
}
.serviceInfoMain p {
  font-size: 14px;
  line-height: 26px;
  margin: 0;
  padding: 0;
}
.about-area-one .about-list-right .content a {
  font-weight: 600;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 3px;
}
.btn3:before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 100%;
  left: 0;
  right: 100%;
  height: 0;
  background: var(--primary-bg-hover-color);
  transition: all 0.5s;
}
.btn3:after {
  content: '';
  position: absolute;
  top: 100%;
  bottom: 0;
  right: 0;
  left: 100%;
  height: 100%;
  background: var(--primary-bg-hover-color);
  transition: all 0.5s;
}
.btn3 span {
  position: relative;
  z-index: 1;
  color: #fff;
}

.about-area-one .about-list-right:hover figure:before {
  left: 0;
  bottom: 0;
  top: 0;
  border-radius: 0px;
}
.about-area-one .about-list-right:hover figure img {
  transform: scale(1.2, 1.2) translateX(-50%);
}
.serviceInfoMain .booknowBtn {
  text-align: center;
  font-size: 18px;
  line-height: 35px;
  margin-top: 10px;
  padding: 5px 20px;
  width: 100%;
}
.btn3 {
  display: inline-block;
  position: relative;
  line-height: 50px;
  background: var(--primary-bg-color);
  font-weight: 600;
  text-transform: uppercase;
  color: #fff;
  padding: 0 27px;
  overflow: hidden;
  vertical-align: middle;
}
.btn3:hover:before {
  right: 50%;
  top: 0;
  bottom: 0;
  height: 100%;
}
.btn3:hover:after {
  left: 50%;
  top: 0;
  bottom: 0;
}
</style> 