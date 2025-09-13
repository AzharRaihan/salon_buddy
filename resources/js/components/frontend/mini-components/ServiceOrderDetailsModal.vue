<script setup>
import { ref, computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { useI18n } from 'vue-i18n';
const { t } = useI18n()
const { formatDate, formatNumber } = useCompanyFormatters()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  orderDetails: {
    type: Object,
    default: () => ({})
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

// Modal methods
const closeModal = () => {
  emit('close')
}

// Computed properties
const hasServices = computed(() => {
  return props.orderDetails.services && props.orderDetails.services.length > 0
})

const statusClass = computed(() => {
  const status = props.orderDetails.status?.toLowerCase()
  switch (status) {
    case 'pending':
      return 'status-pending'
    case 'accepted':
      return 'status-accepted'
    case 'rejected':
      return 'status-rejected'
    case 'completed':
      return 'status-completed'
    default:
      return 'status-pending'
  }


})

</script>

<template>
  <div v-if="isOpen" class="modal-overlay" @click="closeModal">
    <div class="modal-content service-order-modal" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <h3 class="modal-title">{{ t('Service Booking Details') }}</h3>
        <button class="modal-close" @click="closeModal">
          <VIcon icon="tabler-x" size="20" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ t('Loading...') }}</span>
          </div>
          <p class="mt-2">{{ t('Loading order details...') }}</p>
        </div>

        <!-- Order Details -->
        <div v-else class="order-details">
          <!-- Order Info -->
          <div class="order-info-section">
            <h4>{{ t('Booking Information') }}</h4>
            <div class="order-info-grid">
              <div class="info-item">
                <label>{{ t('Booking ID') }}</label>:
                <span>{{ orderDetails.order_id || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>{{ t('Date') }}</label>:
                <span>{{ formatDate(orderDetails.date) || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>{{ t('Branch') }}</label>:
                <span>{{ orderDetails.branch?.name || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>{{ t('Status') }}</label>:
                <div class="status" :class="statusClass">{{ orderDetails.status || 'N/A' }}</div>
              </div>
            </div>
          </div>

          <!-- Services Section -->
          <div class="services-section">
            <h4>{{ t('Services') }}</h4>
            <div class="services-list">
              <table class="table">
                <thead>
                  <tr>
                    <th>{{ t('SN') }}</th>
                    <th>{{ t('Service') }}</th>
                    <th class="text-center">{{ t('Quantity') }}</th>
                    <th class="text-center">{{ t('Time') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr  v-for="(service, index) in orderDetails.services" :key="service.id">
                    <td>{{ index + 1 }}</td>
                    <td>{{ service.name || 'N/A' }}</td>
                    <td class="text-center">{{ formatNumber(service.quantity) || 1 }}</td>
                    <td class="text-center">{{ service.start_time }} - {{ service.end_time }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="closeModal">{{ t('Close') }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  max-width: 700px;
  width: 90%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: #f5f5f5;
}

.modal-body {
  padding: 20px;
  height: 70vh;
  overflow-y: auto;
}

.order-details h4 {
  margin-bottom: 15px;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
}

.order-info-section,
.services-section,
.payment-summary,
.notes-section {
  margin-bottom: 25px;
}

/* .order-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
} */

.info-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.info-item label {
  font-weight: 500;
  color: #666;
  font-size: 0.9rem;
  min-width: 100px;
}

.info-item span {
  font-size: 1rem;
  color: #333;
  padding-left: 10px;
}
.info-item div {
  margin-left: 10px;
}


.services-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.service-item {
  border: 1px solid #e9ecef;
  border-radius: 6px;
  padding: 15px;
  background-color: #f8f9fa;
}

.service-details h5 {
  margin: 0 0 8px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.service-details p {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 0.9rem;
}

.service-meta {
  display: flex;
  gap: 15px;
  margin-bottom: 8px;
}

.service-meta span {
  font-size: 0.9rem;
  color: #666;
}

.service-time {
  font-size: 0.9rem;
  color: #666;
}

.no-services {
  text-align: center;
  padding: 20px;
  color: #666;
}



.notes-section {
  border-top: 1px solid #eee;
  padding-top: 15px;
}

.notes-section p {
  margin: 0;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 6px;
  color: #666;
  font-style: italic;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.spinner-border {
  width: 2rem;
  height: 2rem;
  border: 0.25em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}

.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
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