<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useAuthState } from '@/composables/useAuthState'
import { usePOSPayment } from '@/composables/pos/usePOSPayment'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import LoginModal from '@/components/auth/LoginModal.vue'
import { toast } from 'vue3-toastify';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const router = useRouter()
const cartStore = useShoppingCartStore()
const { formatAmount, formatNumberPrecision } = useCompanyFormatters()

// Customer Authentication
const { isCustomerAuthenticated, getCurrentCustomer } = useCustomerAuth()
const { customerAuthState } = useAuthState()

// Payment Processing
const { isProcessingPayment, paymentError, processPOSPayment, resetPaymentState } = usePOSPayment()

const paymentMethods = ref([])
const selectedPaymentMethod = ref('')
const paidAmount = ref(0)
const isLoading = ref(false)
const customerData = ref(null)
const showLoginModal = ref(false)

// Check if customer is authenticated
const isAuthenticated = computed(() => customerAuthState.value.isAuthenticated)

const fetchPaymentMethods = async () => {
  try {
    isLoading.value = true
    const response = await $api('/get-all-payment-getway-frontend')
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
    toast('Please select a payment method', { type: 'error' })
    return
  }
  
  if (!cartStore.hasItems) {
    toast('Your cart is empty', { type: 'error' })
    return
  }
  
  try {
    isLoading.value = true

    // Set payment amount in cart store
    cartStore.setPaymentAmount(paidAmount.value || cartStore.total)
    cartStore.setPaymentMethod(selectedPaymentMethod.value)
    
    // Create order data with tax breakdown
    const orderData = {
      items: cartStore.items.map(item => ({
        id: item.id,
        qty: item.quantity,
        price: item.price,
        employee_id: null,
        is_free: 'No',
        promotion_id: null,
        promotion_discount: 0
      })),
      subtotal: cartStore.subtotal,
      tax: cartStore.taxAmount,
      tax_breakdown: cartStore.taxBreakdown,
      discount: 0,
      promotionDiscount: 0,
      total: cartStore.total,
      payment_method_id: selectedPaymentMethod.value,
      payment_amount: cartStore.totalPaid,
      due_amount: cartStore.totalDue,
      delivery_charge: cartStore.deliveryCharge,
      delivery_area_id: cartStore.selectedDeliveryAreaId,
      customer_id: customerData.value?.id || null,
      user_id: 1, // Default user ID for frontend orders
      branch_id: 1, // Default branch ID
      order_date: new Date().toISOString().split('T')[0],
      customer_data: customerData.value
    }
    
    // Process payment first (if not cash)
    let paymentResult = { success: true }
    if (selectedPaymentMethod.value) {
      paymentResult = await processPOSPayment(
        selectedPaymentMethod.value,
        cartStore.total,
        orderData
      )
    }
    
    if (paymentResult.success) {
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
        
        // Clear the cart after successful payment
        cartStore.clearCart()
        
        // Clear customer data from localStorage
        localStorage.removeItem('checkout_customer_data')
        
        // Reset payment state
        resetPaymentState()
        
        // Redirect to success page with order info
        router.push({
          path: '/payment-success',
          query: { 
            order: response.data.reference_no,
            amount: response.data.total_amount 
          }
        })
      } else {
        throw new Error(response.message || 'Failed to create order')
      }
    } else {
      throw new Error(paymentResult.message || 'Payment processing failed')
    }
    
  } catch (error) {
    console.error('Payment processing error:', error)
    toast(error.message || 'Payment failed. Please try again.', { type: 'error' })
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
    router.push('/checkout')
  }
}

onMounted(async () => {
  cartStore.loadFromStorage()
  await cartStore.initializeTaxSettings()
  await fetchPaymentMethods()
  
  // Initialize payment amount to total
  paidAmount.value = cartStore.total
  
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

// format padAmount
const paymentAmount = computed(() => {
  return formatNumberPrecision(paidAmount.value)
})

</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Payment" breadcrumb="Payment" />

    <!-- Payment Section -->
    <section class="payment-section default-section-padding">
      <div class="container">
        <div class="payment-form">
          <div class="row payment-section">
            <div class="col-lg-5">
              <div class="payment-details">
                <h3 class="payment-method-title">Payment Method</h3>
                
                <ul class="payment-methods">
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
                        <span class="text-end">{{ formatAmount(cartStore.subtotal) }}</span>
                      </li>
                      <li>
                        <span>Tax</span>
                        <span class="text-end">{{ formatAmount(cartStore.taxAmount) }}</span>
                      </li>
                      
                      <li>
                        <span>Delivery Charge</span>
                        <span class="text-end">{{ formatAmount(cartStore.deliveryCharge) }}</span>
                      </li>
                      <li class="total">
                        <span>Total</span>
                        <span class="text-end">{{ formatAmount(cartStore.total) }}</span>
                      </li>
                      <!-- Payment Amount Input -->
                      <li class="payment-amount">
                        <div class="payment-amount-input">
                          <label for="paid-amount">Amount to Pay</label>
                          <input 
                            id="paid-amount"
                            v-model="paymentAmount" 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            :max="cartStore.total"
                            class="form-control"
                            @input="cartStore.setPaymentAmount(paidAmount)"
                          />
                        </div>
                      </li>
                      <li v-if="cartStore.totalDue > 0" class="due-amount">
                        <span>Due Amount</span>
                        <span class="text-end text-danger">{{ cartStore.totalDue.toFixed(2) }}</span>
                      </li>
                  </ul>
                </div>
              </div>
              <div class="d-flex justify-content-center order-summary-button-group">
                <div class="button-group">
                  <RouterLink to="/customer/checkout" class="btn btn-previous">
                    <span><VIcon size="22" icon="tabler-arrow-left" /></span>
                    Previous
                  </RouterLink>
                  <button 
                    class="btn btn-next" 
                    @click="processPayment"
                    :disabled="isLoading || isProcessingPayment || !selectedPaymentMethod"
                  >
                    {{ (isLoading || isProcessingPayment) ? 'Processing...' : 'Pay Now' }}
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

<style scoped>
.tax-breakdown {
  padding-left: 20px;
  border-left: 2px solid #e9ecef;
}

.tax-breakdown-item {
  display: flex;
  justify-content: space-between;
  padding: 2px 0;
  font-size: 0.9em;
  color: #6c757d;
}

.tax-type {
  font-weight: 500;
}

.payment-amount {
  border-top: 1px solid #e9ecef;
  padding-top: 10px;
  margin-top: 10px;
}

.payment-amount-input {
  display: flex;
  flex-direction: column;
  gap: 5px;
  flex-grow: 1;
}

.payment-amount-input label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 5px;
}

.payment-amount-input input {
  border: 1px solid #ced4da;
  border-radius: 4px;
  padding: 8px 12px;
  font-size: 16px;
}

.due-amount {
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  padding: 8px 12px;
  margin-top: 10px;
}

.due-amount span:first-child {
  font-weight: 600;
  color: #856404;
}

.text-danger {
  color: #dc3545 !important;
}
</style>
