<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useEmployeeRating } from '@/composables/useEmployeeRating'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import LoginModal from '@/components/auth/LoginModal.vue'
import { useI18n } from 'vue-i18n'
import { toast } from 'vue3-toastify';
import { useRoute } from 'vue-router'
const route = useRoute()



const { t } = useI18n()
const { isCustomerAuthenticated } = useCustomerAuth()
const { 
  submitRating, 
  checkRatingEligibility, 
  canRate, 
  ratingEligibility, 
  getEligibilityMessage, 
  getEligibilityMessageType,
  isLoading,
  error,
  clearError
} = useEmployeeRating()

const props = defineProps({
  employeeId: {
    type: [String, Number],
    required: true
  },
  employeeName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['rating-submitted', 'login-required', 'refresh-reviews'])

// Form data
const form = reactive({
  rating: 0,
  comment: ''
})

const showLoginModal = ref(false)
const message = ref('')
const messageType = ref('')
const hoveredRating = ref(0)

// Validation
const isFormValid = computed(() => {
  return form.rating > 0 && form.comment.trim().length > 0
})

// Star rating display
const displayRating = computed(() => {
  return hoveredRating.value || form.rating
})

// Initialize component
onMounted(async () => {
  await checkRatingEligibility(props.employeeId)
})

// Handle star click
const handleStarClick = (rating) => {
  if (!isCustomerAuthenticated.value) {
    // Please login first
    toast(t('Please login first'), { type: 'info' })
    // showLoginModal.value = true
    return
  }
  
  if (!canRate.value) {
    toast(t('You can only rate employees who have provided you service'), { type: 'error' })
    return
  }
  
  form.rating = rating
  clearError()
}

// Handle star hover
const handleStarHover = (rating) => {
  hoveredRating.value = rating
}

// Handle star leave
const handleStarLeave = () => {
  hoveredRating.value = 0
}

// Submit rating
const handleSubmit = async () => {
  if (!isFormValid.value) {
    toast(t('Please provide both rating and comment'), { type: 'error' })
    return
  }

  clearError()
  message.value = ''

  const result = await submitRating(props.employeeId, {
    employee_id: props.employeeId,
    rating: form.rating,
    comment: form.comment.trim()
  })

  if (result.success) {
    toast(t('Rating submitted successfully!'), { type: 'success' })
    
    // Reset form
    form.rating = 0
    form.comment = ''
    
    // Emit success event
    emit('rating-submitted', result.data)
    
    // Emit refresh event to update reviews list
    emit('refresh-reviews')
    
    // Clear local message
    message.value = ''
    messageType.value = ''
  } else {
    message.value = result.message || t('Failed to submit rating')
    messageType.value = 'error'
  }
}

// Handle login modal close
const handleLoginClose = () => {
  showLoginModal.value = false
}

// Handle login success
const handleLoginSuccess = () => {
  showLoginModal.value = false
  // Recheck eligibility after login
  checkRatingEligibility(props.employeeId)
}

// Handle login modal social login
const handleSocialLogin = (provider) => {
  emit('login-required', provider)
}

// Get star icon class
const getStarClass = (index) => {
  const rating = displayRating.value
  if (index <= rating) {
    return 'text-warning'
  }
  return 'text-muted'
}

// Get eligibility message
const eligibilityMessage = computed(() => {
  return getEligibilityMessage(ratingEligibility.value)
})

// Get eligibility message type
const eligibilityMessageType = computed(() => {
  return getEligibilityMessageType(ratingEligibility.value)
})
</script>

<template>
  <div class="employee-rating-form">
    <!-- Login Modal -->
    <LoginModal 
      :is-visible="showLoginModal"
      :return-url="$route.fullPath"
      @close="handleLoginClose"
      @success="handleLoginSuccess"
      @social-login="handleSocialLogin"
    />

    <!-- <LoginModal 
      :is-visible="showLoginModal"
      :return-url="$route.fullPath"
      @close="handleLoginModalClose"
      @success="handleLoginSuccess"
      @social-login="handleSocialLoginRedirect"
    /> -->

    <!-- Rating Form -->
    <div class="rating-form-container">
      <h4 class="rating-form-title">{{ t('Rate') }} {{ employeeName }}</h4>
      
      <!-- Eligibility Message -->
      <div 
        v-if="ratingEligibility && !canRate" 
        :class="['alert', `alert-${eligibilityMessageType}`]"
        class="mb-3"
      >
        {{ eligibilityMessage }}
      </div>

      <!-- Success/Error Messages -->
      <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
        {{ message }}
      </div>

      <!-- Rating Stars -->
      <div class="rating-stars mb-3">
        <label class="form-label">{{ t('Rating') }} <span class="text-danger">*</span></label>
        <div class="stars-container">
          <VIcon 
            v-for="index in 5" 
            :key="index"
            :class="['star-icon', getStarClass(index)]"
            icon="tabler-star-filled"
            size="24"
            @click="handleStarClick(index)"
            @mouseenter="handleStarHover(index)"
            @mouseleave="handleStarLeave"
            :style="{ cursor: canRate ? 'pointer' : 'default' }"
          />
        </div>
        <small class="text-muted">{{ t('Click to rate') }}</small>
      </div>

      <!-- Comment -->
      <div class="form-group mb-3">
        <label for="rating-comment" class="form-label">
          {{ t('Review') }} <span class="text-danger">*</span>
        </label>
        <textarea 
          v-model="form.comment"
          id="rating-comment"
          class="form-control"
          :class="{ 'is-invalid': error }"
          :disabled="!canRate"
          :placeholder="t('Share your experience with this employee...')"
          rows="4"
          maxlength="1000"
        ></textarea>
        <div class="form-text">{{ form.comment.length }}/1000 {{ t('characters') }}</div>
        <div v-if="error" class="invalid-feedback d-block">
          {{ error }}
        </div>
      </div>

      <!-- Submit Button -->
      <div class="form-group">
        <button 
          type="button"
          class="btn btn-primary w-100"
          :disabled="!isFormValid || isLoading || !canRate"
          @click="handleSubmit"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          {{ isLoading ? t('Submitting...') : t('Submit Rating') }}
        </button>
      </div>

      <!-- Login Prompt for Unauthenticated Users -->
      <div v-if="!isCustomerAuthenticated" class="text-center mt-3">
        <p class="text-muted">
          {{ t('Please') }} 
          <RouterLink to="/customer-panel/login" class="text-primary">
            {{ t('login') }}
          </RouterLink> 
          {{ t('to submit a rating') }}
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.employee-rating-form {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.rating-form-container {
  max-width: 100%;
}

.rating-form-title {
  color: #333;
  margin-bottom: 20px;
  font-weight: 600;
}

.rating-stars {
  text-align: center;
}

.stars-container {
  display: flex;
  justify-content: center;
  gap: 4px;
  margin-bottom: 8px;
}

.star-icon {
  transition: all 0.2s ease;
  cursor: pointer;
}

.star-icon:hover {
  transform: scale(1.1);
}

.star-icon.text-warning {
  color: #ffc107 !important;
}

.star-icon.text-muted {
  color: #6c757d !important;
}

.form-control {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-bg-color);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-control:disabled {
  background-color: #f8f9fa;
  opacity: 0.6;
}

.btn-primary {
  background-color: var(--primary-bg-color);
  border-color: var(--primary-bg-color);
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background-color: var(--primary-bg-color);
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.alert {
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 1rem;
}

.alert-success {
  background-color: #d1e7dd;
  border-color: #badbcc;
  color: #0f5132;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c2c7;
  color: #842029;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffecb5;
  color: #664d03;
}

.alert-info {
  background-color: #d1ecf1;
  border-color: #bee5eb;
  color: #055160;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.text-primary {
  color: var(--primary-bg-color) !important;
  text-decoration: none;
  font-weight: 500;
}

.text-primary:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .employee-rating-form {
    padding: 16px;
  }
  
  .rating-form-title {
    font-size: 1.1rem;
  }
  
  .stars-container {
    gap: 2px;
  }
  
  .star-icon {
    font-size: 20px;
  }
}
</style>
