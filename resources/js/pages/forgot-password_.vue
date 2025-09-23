<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { $api } from '@/utils/api'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

const router = useRouter()
const isLoading = ref(false)

// Form data
const form = reactive({
  email: ''
})

// Errors
const errors = ref({})

// Success/Error messages
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Validation
const isFormValid = computed(() => {
  return form.email && form.email.includes('@')
})

// Clear errors when user starts typing
const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field]
  }
}

// Submit forgot password
const submitForgotPassword = async () => {
  if (!isFormValid.value) {
    message.value = 'Please enter a valid email address.'
    messageType.value = 'error'
    return
  }

  isLoading.value = true
  errors.value = {}
  message.value = ''

  try {
    const response = await $api('/customer/forgot-password', {
      method: 'POST',
      body: form
    })

    if (response.success) {
      message.value = response.message || 'Password reset link sent to your email address.'
      messageType.value = 'success'
      
      // Clear form
      form.email = ''
    } else {
      message.value = response.message || 'Failed to send password reset link.'
      messageType.value = 'error'
      
      if (response.errors) {
        errors.value = response.errors
      }
    }
  } catch (error) {
    console.error('Forgot password error:', error)
    
    if (error.data && error.data.errors) {
      errors.value = error.data.errors
      message.value = error.data.message || 'Validation failed'
    } else {
      message.value = error.data?.message || 'Something went wrong. Please try again.'
    }
    messageType.value = 'error'
  } finally {
    isLoading.value = false
  }
}

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
    <CommonPageBanner title="Forgot Password" breadcrumb="Forgot Password" />

    <!-- Forgot Password Section -->
    <section class="login-wrapper default-section-padding-t">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-7 col-lg-6">
            <div class="login-form forgot-password-wrapper">
              <h3 class="text-center login-title">Forgot Your Password</h3>
              <p class="text-center login-title-subtitle">
                Don't worry, happens to all of us. Enter your email below to recover your password
              </p>

              <!-- Success/Error Messages -->
              <div v-if="message" :class="['alert', messageType == 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
                {{ message }}
              </div>

              <form @submit.prevent="submitForgotPassword">
                <div class="form-group mb-3">
                  <label for="email">Email <span class="text-danger">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-mail" size="18" />
                    <input
                      v-model="form.email"
                      @input="clearError('email')"
                      type="email"
                      class="form-control"
                      :class="{ 'is-invalid': errors.email }"
                      id="email"
                      placeholder="Enter your email"
                      autocomplete="email"
                      required
                    >
                  </div>
                  <div v-if="errors.email" class="invalid-feedback d-block">
                    {{ errors.email[0] }}
                  </div>
                </div>

                <div class="form-group">
                  <button 
                    type="submit"
                    class="btn w-100 login-btn"
                  >
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ isLoading ? 'Sending...' : 'Reset Password' }}
                  </button>
                </div>
              </form>

              <div class="text-center pt-3">
                <p>Remember your password?
                  <RouterLink to="/login_">Login</RouterLink>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
