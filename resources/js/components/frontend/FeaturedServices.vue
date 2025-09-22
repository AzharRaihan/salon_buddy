<template>
  <section ref="featuredServicesSection">
    <div class="featured-services default-section-padding">
    <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h6 class="heading-mini-title">{{ t('Service') }}</h6>
              <h2 class="section-title">{{ t('Featured Services') }}</h2>

              <div class="features-category">
                <a 
                  href="#" 
                  class="btn common-animation-button features-category-btn"
                  :class="{ active: selectedCategory === 'all' }"
                  @click.prevent="filterByCategory('all')"
                >
                  {{ t('All') }}
                </a>
                <a 
                  v-for="category in serviceCategories" 
                  :key="category.id"
                  href="#" 
                  class="btn common-animation-button features-category-btn"
                  :class="{ active: selectedCategory === category.id }"
                  @click.prevent="filterByCategory(category.id)"
                >
                  {{ category.name }}
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <div class="features-service-swiper-wrap">
          <div class="swiper featured-services-swiper">
            <transition-group name="fade-up" tag="div" class="swiper-wrapper">
              <div class="swiper-slide" v-for="(service, index) in featuredServices" :key="service.id" :style="{ transitionDelay: (index * 0.1) + 's' }">
                <div class="service-card h-100">
                  <div class="service-content">
                    <img :src="service.image" class="img-fluid service-image" :alt="service.name">
                    <h4 class="service-name">
                      {{ service.name }}
                      <VTooltip
                          activator="parent"
                          location="top"
                        >
                          {{ service.name }}
                        </VTooltip>
                    </h4>
                    <p class="service-info" v-if="parseFloat(service.duration) > 0">
                      <span class="duration">{{ t('Duration') }}: {{ parseFloat(service.duration) > 1 ? service.duration + ' ' + service.duration_type + 's' : service.duration + ' ' + service.duration_type }}</span>
                    </p>
                    <p class="service-info">
                      <span class="duration">{{ formatAmount(service.price) }}</span>
                    </p>
                    <div class="service-footer d-flex justify-content-between align-items-center">
                      <BookNowBtn :link="`/appointment-service?service_id=${encryptId(service.id)}`" :text="t('Book Now')" />
                    </div>
                  </div>
                </div>
              </div>
            </transition-group>
          </div>
          
          <!-- Navigation Buttons -->
          <div class="swiper-button-next common-swiper-button common-animation-button" v-if="featuredServices.length > 0">
            <VIcon size="22" icon="tabler-arrow-narrow-right" />
          </div>
          <div class="swiper-button-prev common-swiper-button common-animation-button" v-if="featuredServices.length > 0">
            <VIcon size="22" icon="tabler-arrow-narrow-left" />
          </div>

        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, watch, nextTick  } from 'vue'
import { Swiper } from 'swiper'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import BookNowBtn from './mini-components/BookNowBtn.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()
// Reactive data
const serviceCategories = ref([])
const featuredServices = ref([])
const selectedCategory = ref('all')
const loading = ref(false)
const websiteSettingsStore = useWebsiteSettingsStore()
const currency = computed(() => websiteSettingsStore.getCurrency)

// Watch for data changes to reinit swiper
watch(featuredServices, async () => {
  await nextTick()
  if (swiperInstance) {
    swiperInstance.destroy(true, true)
  }
  initSwiper()
})

// Fetch service categories
const fetchServiceCategories = async () => {
  try {
    const response = await $api('/get-service-categories')
    if (response.success) {
      serviceCategories.value = response.data
    }
  } catch (error) {
    console.error('Error fetching service categories:', error)
  }
}

// Fetch featured services
const fetchFeaturedServices = async (categoryId = 'all') => {
  try {
    loading.value = true
    const response = await $api(`/get-featured-services?category_id=${categoryId}`)
    if (response.success) {
      // Reset services to trigger transition
      featuredServices.value = []
      await nextTick()
      // Update with new services
      featuredServices.value = response.data
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Filter services by category
const filterByCategory = async (categoryId) => {
  selectedCategory.value = categoryId
  await fetchFeaturedServices(categoryId)
}

// Initialize swiper
let swiperInstance = null
const initSwiper = () => {
  if (swiperInstance) {
    swiperInstance.destroy(true, true)
  }

  swiperInstance = new Swiper('.featured-services-swiper', {
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
      clickable: true,
    },
    speed: 500,
    breakpoints: {
      576: {
        slidesPerView: 1,
        spaceBetween: 30,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1367: {
        slidesPerView: 4,
        spaceBetween: 30,
      }
    }
  })
}

// Component lifecycle
onMounted(async () => {
  await fetchServiceCategories()
  await fetchFeaturedServices()
})

// IRUL ID Encryption
function encryptId(id) {
  return btoa(id) // Base64 encode
}

</script>

<style scoped>
.features-category .btn.active {
  background-color: var(--primary-bg-color);
  color: white;
}

/* Transition animations */
.fade-up-enter-active,
.fade-up-leave-active {
  transition: all 0.5s ease;
}

.fade-up-enter-from,
.fade-up-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.fade-up-move {
  transition: transform 0.5s ease;
}




</style>