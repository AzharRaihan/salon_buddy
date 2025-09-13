<template>
  <div class="payment-example">
    <h3>{{ t('Payment Example') }}</h3>
    
    <!-- Payment Method Selection -->
    <div class="payment-methods">
      <h4>{{ t('Select Payment Method') }}:</h4>
      <div class="payment-options">
        <label v-for="method in paymentMethods" :key="method.id">
          <input 
            type="radio" 
            :value="method.id" 
            v-model="selectedPaymentMethod"
            :disabled="isProcessingPayment"
          >
          {{ method.name }}
        </label>
      </div>
    </div>

    <!-- Amount Input -->
    <div class="amount-input">
      <h4>{{ t('Amount') }}:</h4>
      <input 
        type="number" 
        v-model="amount" 
        :placeholder="t('Enter amount')"
        :disabled="isProcessingPayment"
        min="0"
        step="0.01"
      >
    </div>

    <!-- Process Payment Button -->
    <div class="payment-actions">
      <button 
        @click="processPayment"
        :disabled="!canProcessPayment || isProcessingPayment"
        class="process-btn"
      >
        <span v-if="isProcessingPayment">{{ t('Processing...') }}</span>
        <span v-else>{{ t('Process Payment') }}</span>
      </button>
    </div>

    <!-- Payment Status -->
    <div v-if="paymentError" class="payment-error">
      {{ paymentError }}
    </div>
    
    <div v-if="paymentSuccess" class="payment-success">
      {{ t('Payment completed successfully!') }}
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { usePOSPayment } from '@/composables/pos/usePOSPayment'
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

export default {
  name: 'PaymentExample',
  setup() {
    const { 
      isProcessingPayment, 
      paymentError, 
      paymentSuccess, 
      processPOSPayment, 
      resetPaymentState 
    } = usePOSPayment()

    const selectedPaymentMethod = ref(null)
    const amount = ref(0)

    // Mock payment methods - replace with actual data from your API
    const paymentMethods = ref([
      { id: 1, name: 'Cash', account_type: 'cash' },
      { id: 2, name: 'Razorpay', account_type: 'razorpay' },
      { id: 3, name: 'Stripe', account_type: 'stripe' },
      { id: 4, name: 'PayPal', account_type: 'paypal' },
      { id: 5, name: 'Paytm', account_type: 'paytm' },
      { id: 6, name: 'Paystack', account_type: 'paystack' }
    ])

    const canProcessPayment = computed(() => {
      return selectedPaymentMethod.value && amount.value > 0 && !isProcessingPayment.value
    })

    const processPayment = async () => {
      if (!canProcessPayment.value) return

      try {
        const orderData = {
          order_id: 'ORDER_' + Date.now(),
          customer_name: 'Test Customer',
          customer_email: 'test@example.com',
          items: [
            { name: 'Test Item', quantity: 1, price: amount.value }
          ]
        }

        const result = await processPOSPayment(
          selectedPaymentMethod.value,
          amount.value,
          orderData
        )

        if (result.success) {
          console.log('Payment successful:', result)
          // Handle successful payment (e.g., save order to database)
        }
      } catch (error) {
        console.error('Payment failed:', error)
      }
    }

    // Reset payment state when component unmounts
    const resetState = () => {
      resetPaymentState()
    }

    return {
      selectedPaymentMethod,
      amount,
      paymentMethods,
      isProcessingPayment,
      paymentError,
      paymentSuccess,
      canProcessPayment,
      processPayment,
      resetState
    }
  }
}
</script>

<style scoped>
.payment-example {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.payment-methods {
  margin-bottom: 20px;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.payment-options label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.amount-input {
  margin-bottom: 20px;
}

.amount-input input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.payment-actions {
  margin-bottom: 20px;
}

.process-btn {
  width: 100%;
  padding: 12px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

.process-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.process-btn:hover:not(:disabled) {
  background: #0056b3;
}

.payment-error {
  color: #dc3545;
  padding: 10px;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
  margin-bottom: 10px;
}

.payment-success {
  color: #155724;
  padding: 10px;
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 4px;
  margin-bottom: 10px;
}
</style> 