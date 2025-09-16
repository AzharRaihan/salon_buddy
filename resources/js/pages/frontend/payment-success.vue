<script setup>
import { onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const cartStore = useShoppingCartStore()
const { formatAmount } = useCompanyFormatters()
// Get order info from query parameters
const orderReference = computed(() => route.query.order)
const orderAmount = computed(() => route.query.amount)

onMounted(() => {
  // Ensure cart is cleared when visiting this page
  cartStore.clearCart()
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Payment Successful" breadcrumb="Payment Success" />

    <!-- Success Section -->
    <section class="payment-success-section default-section-padding-t">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center">
            <div class="success-content">
              <div class="success-icon mb-4">
                <VIcon size="120" icon="tabler-circle-check" class="text-success" />
              </div>
              
              <h2 class="success-title mb-3">Payment Successful!</h2>
              <p class="success-message mb-4">
                Thank you for your purchase! Your order has been successfully placed and payment has been confirmed.
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

              <div class="success-actions">
                <div class="row g-3">
                  <div class="col-md-6">
                    <RouterLink to="/frontend/product" class="btn btn-primary w-100">
                      Continue Shopping
                    </RouterLink>
                  </div>
                  <div class="col-md-6">
                    <RouterLink to="/" class="btn btn-primary w-100">
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
.payment-success-section {
  padding-bottom: 350px !important;
}

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
</style> 