import { reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useFormValidation } from './useFormValidation'
import { useI18n } from 'vue-i18n'

/**
 * Composable for checkout form handling
 * Manages customer form data and validation
 */
export function useCheckoutForm() {
  const router = useRouter()
  const { validateForm, showValidationErrors, clearValidationErrors } = useFormValidation()

  const { t } = useI18n()

  // Form data
  const customerForm = reactive({
    f_name: '',
    l_name: '',
    email: '',
    phone: '',
    street: '',
    address: '',
    zip: '',
    notes: ''
  })

  // Validation rules
  const validationRules = {
    f_name: { required: true, label: t('First Name') },
    email: { required: true, label: t('Email Address') },
    phone: { required: true, label: t('Phone number') },
    street: { required: true, label: t('Street/Town') },
    address: { required: true, label: t('Address') },
    zip: { required: true, label: t('Zip Code') }
  }

  // Check if form is valid
  const isFormValid = computed(() => {
    return customerForm.f_name && customerForm.email && customerForm.phone && 
           customerForm.street && customerForm.address && customerForm.zip
  })

  // Pre-fill form with customer data
  const prefillCustomerData = (customer) => {
    if (customer) {
      customerForm.f_name = customer.name?.split(' ')[0] || ''
      customerForm.l_name = customer.name?.split(' ').slice(1).join(' ') || ''
      customerForm.email = customer.email || ''
      customerForm.phone = customer.phone || ''
      customerForm.address = customer.address || ''
    }
  }

  // Validate and proceed to payment
  const proceedToPayment = () => {
    // Validate form
    const isValid = validateForm(customerForm, validationRules)
    
    if (!isValid) {
      showValidationErrors()
      return false
    }

    // Save customer form data to localStorage for payment page
    localStorage.setItem('checkout_customer_data', JSON.stringify(customerForm))
    
    // Navigate to payment page
    router.push('/payment')
    return true
  }

  // Clear validation errors when user starts typing
  const handleInput = (field) => {
    clearValidationErrors(field)
  }

  // Reset form
  const resetForm = () => {
    Object.keys(customerForm).forEach(key => {
      customerForm[key] = ''
    })
    clearValidationErrors()
  }

  return {
    customerForm,
    isFormValid,
    prefillCustomerData,
    proceedToPayment,
    handleInput,
    resetForm
  }
}
