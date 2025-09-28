<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { $api } from '@/utils/api'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

const router = useRouter()
const route = useRoute()
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Form data
const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

// Errors
const errors = ref({})

// Success/Error messages
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Validation
const isFormValid = computed(() => {
  return form.email && 
         form.password && 
         form.password_confirmation && 
         form.password == form.password_confirmation &&
         form.token
})

// Clear errors when user starts typing
const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field]
  }
}

// Submit password reset
const submitPasswordReset = async () => {
  if (!isFormValid.value) {
    message.value = 'Please fill all required fields correctly.'
    messageType.value = 'error'
    return
  }

  isLoading.value = true
  errors.value = {}
  message.value = ''

  try {
    const response = await $api('/reset-password', {
      method: 'POST',
      body: form
    })

    if (response.success) {
      message.value = response.message || 'Password has been reset successfully!'
      messageType.value = 'success'
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        router.push('/login')
      }, 3000)
    } else {
      message.value = response.message || 'Failed to reset password.'
      messageType.value = 'error'
      
      if (response.errors) {
        errors.value = response.errors
      }
    }
  } catch (error) {
    console.error('Password reset error:', error)
    
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

// Extract token and email from URL parameters
const extractParams = () => {
  const urlParams = new URLSearchParams(window.location.search)
  const token = urlParams.get('token')
  const email = urlParams.get('email')

  if (token) {
    form.token = token
  }
  
  if (email) {
    form.email = decodeURIComponent(email)
  }

  // If no token, redirect to forgot password
  if (!token) {
    message.value = 'Invalid or missing reset token. Please request a new password reset link.'
    messageType.value = 'error'
    
    setTimeout(() => {
      router.push('/frontend/forgot-password')
    }, 3000)
  }
}

onMounted(() => {
  extractParams()
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
    <CommonPageBanner title="Reset Password" breadcrumb="Reset Password" />

    <!-- Reset Password Section -->
    <section class="login-wrapper default-section-padding-t">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-7 col-lg-6">
            <div class="login-form forgot-password-wrapper">
              <h3 class="text-center login-title">Reset Password</h3>
              <p class="text-center login-title-subtitle">
                Enter your new password below to complete the reset process
              </p>

              <!-- Success/Error Messages -->
              <div v-if="message" :class="['alert', messageType == 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
                {{ message }}
              </div>

              <form @submit.prevent="submitPasswordReset" v-if="form.token">
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
                      readonly
                    >
                  </div>
                  <div v-if="errors.email" class="invalid-feedback d-block">
                    {{ errors.email[0] }}
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="new-password">New Password <span class="text-danger">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-lock" size="18" />
                    <input
                      v-model="form.password"
                      @input="clearError('password')"
                      :type="showPassword ? 'text' : 'password'"
                      class="form-control"
                      :class="{ 'is-invalid': errors.password }"
                      id="new-password"
                      placeholder="Enter your new password"
                      autocomplete="new-password"
                      required
                    >
                    <VIcon 
                      v-if="showPassword" 
                      class="eye-icon" 
                      icon="tabler-eye" 
                      size="18" 
                      @click="showPassword = !showPassword" 
                    />
                    <VIcon 
                      v-else 
                      class="eye-icon" 
                      icon="tabler-eye-off" 
                      size="18" 
                      @click="showPassword = !showPassword" 
                    />
                  </div>
                  <div v-if="errors.password" class="invalid-feedback d-block">
                    {{ errors.password[0] }}
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="confirm-password">Confirm Password <span class="text-danger">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-lock" size="18" />
                    <input
                      v-model="form.password_confirmation"
                      @input="clearError('password_confirmation')"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      class="form-control"
                      :class="{ 'is-invalid': errors.password_confirmation || (form.password_confirmation && form.password !== form.password_confirmation) }"
                      id="confirm-password"
                      placeholder="Confirm your new password"
                      autocomplete="new-password"
                      required
                    >
                    <VIcon 
                      v-if="showConfirmPassword" 
                      class="eye-icon" 
                      icon="tabler-eye" 
                      size="18" 
                      @click="showConfirmPassword = !showConfirmPassword" 
                    />
                    <VIcon 
                      v-else 
                      class="eye-icon" 
                      icon="tabler-eye-off" 
                      size="18" 
                      @click="showConfirmPassword = !showConfirmPassword" 
                    />
                  </div>
                  <div v-if="errors.password_confirmation" class="invalid-feedback d-block">
                    {{ errors.password_confirmation[0] }}
                  </div>
                  <div v-else-if="form.password_confirmation && form.password !== form.password_confirmation" class="invalid-feedback d-block">
                    Passwords do not match
                  </div>
                </div>

                <div class="form-group">
                  <button 
                    type="submit"
                    class="btn btn-login-lg w-100" 
                    :disabled="!isFormValid || isLoading"
                  >
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ isLoading ? 'Resetting...' : 'Reset Password' }}
                  </button>
                </div>
              </form>

              <div class="text-center pt-3">
                <p>Remember your password?
                  <RouterLink to="/login">Login</RouterLink>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
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

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.eye-icon {
  cursor: pointer;
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
}

.input-icon {
  position: relative;
}

.input-icon .primary-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
}

.input-icon input {
  padding-left: 40px;
  padding-right: 40px;
}

.input-icon input[readonly] {
  background-color: #f8f9fa;
  border-color: #dee2e6;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.forgot-password-wrapper {
  max-width: 500px;
  margin: 0 auto;
}
</style>
