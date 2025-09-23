<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'

// import default avatar

import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const showPassword = ref(false)
const websiteStore = useWebsiteSettingsStore()

// Use customer authentication composable
const { 
  customerLogin, 
  customerSocialLogin, 
  handleCustomerSocialCallback, 
  isLoading, 
  error 
} = useCustomerAuth()

// Use auth state for updates
const { updateCustomerAuthState } = useAuthState()

// Form data
const form = reactive({
  email: '',
  password: ''
})

// Local errors for form validation
const formErrors = ref({})

// Success/Error messages
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Validation
const isFormValid = computed(() => {
  return form.email && form.password
})

// Clear errors when user starts typing
const clearError = (field) => {
  if (formErrors.value[field]) {
    delete formErrors.value[field]
  }
  if (message.value) {
    message.value = ''
  }
}

const applicationMode = ref(false)
const defaultCustomerEmail = ref('')
const defaultCustomerPassword = ref('')
// call a api to check application mode type demo or live 
const checkApplicationMode = async () => {
  const result = await $api('/check-application-mode')
  if (result.data?.mode) {
    applicationMode.value = result.data.mode
    defaultCustomerEmail.value = 'doorsoftdemocustomer@gmail.com'
    defaultCustomerPassword.value = '12345678'
  }
}
onMounted(() => {
  checkApplicationMode()
})



// Submit login
const submitLogin = async () => {
  if (!isFormValid.value) {
    message.value = 'Please fill all required fields.'
    messageType.value = 'error'
    return
  }

  formErrors.value = {}
  message.value = ''

  try {
    const result = await customerLogin(form)

    if (result.success) {
      // Update auth state after successful login
      updateCustomerAuthState()
      
      message.value = 'Login successful!'
      messageType.value = 'success'

      // Redirect after 1 second
      setTimeout(() => {
        router.push('/dashboard_')
      }, 1000)
    } else {
      message.value = result.message || 'Login failed'
      messageType.value = 'error'
      
      if (result.errors) {
        formErrors.value = result.errors
      }
    }
  } catch (err) {
    console.error('Login error:', err)
    message.value = 'Something went wrong. Please try again.'
    messageType.value = 'error'
  }
}

// Handle social login
const handleSocialLogin = (provider) => {
  customerSocialLogin(provider, '/login_')
}

// Handle social login callback
const handleCallback = () => {
  const result = handleCustomerSocialCallback()

  if (result.success) {
    // Update auth state after successful social login
    updateCustomerAuthState()
    
    message.value = 'Social login successful!'
    messageType.value = 'success'

    // Redirect after 1 second
    setTimeout(() => {
      router.push('/dashboard_')
    }, 1000)
  } else if (result.message) {
    message.value = result.message
    messageType.value = 'error'
  }
}

onMounted(() => {
  handleCallback()
  websiteStore.fetchSettings()
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
    <CommonPageBanner :title="t('Login')" :breadcrumb="t('Login')" />

    <!-- Login Section -->
    <section class="login-wrapper default-section-padding-t">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-sm-12 col-md-7 col-lg-5">
            <div class="login-form">
              <h3 class="text-center login-title">{{ t('Login') }}</h3>
              <p class="text-center login-title-subtitle">{{ t('Login to continue') }}</p>

              <!-- Success/Error Messages -->
              <div v-if="message" :class="['alert', messageType == 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
                {{ message }}
              </div>

              <form @submit.prevent="submitLogin">
                <div class="form-group mb-3">
                  <label for="email">{{ t('Email') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-mail" size="18" />
                    <input 
                      v-model="form.email"
                      @input="clearError('email')"
                      type="email" 
                      class="form-control" 
                      :class="{ 'is-invalid': formErrors.email }"
                      id="email" 
                      :placeholder="t('Enter your email')"
                      :value="applicationMode ? defaultCustomerEmail : form.email"
                      autocomplete="email"
                    >
                  </div>
                  <div v-if="formErrors.email" class="invalid-feedback d-block">
                    {{ formErrors.email[0] }}
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="password">{{ t('Password') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-lock" size="18" />
                    <input 
                      v-model="form.password"
                      @input="clearError('password')"
                      :type="showPassword ? 'text' : 'password'" 
                      class="form-control" 
                      :class="{ 'is-invalid': formErrors.password }"
                      id="password" 
                      :placeholder="t('Enter your password')" 
                      autocomplete="current-password"
                      :value="applicationMode ? defaultCustomerPassword : form.password"
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
                  <div v-if="formErrors.password" class="invalid-feedback d-block">
                    {{ formErrors.password[0] }}
                  </div>
                </div>

                <div class="text-right my-3">
                  <RouterLink to="/forgot-password_" class="forgot-password">
                    {{ t('Forgot password?') }}
                  </RouterLink>
                </div>

                <div class="form-group">
                  <button 
                    type="submit"
                    class="btn btn-login-lg w-100 login-btn"
                  >
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ isLoading ? t('Logging in...') : t('Login') }}
                  </button>
                </div>
              </form>

              <div class="divider divider-login">
                <span class="txt">{{ t('Or login with') }}</span>
              </div>

              <div class="login-socialite">
                <ul>
                  <li>
                    <a href="javascript:void(0)" @click="handleSocialLogin('google')">
                      <img src="../@frontend/images/socialite/Google.png" alt="Google" />
                      <span>{{ t('Sign in with Google') }}</span>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0)" @click="handleSocialLogin('facebook')">
                      <img src="../@frontend/images/socialite/FB.png" alt="Facebook" />
                      <span>{{ t('Sign in with Facebook') }}</span>
                    </a>
                  </li>
                </ul>
              </div>

              <div class="text-center">
                <p>
                  {{ t("Don't have an account?") }}
                  <RouterLink to="/register_">{{ t('Create an account') }}</RouterLink>
                </p>
              </div>
            </div>
          </div>
          <div class="d-md-done d-lg-block col-lg-1"></div>
          <div class="d-md-none d-lg-block col-lg-6">
            <div class="login-image">
              <img :src="websiteStore.getLoginImage" alt="Login Image">
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

