<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import { $api } from '@/utils/api'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  },
  returnUrl: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'success', 'social-login'])

const { customerLogin, customerSocialLogin, isLoading, error, handleCustomerSocialCallback } = useCustomerAuth()

// Use auth state for updates
const { updateCustomerAuthState } = useAuthState()

const showPassword = ref(false)
const message = ref('')
const messageType = ref('')
const errors = ref({})

// Form data
const form = reactive({
  email: '',
  password: ''
})

// Social auth URLs
const socialAuthUrls = ref({})

// Validation
const isFormValid = computed(() => {
  return form.email && form.password
})

// Clear errors when user starts typing
const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field]
  }
  message.value = ''
  messageType.value = ''
}

// Submit login
const submitLogin = async () => {
  if (!isFormValid.value) {
    message.value = 'Please fill all required fields.'
    messageType.value = 'error'
    return
  }

  errors.value = {}
  message.value = ''

  const result = await customerLogin(form)

  if (result.success) {
    // Update auth state after successful login
    updateCustomerAuthState()
    
    message.value = 'Login successful!'
    messageType.value = 'success'
    
    // Emit success event to parent
    closeModal()
    
    // Close modal after short delay
    setTimeout(() => {
      window.location.reload()
    }, 100)

    emit('success', result.data.customer)


  } else {
    message.value = result.message || 'Login failed'
    messageType.value = 'error'
    
    // Handle validation errors if any
    if (result.errors) {
      errors.value = result.errors
    }
  }
}

// Handle social login
const handleSocialLogin = async (provider) => {
  // Emit social login event to parent for handling
  emit('social-login', provider)
  
  const success = await customerSocialLogin(provider, props.returnUrl)
  if (!success) {
    message.value = 'Social login setup failed'
    messageType.value = 'error'
  }
}



// Get social auth URLs
const getSocialAuthUrls = async () => {
  try {
    const response = await $api('/customer/social-auth-urls')
    if (response.success) {
      socialAuthUrls.value = response.data
    }
  } catch (error) {
    console.error('Failed to get social auth URLs:', error)
  }
}

// Close modal
const closeModal = () => {
  // Reset form
  form.email = ''
  form.password = ''
  errors.value = {}
  message.value = ''
  messageType.value = ''
  
  emit('close')
}

// Handle clicks outside modal
const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    closeModal()
  }
}

// Handle social login callback
const handleCallback = () => {
  const result = handleCustomerSocialCallback()

  if (result.success) {
    // Update auth state after successful social login
    updateCustomerAuthState()
    
    message.value = 'Social login successful!'
    messageType.value = 'success'

    // Close modal after short delay
    setTimeout(() => {
      closeModal()
      window.location.reload()
    }, 800)
  } else if (result.message) {
    message.value = result.message
    messageType.value = 'error'
  }
}

onMounted(() => {
  getSocialAuthUrls()
  handleCallback()
})
</script>

<template>
  <div v-if="isVisible" class="login-modal-overlay">
    <div class="login-modal-container">
      <div class="login-modal-header">
        <h3>Login to Continue</h3>
        <button class="close-btn" @click="closeModal">
          <VIcon icon="tabler-x" size="20" />
        </button>
      </div>
      
      <div class="login-modal-body">
        <p class="login-subtitle">Please login to continue</p>

        <!-- Success/Error Messages -->
        <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
          {{ message }}
        </div>

        <form @submit.prevent="submitLogin">
          <div class="form-group mb-3">
            <label for="modal-email">Email <span class="text-danger">*</span></label>
            <div class="input-icon">
              <VIcon class="primary-icon" icon="tabler-mail" size="18" />
              <input 
                v-model="form.email"
                @input="clearError('email')"
                type="email" 
                class="form-control" 
                :class="{ 'is-invalid': errors.email }"
                id="modal-email" 
                placeholder="Enter your email" 
                autocomplete="email"
                required
              >
            </div>
            <div v-if="errors.email" class="invalid-feedback d-block">
              {{ errors.email[0] }}
            </div>
          </div>

          <div class="form-group mb-3">
            <label for="modal-password">Password <span class="text-danger">*</span></label>
            <div class="input-icon">
              <VIcon class="primary-icon" icon="tabler-lock" size="18" />
              <input 
                v-model="form.password"
                @input="clearError('password')"
                :type="showPassword ? 'text' : 'password'" 
                class="form-control" 
                :class="{ 'is-invalid': errors.password }"
                id="modal-password" 
                placeholder="Enter your password" 
                autocomplete="current-password"
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

          <div class="form-group">
            <button 
              type="submit"
              class="btn btn-login-lg w-100" 
              :disabled="!isFormValid || isLoading"
            >
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isLoading ? 'Logging in...' : 'Login' }}
            </button>
          </div>
        </form>

        <div class="divider">
          <span class="txt">Or login with</span>
        </div>

        <div class="login-socialite">
          <ul>
            <li>
              <a href="javascript:void(0)" @click="handleSocialLogin('google')">
                <img src="../../@frontend/images/socialite/Google.png" alt="Google" />
                <span>Sign in with Google</span>
              </a>
            </li>
            <li>
              <a href="javascript:void(0)" @click="handleSocialLogin('facebook')">
                <img src="../../@frontend/images/socialite/FB.png" alt="Facebook" />
                <span>Sign in with Facebook</span>
              </a>
            </li>
            <!-- <li>
              <a href="javascript:void(0)" @click="handleSocialLogin('github')">
                <img src="../../@frontend/images/socialite/Git.png" alt="GitHub" />
                <span>Sign in with Github</span>
              </a>
            </li> -->
          </ul>
        </div>

        <div class="text-center">
          <p>
            Don't have an account?
            <RouterLink to="/register" class="register-link">Create an account</RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.login-modal-container {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 450px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.login-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 10px;
  border-bottom: 1px solid #e9ecef;
}

.login-modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background-color: #f8f9fa;
}

.login-modal-body {
  padding: 20px 24px 24px;
}

.login-subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
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
  color: #6c757d;
}

.input-icon input {
  padding-left: 40px;
  padding-right: 40px;
}

.form-control {
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-bg-color);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-login-lg {
  background-color: var(--primary-bg-color);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-login-lg:hover:not(:disabled) {
  background-color: var(--primary-bg-color);
  transform: translateY(-1px);
}

.btn-login-lg:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.divider {
  text-align: center;
  margin: 20px 0;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background-color: #e9ecef;
}

.divider .txt {
  background: white;
  padding: 0 15px;
  color: #666;
  font-size: 14px;
}

.login-socialite ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.login-socialite li {
  margin-bottom: 10px;
}

.login-socialite a {
  min-width: 280px;
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  text-decoration: none;
  color: #333;
  transition: all 0.3s ease;
}


.login-socialite img {
  width: 20px;
  height: 20px;
  margin-right: 12px;
}

.register-link {
  color: var(--primary-bg-color);
  text-decoration: none;
  font-weight: 500;
}

.register-link:hover {
  text-decoration: underline;
}

.login-socialite ul li {
  padding: unset;
  background-color: unset;
}

/* Responsive */
@media (max-width: 480px) {
  .login-modal-container {
    margin: 0;
    border-radius: 0;
  }
  
  .login-modal-header,
  .login-modal-body {
    padding: 16px;
  }
}
</style> 