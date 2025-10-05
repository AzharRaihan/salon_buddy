<script setup>
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const userData = useCookie("userData").value

// Get payment info from query parameters
const paymentReference = computed(() => route.query.reference)
const paymentStatus = computed(() => route.query.status)
const errorMessage = computed(() => route.query.error)

onMounted(() => {
  // Handle Paystack callback for failed payments
  if (route.query.reference && (route.query.status === 'failed' || route.query.status === 'cancelled')) {
    // Send message to parent window if this is in a popup
    if (window.opener) {
      window.opener.postMessage({
        type: 'PAYSTACK_CANCELLED',
        reference: route.query.reference,
        status: route.query.status
      }, window.location.origin)
      
      // Close the popup
      window.close()
    }
  }
  
  // If user is logged in (POS context), redirect to POS after a delay
  if (userData) {
    setTimeout(() => {
      router.push('/pos')
    }, 5000)
  }
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Payment Cancelled" breadcrumb="Payment Cancelled" />

    <!-- Cancel Section -->
    <section class="payment-cancel-section default-section-padding">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center">
            <div class="cancel-content">
              <div class="cancel-icon mb-4">
                <VIcon size="120" icon="tabler-circle-x" class="text-danger" />
              </div>
              
              <h2 class="cancel-title mb-3">Payment Cancelled</h2>
              <p class="cancel-message mb-4">
                <span v-if="paymentStatus === 'cancelled'">
                  Your payment was cancelled. No charges have been made to your account.
                </span>
                <span v-else-if="paymentStatus === 'failed'">
                  Your payment failed to process. Please try again or use a different payment method.
                </span>
                <span v-else>
                  There was an issue processing your payment. Please try again.
                </span>
              </p>
              
              <!-- Error Details -->
              <div v-if="errorMessage" class="error-info mb-4">
                <div class="card bg-light p-3">
                  <h6 class="mb-1">Error Details</h6>
                  <p class="mb-0 text-danger">{{ errorMessage }}</p>
                </div>
              </div>
              
              <!-- Payment Reference -->
              <div v-if="paymentReference" class="payment-info mb-4">
                <div class="card bg-light p-3">
                  <h6 class="mb-1">Payment Reference</h6>
                  <p class="mb-0 fw-bold text-primary">{{ paymentReference }}</p>
                </div>
              </div>
              
              <div class="cancel-details mb-4">
                <div class="card p-4">
                  <h5 class="mb-3">What can you do?</h5>
                  <ul class="list-unstyled text-start">
                    <li class="mb-2">
                      <VIcon icon="tabler-arrow-right" class="text-primary me-2" />
                      Try the payment again with the same or different payment method
                    </li>
                    <li class="mb-2">
                      <VIcon icon="tabler-arrow-right" class="text-primary me-2" />
                      Check your payment method details and try again
                    </li>
                    <li class="mb-2">
                      <VIcon icon="tabler-arrow-right" class="text-primary me-2" />
                      Contact support if the issue persists
                    </li>
                  </ul>
                </div>
              </div>

              <!-- POS User Redirect Message -->
              <div v-if="userData" class="pos-redirect-message mb-4">
                <div class="alert alert-info text-center">
                  <VIcon icon="tabler-info-circle" class="me-2" />
                  You will be redirected to POS in a few seconds...
                </div>
              </div>

              <div class="cancel-actions" v-if="!userData">
                <div class="row g-3">
                  <div class="col-md-6">
                    <RouterLink to="/payment" class="btn btn-primary w-100 common-animation-button large-btn">
                      Try Payment Again
                    </RouterLink>
                  </div>
                  <div class="col-md-6">
                    <RouterLink to="/" class="btn btn-secondary w-100 common-animation-button large-btn">
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
.cancel-title {
  color: var(--title-color, #333) !important;
  font-size: 2.5rem !important;
  font-weight: 600 !important;
}

.cancel-message {
  color: var(--text-color, #666) !important;
  font-size: 1.1rem !important;
  line-height: 1.6 !important;
}

.cancel-details .card {
  border: 1px solid #e9ecef !important;
  border-radius: 12px !important;
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

.payment-info h6, .cancel-details ul li, .cancel-details h5 {
  color: #333;
}
</style>
