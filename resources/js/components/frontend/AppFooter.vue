<template>
  <footer class="app-footer">
    <!-- Main Footer -->
    <section class="main-footer">
      <div class="container">
        <!-- Footer Special Section -->
        <div class="footer-special-section">
          <div class="special-inner">
            <p>{{ websiteStore.getTestimonialData.title }}</p>
            <h2>{{ websiteStore.getTestimonialData.heading }}</h2>
            <BookingSamllBtn :link="'/appointment-service'" :text="t('Book Appointment')" />
          </div>
        </div>
        <div class="footer-row">
          <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6">
              <div class="footer-widget">
                <div class="footer-logo">
                  <img :src="websiteStore.getFooterLogo" alt="logo" class="logo-img">
                </div>
                <p class="footer-description">
                  {{ websiteStore.getFooterDescription }}
                </p>
                <div class="social-links mt-4" v-if="activeSocialMedia.length > 0">
                  <div class="social-icons">
                    <template v-for="social in activeSocialMedia" :key="social.name">
                      <a v-if="social.url" :href="social.url" target="_blank" class="social-link">
                        <VIcon size="22" :icon="getSocialIcon(social.name)" />
                      </a>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
              <div class="footer-widget">
                <h5 class="widget-title">{{ t('Pages') }}</h5>
                <ul class="footer-links">
                  <li><RouterLink to="/aboutus">{{ t('About Us') }}</RouterLink></li>
                  <li><RouterLink to="/gallery">{{ t('Gallery') }}</RouterLink></li>
                  <li><RouterLink to="/team-members">{{ t('Team Membar') }}</RouterLink></li>
                </ul>
              </div>
            </div>

            <!-- Services -->
            <div class="col-lg-2 col-md-6">
              <div class="footer-widget">
                <h5 class="widget-title">{{ t('Links') }}</h5>
                <ul class="footer-links">
                  <li><RouterLink to="/contact-us">{{ t('Contact Us') }}</RouterLink></li>
                  <li><RouterLink to="/faq">{{ t('Help & FAQ') }}</RouterLink></li>
                </ul>
              </div>
            </div>

            <!-- Business Hours -->
            <div class="col-lg-4 col-md-6">
              <div class="footer-widget">
                <h5 class="widget-title">{{ t('Information') }}</h5>
                <div class="business-hours">
                  <div class="hours-item">
                    <span class="day">{{ businessHours.openDayStart }} - {{ businessHours.openDayEnd }}</span>
                    <span class="time">{{ formatTime(businessHours.openTimeStart) }} - {{ formatTime(businessHours.openTimeEnd) }}</span>
                  </div>
                </div>
                <div class="contact-info">
                  <div class="contact-item">
                    <VIcon  size="22" icon="tabler-map-pin" />
                    <span>{{ websiteStore.getAddress }}</span>
                  </div>
                  <div class="contact-item">
                    <VIcon  size="22" icon="tabler-phone" />
                    <span>{{ websiteStore.getPhone }}</span>
                  </div>
                  <div class="contact-item">
                    <VIcon  size="22" icon="tabler-mail" />
                    <span>{{ websiteStore.getEmail }}</span>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer Bottom -->
    <section class="footer-bottom">
      <div class="container">
        <div class="row align-items-center bottom-footer">
          <div class="col-sm-12 12">
            <p class="copyright">
              {{ websiteStore.getFooterCopyright }}
            </p>
          </div>
        </div>
      </div>
    </section>
  </footer>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import BookAppointmentBtn from './mini-components/BookAppointmentBtn.vue'
import { useI18n } from 'vue-i18n';
import BookingSamllBtn from './mini-components/BookingSamllBtn.vue'
const { t } = useI18n()
const websiteStore = useWebsiteSettingsStore()
// Business hours from website settings
const businessHours = computed(() => websiteStore.getBusinessHours)
// Active social media links
const activeSocialMedia = computed(() => {
  return websiteStore.getSocialMedia.filter(social => social.is_active && social.url)
})

// Social media icon mapping
const getSocialIcon = (socialName) => {
  const iconMap = {
    'Facebook': 'tabler-brand-facebook',
    'Twitter': 'tabler-brand-twitter', 
    'Instagram': 'tabler-brand-instagram',
    'YouTube': 'tabler-brand-youtube',
    'TikTok': 'tabler-brand-tiktok',
  }
  return iconMap[socialName] || 'tabler-link'
}
// Format time from 24h to 12h format
const formatTime = (time24) => {
  if (!time24) return ''
  const [hours, minutes] = time24.split(':')
  const hour = parseInt(hours, 10)
  const period = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour
  return `${hour12}:${minutes} ${period}`
}
// Initialize website settings on component mount
onMounted(async () => {
  await websiteStore.initializeSettings()
})
</script>

<style scoped>
.app-footer {
  background-image: url('../../../../public/assets/images/default-images/footer.png');
}
</style>