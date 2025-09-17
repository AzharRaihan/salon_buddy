import { ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'

/**
 * Creates a form object with validation and state management
 * @param {string} formName - Name of the form for identification
 * @param {Object} initialData - Initial form data
 * @param {Object} validationRules - Validation rules for the form
 * @returns {Object} Form object with methods and reactive data
 */
export function createPOSForm(formName, initialData = {}, validationRules = {}) {
  const formData = reactive({ ...initialData })
  const errors = ref({})
  const isSubmitting = ref(false)
  const originalData = { ...initialData }
  const { t } = useI18n()

  // Computed property for form validity
  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0 && 
           Object.keys(validationRules).every(field => {
             const value = formData[field]
             const rules = validationRules[field]
             
             // Check required fields
             if (rules?.required && (!value || value.toString().trim() === '')) {
               return false
             }
             
             return true
           })
  })

  // Validate a single field
  const validateField = (field, value) => {

    const rules = validationRules[field]
    if (!rules) return true

    const fieldErrors = []

    // Required validation
    if (rules.required && (!value || value.toString().trim() === '')) {
      fieldErrors.push(`${rules.label || field} ${t('is required')}`)
    }

    // Email validation
    if (rules.email && value && !isValidEmail(value)) {
      fieldErrors.push(t('Please enter a valid email address'))
    }

    // Phone validation
    if (rules.phone && value && !isValidPhone(value)) {
      fieldErrors.push(t('Please enter a valid phone number'))
    }

    // Min length validation
    if (rules.minLength && value && value.length < rules.minLength) {
      fieldErrors.push(`${rules.label || field} ${t('must be at least')} ${rules.minLength} ${t('characters')}`)
    }

    // Max length validation
    if (rules.maxLength && value && value.length > rules.maxLength) {
      fieldErrors.push(`${rules.label || field} ${t('must not exceed')} ${rules.maxLength} ${t('characters')}`)
    }

    // Custom validation
    if (rules.custom && value) {
      const customResult = rules.custom(value)
      if (customResult !== true) {
        fieldErrors.push(customResult)
      }
    }

    if (fieldErrors.length > 0) {
      errors.value[field] = fieldErrors
    } else {
      delete errors.value[field]
    }

    return fieldErrors.length === 0
  }

  // Validate entire form
  const validateForm = () => {
    errors.value = {}
    let isFormValid = true

    Object.keys(validationRules).forEach(field => {
      const value = formData[field]
      const fieldValid = validateField(field, value)
      if (!fieldValid) {
        isFormValid = false
      }
    })

    return isFormValid
  }

  // Update a single field
  const updateField = (field, value) => {
    formData[field] = value
    validateField(field, value)
  }

  // Update multiple fields
  const updateFields = (data) => {
    Object.keys(data).forEach(field => {
      formData[field] = data[field]
      validateField(field, data[field])
    })
  }

  // Reset form to initial state
  const resetForm = () => {
    Object.keys(originalData).forEach(field => {
      formData[field] = originalData[field]
    })
    errors.value = {}
  }

  // Handle form submission
  const handleSubmit = async (callback) => {
    if (!validateForm()) {
      return false
    }

    isSubmitting.value = true
    try {
      const result = await callback(formData)
      return result
    } catch (error) {
      console.error(`Error submitting ${formName}:`, error)
      throw error
    } finally {
      isSubmitting.value = false
    }
  }

  // Clear field error
  const clearFieldError = (field) => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }

  // Clear all errors
  const clearAllErrors = () => {
    errors.value = {}
  }

  // Helper functions
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  const isValidPhone = (phone) => {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
    return phoneRegex.test(phone.replace(/\s/g, ''))
  }

  return {
    formData,
    errors,
    isValid,
    isSubmitting,
    updateField,
    updateFields,
    resetForm,
    handleSubmit,
    validateField,
    validateForm,
    clearFieldError,
    clearAllErrors
  }
}

/**
 * Composable for form validation
 * Provides reusable validation logic for forms
 */
export function useFormValidation() {
  const { t } = useI18n()
  const errors = ref({})
  const isValid = ref(false)

  // Clear validation errors for a specific field
  const clearFieldError = (field) => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
    updateValidationState()
  }

  // Clear all validation errors
  const clearAllErrors = () => {
    errors.value = {}
    updateValidationState()
  }

  // Validate a single field
  const validateField = (field, value, rules = {}) => {
    const fieldErrors = []

    // Required validation
    if (rules.required && (!value || value.trim() === '')) {
      fieldErrors.push(`${rules.label || field} ${t('is required')}`)
    }

    // Email validation
    if (rules.email && value && !isValidEmail(value)) {
      fieldErrors.push(t('Please enter a valid email address'))
    }

    // Phone validation
    if (rules.phone && value && !isValidPhone(value)) {
      fieldErrors.push(t('Please enter a valid phone number'))
    }

    // Min length validation
    if (rules.minLength && value && value.length < rules.minLength) {
      fieldErrors.push(`${rules.label || field} ${t('must be at least')} ${rules.minLength} ${t('characters')}`)
    }

    // Max length validation
    if (rules.maxLength && value && value.length > rules.maxLength) {
      fieldErrors.push(`${rules.label || field} ${t('must not exceed')} ${rules.maxLength} ${t('characters')}`)
    }

    // Custom validation
    if (rules.custom && value) {
      const customResult = rules.custom(value)
      if (customResult !== true) {
        fieldErrors.push(customResult)
      }
    }

    if (fieldErrors.length > 0) {
      errors.value[field] = fieldErrors
    } else {
      delete errors.value[field]
    }

    updateValidationState()
    return fieldErrors.length === 0
  }

  // Validate entire form
  const validateForm = (formData, validationRules) => {
    clearAllErrors()
    let isFormValid = true

    Object.keys(validationRules).forEach(field => {
      const value = formData[field]
      const rules = validationRules[field]
      const fieldValid = validateField(field, value, rules)
      if (!fieldValid) {
        isFormValid = false
      }
    })

    return isFormValid
  }

  // Show validation errors in DOM
  const showValidationErrors = () => {
    Object.keys(errors.value).forEach(field => {
      const element = document.getElementById(field)
      if (element) {
        element.classList.add('is-invalid')
        // Add error message
        let errorDiv = element.parentNode.querySelector('.invalid-feedback')
        if (!errorDiv) {
          errorDiv = document.createElement('div')
          errorDiv.className = 'invalid-feedback'
          element.parentNode.appendChild(errorDiv)
        }
        errorDiv.textContent = errors.value[field][0] // Show first error
      }
    })
  }

  // Clear validation errors from DOM
  const clearValidationErrors = (field) => {
    if (field) {
      // Clear specific field
      const element = document.getElementById(field)
      if (element) {
        element.classList.remove('is-invalid')
        const errorDiv = element.parentNode.querySelector('.invalid-feedback')
        if (errorDiv) {
          errorDiv.remove()
        }
      }
    } else {
      // Clear all fields
      Object.keys(errors.value).forEach(fieldName => {
        const element = document.getElementById(fieldName)
        if (element) {
          element.classList.remove('is-invalid')
          const errorDiv = element.parentNode.querySelector('.invalid-feedback')
          if (errorDiv) {
            errorDiv.remove()
          }
        }
      })
    }
  }

  // Update overall validation state
  const updateValidationState = () => {
    isValid.value = Object.keys(errors.value).length === 0
  }

  // Helper functions
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  const isValidPhone = (phone) => {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
    return phoneRegex.test(phone.replace(/\s/g, ''))
  }

  return {
    errors,
    isValid,
    clearFieldError,
    clearAllErrors,
    validateField,
    validateForm,
    showValidationErrors,
    clearValidationErrors
  }
}