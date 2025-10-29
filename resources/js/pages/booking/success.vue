<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  booking_id: {
    type: String,
    default: null
  }
})

const booking = ref(null)
const loading = ref(true)

const fetchBookingDetails = async () => {
  if (!props.booking_id) {
    router.visit('/')
    return
  }

  try {
    const response = await $api(`/booking-details/${props.booking_id}`)
    booking.value = response.data
  } catch (error) {
    console.error('Error fetching booking details:', error)
    router.visit('/')
  } finally {
    loading.value = false
  }
}

const goToHome = () => {
  router.visit('/')
}

const downloadReceipt = async () => {
  try {
    const response = await $api(`/booking-receipt/${props.booking_id}`, {
      responseType: 'blob'
    })
    
    // Create blob link to download
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `booking-${props.booking_id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error downloading receipt:', error)
  }
}

onMounted(() => {
  fetchBookingDetails()
})

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div class="booking-success-page">
    <!-- Page Banner -->
    <section class="success-banner">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center">
            <div v-if="loading" class="loading-section">
              <VProgressCircular size="64" indeterminate color="success" />
              <h3 class="mt-4">Loading booking details...</h3>
            </div>
            
            <div v-else-if="booking" class="success-content">
              <!-- Success Icon -->
              <div class="success-icon">
                <VIcon size="80" color="success">tabler-circle-check</VIcon>
              </div>
              
              <!-- Success Message -->
              <h1 class="success-title">Booking Confirmed!</h1>
              <p class="success-subtitle">
                Thank you! Your appointment has been successfully booked.
              </p>
              
              <!-- Booking Reference -->
              <div class="booking-reference">
                <h4>Booking Reference: <span class="text-primary">#{{ booking.reference_no }}</span></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Booking Details -->
    <section v-if="booking && !loading" class="booking-details-section default-section-padding">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <VCard class="booking-details-card">
              <VCardTitle class="text-center">
                <h3>Booking Details</h3>
              </VCardTitle>
              
              <VCardText>
                <VRow>
                  <!-- Customer Information -->
                  <VCol cols="12" md="6">
                    <h5 class="section-title">Customer Information</h5>
                    <div class="info-item">
                      <strong>Name:</strong> {{ booking.customer_name }}
                    </div>
                    <div class="info-item">
                      <strong>Email:</strong> {{ booking.customer_email }}
                    </div>
                    <div class="info-item">
                      <strong>Phone:</strong> {{ booking.customer_phone }}
                    </div>
                    <div class="info-item" v-if="booking.customer_address">
                      <strong>Address:</strong> {{ booking.customer_address }}
                    </div>
                  </VCol>
                  
                  <!-- Appointment Information -->
                  <VCol cols="12" md="6">
                    <h5 class="section-title">Appointment Information</h5>
                    <div class="info-item">
                      <strong>Branch:</strong> {{ booking.branch?.name }}
                    </div>
                    <div class="info-item">
                      <strong>Date:</strong> {{ new Date(booking.appointment_date).toLocaleDateString() }}
                    </div>
                    <div class="info-item">
                      <strong>Time:</strong> {{ booking.appointment_time }}
                    </div>
                    <div class="info-item">
                      <strong>Status:</strong> 
                      <VChip :color="booking.status == 'confirmed' ? 'success' : 'warning'" size="small">
                        {{ booking.status }}
                      </VChip>
                    </div>
                  </VCol>
                </VRow>

                <!-- Services -->
                <div class="services-section mt-4">
                  <h5 class="section-title">Selected Services</h5>
                  <VCard variant="outlined" class="mb-3" v-for="service in booking.services" :key="service.id">
                    <VCardText>
                      <div class="d-flex justify-space-between align-center">
                        <div>
                          <h6>{{ service.name }}</h6>
                          <p class="text-body-2 text-grey">{{ service.duration }}</p>
                        </div>
                        <div class="text-right">
                          <strong>₹{{ service.price }}</strong>
                        </div>
                      </div>
                    </VCardText>
                  </VCard>
                </div>

                <!-- Payment Summary -->
                <div class="payment-summary mt-4">
                  <h5 class="section-title">Payment Summary</h5>
                  <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₹{{ booking.subtotal }}</span>
                  </div>
                  <div class="summary-row">
                    <span>Tax:</span>
                    <span>₹{{ booking.tax_amount }}</span>
                  </div>
                  <div class="summary-row total-row">
                    <strong>Total:</strong>
                    <strong>₹{{ booking.total_amount }}</strong>
                  </div>
                  <div class="summary-row">
                    <span>Payment Account:</span>
                    <span class="text-capitalize">{{ booking.payment_method }}</span>
                  </div>
                  <div class="summary-row">
                    <span>Payment Status:</span>
                    <VChip :color="booking.payment_status == 'completed' ? 'success' : 'warning'" size="small">
                      {{ booking.payment_status }}
                    </VChip>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-4 text-center">
                  <VBtn 
                    color="primary" 
                    variant="outlined" 
                    @click="downloadReceipt"
                    class="mr-3"
                  >
                    <VIcon start>tabler-download</VIcon>
                    Download Receipt
                  </VBtn>
                  
                  <VBtn 
                    color="success" 
                    @click="goToHome"
                  >
                    <VIcon start>tabler-home</VIcon>
                    Back to Home
                  </VBtn>
                </div>
              </VCardText>
            </VCard>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.booking-success-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.success-banner {
  padding: 4rem 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.success-content {
  padding: 2rem 0;
}

.success-icon {
  margin-bottom: 1.5rem;
}

.success-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.success-subtitle {
  font-size: 1.2rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.booking-reference {
  background: rgba(255, 255, 255, 0.1);
  padding: 1rem 2rem;
  border-radius: 8px;
  backdrop-filter: blur(10px);
}

.booking-details-section {
  padding: 3rem 0;
}

.booking-details-card {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.section-title {
  color: #333;
  margin-bottom: 1rem;
  font-weight: 600;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 0.5rem;
}

.info-item {
  margin-bottom: 0.75rem;
  padding: 0.5rem 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.total-row {
  font-size: 1.1rem;
  border-bottom: 2px solid #ddd;
  margin-top: 0.5rem;
}

.action-buttons {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f0f0f0;
}

.loading-section {
  padding: 4rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .success-title {
    font-size: 2rem;
  }
  
  .success-subtitle {
    font-size: 1rem;
  }
  
  .booking-reference h4 {
    font-size: 1.1rem;
  }
}
</style> 