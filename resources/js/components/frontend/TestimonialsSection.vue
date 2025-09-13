<template>
  <section class="testimonials-section" :class="classSectionPadding">
    <div class="testimonials-section-bg">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 text-center">
            <div class="section-header text-center">
              <h6 class="heading-mini-title">{{ t('Testimonial') }}</h6>
              <h2 class="section-title">{{ t('What Our Happy User Says') }}</h2>
            </div>
          </div>
        </div>
        
        <div class="row g-4">
          <div class="testimonials-slider-container">
            <!-- Loading State -->
            <div v-if="loading" class="testimonials-loading">
              <div class="loading-spinner"></div>
              <p>{{ t('Loading testimonials...') }}</p>
            </div>
            
            <!-- Error State -->
            <div v-else-if="error" class="testimonials-error">
              <p>{{ error }}</p>
              <button @click="fetchTestimonials" class="btn btn-primary">{{ t('Retry') }}</button>
            </div>
            
            <!-- Testimonials Slider -->
            <div v-else-if="testimonials.length > 0" class="swiper testimonials-swiper">
              <div class="swiper-wrapper testimonials-swiper-wrap">
                <div 
                  v-for="testimonial in testimonials" 
                  :key="testimonial.id"
                  class="swiper-slide"
                >
                  <TestimonialCard :testimonial="testimonial" />
                </div>
              </div>
              <!-- Pagination -->
              <div class="swiper-pagination" v-if="paginationShow"></div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="testimonials-empty">
              <p>{{ t('No testimonials available at the moment.') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted } from 'vue'
import { Swiper } from 'swiper'
import { Navigation, Autoplay, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { useTestimonials } from '@/composables/useTestimonials'
import TestimonialCard from '@/components/TestimonialCard.vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
// Props
const props = defineProps({
  paginationShow: {
    type: Boolean,
    default: true
  },
  classSectionPadding: {
    type: String,
    default: ''
  }
})

// Composables
const { 
  testimonials, 
  loading, 
  error, 
  fetchTestimonials 
} = useTestimonials()

// Initialize testimonials slider
const initTestimonialsSlider = () => {
  if (testimonials.value.length > 0) {
    new Swiper('.testimonials-swiper', {
      modules: [Pagination, Autoplay],
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      breakpoints: {
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
        1366: {
          slidesPerView: 3.5,
          spaceBetween: 30,
        },
        1920: {
          slidesPerView: 4,
          spaceBetween: 30,
        }
      },
      speed: 800,
    })
  }
}

// Lifecycle
onMounted(async () => {
  await fetchTestimonials()
  
  // Initialize slider after data is loaded
  if (testimonials.value.length > 0) {
    // Small delay to ensure DOM is ready
    setTimeout(() => {
      initTestimonialsSlider()
    }, 100)
  }
})
</script>

<style scoped>
.testimonials-loading,
.testimonials-error,
.testimonials-empty {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.testimonials-error button {
  margin-top: 16px;
}

.testimonials-slider-container {
  width: 100%;
  position: relative;
}

/* Ensure proper spacing for swiper slides */
.swiper-slide {
  height: auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .testimonials-loading,
  .testimonials-error,
  .testimonials-empty {
    padding: 20px 10px;
  }
}
</style>