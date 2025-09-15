<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
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

const paymentMethods = ref([])
const selectedPaymentMethod = ref('')
const isLoading = ref(false)
const customerData = ref(null)
const showLoginModal = ref(false)

// Check if customer is authenticated
const isAuthenticated = computed(() => customerAuthState.value.isAuthenticated)

const fetchPaymentMethods = async () => {
  try {
    isLoading.value = true
    const response = await $api('/get-payment-methods')
    if (response.success) {
      paymentMethods.value = response.data
    }
  } catch (error) {
    console.error('Error fetching payment methods:', error)
  } finally {
    isLoading.value = false
  }
}

const processPayment = async () => {
  if (!isAuthenticated.value) {
    showLoginModal.value = true
    return
  }
  
  if (!selectedPaymentMethod.value) {
    alert('Please select a payment method')
    return
  }
  
  if (!cartStore.hasItems) {
    alert('Your cart is empty')
    return
  }
  
  try {
    isLoading.value = true
    
    // Create order data
    const orderData = {
      items: cartStore.items,
      subtotal: cartStore.subtotal,
      tax_amount: cartStore.taxAmount,
      delivery_charge: cartStore.deliveryCharge,
      delivery_area_id: cartStore.selectedDeliveryAreaId,
      total_amount: cartStore.total,
      payment_method_id: selectedPaymentMethod.value,
      customer_data: customerData.value
    }
    
    // Create order in database
    const response = await $api('/create-order', {
      method: 'POST',
      body: JSON.stringify(orderData),
      headers: {
        'Content-Type': 'application/json',
      },
    })
    
    if (response.success) {
      // Order created successfully
      console.log('Order created:', response.data)
      
      // Here you would integrate with actual payment processing
      // For now, we'll simulate a successful payment
      
      // Clear the cart after successful payment
      cartStore.clearCart()
      
      // Clear customer data from localStorage
      localStorage.removeItem('checkout_customer_data')
      
      // Redirect to success page with order info
      router.push({
        path: '/frontend/payment-success',
        query: { 
          order: response.data.reference_no,
          amount: response.data.total_amount 
        }
      })
    } else {
      throw new Error(response.message || 'Failed to create order')
    }
    
  } catch (error) {
    console.error('Payment processing error:', error)
    alert('Payment failed. Please try again.')
  } finally {
    isLoading.value = false
  }
}

// Handle login success
const handleLoginSuccess = (userData) => {
  showLoginModal.value = false
}

// Handle login modal close
const handleLoginModalClose = () => {
  showLoginModal.value = false
  // Redirect back to checkout if not authenticated
  if (!isAuthenticated.value) {
    router.push('/frontend/checkout')
  }
}

onMounted(async () => {
  cartStore.loadFromStorage()
  await cartStore.initializeTaxSettings()
  await fetchPaymentMethods()
  
  // Check if customer is authenticated
  if (!isAuthenticated.value) {
    showLoginModal.value = true
  }
  
  // Load customer data from localStorage
  const savedCustomerData = localStorage.getItem('checkout_customer_data')
  if (savedCustomerData) {
    try {
      customerData.value = JSON.parse(savedCustomerData)
    } catch (error) {
      console.error('Error parsing customer data:', error)
    }
  }
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Payment" breadcrumb="Payment" />

    <!-- Payment Section -->
    <section class="payment-section default-section-padding-t">
      <div class="container">
        <div class="payment-form">
          <div class="row payment-section">
            <div class="col-lg-5">
              <div class="payment-details">
                <h3 class="payment-method-title">Payment Method</h3>
                <div v-if="isLoading" class="text-center py-4">
                  <p>Loading payment methods...</p>
                </div>
                <ul v-else class="payment-methods">
                  <li v-for="method in paymentMethods" :key="method.id" class="payment-method-li">
                    <label class="payment-method-item" :for="`payment_${method.id}`">
                      <div class="method-select">
                        <input 
                          type="radio" 
                          :id="`payment_${method.id}`" 
                          name="payment_method" 
                          :value="method.id"
                          v-model="selectedPaymentMethod"
                        >
                        <div class="radio-circle"></div>
                        <span>{{ method.name }}</span>
                      </div>
                      <div class="method-logo">
                        <img :src="method.payment_method_icon_url" :alt="method.name">
                      </div>
                    </label>
                  </li>
                </ul>
              </div>
            </div>
            <div class="col-lg-2"></div>
            <div class="col-lg-5">
              <div class="payment-details order-summary-section" v-if="cartStore.hasItems">
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
              <div class="d-flex justify-content-center order-summary-button-group">
                <div class="button-group">
                  <RouterLink to="/frontend/checkout" class="btn btn-previous">
                    <span><VIcon size="22" icon="tabler-arrow-left" /></span>
                    Previous
                  </RouterLink>
                  <button 
                    class="btn btn-next" 
                    @click="processPayment"
                    :disabled="isLoading || !selectedPaymentMethod"
                  >
                    {{ isLoading ? 'Processing...' : 'Pay Now' }}
                    <span><VIcon size="22" icon="tabler-arrow-right" /></span>
                  </button>
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
