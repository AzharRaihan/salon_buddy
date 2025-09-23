<script setup>
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import BookingSamllBtn2 from '@/components/frontend/mini-components/BookingSamllBtn2.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { ref, onMounted, computed } from 'vue'
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

const websiteStore = useWebsiteSettingsStore()

// Form data
const name = ref('')
const email = ref('')
const subject = ref('')
const message = ref('')
const branches = ref([])
const loading = ref(false)
const errors = ref({})

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
  const hour12 = hour == 0 ? 12 : hour > 12 ? hour - 12 : hour
  return `${hour12}:${minutes} ${period}`
}

// Fetch branches
const fetchBranches = async () => {
  try {
    const response = await $api('/get-all-branches')
    branches.value = response.data
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}

// Form validation
const validateForm = () => {
  const newErrors = {}
  
  if (!name.value.trim()) {
    newErrors.name = ['Name is required']
  }
  
  if (!email.value.trim()) {
    newErrors.email = ['Email is required']
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    newErrors.email = ['Please enter a valid email address']
  }
  
  if (!subject.value.trim()) {
    newErrors.subject = ['Subject is required']
  }
  
  if (!message.value.trim()) {
    newErrors.message = ['Message is required']
  }

  errors.value = newErrors
  return Object.keys(newErrors).length == 0
}

// Submit contact form
const submitForm = async () => {
  if (!validateForm()) {
    return
  }

  loading.value = true
  errors.value = {}
  
  try {
    const response = await $api('/send-contact-us-message', {
      method: 'POST',
      body: {
        name: name.value,
        email: email.value,
        subject: subject.value,
        message: message.value
      }
    })

    // Clear form on success
    name.value = ''
    email.value = ''
    subject.value = ''
    message.value = ''

    // Show success message
    toast('Message sent successfully!', {
      type: 'success',
    })

  } catch (error) {
    if (error.response?.data?.errors) {
      toast(error.response.data.message, {
        type: 'error',
      })
      errors.value = error.response.data.errors
    } else {
      toast('Something went wrong. Please try again.', {
        type: 'error',
      })
    }
  } finally {
    loading.value = false
  }
}

// Initialize website settings and fetch branches on component mount
onMounted(async () => {
  await websiteStore.initializeSettings()
  await fetchBranches()
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
    <CommonPageBanner :title="t('Contact Us')" :breadcrumb="t('Contact Us')" />
    <section class="contact-us-wrapper default-section-padding">
      <div class="container">
        <div class="row">
          <div class="col-md-12 col-lg-6">
            <div class="contact-us-content">
              <h3>{{ t("Let's get in touch with us.") }}</h3>
              <p>{{ t("We're happy to help! Fill out our form in the next step to get started.") }}</p>
              <div class="contact-us-form">
                <form @submit.prevent="submitForm">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <input type="text" class="form-control" id="name" :placeholder="t('Enter your name *')" v-model="name"
                          :class="{ 'is-invalid': errors.name }">
                        <div class="invalid-feedback" v-if="errors.name">{{ errors.name[0] }}</div>
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <input type="email" class="form-control" id="email" :placeholder="t('Enter your email *')" v-model="email"
                          :class="{ 'is-invalid': errors.email }">
                        <div class="invalid-feedback" v-if="errors.email">{{ errors.email[0] }}</div>
                      </div>
                    </div>
                    <div class="col-md-12 mb-3">
                      <div class="form-group">
                        <input type="text" class="form-control" id="subject" :placeholder="t('Enter your subject *')" v-model="subject"
                          :class="{ 'is-invalid': errors.subject }">
                        <div class="invalid-feedback" v-if="errors.subject">{{ errors.subject[0] }}</div>
                      </div>
                    </div>
                    <div class="col-md-12 mb-3">
                      <div class="form-group">
                        <textarea class="form-control" id="message" :placeholder="t('Enter your message *')" rows="4" v-model="message"
                          :class="{ 'is-invalid': errors.message }"></textarea>
                        <div class="invalid-feedback" v-if="errors.message">{{ errors.message[0] }}</div>
                      </div>
                    </div>
                  </div>
                  
                  <BookingSamllBtn2 type="submit"  :text="loading ? t('Sending...') : t('Send Message')" :disabled="loading" />
                </form>
              </div>
            </div>
          </div>
          <div class="col-md-12 col-lg-6">
            <div class="contact-address-part">
              <div class="contact-us-info address-info">
                <h3>{{ t('Address') }}</h3>
                <p>{{ websiteStore.getAddress }}</p>
              </div>
              <div class="contact-us-info contact-info">
                <h3>{{ t('Contact') }}</h3>
                <p>{{ t('Phone') }} : {{ websiteStore.getPhone }}</p>
                <p>{{ t('Email') }} : {{ websiteStore.getEmail }}</p>
              </div>
              <div class="contact-us-info open-close-info">
                <h3>{{ t('Open Time') }}</h3>
                <p>{{ businessHours.openDayStart }} - {{ businessHours.openDayEnd }} : {{ formatTime(businessHours.openTimeStart) }} - {{ formatTime(businessHours.openTimeEnd) }}</p>
              </div>
              <div class="social-icon-wrapper">
                <a v-for="social in activeSocialMedia" :key="social.id" :href="social.url" class="social-icon">
                  <VIcon :icon="getSocialIcon(social.name)" />
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="row store-card-wrapper">
          <div class="col-md-4" v-for="branch in branches" :key="branch.id">
            <div class="store-card">
              <div class="store-card-image">
                <img :src="branch.photo_url" alt="branch-image">
              </div>
              <div class="store-card-content">
                <p><VIcon icon="tabler-building-warehouse" /> {{ branch.branch_name }}</p>
                <p><VIcon icon="tabler-phone" />{{ branch.phone }}</p>
                <p><VIcon icon="tabler-clock" />{{ branch.open_day_start }} - {{ branch.open_day_end }} : {{ formatTime(branch.open_day_start_time) }} - {{ formatTime(branch.open_day_end_time) }}</p>
                <p><VIcon icon="tabler-map-pin" />{{ branch.address }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row" v-if="websiteStore.getGoogleMapUrl">
          <div class="col-12">
            <div class="google-map">
              <iframe :src="websiteStore.getGoogleMapUrl" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
