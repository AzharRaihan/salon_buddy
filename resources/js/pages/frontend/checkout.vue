<script setup>
import { onMounted, ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import BookingSamllBtn2 from '@/components/frontend/mini-components/BookingSamllBtn2.vue'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import LoginModal from '@/components/auth/LoginModal.vue'

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
const { customerAuthState } = useAuthState()

// Form data
const customerForm = reactive({
  f_name: '',
  l_name: '',
  email: '',
  phone: '',
  street: '',
  address: '',
  zip: '',
  notes: ''
})

// Modal state
const showLoginModal = ref(false)
const isLoading = ref(false)

// Check if customer is authenticated
const isAuthenticated = computed(() => customerAuthState.value.isAuthenticated)

// Check if form is valid
const isFormValid = computed(() => {
  return customerForm.f_name && customerForm.email && customerForm.phone && 
         customerForm.street && customerForm.address && customerForm.zip
})

// Clear validation errors when user starts typing
const clearValidationErrors = (field) => {
  const element = document.getElementById(field)
  if (element) {
    element.classList.remove('is-invalid')
    const errorDiv = element.parentNode.querySelector('.invalid-feedback')
    if (errorDiv) {
      errorDiv.remove()
    }
  }
}

// Pre-fill form with customer data
const prefillCustomerData = () => {
  if (isAuthenticated.value) {
    const customer = getCurrentCustomer()
    if (customer) {
      customerForm.f_name = customer.name?.split(' ')[0] || ''
      customerForm.l_name = customer.name?.split(' ').slice(1).join(' ') || ''
      customerForm.email = customer.email || ''
      customerForm.phone = customer.phone || ''
      customerForm.address = customer.address || ''
    }
  }
}

// Handle login success
const handleLoginSuccess = (userData) => {
  showLoginModal.value = false
  prefillCustomerData()
}

// Handle login modal close
const handleLoginModalClose = () => {
  showLoginModal.value = false
  // Redirect back to shopping cart if not authenticated
  if (!isAuthenticated.value) {
    router.push('/frontend/shopping-cart')
  }
}

// Proceed to payment
const proceedToPayment = () => {

  if (!isAuthenticated.value) {
    showLoginModal.value = true
    return
  }
  
  // Validate required fields
  const errors = {}
  
  if (!customerForm.f_name) {
    errors.f_name = 'First name is required'
  }
  if (!customerForm.email) {
    errors.email = 'Email is required'
  } else if (!customerForm.email.includes('@') || !customerForm.email.includes('.')) {
    errors.email = 'Please enter a valid email'
  }
  if (!customerForm.phone) {
    errors.phone = 'Phone is required'
  }
  if (!customerForm.street) {
    errors.street = 'Street is required'
  }
  if (!customerForm.address) {
    errors.address = 'Address is required'
  }
  if (!customerForm.zip) {
    errors.zip = 'Zip code is required'
  }
  
  if (Object.keys(errors).length > 0) {
    console.log('errors', errors)
    // Show validation errors
    Object.keys(errors).forEach(field => {
      const element = document.getElementById(field)
      if (element) {
        element.classList.add('is-invalid')
        // Add error message
        let errorDiv = element.parentNode.querySelector('.invalid-feedback')
        if (!errorDiv) {
          errorDiv = document.createElement('div')
          errorDiv.className = 'invalid-feedback'
          element.parentNode.appendChild(errorDiv)
        }
        errorDiv.textContent = errors[field]
      }
    })
    return
  }
  
  // Save customer form data to localStorage for payment page
  localStorage.setItem('checkout_customer_data', JSON.stringify(customerForm))
  
  router.push('/frontend/payment')
}

onMounted(async () => {
  cartStore.loadFromStorage()
  await cartStore.initializeTaxSettings()
  
  // Check if customer is authenticated
  if (!isAuthenticated.value) {
    showLoginModal.value = true
  } else {
    prefillCustomerData()
  }
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Checkout" breadcrumb="Checkout" />

    <!-- Checkout Page Section -->
    <section class="checkout-page-section default-section-padding-t">
      <div class="container">
        <div class="shopping-cart-table">
          <div class="row">
            <div class="col-lg-8">
              <div class="checkout-page">
                <div class="contact-infor-wrap">
                  <h4>Contact Information</h4>
                  <div class="contact-infor-form">
                    <div class="row">
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="f_name">First Name <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="f_name" 
                            v-model="customerForm.f_name" 
                            placeholder="Enter your first name"
                            @input="clearValidationErrors('f_name')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="l_name">Last Name</label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="l_name" 
                            v-model="customerForm.l_name" 
                            placeholder="Enter your last name"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="email">Email Address <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="email" 
                            v-model="customerForm.email" 
                            placeholder="Enter your email address"
                            @input="clearValidationErrors('email')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-6 mb-3">
                        <div class="form-group">
                          <label for="phone">Phone Number <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="phone" 
                            v-model="customerForm.phone" 
                            placeholder="Enter your phone number"
                            @input="clearValidationErrors('phone')"
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="contact-infor-wrap mt-4">
                  <h4 class="mb-3">Delivery Information</h4>
                  <div class="contact-infor-form">
                    <div class="row">
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="street">Street/Town <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="street" 
                            v-model="customerForm.street" 
                            placeholder="Enter your street/town"
                          >
                        </div>
                      </div>
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="address">Address <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="address" 
                            v-model="customerForm.address" 
                            placeholder="Enter your address"
                            @input="clearValidationErrors('address')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-4 col-md-6 mb-3">
                        <div class="form-group">
                          <label for="zip">Zip Code <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            class="form-control" 
                            id="zip" 
                            v-model="customerForm.zip" 
                            placeholder="Enter your zip code"
                            @input="clearValidationErrors('zip')"
                          >
                        </div>
                      </div>
                      <div class="col-lg-12 mb-3">
                        <div class="form-group">
                          <label for="notes">Notes</label>
                          <textarea name="notes" id="notes" class="form-control" v-model="customerForm.notes" placeholder="Enter your notes"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center order-summary-button-group">
                  <div class="booking-small-btn">
                    <button type="button" class="btn btn-booking" @click="proceedToPayment">
                      <span>Proceed to checkout</span>
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
                <h3>Order Summary</h3>
                <div class="order-summary-table">
                  <ul class="table">
                    <li>
                      <span>Subtotal</span>
                      <span class="text-end">{{ cartStore.subtotal.toFixed(2) }}</span>
                    </li>
                    <li>
                      <span>Tax</span>
                      <span class="text-end">{{ cartStore.taxAmount.toFixed(2) }}</span>
                    </li>
                    <li>
                      <span>Delivery Charge</span>
                      <span class="text-end">{{ cartStore.deliveryCharge.toFixed(2) }}</span>
                    </li>
                    <li class="total">
                      <span>Total</span>
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
</style>