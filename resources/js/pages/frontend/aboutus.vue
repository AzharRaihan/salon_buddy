<script setup>
import AboutSection from '@/components/frontend/AboutSection.vue'
import TeamSection from '@/components/frontend/TeamSection.vue'
import TestimonialsSection from '@/components/frontend/TestimonialsSection.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import BookingSamllBtn from '@/components/frontend/mini-components/BookingSamllBtn.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

const websiteStore = useWebsiteSettingsStore()
const businessHours = computed(() => websiteStore.getBusinessHours)
const aboutUs = ref([])


// Format time from 24h to 12h format
const formatTime = (time24) => {
  if (!time24) return ''
  const [hours, minutes] = time24.split(':')
  const hour = parseInt(hours, 10)
  const period = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour == 0 ? 12 : hour > 12 ? hour - 12 : hour
  return `${hour12}:${minutes} ${period}`
}

const fetchAboutUs = async () => {
  try {
    const response = await $api('/get-about-us')
    aboutUs.value = response.data
  } catch (error) {
    console.error('Error fetching about us:', error)
  }
}


onMounted(async () => { 
    await websiteStore.initializeSettings()
    await fetchAboutUs()
})

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="t('About Us')" :breadcrumb="t('About Us')" />

    <!-- About Us Section -->
    <section class="about-us-section-discover default-section-padding-t">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <div class="row image-row">
              <div class="col-md-6">
                <div class="image-wrapper">
                  <img :src="aboutUs.section_1_image_url" alt="" class="img-fluid">
                </div>
                <div class="discover-image-wrapper">
                  <div class="discover-image-overlay"></div>
                  <div class="discover-image-content">
                    <div class="discover-image-content-inner">
                      <h2>{{ aboutUs.section_1_experience }}+</h2>
                      <p>{{ t('Years of Experience') }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="image-wrapper">
                  <img :src="aboutUs.section_1_image_2_url" alt="" class="img-fluid" >
                </div>
              </div>
            </div>
            
          </div>
          <div class="col-lg-5">
            <div class="discover-content">
              <h2>{{ aboutUs.section_1_heading }}</h2>
              <p>{{ aboutUs.section_1_description }}</p>
              <div class="discover-content-list">
                <ul>
                  <li>
                    <span class="first-el">
                      <VIcon size="24" icon="tabler-clock-24" />
                    </span>
                    <div class="second-el">
                      <p class="title">{{ t('Opening Hours') }}</p>
                      <p class="sub-title">{{ businessHours.openDayStart }} - {{ businessHours.openDayEnd }} : {{ formatTime(businessHours.openTimeStart) }} - {{ formatTime(businessHours.openTimeEnd) }}</p>
                    </div>
                  </li>
                  <li>
                    <span class="first-el">
                      <VIcon size="24" icon="tabler-phone" />
                    </span>
                    <div class="second-el">
                      <p class="title">{{ t('Contact Us') }}</p>
                      <p class="sub-title">{{ websiteStore.getPhone }}</p>
                    </div>
                  </li>
                </ul>
                <BookingSamllBtn :link="aboutUs.section_1_btn_link" :text="t('Discover More')" target="_blank" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <AboutSection :showAboutUs="false" :showUserCounter="true" classBind="" />

    <!-- About Video Section -->
    <div class="about-use-video-section">
      <div class="container">
        <div class="about-use-video" :style="{ backgroundImage: `url(${aboutUs.section_play_image_url})` }">
          <div class="about-use-video-overlay"></div>
          <div class="about-use-video-content">
            <div class="row justify-content-center">
              <div class="col-xl-8 text-center">
                <h1>{{ aboutUs.section_play_title }}</h1>
                <div class="about-use-video-play-btn">
                  <div class="play-container">
                    <div class="circle circle1"></div>
                    <div class="circle circle2"></div>
                    <div class="circle circle3">
                      <a :href="aboutUs.section_play_link" target="_blank">
                        <i class="fas fa-play"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Section -->
    <TeamSection />

    <!-- Testimonials -->
    <TestimonialsSection :paginationShow="true" classSectionPadding="default-section-padding" />


    <!-- About Section -->
    <AboutSection :showAboutUs="true" :showUserCounter="false" :classBind="'default-section-padding'" />
  </div>
</template>

<style scoped>
.about-us-section-discover::before {
  background-image: url('../../@frontend/images/about_us_page_shpage_left.png');
}
/* Right image (bottom-right) */
.about-us-section-discover::after {
  background-image: url('../../@frontend/images/about_us_page_shpage_right.png');
}
</style>