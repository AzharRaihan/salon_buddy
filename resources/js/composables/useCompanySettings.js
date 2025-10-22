// useCompanySettings.js
import { ref, watch } from 'vue'

// Get initial data from cookie or set default structure
const getInitialSettings = () => {
  const cookieData = useCookie('company_settings').value
  return cookieData || {
    default_email_select: 'No',
    default_sms_select: 'No',
    default_whatsapp_select: 'No'
  }
}

// Shared reactive state for company settings (stored in cookie)
const companySettings = ref(getInitialSettings())

// Watch for changes and sync to cookie
watch(companySettings, (newVal) => {
  useCookie('company_settings').value = newVal
}, { deep: true })

// Fetch company settings from API and update cookie
async function fetchCompanySettings() {
  try {
    const response = await $api('/get-company-info')
    
    if (response.success && response.data) {
      // Update the reactive ref with new data
      companySettings.value = {
        ...response.data,
        default_email_select: response.data.default_email_select || 'No',
        default_sms_select: response.data.default_sms_select || 'No',
        default_whatsapp_select: response.data.default_whatsapp_select || 'No'
      }
    }
  } catch (err) {
    console.error('Failed to fetch company settings:', err)
  }
}

// Initialize settings on first import
if (!companySettings.value || !companySettings.value.default_email_select) {
  fetchCompanySettings()
}

export function useCompanySettings() {
  // Reactive computed properties for notification defaults
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
    defaultEmailSelect,
    defaultSmsSelect,
    defaultWhatsappSelect,
    fetchCompanySettings
  }
}

