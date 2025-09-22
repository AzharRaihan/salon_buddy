<script setup>
import { ref, computed, onMounted } from 'vue'
import { useEmployeeRating } from '@/composables/useEmployeeRating'
import { useI18n } from 'vue-i18n'
import defaultAvatar from '../../../../public/assets/images/default-images/avatar.png'

const { t } = useI18n()
const { 
  fetchEmployeeRatings, 
  ratings, 
  ratingSummary, 
  generateStarRating,
  isLoading,
  error
} = useEmployeeRating()

const props = defineProps({
  employeeId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['refresh'])

const currentPage = ref(1)
const perPage = ref(5)
const hasMoreReviews = ref(false)

// Computed properties
const averageRating = computed(() => ratingSummary.value.average_rating || 0)
const totalRatings = computed(() => ratingSummary.value.total_ratings || 0)

// Initialize component
onMounted(async () => {
  await loadReviews()
})

// Load reviews
const loadReviews = async (page = 1) => {
  const result = await fetchEmployeeRatings(props.employeeId, page, perPage.value)
  
  if (result.success) {
    currentPage.value = result.reviews.current_page
    hasMoreReviews.value = result.reviews.current_page < result.reviews.last_page
  }
}

// Refresh reviews (reset to first page)
const refreshReviews = async () => {
  currentPage.value = 1
  await loadReviews(1)
}

// Load more reviews
const loadMore = async () => {
  if (hasMoreReviews.value && !isLoading.value) {
    await loadReviews(currentPage.value + 1)
  }
}

// Generate star display for rating
const getStarDisplay = (rating) => {
  return generateStarRating(rating)
}

// Get star icon class
const getStarClass = (starType) => {
  switch (starType) {
    case 'full':
      return 'text-warning'
    case 'half':
      return 'text-warning'
    case 'empty':
      return 'text-muted'
    default:
      return 'text-muted'
  }
}

// Get star icon
const getStarIcon = (starType) => {
  switch (starType) {
    case 'full':
      return 'tabler-star-filled'
    case 'half':
      return 'tabler-star-half-filled'
    case 'empty':
      return 'tabler-star'
    default:
      return 'tabler-star'
  }
}

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Expose methods for parent component
defineExpose({
  refreshReviews
})
</script>

<template>
  <div class="employee-reviews">
    <!-- Reviews Summary -->
    <div class="reviews-summary mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h4 class="reviews-title">{{ t('Customer Reviews') }}</h4>
          <div class="rating-overview">
            <div class="average-rating">
              <span class="rating-number">{{ averageRating }}</span>
              <div class="stars-display">
                <VIcon 
                  v-for="(starType, index) in getStarDisplay(averageRating)" 
                  :key="index"
                  :class="['star-icon', getStarClass(starType)]"
                  :icon="getStarIcon(starType)"
                  size="16"
                />
              </div>
            </div>
            <p class="rating-count">{{ totalRatings }} {{ t('reviews') }}</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="rating-breakdown">
            <!-- You can add rating breakdown here if needed -->
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && ratings.length === 0" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ t('Loading...') }}</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error && ratings.length === 0" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- No Reviews -->
    <div v-if="!isLoading && ratings.length === 0 && !error" class="no-reviews text-center py-4">
      <VIcon icon="tabler-message-circle" size="48" class="text-muted mb-3" />
      <p class="text-muted">{{ t('No reviews yet') }}</p>
    </div>

    <!-- Reviews List -->
    <div v-if="ratings.length > 0" class="reviews-list">
      <div 
        v-for="review in ratings" 
        :key="review.id" 
        class="review-item"
      >
        <div class="review-header">
          <div class="reviewer-info">
            <div class="reviewer-avatar">
              <img 
                :src="defaultAvatar" 
                :alt="review.customer.name"
                class="avatar-img"
              >
            </div>
            <div class="reviewer-details">
              <h6 class="reviewer-name">{{ review.customer.name }}</h6>
              <div class="review-rating">
                <VIcon 
                  v-for="(starType, index) in getStarDisplay(review.rating)" 
                  :key="index"
                  :class="['star-icon', getStarClass(starType)]"
                  :icon="getStarIcon(starType)"
                  size="14"
                />
              </div>
            </div>
          </div>
          <div class="review-date">
            {{ formatDate(review.date) }}
          </div>
        </div>
        
        <div class="review-content">
          <p class="review-comment">{{ review.comment }}</p>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="hasMoreReviews" class="text-center mt-4">
        <button 
          class="btn btn-outline-primary"
          :disabled="isLoading"
          @click="loadMore"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          {{ isLoading ? t('Loading...') : t('Load More Reviews') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.employee-reviews {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.reviews-title {
  color: #333;
  margin-bottom: 16px;
  font-weight: 600;
}

.rating-overview {
  display: flex;
  align-items: center;
  gap: 16px;
}

.average-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rating-number {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
}

.stars-display {
  display: flex;
  gap: 2px;
}

.star-icon {
  transition: all 0.2s ease;
}

.star-icon.text-warning {
  color: #ffc107 !important;
}

.star-icon.text-muted {
  color: #6c757d !important;
}

.rating-count {
  color: #666;
  margin: 0;
  font-size: 14px;
}

.reviews-list {
  margin-top: 24px;
}

.review-item {
  border-bottom: 1px solid #eee;
  padding: 20px 0;
}

.review-item:last-child {
  border-bottom: none;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.reviewer-details {
  flex: 1;
}

.reviewer-name {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.review-rating {
  display: flex;
  gap: 2px;
}

.review-date {
  color: #666;
  font-size: 12px;
  flex-shrink: 0;
}

.review-content {
  margin-left: 52px;
}

.review-comment {
  margin: 0;
  color: #555;
  line-height: 1.6;
  font-size: 14px;
}

.no-reviews {
  padding: 40px 20px;
}

.btn-outline-primary {
  border-color: var(--primary-bg-color);
  color: var(--primary-bg-color);
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover:not(:disabled) {
  background-color: var(--primary-bg-color);
  color: white;
}

.btn-outline-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .employee-reviews {
    padding: 16px;
  }
  
  .rating-overview {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .review-header {
    flex-direction: column;
    gap: 8px;
  }
  
  .review-content {
    margin-left: 0;
  }
  
  .reviewer-info {
    width: 100%;
  }
  
  .review-date {
    align-self: flex-start;
  }
}
</style>
