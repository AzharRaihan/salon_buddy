<script setup>
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'


definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const cartStore = useShoppingCartStore()
const { formatAmount } = useCompanyFormatters()
const userData = useCookie("userData").value

// Get order info from query parameters
const orderReference = computed(() => route.query.order || route.query.reference)
const orderAmount = computed(() => route.query.amount)
const paymentStatus = computed(() => route.query.status)
const isLoading = ref(false)

onMounted(async () => {
  // Ensure cart is cleared when visiting this page
  cartStore.clearCart()
  
  // Handle Paystack callback
  if (route.query.reference && route.query.status === 'success') {
    await handlePaystackCallback()
  }
  
  // If user is logged in (POS context), redirect to POS after a delay
  if (userData && paymentStatus.value === 'success') {
    setTimeout(() => {
      router.push('/pos')
    }, 3000)
  }
})

// Handle Paystack payment callback
const handlePaystackCallback = async () => {
  try {
    isLoading.value = true
    
    // Send message to parent window if this is in a popup
    if (window.opener) {
      window.opener.postMessage({
        type: 'PAYSTACK_SUCCESS',
        reference: route.query.reference,
        status: route.query.status
      }, window.location.origin)
      
      // Close the popup
      window.close()
    }
  } catch (error) {
    console.error('Error handling Paystack callback:', error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Payment Successful" breadcrumb="Payment Success" />

    <!-- Success Section -->
    <section class="payment-success-section default-section-padding">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center">
            <div class="success-content">
              <div class="success-icon mb-4">
                <VIcon size="120" icon="tabler-circle-check" class="text-success" />
              </div>
              
              <h2 class="success-title mb-3">
                {{ paymentStatus === 'success' ? 'Payment Successful!' : 'Payment Processing...' }}
              </h2>
              <p class="success-message mb-4">
                <span v-if="paymentStatus === 'success'">
                  Thank you for your purchase! Your order has been successfully placed and payment has been confirmed.
                </span>
                <span v-else-if="isLoading">
                  Processing your payment... Please wait.
                </span>
                <span v-else>
                  Your payment is being processed. You will be redirected shortly.
                </span>
              </p>
              
              <!-- Order Details -->
              <div v-if="orderReference" class="order-info mb-4">
                <div class="card bg-light p-3">
                  <div class="row text-center">
                    <div class="col-md-6">
                      <h6 class="mb-1">Order Reference</h6>
                      <p class="mb-0 fw-bold text-primary">{{ orderReference }}</p>
                    </div>
                    <div class="col-md-6" v-if="orderAmount">
                      <h6 class="mb-1">Total Amount</h6>
                      <p class="mb-0 fw-bold text-success">{{ formatAmount(orderAmount) }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="success-details mb-4">
                <div class="card p-4">
                  <h5 class="mb-3">What's Next?</h5>
                  <ul class="list-unstyled text-start">
                    <li class="mb-2">
                      <VIcon icon="tabler-check" class="text-success me-2" />
                      You will receive an order confirmation email shortly
                    </li>
                    <li class="mb-2">
                      <VIcon icon="tabler-check" class="text-success me-2" />
                      Our team will prepare your order for processing
                    </li>
                    <li class="mb-2">
                      <VIcon icon="tabler-check" class="text-success me-2" />
                      You'll be notified when your order is ready for pickup/delivery
                    </li>
                  </ul>
                </div>
              </div>

              <!-- POS User Redirect Message -->
              <div v-if="userData && paymentStatus === 'success'" class="pos-redirect-message mb-4">
                <div class="alert alert-info text-center">
                  <VIcon icon="tabler-info-circle" class="me-2" />
                  You will be redirected to POS in a few seconds...
                </div>
              </div>

              <div class="success-actions" v-if="!userData || paymentStatus !== 'success'">
                <div class="row g-3">
                  <div class="col-md-6">
                    <RouterLink to="/product" class="btn btn-primary w-100 common-animation-button large-btn">
                      Continue Shopping
                    </RouterLink>
                  </div>
                  <div class="col-md-6">
                    <RouterLink to="/" class="btn btn-primary w-100 common-animation-button large-btn">
                      Back to Home
                    </RouterLink>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.success-title {
  color: var(--title-color, #333) !important;
  font-size: 2.5rem !important;
  font-weight: 600 !important;
}

.success-message {
  color: var(--text-color, #666) !important;
  font-size: 1.1rem !important;
  line-height: 1.6 !important;
}

.success-details .card {
  border: 1px solid #e9ecef !important;
  border-radius: 12px !important ;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
}



.large-btn {
  text-align: center;
  padding: 7px 20px;
  color: #ffffff;
  background-color: var(--primary-bg-color) !important;
  height: 46px;
  justify-content: center;
  align-items: center;
}
.large-btn::before {
  background-color: var(--primary-bg-hover-color);
}
.large-btn::after {
  color: white;
  background-color: var(--primary-bg-color);
}
.large-btn:hover {
  color: var(--color-white);
}


.order-info h6, .success-details ul li, .success-details h5 {
  color: #333;
}
</style> 