<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useShoppingCartStore } from '@/stores/shoppingCart.js'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { toast } from 'vue3-toastify';

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const route = useRoute()
const router = useRouter()
const cartStore = useShoppingCartStore()
const { formatAmount, formatDate } = useCompanyFormatters()

// Reactive data
const isLoading = ref(false)
const isSubmitting = ref(false)
const saleData = ref(null)
const ratings = ref({})
const comments = ref({})
const submitSuccess = ref(false)

// Get reference and customerId from query parameters
const referenceNo = computed(() => route.query.reference)
const customerId = computed(() => route.query.customerId)

onMounted(async () => {
  // Ensure cart is cleared when visiting this page
  cartStore.clearCart()
  
  if (referenceNo.value && customerId.value) {
    await fetchSaleDetails()
  } 
  else {
    router.push('/')
  }
})

// Fetch sale details
const fetchSaleDetails = async () => {
  try {
    isLoading.value = true
    
    const response = await $api('/sale-details-for-rating', {
      params: {
        reference: referenceNo.value,
        customerId: customerId.value
      }
    })

    
    if (response.success) {
      saleData.value = response.data
      // Initialize ratings object
      response.data.sale_details.forEach(detail => {
        ratings.value[detail.sale_detail_id] = 0
        comments.value[detail.sale_detail_id] = ''
      })
    } else {
      throw new Error(response.message || 'Failed to fetch sale details')
    }
  } catch (error) {
    router.push('/')
  } finally {
    isLoading.value = false
  }
}

// Set rating for a service
const setRating = (saleDetailId, rating) => {
  ratings.value[saleDetailId] = rating
}

// Set comment for a service
const setComment = (saleDetailId, comment) => {
  comments.value[saleDetailId] = comment
}

// Submit ratings
const submitRatings = async () => {
  try {
    isSubmitting.value = true
    
    // Prepare ratings data
    const ratingsData = []
    Object.keys(ratings.value).forEach(saleDetailId => {
      if (ratings.value[saleDetailId] > 0) {
        const detail = saleData.value.sale_details.find(d => d.sale_detail_id == saleDetailId)
        ratingsData.push({
          sale_detail_id: saleDetailId,
          item_id: detail.item_id,
          employee_id: detail?.employee_id,
          rating: ratings.value[saleDetailId],
          comment: comments.value[saleDetailId] || ''
        })
      }
    })


    if (ratingsData.length === 0) {
      toast('Please select at least one rating before submitting.', { type: 'error' })
      return
    }
    
    const response = await $api('/submit-service-ratings', {
      method: 'POST',
      body: {
        ratings: ratingsData,
        sale_id: saleData.value.sale.id,
        customer_id: saleData.value.customer.id
      },
    })
    
    if (response.success) {
      toast('Ratings submitted successfully.', { type: 'success' })
      submitSuccess.value = true
    } else {
      throw new Error(response.message || 'Failed to submit ratings')
    }
  } catch (error) {
    console.error('Error submitting ratings:', error)
    toast(error.message || 'Failed to submit ratings', { type: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

// Get star rating display
const getStarRating = (saleDetailId) => {
  return ratings.value[saleDetailId] || 0
}
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner title="Rate Our Services" breadcrumb="Service Rating" />

    <!-- Rating Section -->
    <section class="rating-section default-section-padding">
      <div class="container">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading sale details...</p>
        </div>

        <!-- Success State -->
        <div v-else-if="submitSuccess" class="text-center py-5">
          <div class="success-icon mb-4">
            <VIcon size="120" icon="tabler-circle-check" class="text-success" />
          </div>
          <h2 class="success-title mb-3">Thank You!</h2>
          <p class="success-message mb-4">
            Your ratings have been submitted successfully. We appreciate your feedback!
          </p>
          <div class="success-actions">
            <RouterLink to="/" class="btn btn-primary common-animation-button large-btn">
              <VIcon icon="tabler-arrow-narrow-left" class="me-2" />
              Back to Home
            </RouterLink>
          </div>
        </div>

        <!-- Rating Form -->
        <div v-else-if="saleData" class="row justify-content-center">
          <div class="col-lg-10">


            <!-- Customer & Branch Information -->
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header text-white">
                    <h5 class="mb-0 d-flex align-items-center">
                      <VIcon icon="tabler-user" class="me-2" />
                      Customer Information
                    </h5>
                  </div>
                  <div class="card-body">
                    <p><strong>Name:</strong> {{ saleData.customer.name }}</p>
                    <p><strong>Email:</strong> {{ saleData.customer.email }}</p>
                    <p v-if="saleData.customer.phone"><strong>Phone:</strong> {{ saleData.customer.phone }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header text-white">
                    <h5 class="mb-0 d-flex align-items-center">
                      <VIcon icon="tabler-building-store" class="me-2" />
                      Branch Information
                    </h5>
                  </div>
                  <div class="card-body">
                    <p><strong>Branch:</strong> {{ saleData.branch.name }}</p>
                    <p v-if="saleData.branch.address"><strong>Address:</strong> {{ saleData.branch.address }}</p>
                    <p v-if="saleData.branch.phone"><strong>Phone:</strong> {{ saleData.branch.phone }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sale Information -->
            <div class="sale-info-card mb-4">
              <div class="card">
                <div class="card-header text-white">
                  <h5 class="mb-0 d-flex align-items-center">
                    <VIcon icon="tabler-receipt" class="me-2" />
                    Sale Information
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <p><strong>Reference:</strong> {{ saleData.sale.reference_no }}</p>
                      <p><strong>Date:</strong> {{ formatDate(saleData.sale.order_date) }}</p>
                      <p><strong>Status:</strong> 
                        <span class="ms-2 badge bg-success">{{ saleData.sale.order_status }}</span>
                      </p>
                    </div>
                    <div class="col-md-6">
                      <p><strong>Total Amount:</strong>  {{ formatAmount(saleData.sale.total_payable) }}</p>
                      <p><strong>Total Paid:</strong>  {{ formatAmount(saleData.sale.total_paid) }}</p>
                      <p v-if="saleData.sale.total_due > 0"><strong>Total Due:</strong> {{ formatAmount(saleData.sale.total_due) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Services Rating Table -->
            <div class="services-rating-card">
              <div class="card">
                <div class="card-header text-white">
                  <h5 class="mb-0 d-flex align-items-center">
                    <VIcon icon="tabler-star" class="me-2" />
                    Rate Your Services
                  </h5>
                </div>
                <div class="card-body">
                  <p class="text-muted mb-4">Please rate each service you received. Your feedback helps us improve our services.</p>
                  
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead class="table-light">
                        <tr>
                          <th width="5%">SN</th>
                          <th width="35%">Service Name</th>
                          <th width="15%">Quantity</th>
                          <th width="15%">Price</th>
                          <th width="30%">Rating & Comment</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(detail, index) in saleData.sale_details" :key="detail.sale_detail_id">
                          <td>{{ index + 1 }}</td>
                          <td>
                            <strong>{{ detail.item_name }}</strong>
                            <p v-if="detail.item_description" class="text-muted small mb-0">{{ detail.item_description }}</p>
                          </td>
                          <td>{{ detail.quantity }}</td>
                          <td>{{ formatAmount(detail.unit_price) }}</td>
                          <td>
                            <!-- Star Rating -->
                            <div class="star-rating mb-2">
                              <span 
                                v-for="star in 5" 
                                :key="star"
                                @mouseenter="setRating(detail.sale_detail_id, star)"
                                class="star"
                                :class="{ 'active': star <= getStarRating(detail.sale_detail_id) }"
                                @click="setRating(detail.sale_detail_id, star)"
                              >
                                ★
                              </span>
                            </div>
                            <!-- Comment -->
                            <textarea
                              v-model="comments[detail.sale_detail_id]"
                              class="form-control form-control-sm"
                              rows="2"
                              placeholder="Write your comment here..."
                              @input="setComment(detail.sale_detail_id, $event.target.value)"
                            ></textarea>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Submit Button -->
                  <div class="text-center mt-4 booking-small-btn d-flex justify-content-center"> 
                    <button @click="submitRatings" 
                      :disabled="isSubmitting"
                      class="btn btn-booking" type="submit">
                      <span>{{ isSubmitting ? 'Submitting...' : 'Submit Ratings' }}</span>
                      <div class="arrow-icon-wrap">
                        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else class="text-center py-5">
          <div class="error-icon mb-4">
            <VIcon size="120" icon="tabler-alert-circle" class="text-danger" />
          </div>
          <h2 class="error-title mb-3">Sale Not Found</h2>
          <p class="error-message mb-4">
            The sale reference you're looking for could not be found.
          </p>
          <RouterLink to="/" class="btn btn-primary common-animation-button">
            <VIcon icon="tabler-arrow-narrow-left" class="me-2" />
            Back to Home
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.success-title, .error-title {
  color: var(--title-color, #333) !important;
  font-size: 2.5rem !important;
  font-weight: 600 !important;
}

.success-message, .error-message {
  color: var(--text-color, #666) !important;
  font-size: 1.1rem !important;
  line-height: 1.6 !important;
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

/* Star Rating Styles */
.star-rating {
  display: flex;
  gap: 2px;
  margin-bottom: 8px;
}

.star {
  font-size: 24px;
  color: #ddd;
  cursor: pointer;
  transition: color 0.2s ease;
  user-select: none;
}

.star:hover,
.star.active {
  color: #ffc107;
}

.star:hover ~ .star {
  color: #ddd;
}

/* Card Styles */
.card {
  border: none;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.card-header {
  border-radius: 12px 12px 0 0 !important;
  background-color: var(--primary-bg-color) !important;
  border: none;
}

/* Table Styles */
.table {
  margin-bottom: 0;
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #495057;
}

.table td {
  vertical-align: middle;
  border-color: #e9ecef;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.05);
}

/* Form Styles */
.form-control {
  border-radius: 8px;
  border: 1px solid #ced4da;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .star {
    font-size: 20px;
  }
  
  .table-responsive {
    font-size: 0.9rem;
  }
  
  .card-body p {
    margin-bottom: 0.5rem;
  }
}

/* Loading spinner */
.spinner-border {
  width: 3rem;
  height: 3rem;
}

/* Badge styles */
.badge {
  font-size: 0.75em;
  padding: 0.375rem 0.75rem;
}
.form-control:focus {
    border-color: var(--primary-bg-color);
    box-shadow: none;
}
</style> 