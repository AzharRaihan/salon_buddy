<template>
  <div class="star-rating">
    <div class="stars">
      <div 
        v-for="star in starDisplay" 
        :key="star.index"
        class="star-container"
      >
        <!-- Background star (empty) -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round" 
          stroke-linejoin="round"
          class="star star-outline"
        >
          <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
        </svg>
        
        <!-- Foreground star (filled) with clip path for partial fill -->
        <svg
          v-if="star.fillPercentage > 0"
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="currentColor"
          stroke="none"
          class="star star-filled"
          :style="{ 
            clipPath: star.fillPercentage === 100 
              ? 'none' 
              : `inset(0 ${100 - star.fillPercentage}% 0 0)` 
          }"
        >
          <path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" />
        </svg>
      </div>
    </div>
    
    <!-- Rating text (optional) -->
    <span v-if="showRatingText" class="rating-text">{{ rating }}/5</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  rating: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 5
  },
  showRatingText: {
    type: Boolean,
    default: true
  }
})

/**
 * Compute star display data based on rating
 */
const starDisplay = computed(() => {
  const stars = []
  const fullStars = Math.floor(props.rating)
  const hasHalfStar = props.rating % 1 !== 0
  
  for (let i = 1; i <= 5; i++) {
    if (i <= fullStars) {
      stars.push({ index: i, fillPercentage: 100, isFilled: true })
    } else if (i === fullStars + 1 && hasHalfStar) {
      const halfStarPercentage = Math.round((props.rating % 1) * 100)
      stars.push({ index: i, fillPercentage: halfStarPercentage, isFilled: false })
    } else {
      stars.push({ index: i, fillPercentage: 0, isFilled: false })
    }
  }
  
  return stars
})
</script>

<style scoped>
.star-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}
.stars {
  display: flex;
  align-items: center;
  gap: 2px;
}
.star-container {
  position: relative;
  display: inline-block;
}
.star {
  display: block;
}
.star-outline {
  color: #d1d5db; /* Gray color for empty stars */
}
.star-filled {
  position: absolute;
  top: 0;
  left: 0;
  color: #fbbf24; /* Yellow color for filled stars */
  z-index: 1;
}
.rating-text {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}
</style>
