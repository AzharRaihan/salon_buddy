import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export const useBookingFlow = () => {
  const currentStep = ref(1)
  const maxSteps = 3 // Reduced from 4 to 3 (removed payment step)

  // Step 1 - Service Selection
  const selectedBranch = ref(null)
  const selectedService = ref(null)
  const selectedServices = ref([])
  const selectedTime = ref('')
  const branches = ref([])
  const services = ref([])

  // Step 2 - Customer Details
  const customerForm = ref({
    name: '',
    email: '',
    phone: '',
    address: ''
  })

  // Calculations
  const subtotal = computed(() => {
    return selectedServices.value.reduce((total, service) => {
      return total + (parseFloat(service.price) || 0)
    }, 0)
  })

  const tax = computed(() => {
    return subtotal.value * 0.1 // 10% tax
  })

  const total = computed(() => {
    return subtotal.value + tax.value
  })

  // Step Navigation - selectedDate will be passed as parameter
  const canProceedToStep2 = (selectedDate) => {
    return selectedBranch.value && 
           selectedServices.value.length > 0 && 
           selectedDate && 
           selectedTime.value &&
           selectedTime.value.trim() !== ''
  }

  const canProceedToStep3 = computed(() => {
    return customerForm.value.name && 
           customerForm.value.email && 
           customerForm.value.phone &&
           customerForm.value.email.includes('@') && 
           customerForm.value.email.includes('.') &&
           customerForm.value.phone.length >= 6
  })

  // Check if date is locked (services already added)
  const isDateLocked = computed(() => {
    return selectedServices.value.length > 0
  })

  // Check if branch is locked (services already added)
  const isBranchLocked = computed(() => {
    return selectedServices.value.length > 0
  })

  // Check if time is locked (services already added)
  const isTimeLocked = computed(() => {
    return selectedServices.value.length > 0
  })

  // Methods
  const nextStep = () => {
    if (currentStep.value < maxSteps) {
      currentStep.value++
      return true
    }
    return false
  }

  const previousStep = () => {
    if (currentStep.value > 1) {
      currentStep.value--
      return true
    }
    return false
  }

  const addServiceToCart = (service, selectedDate) => {
    // Check if service already exists
    const existingService = selectedServices.value.find(s => s.id === service.id)
    if (existingService) {
      return { success: false, message: 'Service already in cart' }
    }

    // Check if this is the first service or if it's the same date
    if (selectedServices.value.length === 0) {
      // First service - use the selected date and time
      let dateString = null
      if (selectedDate) {
        if (selectedDate instanceof Date) {
          dateString = selectedDate.toISOString().split('T')[0]
        } else {
          // If it's a string, try to parse it
          try {
            const dateObj = new Date(selectedDate)
            dateString = dateObj.toISOString().split('T')[0]
          } catch (e) {
            console.error('Invalid date format:', selectedDate)
            return { success: false, message: 'Invalid date format' }
          }
        }
      }
      
      const serviceToAdd = {
        id: service.id,
        name: service.name,
        price: parseFloat(service.price) || parseFloat(service.sale_price) || 0,
        duration: service.duration || '1hr',
        category: service.category || 'General',
        date: dateString,
        time: selectedTime.value
      }
      selectedServices.value.push(serviceToAdd)
      return { success: true, message: 'Service added successfully' }
    } else {
      // Add service with the same date as existing services
      const existingDate = selectedServices.value[0].date
      const serviceToAdd = {
        id: service.id,
        name: service.name,
        price: parseFloat(service.price) || parseFloat(service.sale_price) || 0,
        duration: service.duration || '1hr',
        category: service.category || 'General',
        date: existingDate, // Use same date as existing services
        time: selectedTime.value
      }
      selectedServices.value.push(serviceToAdd)
      return { success: true, message: 'Service added successfully' }
    }
  }

  const removeServiceFromCart = (serviceId) => {
    const index = selectedServices.value.findIndex(s => s.id === serviceId)
    if (index > -1) {
      selectedServices.value.splice(index, 1)
    }
  }

  const resetBooking = () => {
    currentStep.value = 1
    selectedBranch.value = null
    selectedService.value = null
    selectedServices.value = []
    selectedTime.value = ''
    customerForm.value = {
      name: '',
      email: '',
      phone: '',
      address: ''
    }
  }

  return {
    // State
    currentStep,
    maxSteps,
    selectedBranch,
    selectedService,
    selectedServices,
    selectedTime,
    branches,
    services,
    customerForm,
    
    // Computed
    subtotal,
    tax,
    total,
    canProceedToStep2,
    canProceedToStep3,
    isDateLocked,
    isBranchLocked,
    isTimeLocked,
    
    // Methods
    nextStep,
    previousStep,
    addServiceToCart,
    removeServiceFromCart,
    resetBooking
  }
}