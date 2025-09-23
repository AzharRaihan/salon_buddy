<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue'
import { useApi } from '@/composables/useApi'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { useBookingFlow } from '@/composables/useBookingFlow'
import { useBookingPersistence } from '@/composables/useBookingPersistence'
import { useCalendar } from '@/composables/useCalendar'
import { useCartAnimation } from '@/composables/useCartAnimation'
import CommonPageBanner from '@/components/frontend/CommonPageBanner.vue'
import BookingSteps from '@/components/booking/BookingSteps.vue'
import BookAppointmentBtn from '@/components/frontend/mini-components/BookAppointmentBtn.vue'
import LoginModal from '@/components/auth/LoginModal.vue'
import { useRoute } from 'vue-router'
import BookingSamllBtnSubmit from '@/components/frontend/mini-components/BookingSamllBtnSubmit.vue'
import { toast } from 'vue3-toastify';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()

const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

const route = useRoute()

// Composables
const { execute: apiCall } = useApi()
const { isCustomerAuthenticated, customer, handleCustomerSocialCallback, getCurrentCustomer } = useCustomerAuth()
const { 
  saveBookingData, 
  restoreBookingData, 
  clearBookingData, 
  hasSavedBookingData,
  createAutoSaveWatcher 
} = useBookingPersistence()

const {
  currentStep,
  maxSteps,
  selectedBranch,
  selectedService,
  selectedServices,
  selectedTime,
  branches,
  services,
  customerForm,
  subtotal,
  tax,
  total,
  canProceedToStep2,
  canProceedToStep3,
  isDateLocked,
  isBranchLocked,
  isTimeLocked,
  nextStep,
  previousStep,
  addServiceToCart,
  removeServiceFromCart
} = useBookingFlow()

const {
  selectedDate,
  calendarDays,
  currentMonthYear,
  selectDate,
  previousMonth,
  nextMonth,
  isDateAvailable,
  isCheckingAvailability
} = useCalendar()

// Cart animation composable
const { 
  isAnimating, 
  animationTarget, 
  triggerCartAnimation, 
  triggerRemoveAnimation, 
  triggerCartUpdate,
  triggerValidationError
} = useCartAnimation()

// Local state
const validationErrors = ref({})
const serviceId = ref(null)
const successBookingId = ref(null)
const isAddingToCart = ref(false)

// Template refs for animation
const addToCartButtonRef = ref(null)
const cartSectionRef = ref(null)



// Authentication modal state
const showLoginModal = ref(false)
const authCheckComplete = ref(false)

// Methods
const fetchBranchesAndServices = async () => {
  try {
    const [branchResponse, serviceResponse] = await Promise.all([
      $api('/get-all-branches'),
      $api('/get-service-list')
    ])
    
    branches.value = branchResponse.data.map(branch => ({
      ...branch,
      title: branch.branch_name || branch.name,
      value: branch.id
    }))
    
    services.value = serviceResponse.data.map(service => ({
      ...service,
      title: service.name,
      value: service.id
    }))

    if (serviceId.value) {
      const foundService = services.value.find(s => s.id == serviceId.value || s.value == serviceId.value)
      if (foundService) {
        selectedService.value = foundService
      }
    }

  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const handleBranchSelection = (branchId) => {
  // Only allow branch selection if no services are in cart
  if (selectedServices.value.length > 0) {
    return
  }
  
  // Find the full branch object by ID
  const fullBranch = branches.value.find(branch => branch.id == branchId || branch.value == branchId)
  selectedBranch.value = fullBranch
}

const handleServiceSelection = (serviceId) => {
  // Find the full service object by ID
  const fullService = services.value.find(service => service.id == serviceId || service.value == serviceId)
  selectedService.value = fullService
}


const bookingFormRef = ref(null)
const addToCart = async () => {
  if (!selectedBranch.value || !selectedService.value || !selectedDate.value || !selectedTime.value) {
    validationErrors.value = {
      branch: !selectedBranch.value ? 'Please select a branch' : '',
      service: !selectedService.value ? 'Please select a service' : '',
      date: !selectedDate.value ? 'Please select a date' : '',
      time: !selectedTime.value ? 'Please select a time' : ''
    }
    
    // Show toast notifications for missing fields
    if (!selectedBranch.value) triggerValidationError('branch')
    if (!selectedService.value) triggerValidationError('service')
    if (!selectedDate.value) triggerValidationError('date')
    if (!selectedTime.value) triggerValidationError('time')
    
    return
  }

  // ✅ Use nextTick + ref correctly
  await nextTick()
  if (bookingFormRef.value) {
    bookingFormRef.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }


  
  isAddingToCart.value = true
  validationErrors.value = {}
  
  // Simulate a small delay for better UX
  await new Promise(resolve => setTimeout(resolve, 300))
  
  // Add the selected service to cart - pass selectedDate from calendar
  const result = addServiceToCart(selectedService.value, selectedDate.value)
  
  if (result.success) {
    // Trigger cart animation and show toast notification
    triggerCartAnimation(
      selectedService.value, 
      addToCartButtonRef.value?.$el || addToCartButtonRef.value,
      cartSectionRef.value
    )
  } else {
    // Show error toast for duplicate service
    triggerCartUpdate(result.message, 'warning')
  }
  
  // Reset service selection for next addition (but keep branch, date, and time)
  selectedService.value = null
  isAddingToCart.value = false
}

const removeFromCart = (serviceId) => {
  // Find the service before removing it for the animation
  const serviceToRemove = selectedServices.value.find(s => s.id == serviceId)
  
  // Remove the service from cart
  removeServiceFromCart(serviceId)
  
  // Trigger remove animation and show toast notification
  if (serviceToRemove) {
    triggerRemoveAnimation(serviceToRemove)
  }
}


// Handle date selection with proper async handling
const handleDateSelection = async (day) => {
  const result = await selectDate(day)
  
  if (!result.success) {
    console.log('Date selection failed:', result.message)
    toast(result.message, { type: 'error' })
  } else {
    console.log('Date selected successfully')
    // Optional: Show success message
    // toast('Date selected successfully', { type: 'success' })
  }
}

const handleNextStep = () => {
  // Clear previous validation errors
  validationErrors.value = {}
  
  // Step-specific validation
  if (currentStep.value == 1) {
    const errors = {}
    
    if (!selectedBranch.value) {
      errors.branch = 'Please select a branch'
      toast('Please select a branch', {
        type: 'error'
      })
    }
    
    if (selectedServices.value.length == 0) {
      errors.services = 'Please add at least one service'
      toast('Please add at least one service', {
        type: 'error'
      })
    }
    
    if (!selectedDate.value) {
      errors.date = 'Please select a date'
      toast('Please select a date', {
        type: 'error'
      })
    }
    
    if (!selectedTime.value) {
      errors.time = 'Please select a time'
      toast('Please select a time', {
        type: 'error'
      })
    }
    
    if (Object.keys(errors).length > 0) {
      validationErrors.value = errors
      return
    }

    // Check authentication before moving to step 2
    if (!isCustomerAuthenticated.value) {
      // Save current booking data before showing login modal
      saveBookingData({
        currentStep: currentStep.value,
        selectedBranch: selectedBranch.value,
        selectedServices: selectedServices.value,
        selectedDate: selectedDate.value,
        selectedTime: selectedTime.value,
        customerForm: customerForm.value ?? customer.value
      })
      showLoginModal.value = true
      return
    }
  }
  
  if (currentStep.value == 2) {
    if (!canProceedToStep3.value) {
      const errors = {}
      if (!customerForm.value.name ?? customer.value.name) {
        errors.name = 'Name is required'
      }
      if (!customerForm.value.email ?? customer.value.email) {
        errors.email = 'Email is required'
      } else if (!customerForm.value.email.includes('@') || !customerForm.value.email.includes('.')) {
        errors.email = 'Please enter a valid email'
      }
      if (!customerForm.value.phone ?? customer.value.phone) {
        errors.phone = 'Phone is required'
      } else if ((customerForm.value.phone.length < 6 || isNaN(customerForm.value.phone.replace(/[\s+()-]/g, ''))) ?? (customer.value.phone.length < 6 || isNaN(customer.value.phone.replace(/[\s+()-]/g, '')))) {
        errors.phone = 'Please enter a valid phone number'
      }
      
      validationErrors.value = errors
      return
    }
    
    // Process booking
    handleBooking()
    return
  }
  
  // Move to next step
  nextStep()
}

const handlePreviousStep = () => {
  validationErrors.value = {}
  previousStep()
}

const handleBooking = async () => {
  try {
    // Validate customer data exists
    if ((!customerForm.value.name ?? customer.value.name) || (!customerForm.value.email ?? customer.value.email) || (!customerForm.value.phone ?? customer.value.phone)) {
      validationErrors.value = {
        booking: 'Customer information is missing. Please go back and fill in all required fields.'
      }
      return
    }

    const orderData = {
      customer_name: customerForm.value.name.trim() ?? customer.value.name.trim(),
      customer_email: customerForm.value.email.trim() ?? customer.value.email.trim(),
      customer_phone: customerForm.value.phone.trim() ?? customer.value.phone.trim(),
      customer_address: customerForm.value.address?.trim() || '',
      branch_id: selectedBranch.value.id,
      appointment_date: selectedServices.value[0]?.date || new Date().toISOString().split('T')[0],
      appointment_time: selectedTime.value,
      services: selectedServices.value.map(service => ({
        id: service.id,
        name: service.name,
        price: service.price,
        duration: service.duration,
        time: service.time || selectedTime.value
      })),
      subtotal: parseFloat(subtotal.value),
      tax_amount: parseFloat(tax.value),
      total_amount: parseFloat(total.value),
      payment_method: 'cash' // Default to cash since no payment step
    }
    
    // Create booking
    const response = await $api('/create-booking', {
      method: 'POST',
      body: orderData
    })

    if (response.success) {
      successBookingId.value = response.data
      // Move to step 3 (success)
      currentStep.value = 3
      // Clear booking data after successful booking
      clearBookingData()
    } else {
      validationErrors.value = {
        booking: response.message || 'Booking failed. Please try again.'
      }
    }
  } catch (error) {
    console.error('Booking error:', error)
    validationErrors.value = {
      booking: 'Booking failed. Please try again.'
    }
  }
}

// Authentication-related methods
const saveCurrentBookingData = () => {
  saveBookingData({
    currentStep: currentStep.value,
    selectedBranch: selectedBranch.value,
    selectedServices: selectedServices.value,
    selectedDate: selectedDate.value,
    selectedTime: selectedTime.value,
    customerForm: customerForm.value ?? customer.value
  })
}

const handleLoginSuccess = (userData) => {
  console.log('Login successful:', userData)
  
  // Pre-fill customer form with user data
  if (userData) {
    customerForm.value.name = userData.name ?? customer.value.name
    customerForm.value.email = userData.email ?? customer.value.email
    customerForm.value.phone = userData.phone ?? customer.value.phone
    customerForm.value.address = userData.address ?? customer.value.address
  }

  // Move to step 2
  nextStep()
  showLoginModal.value = false
  
  // Clear saved booking data as it's no longer needed
  clearBookingData()
}

const handleLoginModalClose = () => {
  showLoginModal.value = false
}

const handleSocialLoginRedirect = (provider) => {
  // Social login will redirect, so we need to preserve data
  saveCurrentBookingData()
}

const initializeUserData = () => {
  // Check if customer is authenticated and pre-fill form
  if (isCustomerAuthenticated.value) {
    const currentCustomer = getCurrentCustomer()
    if (currentCustomer) {
      customerForm.value.name = currentCustomer.name ?? customer.value.name
      customerForm.value.email = currentCustomer.email ?? customer.value.email
      customerForm.value.phone = currentCustomer.phone ?? customer.value.phone
      customerForm.value.address = currentCustomer.address ?? customer.value.address
    }
  }
  
  // Check for saved booking data and restore if available
  if (hasSavedBookingData()) {
    const bookingFlowRefs = {
      currentStep,
      selectedBranch,
      selectedServices,
      selectedDate,
      selectedTime,
      customerForm: customerForm.value ?? customer.value
    }
    
    const restored = restoreBookingData(bookingFlowRefs)
    if (restored) {
      console.log('Booking data restored from previous session')
      // If customer is now authenticated and we're on step 1, we can proceed to step 2
      if (isCustomerAuthenticated.value && currentStep.value == 1) {
        nextStep()
      }
    }
  }
  
  authCheckComplete.value = true
}

// Watch for authentication state changes
watch(isCustomerAuthenticated, (newValue) => {
  if (newValue && authCheckComplete.value) {
    // Customer just logged in, initialize their data
    initializeUserData()
  }
})
watch(
  () => route.query.service_id,
  (newVal) => {
    if (!newVal) return 

    let id = null
    try {
      id = atob(newVal)
    } catch (e) {
      console.warn("Invalid service_id in URL")
      return
    }

    if (id && services.value.length) {
      const foundService = services.value.find(
        (s) => s.id == id || s.value == id
      )
      if (foundService) {
        selectedService.value = foundService
      }
    }
  },
  { immediate: true } // optional: run on component mount
)
onMounted(async () => {
  // Handle social login callback first
  const socialResult = handleCustomerSocialCallback()
  if (socialResult.success) {
    console.log('Customer social login successful')
  }

  if(route.query.service_id) {
    const service_query = atob(route.query.service_id)
    if (service_query) {
      serviceId.value = service_query
    }
  }
  
  await fetchBranchesAndServices()
  
  // Initialize user data and restore booking data if available
  initializeUserData()
  
  // Set up auto-save watcher for booking data
  const bookingFlowRefs = {
    currentStep,
    selectedBranch,
    selectedServices,
    selectedDate,
    selectedTime,
    customerForm: customerForm.value ?? customer.value
  }
  createAutoSaveWatcher(bookingFlowRefs)
})

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})
</script>

<template>
  <div>
    <!-- Common Page Banner -->
    <CommonPageBanner :title="t('Appointment Details')" :breadcrumb="t('Appointment Details')" />

    <!-- Calendar Section -->
    <section class="calendar-booking-section default-section-padding-t" ref="bookingFormRef">
      <div class="container">
        <!-- Booking Steps -->
        <div class="row justify-content-center">
          <div class="col-xl-7 col-lg-9 col-md-10 col-sm-12">
            <BookingSteps :current-step="currentStep" :max-steps="maxSteps" />
          </div>
        </div>

        <!-- Error Messages -->
        <div v-if="validationErrors.booking" class="row justify-content-center mb-4">
          <div class="col-lg-8">
            <VAlert type="error" variant="tonal">
              {{ validationErrors.booking }}
            </VAlert>
          </div>
        </div>



        <div class="appointment-booking-form">
          <!-- Booking Step 1 - Service Selection -->
          <div v-show="currentStep == 1" class="row">
            <div class="col-lg-6 col-md-12">
              <div class="calendar-card">
                <div class="row">
                  <div class="col-md-6">
                    <div class="service-title-selection">
                      <!-- Branch Selection -->
                      <h6 class="branch-title">{{ t('Branch') }} <span class="required-star">*</span></h6>
                      <AppAutocomplete 
                        :model-value="selectedBranch?.id"
                        class="branch-selection" 
                        :items="branches" 
                        :placeholder="t('Select a branch')"
                        :error="!!validationErrors.branch"
                        :error-messages="validationErrors.branch"
                        :disabled="isBranchLocked"
                        @update:model-value="handleBranchSelection"
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="service-title-selection">
                      <!-- Service Selection -->
                      <h6 class="service-title">{{ t('Service') }} <span class="required-star">*</span></h6>
                      <AppAutocomplete 
                        :model-value="selectedService?.id"
                        class="service-selection" 
                        :items="services" 

                        :placeholder="t('Select a service')"
                        :error="!!validationErrors.service"
                        :error-messages="validationErrors.service"
                        @update:model-value="handleServiceSelection"
                      />
                    </div>
                  </div>
                </div>

                <!-- Calendar -->
                <div class="calendar-container">
                  <div class="calendar-header">
                    <h5 class="calendar-title">{{ currentMonthYear }}</h5>
                    <div class="calendar-nav">
                      <button class="btn btn-sm btn-outline-secondary" @click="previousMonth">
                        <VIcon icon="tabler-arrow-narrow-left" size="20" />
                      </button>
                      <button class="btn btn-sm btn-outline-secondary" @click="nextMonth">
                        <VIcon icon="tabler-arrow-narrow-right" size="20" />
                      </button>
                    </div>
                  </div>

                  <div class="calendar-grid">
                    <div class="calendar-weekdays">
                      <div class="weekday">Mon</div>
                      <div class="weekday">Tue</div>
                      <div class="weekday">Wed</div>
                      <div class="weekday">Thu</div>
                      <div class="weekday">Fri</div>
                      <div class="weekday">Sat</div>
                      <div class="weekday">Sun</div>
                    </div>
                    <div class="calendar-days">
                      <div v-for="day in calendarDays" :key="day.date" 
                          class="calendar-day" 
                          :class="{ 
                            'selected': day.selected,
                            'other-month': day.otherMonth,
                            'available': day.available,
                            'disabled': (!day.available || isDateLocked || isCheckingAvailability) && !day.selected,
                            'loading': isCheckingAvailability
                          }"
                          @click="day.available && !isDateLocked && !isCheckingAvailability && handleDateSelection(day)">
                        {{ day.day }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Time Selection -->
                <div class="time-selection mt-3">
                  <h6 class="time-title">{{ t('Time') }} <span class="required-star">*</span></h6>
                  <select 
                    v-model="selectedTime" 
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.time }"
                    :disabled="isTimeLocked"
                  >
                    <option value="">{{ t('Select a time') }}</option>
                    <option value="09:00">9:00 AM</option>
                    <option value="09:30">9:30 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="10:30">10:30 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="12:00">12:00 PM</option>
                    <option value="12:30">12:30 PM</option>
                    <option value="13:00">1:00 PM</option>
                    <option value="13:30">1:30 PM</option>
                    <option value="14:00">2:00 PM</option>
                    <option value="14:30">2:30 PM</option>
                    <option value="15:00">3:00 PM</option>
                    <option value="15:30">3:30 PM</option>
                    <option value="16:00">4:00 PM</option>
                    <option value="16:30">4:30 PM</option>
                    <option value="17:00">5:00 PM</option>
                    <option value="17:30">5:30 PM</option>
                    <option value="18:00">6:00 PM</option>
                  </select>
                  <div v-if="validationErrors.time" class="invalid-feedback d-block">
                    {{ validationErrors.time }}
                  </div>
                </div>
                <div class="mt-4">
                  <BookingSamllBtnSubmit 
                  @click="addToCart" 
                  :disabled="!selectedBranch || !selectedService || !selectedDate || !selectedTime || isAddingToCart" 
                  :text="t('Add To Cart')" 
                  />
                </div>
              </div>
            </div>

            <!-- Selected Services Section -->
            <div class="col-lg-6 col-md-12">
              <div ref="cartSectionRef" class="selected-services-card" :class="{ 'cart-animation': isAnimating }">
                <h5 v-if="selectedServices.length !== 0" class="services-title">{{ t('Your Selected Services') }}</h5>
                
                <div v-if="selectedServices.length == 0" class="empty-cart text-center py-4">
                  <VIcon size="48" color="grey-lighten-2" icon="tabler-shopping-cart-off" />
                  <p class="text-grey mt-2">{{ t('No services selected') }}</p>
                  <small class="text-muted">
                    {{ t('Select a branch, choose a service, pick a date, then click "Add To Cart".') }}<br>
                    {{ t('You can add multiple services for the same date and time!') }}
                  </small>
                </div>

                <div v-else>
                  <!-- Services Table -->
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>{{ t('SN') }}</th>
                          <th>{{ t('Service Name') }}</th>
                          <th class="text-center">{{ t('Price') }}</th>
                          <th class="text-center">{{ t('Action') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(service, index) in selectedServices" :key="service.id" class="service-row">
                          <td>{{ index + 1 }}</td>
                          <td>
                            <div>
                              <strong>{{ service.name || 'Service Name Not Available' }}</strong>
                              <br>
                              <small class="text-muted">{{ t('Duration') }}: {{ service.duration || '1hr' }}</small>
                            </div>
                          </td>
                          <td class="text-center">{{ formatAmount(service.price || 0) }}</td>
                          <td class="text-center">
                            <button 
                              class="btn btn-sm remove-service-btn"
                              @click="removeFromCart(service.id)"
                              :title="t('Remove service')"
                            >
                              <VIcon icon="tabler-trash" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
          
                <!-- Step 1 Next and Previous Button -->
                <div v-if="selectedServices.length !== 0" class="button-group d-flex justify-content-end align-items-end next-pre-section-1">
                  <BookingSamllBtnSubmit 
                  @click="handleNextStep" 
                  :disabled="!canProceedToStep2(selectedDate)" :text="t('Next')" 
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Step 2 - Customer Details -->
          <div v-show="currentStep == 2" class="row details-section">
            <!-- Welcome message for authenticated customers -->
            <div v-if="isCustomerAuthenticated" class="col-12 mb-4">
              <div class="alert alert-success d-flex align-items-center">
                <VIcon icon="tabler-user-check" size="20" class="me-2" />
                <span>{{ t('Welcome back') }}, {{ customer?.name || 'Customer' }}! {{ t('Your information has been pre-filled.') }}</span>
              </div>
            </div>
            
            <div class="col-lg-4 mb-3">
              <div class="form-group">
                <label for="name">{{ t('Name') }} <span class="required-star">*</span></label>
                <input
                  :value="customerForm.name ?? customer.name"
                  @input="customerForm.name = $event.target.value"
                  type="text"
                  class="form-control"
                  id="name"
                  :placeholder="t('Enter your name')"
                  :class="{ 'is-invalid': currentStep == 2 && validationErrors.name }"
                />
                <div v-if="currentStep == 2 && validationErrors.name" class="invalid-feedback">
                  {{ validationErrors.name }}
                </div>
              </div>
            </div>
            <div class="col-lg-4 mb-3">
              <div class="form-group">
                <label for="email">{{ t('Email') }} <span class="required-star">*</span></label>
                <input 
                  :value="customerForm.email ?? customer.email"
                  @input="customerForm.email = $event.target.value"
                  type="email" 
                  class="form-control" 
                  id="email" 
                  :placeholder="t('Enter your email')"
                  :class="{ 'is-invalid': currentStep == 2 && validationErrors.email }"
                >
                <div v-if="currentStep == 2 && validationErrors.email" class="invalid-feedback">
                  {{ validationErrors.email }}
                </div>
              </div>
            </div>
            <div class="col-lg-4 mb-3">
              <div class="form-group">
                <label for="phone">{{ t('Phone') }} <span class="required-star">*</span></label>
                <input 
                  :value="customerForm.phone ?? customer.phone"
                  @input="customerForm.phone = $event.target.value"
                  type="text" 
                  class="form-control" 
                  id="phone" 
                  :placeholder="t('Enter your phone')"
                  :class="{ 'is-invalid': currentStep == 2 && validationErrors.phone }"
                >
                <div v-if="currentStep == 2 && validationErrors.phone" class="invalid-feedback">
                  {{ validationErrors.phone }}
                </div>
              </div>
            </div>
            <div class="col-lg-8 mb-3">
              <div class="form-group">
                <label for="address">{{ t('Address') }}</label>
                <textarea 
                  :value="customerForm.address ?? customer.address"
                  @input="customerForm.address = $event.target.value"
                  rows="5" 
                  name="address" 
                  id="address" 
                  class="form-control" 
                  :placeholder="t('Enter your address')"
                ></textarea>
              </div>
            </div>
            <div class="col-lg-4 mb-3 d-flex justify-content-end align-items-end">
              <div class="button-group">
                <button 
                  class="btn btn-previous"
                  @click="handlePreviousStep"
                >
                  <span><VIcon size="22" icon="tabler-arrow-narrow-left" /></span>
                  {{ t('Previous') }}
                </button>

                <BookingSamllBtnSubmit 
                  @click="handleNextStep" 
                  :disabled="!canProceedToStep3" :text="t('Next')" 
                />
              </div>
            </div>
          </div>

          <!-- Booking Step 3 - Success -->
          <div v-show="currentStep == 3" class="row success-section">
            <div class="col-lg-12">
              <div class="success-card"> 
                <div class="text-center">
                  <div class="success-icon">
                    <VIcon icon="tabler-circle-check" size="60" color="success" />
                  </div>
                  <h2 class="success-title">{{ t('Booking Submitted!') }}</h2>
                  <p class="success-message">
                    {{ t('Your appointment has been successfully booked. currently your booking is pending.') }} <br> {{ t('you will get a confirmation email shortly.') }}
                  </p>
                </div>
                
                <div class="booking-details-section">
                  <div class="booking-details">
                    <div class="detail-card">
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Booking ID') }}</span>:
                        <span class="detail-value">{{ successBookingId || 'N/A' }}</span>
                      </div>
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Branch') }}</span>:
                        <span class="detail-value">{{ selectedBranch?.branch_name || selectedBranch?.name || 'N/A' }}</span>
                      </div>
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Date') }}</span>:
                        <span class="detail-value">{{ selectedServices[0]?.date ? new Date(selectedServices[0].date).toLocaleDateString() : 'N/A' }}</span>
                      </div>
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Time') }}</span>:
                        <span class="detail-value">{{ selectedTime || 'N/A' }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="booking-details">
                    <div class="detail-card">
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Customer') }}</span>:
                        <span class="detail-value">{{ customerForm.name ?? customer.name }}</span>
                      </div>
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Email') }}</span>:
                        <span class="detail-value">{{ customerForm.email ?? customer.email }}</span>
                      </div>
                      <div class="detail-row">
                        <span class="detail-label">{{ t('Phone') }}</span>:
                        <span class="detail-value">{{ customerForm.phone ?? customer.phone }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="services-summary">
                  <h5>{{ t('Services Booked') }}</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>{{ t('SN') }}</th>
                        <th>{{ t('Service Name') }}</th>
                        <th class="text-center">{{ t('Date') }}</th>
                        <th class="text-center">{{ t('Time') }}</th>
                        <th class="text-center">{{ t('Price') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(service, index) in selectedServices" :key="service.id">
                        <td>{{ index + 1 }}</td>
                        <td>{{ service.name }}</td>
                        <td class="text-center">{{ formatDate(service.date) }}</td>
                        <td class="text-center">{{ service.time || selectedTime }}</td>
                        <td class="text-center">{{ formatAmount(service.price || 0) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="success-actions mt-4 text-center">
                  <BookAppointmentBtn :text="t('Go to Homepage')" :link="'/'" @click="window.location.href = '/'"/>
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
      @social-login="handleSocialLoginRedirect"
    />
  </div>
</template>

<style scoped>
/* Calendar disabled state */
.calendar-day.disabled {
  cursor: not-allowed;
}

.calendar-day.disabled:hover {
  background-color: #f8f9fa;
  transform: none;
}


/* Time selection styles */
.time-selection {
  margin-top: 1rem;
}

.time-title {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #333;
}

.time-selection select {
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 10px 12px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.time-selection select:focus {
  outline: none;
  border-color: var(--primary-bg-color);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.time-selection select:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Booking info styles */
.booking-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  border-left: 4px solid var(--primary-bg-color);
}

.booking-date-time {
  font-size: 14px;
  line-height: 1.6;
}

/* Table styles */
.table {
  margin-bottom: 0;
}

.table th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #333;
}

.table td {
  vertical-align: middle;
  border-bottom: 1px solid #dee2e6;
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: rgba(0, 0, 0, 0.02);
}

/* Service name styling */
.service-name {
  font-weight: 500;
  color: #333;
}

/* Action button styling */
.btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
  transition: all 0.2s ease;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
  transform: translateY(-1px);
}

/* Cart Animation Styles */
.cart-animation {
  animation: cartPulse 0.8s ease-in-out;
  animation-delay: 0.4s;
}

.btn-animation {
  animation: buttonPulse 0.6s ease-in-out;
}

.btn-loading {
  opacity: 0.7;
  cursor: not-allowed;
  position: relative;
}

.btn-loading::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  margin: auto;
  border: 2px solid transparent;
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
}

@keyframes spin {
  0% { transform: translateY(-50%) rotate(0deg); }
  100% { transform: translateY(-50%) rotate(360deg); }
}

@keyframes cartPulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(9, 141, 156, 1);
  }
  25% {
    transform: scale(1.05);
    box-shadow: 0 0 0 15px rgba(9, 141, 156, 1);
  }
  50% {
    transform: scale(1.02);
    box-shadow: 0 0 0 10px rgba(9, 141, 156, 1);
  }
  75% {
    transform: scale(1.03);
    box-shadow: 0 0 0 5px rgba(9, 141, 156, 1);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(9, 141, 156, 1);
  }
}

@keyframes buttonPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

/* Service row animation for removal */
.table tbody tr {
  transition: all 0.3s ease;
}

.table tbody tr.removing {
  opacity: 0;
  transform: translateX(-20px);
}

/* Service row animation for addition */
.service-row {
  animation: slideInFromRight 0.5s ease-out;
}

@keyframes slideInFromRight {
  0% {
    opacity: 0;
    transform: translateX(30px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Highlight new service row */
.service-row.highlight {
  background-color: rgba(9, 141, 156, 1);
  animation: highlightPulse 1s ease-in-out;
}

@keyframes highlightPulse {
  0% {
    background-color: rgba(9, 141, 156, 1);
  }
  100% {
    background-color: rgba(9, 141, 156, 1);
  }
}

/* Add to cart button hover effect */
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(9, 141, 156, 1);
  transition: all 0.3s ease;
}

/* Remove button hover effect */
.remove-service-btn {
  transition: all 0.2s ease;
}

.remove-service-btn:hover {
  transform: scale(1.1);
  background-color: #dc3545 !important;
  color: white !important;
}

/* Flying Item Animation Styles */
.flying-item {
  position: fixed;
  z-index: 9999;
  pointer-events: none;
  transform: translate(-50%, -50%);
}

.flying-item-content {
  background: linear-gradient(135deg, #098d9c, #098d9c);
  color: white;
  padding: 8px 12px;
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(9, 141, 156, 1), 0 0 20px rgba(9, 141, 156, 1);
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  border: 2px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.flying-item-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: shimmer 1s ease-in-out;
}

@keyframes shimmer {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}

.flying-item-icon {
  font-size: 14px;
}

.flying-item-text {
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Flying item entrance animation */
.flying-item {
  animation: flyingItemEntrance 0.1s ease-out;
}

@keyframes flyingItemEntrance {
  0% {
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
  }
  100% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
  }
}

/* Curved flying animation */
@keyframes flyingItemCurved {
  0% {
    left: var(--start-x);
    top: var(--start-y);
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
  }
  50% {
    left: var(--control-x);
    top: var(--control-y);
    transform: translate(-50%, -50%) scale(1.2);
    opacity: 0.8;
  }
  100% {
    left: var(--end-x);
    top: var(--end-y);
    transform: translate(-50%, -50%) scale(0.3);
    opacity: 0;
  }
}
</style>