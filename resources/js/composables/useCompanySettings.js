// useCompanySettings.js
import { ref, watch, computed } from 'vue'

// Shared reactive state for company settings
const companySettings = ref(null)
const isLoading = ref(false)
const error = ref(null)

// Fetch company settings from API
async function fetchCompanySettings() {
  if (isLoading.value) return // Prevent duplicate calls
  
  try {
    isLoading.value = true
    error.value = null
    const response = await $api('/get-company-info')
    
    if (response.success) {
      companySettings.value = response.data
    }
  } catch (err) {
    error.value = err
    console.error('Failed to fetch company settings:', err)
  } finally {
    isLoading.value = false
  }
}

// Initialize settings on first import
if (!companySettings.value) {
  fetchCompanySettings()
}

export function useCompanySettings() {
  // Computed properties for notification defaults
  const defaultEmailSelect = computed(() => {
    return companySettings.value?.default_email_select === 'Yes'
  })
  
  const defaultSmsSelect = computed(() => {
    return companySettings.value?.default_sms_select === 'Yes'
  })
  
  const defaultWhatsappSelect = computed(() => {
    return companySettings.value?.default_whatsapp_select === 'Yes'
  })

  return {
    companySettings,
    isLoading,
    error,
    defaultEmailSelect,
    defaultSmsSelect,
    defaultWhatsappSelect,
    fetchCompanySettings
  }
}

