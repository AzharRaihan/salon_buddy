<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import { useCheckoutForm } from '@/composables/useCheckoutForm'
import BookingSamllBtn2 from '@/components/frontend/mini-components/BookingSamllBtn2.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import LoginModal from '@/components/auth/LoginModal.vue'

import { useI18n } from 'vue-i18n';
const { t } = useI18n()

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const router = useRouter()
const cartStore = useShoppingCartStore()

// Customer Authentication
const { isCustomerAuthenticated, getCurrentCustomer } = useCustomerAuth()
const { customerAuthState, updateCustomerAuthState } = useAuthState()

// Checkout form handling
const { 
  customerForm, 
  isFormValid, 
  prefillCustomerData, 
  proceedToPayment: validateAndProceed,
  handleInput 
} = useCheckoutForm()

// Modal state
const showLoginModal = ref(false)
const isLoading = ref(false)

// Check if customer is authenticated
const isAuthenticated = computed(() => customerAuthState.value.isAuthenticated)

// Handle login success
const handleLoginSuccess = (userData) => {
  showLoginModal.value = false
  prefillCustomerData(userData)
  // Update auth state after successful login
  updateCustomerAuthState()
}

// Handle login modal close
const handleLoginModalClose = () => {
  showLoginModal.value = false
  // Only redirect if user is still not authenticated after modal close
  // This prevents redirect on successful login
  setTimeout(() => {
    if (!isAuthenticated.value) {
      router.push('/frontend/shopping-cart')
    }
  }, 100)
}

// Proceed to payment with authentication check
const proceedToPayment = () => {
  if (!isAuthenticated.value) {
    showLoginModal.value = true
    return
  }
  
  validateAndProceed()
}

onMounted(async () => {
  cartStore.loadFromStorage()
  await cartStore.initializeTaxSettings()
  
  // Check if customer is authenticated
  if (!isAuthenticated.value) {
    showLoginModal.value = true
  } else {
    const customer = getCurrentCustomer()
    prefillCustomerData(customer)
  }
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="t('Checkout')" :breadcrumb="t('Checkout')" />

    <!-- Checkout Page Section -->
    <section class="checkout-page-section default-section-padding-t">
      <div class="container">
        <div class="shopping-cart-table">
          <div class="row">
            <div class="col-lg-8">
              <div class="checkout-page">
                <div class="contact-infor-wrap">
                  <h4>{{ t('Contact Information') }}</h4>
                  <div class="contact-infor-form">
                    <div class="row">
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="f_name">{{ t('First Name') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="f_name" 
                            v-model="customerForm.f_name" 
                            :placeholder="t('Enter your first name')"
                            @input="handleInput('f_name')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="l_name">{{ t('Last Name') }}</label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="l_name" 
                            v-model="customerForm.l_name" 
                            :placeholder="t('Enter your last name')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="email">{{ t('Email Address') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="email" 
                            v-model="customerForm.email" 
                            :placeholder="t('Enter your email address')"
                            @input="handleInput('email')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="phone">{{ t('Phone Number') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="phone" 
                            v-model="customerForm.phone" 
                            :placeholder="t('Enter your phone number')"
                            @input="handleInput('phone')"
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="contact-infor-wrap mt-4">
                  <h4 class="mb-3">{{ t('Delivery Information') }}</h4>
                  <div class="contact-infor-form">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="street">{{ t('Street/Town') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="street" 
                            v-model="customerForm.street" 
                            :placeholder="t('Enter your street/town')"
                            @input="handleInput('street')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="address">{{ t('Address') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="address" 
                            v-model="customerForm.address" 
                            :placeholder="t('Enter your address')"
                            @input="handleInput('address')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="zip">{{ t('Zip Code') }} <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="zip" 
                            v-model="customerForm.zip" 
                            :placeholder="t('Enter your zip code')"
                            @input="handleInput('zip')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-12 mb-3">
                        <div class="form-group">
                          <label for="notes">{{ t('Notes') }}</label>
                          <textarea name="notes" id="notes" class="form-control" v-model="customerForm.notes" :placeholder="t('Enter your notes')"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center order-summary-button-group">
                  <div class="booking-small-btn">
                    <button type="button" class="btn btn-booking" @click="proceedToPayment">
                      <span>{{ t('Proceed to checkout') }}</span>
                      <div class="arrow-icon-wrap">
                        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="order-summary-section" v-if="cartStore.hasItems">
                <h3>{{ t('Order Summary') }}</h3>
                <div class="order-summary-table">
                  <ul class="table">
                    <li>
                      <span>{{ t('Subtotal') }}</span>
                      <span class="text-end">{{ cartStore.subtotal.toFixed(2) }}</span>
                    </li>
                    <li>
                      <span>{{ t('Tax') }}</span>
                      <span class="text-end">{{ cartStore.taxAmount.toFixed(2) }}</span>
                    </li>
                    <li>
                      <span>{{ t('Delivery Charge') }}</span>
                      <span class="text-end">{{ cartStore.deliveryCharge.toFixed(2) }}</span>
                    </li>
                    <li class="total">
                      <span>{{ t('Total') }}</span>
                      <span class="text-end">{{ cartStore.total.toFixed(2) }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Login Modal -->
    <LoginModal 
      :is-visible="showLoginModal"
      :return-url="$route.fullPath"
      @close="handleLoginModalClose"
      @success="handleLoginSuccess"
    />
  </div>
</template>

<style scoped>
.form-control.is-invalid {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}

.form-group {
  position: relative;
}
[dir="rtl"] .form-control.is-invalid, .was-validated .form-control:invalid {
  background-position: left calc(.375em + .1875rem) center;
}
</style>