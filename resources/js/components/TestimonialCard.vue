<template>
  <div class="testimonial-card h-100">
    <!-- Rating Section -->
    <div class="testimonial-rating">
      <StarRating :rating="testimonial.rating" />
    </div>
    
    <!-- Review Content -->
    <div class="testimonial-content">
      <p class="testimonial-text">{{ testimonial.review }}</p>
    </div>
    
    <!-- Customer Information -->
    <div class="testimonial-footer">
      <div class="testimonial-header">
        <div class="customer-image">
          <img 
            :src="testimonial.photo_url" 
            :alt="testimonial.name"
            class="img-fluid"
            @error="handleImageError"
          >
        </div>
        <div class="customer-info">
          <h5 class="customer-name">{{ testimonial.name }}</h5>
          <p class="testimonial-date">{{ testimonial.date }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import StarRating from './StarRating.vue'

const props = defineProps({
  testimonial: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.id && value.name && value.rating !== undefined && value.review
    }
  }
})

/**
 * Handle image loading errors by setting a default image
 */
const handleImageError = (event) => {
  event.target.src = '/assets/images/system-config/default-picture.png'
}
</script>

<style scoped>
.testimonial-rating {
  margin-bottom: 16px;
}
.testimonial-date {
  font-size: 11px;
  color: #9ca3af;
  margin: 0;
  line-height: 1.2;
}
</style>
