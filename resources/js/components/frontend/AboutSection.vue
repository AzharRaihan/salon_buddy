<template>
  <!-- Loading State -->
  <div v-if="isLoading" class="text-center py-5">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <!-- Error State -->
  <div v-else-if="error" class="alert alert-danger mx-3" role="alert">
    <h4 class="alert-heading">Error Loading Content</h4>
    <p>{{ error.message || 'Something went wrong while loading the about section.' }}</p>
    <button @click="fetchAboutUs" class="btn btn-outline-danger">
      <VIcon icon="tabler-refresh" class="me-1" />
      Retry
    </button>
  </div>

  <!-- About Section -->
  <section 
    v-else-if="showAboutUs" 
    :class="`about-section ${classBind}`" 
    :style="{ backgroundImage: `url(${aboutUs.section_discover_bg_image_url})` }"
  >
    <div ref="aboutSection">
      <transition-group name="fade-up" tag="div" class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-us-with-play">
              <div class="play-video">
                <div class="play-inner">
                  <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-player-play"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4v16a1 1 0 0 0 1.524 .852l13 -8a1 1 0 0 0 0 -1.704l-13 -8a1 1 0 0 0 -1.524 .852z" /></svg>
                </div>
              </div>
              <div class="about-image">
                <img 
                  :src="aboutUs.section_discover_front_image_url" 
                  class="img-fluid" 
                  alt="About Us"
                  loading="lazy"
                >
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="about-content">
              <div class="section-header pb-0">
                <h6 class="heading-mini-title">{{ t('Why Choose Us') }}</h6>
                <h3 class="section-title">{{ aboutUs.section_discover_heading }}</h3>
                <p class="about-description">
                  {{ aboutUs.section_discover_description }}
                </p>
              </div>
              <div class="about-features">
                <div class="feature-item" v-for="i in 3" :key="i">
                  <div class="feature-icon">
                    <img 
                      :src="aboutUs[`section_discover_item_${i}_image_url`]" 
                      :alt="`Feature ${i}`"
                      loading="lazy"
                    >
                  </div>
                  <div class="feature-content">
                    <h5>{{ aboutUs[`section_discover_item_${i}_heading`] }}</h5>
                    <p>{{ aboutUs[`section_discover_item_${i}_description`] }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition-group>
    </div>
  
  </section>

  <!-- Counter Section -->
  <section v-if="showUserCounter && !isLoading && !error" class="about-counter-section">
    <div class="container">
      <div class="about-stats">
        <div class="row">
          <div 
            v-for="(stat, index) in stats" 
            :key="index"
            class="col-6 col-sm-6 col-md-6 col-lg-3 col-xl-3"
          >
            <div class="stat-item">
              <div class="img-area">
                <img :src="stat.image" :alt="stat.label" loading="lazy">
              </div>
              <div class="start-el-wrap">
                <h3 class="stat-number">
                  <CountUp :end-val="stat.value" :duration="2.5" :options="{ suffix: '+' }" />
                </h3>
                <p class="stat-label">{{ stat.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import CountUp from 'vue-countup-v3'
import { useAboutSection } from '@/composables/useAboutSection'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

// Animation
const visible = ref(false)
const aboutSection = ref(null)
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
    observer.observe(aboutSection.value)
  })
// Animation End

// Define props
const props = defineProps({
  showAboutUs: {
    type: Boolean,
    default: false
  },
  showUserCounter: {
    type: Boolean,
    default: false
  },
  classBind: {
    type: String,
    default: ''
  }
})

// Composables
const { aboutUs, stats, isLoading, error, fetchAboutUs } = useAboutSection()

// Lifecycle hooks
onMounted(async () => {
  await fetchAboutUs()
})
</script>

<style scoped>
.about-section {
  background-image: url('../../@pos/assets/images/homepage/why-choos-us-sec-bg.png');
}
</style>