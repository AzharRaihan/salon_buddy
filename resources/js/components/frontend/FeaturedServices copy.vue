<template>
  <section ref="featuredServicesSection">
    <div class="featured-services default-section-padding">
    <transition name="fade-up" @after-enter="initSwiper">
      <div class="container" v-if="visible" :style="{ transitionDelay: (0.7) + 's' }">
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h6 class="heading-mini-title">{{ t('Service') }}</h6>
              <h2 class="section-title">{{ t('Featured Services') }}</h2>

              <div class="features-category">
                <a 
                  href="#" 
                  class="btn"
                  :class="{ active: selectedCategory === 'all' }"
                  @click.prevent="filterByCategory('all')"
                >
                  {{ t('All') }}
                </a>
                <a 
                  v-for="category in serviceCategories" 
                  :key="category.id"
                  href="#" 
                  class="btn"
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
            <div class="swiper-wrapper" >
              <div class="swiper-slide" v-for="(service, index) in featuredServices" :key="service.id">
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
                      <BookNowBtn :link="`/frontend/appointment-service?service_id=${encryptId(service.id)}`" :text="t('Book Now')" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Navigation Buttons -->
          <div class="swiper-button-next" v-if="featuredServices.length > 0">
            <VIcon size="22" icon="tabler-arrow-narrow-right" />
          </div>
          <div class="swiper-button-prev" v-if="featuredServices.length > 0">
            <VIcon size="22" icon="tabler-arrow-narrow-left" />
          </div>

        </div>
      </div>
    </transition>
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

// Animation
const visible = ref(false)
const featuredServicesSection = ref(null)
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
    observer.observe(featuredServicesSection.value)
  })
// Animation End

// Reactive flag to know when data is ready
const dataReady = ref(false)

// Watch for data + visibility
watch(
  () => [visible.value, dataReady.value],
  ([isVisible, isDataReady]) => {
    if (isVisible && isDataReady) {
      nextTick(() => {
        initSwiper()
      })
    }
  }
)


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
const vEllipsisTooltip = {
  mounted(el, binding) {
    nextTick(() => {
      const isOverflowing = el.scrollHeight > el.clientHeight || el.scrollWidth > el.clientWidth
      if (isOverflowing) {
        el.setAttribute('title', binding.value) // fallback native tooltip
      } else {
        el.removeAttribute('title')
      }
    })
  }
}
// Fetch featured services
const fetchFeaturedServices = async (categoryId = 'all') => {
  try {
    loading.value = true
    const response = await $api(`/get-featured-services?category_id=${categoryId}`)
    if (response.success) {
      featuredServices.value = response.data
      dataReady.value = true
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
    swiperInstance.destroy(true, true) // cleanup old instance if exists
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
  
  // Initialize swiper after data is loaded
  // setTimeout(() => {
  //   initSwiper()
  // }, 100)
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
</style>