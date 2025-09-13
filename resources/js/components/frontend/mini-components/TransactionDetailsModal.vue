<script setup>
import { ref, watch } from 'vue'
import { useTransactionHistory } from '@/composables/useTransactionHistory'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  transactionId: {
    type: Number,
    default: null
  },
  sourceType: {
    type: String,
    default: 'booking'
  },
  transactionType: {
    type: String,
    default: 'Service'
  }
})

const emit = defineEmits(['close'])

const { fetchTransactionDetails, isLoading, error } = useTransactionHistory()

const transactionDetails = ref(null)

// Watch for modal open/close and transaction changes
watch([() => props.isOpen, () => props.transactionId, () => props.sourceType], async ([isOpen, transactionId, sourceType]) => {
  if (isOpen && transactionId) {
    try {
      transactionDetails.value = await fetchTransactionDetails(transactionId, sourceType, props.transactionType)
    } catch (err) {
      console.error('Failed to fetch transaction details:', err)
    }
  }
}, { immediate: true })

const closeModal = () => {
  emit('close')
  transactionDetails.value = null
}

// Format amount with proper sign
const formatAmount = (amount) => {
  const formattedAmount = Math.abs(amount).toFixed(2)
  return amount < 0 ? `-$${formattedAmount}` : `$${formattedAmount}`
}

// Get status class for styling
const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'status-active'
    case 'pending':
      return 'status-pending'
    case 'failed':
    case 'cancelled':
      return 'status-cancel'
    default:
      return 'status-pending'
  }
}
</script>

<template>
  <!-- Modal Backdrop -->
  <div v-if="isOpen" class="modal-backdrop" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h4 class="modal-title">Transaction Details</h4>
        <button type="button" @click="closeModal">
          <VIcon icon="tabler-x" />
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading transaction details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <VIcon icon="tabler-alert-circle" size="20" />
          {{ error }}
        </div>

        <!-- Transaction Details -->
        <div v-else-if="transactionDetails" class="transaction-details">
          <!-- Transaction Info -->
          <div class="transaction-info">
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Transaction ID:</label>
                  <span>{{ transactionDetails.transaction_id }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Type:</label>
                  <span>{{ transactionDetails.type }}</span>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Date:</label>
                  <span>{{ transactionDetails.date }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Status:</label>
                  <span class="status" :class="getStatusClass(transactionDetails.status)">
                    {{ transactionDetails.status }}
                  </span>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Payment Method:</label>
                  <span>{{ transactionDetails.payment_method }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Total Amount:</label>
                  <span class="amount">
                    {{ formatAmount(transactionDetails.total_amount || transactionDetails.total_payable) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Service booking specific info -->
            <div v-if="transactionDetails.type === 'Service' && transactionDetails.branch" class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Branch:</label>
                  <span>{{ transactionDetails.branch.name }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Payment Status:</label>
                  <span class="status" :class="getStatusClass(transactionDetails.payment_status)">
                    {{ transactionDetails.payment_status }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Additional note for services -->
            <div v-if="transactionDetails.note" class="row">
              <div class="col-12">
                <div class="info-item">
                  <label>Note:</label>
                  <span>{{ transactionDetails.note }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Items/Services List -->
          <div class="transaction-items">
            <h5>{{ transactionDetails.type === 'Service' ? 'Services' : 'Products' }}</h5>
            
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in transactionDetails.items" :key="item.id">
                    <td>
                      <div class="item-info">
                        <span class="item-name">{{ item.name }}</span>
                        <small v-if="item.description" class="item-description">{{ item.description }}</small>
                      </div>
                    </td>
                    <td>{{ item.quantity }}</td>
                    <td>{{ formatAmount(item.price || item.unit_price) }}</td>
                    <td>{{ formatAmount(item.total_price || item.total_payable) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Summary -->
          <div class="transaction-summary">
            <div class="row">
              <div class="col-md-6 offset-md-6">
                <div class="summary-item" v-if="transactionDetails.subtotal">
                  <span>Subtotal:</span>
                  <span>{{ formatAmount(transactionDetails.subtotal) }}</span>
                </div>
                <div class="summary-item" v-if="transactionDetails.tax_amount || transactionDetails.total_tax">
                  <span>Tax:</span>
                  <span>{{ formatAmount(transactionDetails.tax_amount || transactionDetails.total_tax) }}</span>
                </div>
                <div class="summary-item summary-total">
                  <span>Total:</span>
                  <span>{{ formatAmount(transactionDetails.total_amount || transactionDetails.total_payable) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
}

.transaction-info {
  margin-bottom: 30px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #f8f9fa;
}

.info-item label {
  font-weight: 600;
  color: #6c757d;
}

.info-item span {
  font-weight: 500;
  color: #212529;
}

.amount {
  font-weight: 600;
  color: #28a745;
}

.transaction-items {
  margin-bottom: 30px;
}

.item-info {
  display: flex;
  flex-direction: column;
}

.item-name {
  font-weight: 600;
  color: #212529;
}

.item-description {
  color: #6c757d;
  margin-top: 4px;
}

.transaction-summary {
  border-top: 1px solid #e9ecef;
  padding-top: 20px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 5px 0;
}

.summary-total {
  font-weight: 600;
  font-size: 1.1rem;
  border-top: 1px solid #e9ecef;
  padding-top: 10px;
  margin-top: 10px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}
</style> 