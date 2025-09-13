<script setup>
import { ref, watch } from 'vue'
import { useProductOrders } from '@/composables/useProductOrders'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatDate, formatNumber } = useCompanyFormatters()
// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  orderId: {
    type: [String, Number],
    default: null
  }
})

// Emits
const emit = defineEmits(['close'])

// Composables
const { fetchProductOrderDetails, downloadProductOrderInvoice, testRoute, testPdfGeneration, debugInvoiceEndpoint, isLoading, error } = useProductOrders()

// Reactive data
const orderDetails = ref(null)
const isDownloading = ref(false)
const debugResult = ref(null)
const testPdfResult = ref(null)

// Watch for modal open/close and orderId changes
watch([() => props.isOpen, () => props.orderId], async ([isOpen, orderId]) => {
  if (isOpen && orderId) {
    await loadOrderDetails(orderId)
  }
})

// Load order details
const loadOrderDetails = async (orderId) => {
  try {
    orderDetails.value = await fetchProductOrderDetails(orderId)
  } catch (err) {
    console.error('Failed to load order details:', err)
  }
}

// Close modal
const closeModal = () => {
  emit('close')
  orderDetails.value = null
  debugResult.value = null
  testPdfResult.value = null
}

// Download invoice
const downloadInvoice = async () => {
  if (!props.orderId) return
  
  try {
    isDownloading.value = true
    await downloadProductOrderInvoice(props.orderId)
  } catch (err) {
    console.error('Failed to download invoice:', err)
    // Show user-friendly error message
    alert(`Failed to download invoice: ${err.message}`)
  } finally {
    isDownloading.value = false
  }
}

// Test PDF generation
const testPdf = async () => {
  try {
    testPdfResult.value = await testPdfGeneration()
    console.log('Test PDF result:', testPdfResult.value)
    
    if (testPdfResult.value.success) {
      alert('✅ Test PDF generated successfully! If it downloaded, the PDF library is working.')
    } else {
      alert(`❌ Test PDF failed: ${testPdfResult.value.message}. Check console for details.`)
    }
  } catch (err) {
    console.error('Test PDF failed:', err)
    alert('Test PDF failed! Check console for details.')
  }
}

// Debug invoice endpoint
const debugInvoice = async () => {
  if (!props.orderId) return
  
  try {
    debugResult.value = await debugInvoiceEndpoint(props.orderId)
    console.log('Debug result:', debugResult.value)
    
    if (debugResult.value.success) {
      alert('✅ Invoice endpoint is working correctly! Check console for details.')
    } else {
      alert(`❌ Issue found: ${debugResult.value.message}. Check console for details.`)
    }
  } catch (err) {
    console.error('Debug failed:', err)
    alert('Debug failed! Check console for details.')
  }
}

// Test route (for debugging)
const testApiRoute = async () => {
  try {
    const result = await testRoute()
    console.log('Test successful:', result)
    alert('Test successful! Check console for details.')
  } catch (err) {
    console.error('Test failed:', err)
    alert('Test failed! Check console for details.')
  }
}

// Format price
const formatPrice = (price) => {
  return parseFloat(price || 0).toFixed(2)
}

// Get status class
const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'confirmed':
      return 'status-confirmed'
    case 'pending':
      return 'status-pending'
    case 'cancelled':
      return 'status-cancel'
    case 'completed':
      return 'status-completed'
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
        <h4 class="modal-title">{{ t('Order Details') }}</h4>
        <button type="button" @click="closeModal" >
          <VIcon icon="tabler-x" />
        </button>
      </div>

      <div class="modal-body">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ t('Loading...') }}</span>
          </div>
          <p class="mt-2">{{ t('Loading order details...') }}</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <VIcon icon="tabler-alert-circle" size="20" />
          {{ error }}
        </div>

        <!-- Order Details -->
        <div v-else-if="orderDetails" class="order-details">
          <!-- Order Info -->
          <div class="order-info-section">
            <h5>{{ t('Order Information') }}</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>{{ t('Order ID') }}</label>:
                  <span>{{ orderDetails.order_id }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>{{ t('Order Date') }}</label>:
                  <span>{{ formatDate(orderDetails.date) }}</span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>{{ t('Status') }}</label>:
                  <div class="status" :class="getStatusClass(orderDetails.status)">
                    {{ orderDetails.status }}
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>{{ t('Payment Method') }}</label>:
                  <span>{{ orderDetails.payment_method }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Products Section -->
          <div class="products-section">
            <h5>{{ t('Products Ordered') }}</h5>

            <table class="table">
              <thead>
                <tr>
                  <th>{{ t('SN') }}</th>
                  <th>{{ t('Product') }}</th>
                  <th class="text-center">{{ t('Quantity') }}</th>
                  <th class="text-center">{{ t('Unit Price') }}</th>
                  <th class="text-center">{{ t('Subtotal') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(product, index) in orderDetails.products" :key="product.id">
                  <td>{{ index + 1 }}</td>
                  <td>
                    {{ product.name }}
                  </td>
                  <td class="text-center">{{ formatNumber(product.quantity) }}</td>
                  <td class="text-center">${{ formatPrice(product.unit_price) }}</td>
                  <td class="text-center">${{ formatPrice(product.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Order Summary -->
          <div class="order-summary">
            <h5>{{ t('Order Summary') }}</h5>
            <div class="summary-row">
              <span class="label">{{ t('Subtotal') }}:</span>
              <span class="value">{{ formatPrice(orderDetails.subtotal_without_tax_discount) }}</span>
            </div>
            <div class="summary-row">
              <span class="label">{{ t('Total Tax') }}:</span>
              <span class="value">{{ formatPrice(orderDetails.total_tax) }}</span>
            </div>
            <div class="summary-row total">
              <span class="label">{{ t('Total Amount') }}:</span>
              <span class="value">{{ formatPrice(orderDetails.total_payable) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <!-- Test PDF button -->
        <button 
          type="button" 
          class="btn btn-success" 
          @click="testPdf"
          style="margin-right: 10px;"
        >
          <VIcon icon="tabler-file-type-pdf" size="16" />
          Test PDF
        </button>
        
        <!-- Debug buttons for troubleshooting -->
        <button 
          type="button" 
          class="btn btn-warning" 
          @click="debugInvoice"
          :disabled="!props.orderId"
          style="margin-right: 10px;"
        >
          <VIcon icon="tabler-bug-report" size="16" />
          Debug Invoice
        </button>
        
        <!-- Temporary test button for debugging -->
        <!-- <button 
          type="button" 
          class="btn btn-info" 
          @click="testApiRoute"
          style="margin-right: 10px;"
        >
          <VIcon icon="tabler-bug" size="16" />
          Test API
        </button> -->
        
        <!-- <button 
          type="button" 
          class="btn btn-primary" 
          @click="downloadInvoice"
          :disabled="isDownloading"
        >
          <VIcon icon="tabler-download" size="16" />
          {{ isDownloading ? 'Downloading...' : 'Download Invoice' }}
        </button> -->
        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
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
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
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
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.15s ease;
}

.btn-close:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.modal-body {
  padding: 1.5rem;
  height: 75vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
}

.order-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-info-section h5,
.products-section h5,
.order-summary h5 {
  margin-bottom: 1rem;
  color: #333;
  font-weight: 600;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.info-item label {
  font-weight: 500;
  color: #666;
  font-size: 0.9rem;
  min-width: 120px;
}

.info-item span {
  font-size: 1rem;
  color: #333;
  padding-left: 10px;
}
.info-item div {
  margin-left: 10px;
}

.info-item strong {
  color: #555;
}

.order-summary {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.summary-row.total {
  font-weight: 600;
  font-size: 1.1rem;
  padding-top: 0.75rem;
  border-top: 1px solid #dee2e6;
}
.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background-color 0.15s ease;
}
.btn-primary {
  background-color: #007bff;
  color: white;
  margin-right: 10px;
}
.btn-primary:hover {
  background-color: #0056b3;
}
.btn-primary:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
.btn-info {
  background-color: #17a2b8;
  color: white;
}
.btn-info:hover {
  background-color: #138496;
}
.btn-success {
  background-color: #28a745;
  color: white;
}
.btn-success:hover {
  background-color: #218838;
}
.btn-warning {
  background-color: #ffc107;
  color: #212529;
}
.btn-warning:hover {
  background-color: #e0a800;
}
.btn-secondary {
  background-color: #6c757d;
  color: white;
}
.btn-secondary:hover {
  background-color: #5a6268;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }
}
.status {
  padding: 5px 10px;
  border-radius: 25px;
  font-size: 12px;
  font-weight: 500;
  height: 30px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.status-accepted {
  background-color: #2196F32a;
  color: #2196F3;
}
.status-pending {
  background-color: #ffc1072a;
  color: #FFC107;
}
.status-rejected {
  background-color: #f443362a;
  color: #F44336;
}
.status-completed {
  background-color: #0dca002a;
  color: #4CAF50;
}
</style> 