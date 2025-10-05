import { ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { toast } from 'vue3-toastify'

/**
 * Composable for managing website policy forms (Terms & Conditions, Privacy Policy)
 * Provides reusable form logic for policy management
 */
export function useWebsitePolicyForm(policyType = 'terms_and_conditions') {
  const { t } = useI18n()
  
  // Reactive state
  const loading = ref(false)
  const editorContent = ref('')
  const form = reactive({
    [policyType]: '',
  })
  
  const errors = ref({})
  
  // Computed properties
  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0 && 
           form[policyType] && 
           form[policyType].trim() !== ''
  })
  
  const isDirty = computed(() => {
    return form[policyType] !== editorContent.value
  })
  
  // API endpoints based on policy type
  const endpoints = {
    terms_and_conditions: {
      fetch: '/website-terms-and-conditions',
      update: '/website-terms-and-conditions-update'
    },
    privacy_policy: {
      fetch: '/website-privacy-policy',
      update: '/website-privacy-policy-update'
    }
  }
  
  const currentEndpoints = endpoints[policyType] || endpoints.terms_and_conditions

  
  /**
   * Fetch policy data from API
   */
  const fetchPolicy = async () => {
    try {
      loading.value = true
      
      const response = await $api(currentEndpoints.fetch)
      
      if (response && response.success && response.data) {
        const data = response.data
        form[policyType] = data[policyType] || ''
        editorContent.value = form[policyType]
      }
    } catch (err) {
      console.error(`Error fetching ${policyType}:`, err)
      toast(t('Error loading policy data'), { type: 'error' })
    } finally {
      loading.value = false
    }
  }
  
  /**
   * Validate form data
   */
  const validateForm = () => {
    errors.value = {}
    let isValid = true

    const value = String(form[policyType] ?? '').trim()
    
    if (!value) {
      errors.value[policyType] = t('Policy content is required')
      isValid = false
    }
  
    return isValid
  }
  
  /**
   * Save policy data
   */
  const savePolicy = async () => {
    if (!validateForm()) {
      toast(t('Please fill all required fields correctly'), {
        type: 'error'
      })
      return false
    }
    
    try {
      loading.value = true
      const formData = new FormData()
      formData.append(policyType, form[policyType])
      
      const response = await $api(currentEndpoints.update, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
        },
      })
      
      if (response.success) {
        toast(t('Policy updated successfully'), {
          type: 'success'
        })
        await fetchPolicy() // Refresh data after update
        return true
      } else {
        throw new Error(response.message || t('Failed to update policy'))
      }
    } catch (err) {
      console.error(`Error updating ${policyType}:`, err)
      toast(err.message || t('Error updating policy'), {
        type: 'error'
      })
      return false
    } finally {
      loading.value = false
    }
  }
  
  /**
   * Reset form to original state
   */
  const resetForm = () => {
    fetchPolicy()
    errors.value = {}
  }
  
  /**
   * Handle editor input changes
   */
  const handleEditorInput = (content) => {
    // Extract the actual HTML content if it's an event object
    const htmlContent = content.target?.innerHTML || content
    
    form[policyType] = htmlContent
    editorContent.value = htmlContent
    // Clear field error when user starts typing
    if (errors.value[policyType]) {
      delete errors.value[policyType]
    }
  }
  
  /**
   * Clear specific field error
   */
  const clearFieldError = (field) => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }
  
  /**
   * Clear all errors
   */
  const clearAllErrors = () => {
    errors.value = {}
  }
  
  return {
    // State
    loading,
    editorContent,
    form,
    errors,
    
    // Computed
    isValid,
    isDirty,
    
    // Methods
    fetchPolicy,
    savePolicy,
    resetForm,
    handleEditorInput,
    clearFieldError,
    clearAllErrors,
    validateForm
  }
}
