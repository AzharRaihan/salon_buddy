<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const router = useRouter()
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const websiteStore = useWebsiteSettingsStore()

console.log('This is websiteStore', websiteStore)

// Use customer authentication composable
const { 
  customerRegister, 
  customerSocialLogin, 
  isLoading, 
  error 
} = useCustomerAuth()

// Form data
const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  // agree_policy: false
})

// Local errors for form validation
const formErrors = ref({})

// Success/Error messages
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Validation
const isFormValid = computed(() => {
  return form.first_name && 
         form.last_name && 
         form.email && 
         form.password && 
         form.password_confirmation && 
         form.password == form.password_confirmation
        //  form.agree_policy
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

// Submit registration
const submitRegistration = async () => {
  if (!isFormValid.value) {
    message.value = 'Please fill all required fields correctly.'
    messageType.value = 'error'
    return
  }

  formErrors.value = {}
  message.value = ''

  try {
    const result = await customerRegister(form)

    if (result.success) {
      message.value = 'Registration successful!'
      messageType.value = 'success'

      // Redirect after 2 seconds
      setTimeout(() => {
        router.push('/customer/dashboard')
      }, 2000)
    } else {
      message.value = result.message || 'Registration failed'
      messageType.value = 'error'
      
      if (result.errors) {
        formErrors.value = result.errors
      }
    }
  } catch (err) {
    console.error('Registration error:', err)
    message.value = 'Something went wrong. Please try again.'
    messageType.value = 'error'
  }
}

// Handle social login
const handleSocialLogin = (provider) => {
  customerSocialLogin(provider, '/register')
}

onMounted(() => {
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
    <CommonPageBanner :title="t('Register')" :breadcrumb="t('Register')" />

    <!-- Login Section -->
    <section class="login-wrapper default-section-padding-t">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-sm-12 col-md-7 col-lg-5">
            <div class="login-form">
              <h3 class="text-center login-title">{{ t('Register') }}</h3>
              <p class="text-center login-title-subtitle">{{ t('Create an account to continue') }}</p>

              <!-- Success/Error Messages -->
              <div v-if="message" :class="['alert', messageType == 'success' ? 'alert-success' : 'alert-danger']" class="mb-3">
                {{ message }}
              </div>

              <form @submit.prevent="submitRegistration">
                <div class="form-group mb-3">
                  <label for="first_name">{{ t('First Name') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-user" size="18" />
                    <input 
                      v-model="form.first_name"
                      @input="clearError('first_name')"
                      type="text" 
                      class="form-control" 
                      id="first_name" 
                      :placeholder="t('Enter your first name')" 
                      autocomplete="given-name"
                    >
                  </div>
                  <div v-if="formErrors.first_name" class="invalid-feedback d-block">
                    {{ formErrors.first_name[0] }}
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="last_name">{{ t('Last Name') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-user" size="18" />
                    <input 
                      v-model="form.last_name"
                      @input="clearError('last_name')"
                      type="text" 
                      class="form-control" 
                      id="last_name" 
                      :placeholder="t('Enter your last name')" 
                      autocomplete="family-name"
                    >
                  </div>
                  <div v-if="formErrors.last_name" class="invalid-feedback d-block">
                    {{ formErrors.last_name[0] }}
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="email">{{ t('Email') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-mail" size="18" />
                    <input 
                      v-model="form.email"
                      @input="clearError('email')"
                      type="email" 
                      class="form-control"
                      id="email" 
                      :placeholder="t('Enter your email')" 
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
                      id="password" 
                      :placeholder="t('Enter your password')" 
                      autocomplete="new-password"
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

                <div class="form-group mb-3">
                  <label for="confirm_password">{{ t('Confirm Password') }} <span class="required-star">*</span></label>
                  <div class="input-icon">
                    <VIcon class="primary-icon" icon="tabler-lock" size="18" />
                    <input 
                      v-model="form.password_confirmation"
                      @input="clearError('password_confirmation')"
                      :type="showConfirmPassword ? 'text' : 'password'" 
                      class="form-control"
                      id="confirm_password" 
                      :placeholder="t('Confirm your password')" 
                      autocomplete="new-password"
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
                  <div v-if="formErrors.password_confirmation" class="invalid-feedback d-block">
                    {{ formErrors.password_confirmation[0] }}
                  </div>
                  <div v-else-if="form.password_confirmation && form.password !== form.password_confirmation" class="invalid-feedback d-block">
                    Passwords do not match
                  </div>
                </div>

                <!-- <div class="my-3">
                  <div class="form-check">
                    <input 
                      v-model="form.agree_policy"
                      @change="clearError('agree_policy')"
                      type="checkbox" 
                      class="form-check-input" 
                      :class="{ 'is-invalid': formErrors.agree_policy }"
                      id="agree_policy"
                      required
                    >
                    <label class="form-check-label" for="agree_policy">
                      I agree with <a href="#" target="_blank">support policy</a>
                    </label>
                    <div v-if="formErrors.agree_policy" class="invalid-feedback d-block">
                      {{ formErrors.agree_policy[0] }}
                    </div>
                  </div>
                </div> -->

                <div class="form-group">
                  <button 
                    type="submit"
                    class="btn w-100 login-btn"
                  >
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ isLoading ? t('Creating Account...') : t('Create an account') }}
                  </button>
                </div>
              </form>

              <div class="divider divider-login">
                <span class="txt">{{ t('Or sign up with') }}</span>
              </div>

              <div class="login-socialite">
                <ul>
                  <li>
                    <a href="javascript:void(0)" @click="handleSocialLogin('facebook')">
                      <img src="../../@frontend/images/socialite/FB.png" alt="Facebook" />
                      <span>{{ t('Sign up with Facebook') }}</span>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0)" @click="handleSocialLogin('google')">
                      <img src="../../@frontend/images/socialite/Google.png" alt="Google" />
                      <span>{{ t('Sign up with Google') }}</span>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0)" @click="handleSocialLogin('github')">
                      <img src="../../@frontend/images/socialite/Git.png" alt="GitHub" />
                      <span>{{ t('Sign up with Github') }}</span>
                    </a>
                  </li>
                </ul>
              </div>

              <div class="text-center">
                <p>{{ t('Already have an account?') }}
                  <RouterLink to="/login">{{ t('Login') }}</RouterLink>
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
