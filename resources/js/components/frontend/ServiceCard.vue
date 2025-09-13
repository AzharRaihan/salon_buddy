<template>
  <div class="service-card-inner h-100">
    <div class="service-content">
      <img 
        :src="service.image" 
        class="img-fluid service-image"
        :alt="service.name"
        @error="handleImageError"
      >
      <h4 class="service-name" v-tooltip="service.name">
        {{ service.name }}
      </h4>
      <p class="service-info" v-if="parseFloat(service.duration) > 0">
        <span class="duration">{{ t('Duration') }}: {{ parseFloat(service.duration) > 1 ? service.duration + ' ' + service.duration_type + 's' : service.duration + ' ' + service.duration_type }}</span>
      </p>
      <p class="service-info">
        <span class="duration">{{ formatAmount(service.price) }}</span>
      </p>
      <div class="service-footer d-flex justify-content-between align-items-center">
        <BookNowBtn 
          :link="bookingLink" 
          :text="bookingText"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import BookNowBtn from './mini-components/BookNowBtn.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';


const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()
defineProps({
  service: {
    type: Object,
    required: true,
    validator: (service) => {
      return service && 
        typeof service.id !== 'undefined' &&
        typeof service.duration !== 'undefined' &&
        typeof service.duration_type !== 'undefined' &&
        typeof service.name === 'string' &&
        typeof service.price !== 'undefined'
    }
  },
  bookingLink: {
    type: String,
    default: '#'
  },
  bookingText: {
    type: String,
    default: 'Book Now'
  }
})
// Emits
const emit = defineEmits(['book-service', 'image-error'])

const handleImageError = (event) => {
  // Set fallback image
  event.target.src = '/assets/images/system-config/default-picture.png'
  emit('image-error', props.service)
}

</script>