import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

/**
 * Composable for managing frontend policy pages
 * Provides data fetching and display logic for public policy pages
 */
export function useFrontendPolicy(policyType = 'terms_and_conditions') {
  const { t } = useI18n()
  
  // Reactive state
  const loading = ref(false)
  const policyData = ref(null)
  const error = ref(null)
  
  // API endpoints
  const endpoints = {
    terms_and_conditions: '/website-terms-and-conditions',
    privacy_policy: '/website-privacy-policy'
  }
  
  const currentEndpoint = endpoints[policyType] || endpoints.terms_and_conditions
  
  /**
   * Fetch policy data for frontend display
   */
  const fetchPolicyData = async () => {
    try {
      loading.value = true
      error.value = null
      
      const response = await $api(currentEndpoint)
      
      if (response.success && response.data) {
        policyData.value = response.data
      } else {
        error.value = t('Failed to load policy data')
      }
    } catch (err) {
      console.error(`Error fetching ${policyType} for frontend:`, err)
      error.value = t('Error loading policy data')
    } finally {
      loading.value = false
    }
  }
  
  /**
   * Get policy content with fallback
   */
  const getPolicyContent = () => {
    if (!policyData.value) return ''
    
    const content = policyData.value[policyType]
    return content || getDefaultContent()
  }
  
  /**
   * Get default content if no policy is set
   */
  const getDefaultContent = () => {
    const defaultContent = {
      terms_and_conditions: t('Terms and conditions content will be displayed here once configured by the administrator.'),
      privacy_policy: t('Privacy policy content will be displayed here once configured by the administrator.')
    }
    
    return defaultContent[policyType] || ''
  }
  
  /**
   * Get page title
   */
  const getPageTitle = () => {
    const titles = {
      terms_and_conditions: t('Terms & Conditions'),
      privacy_policy: t('Privacy Policy')
    }
    
    return titles[policyType] || t('Policy')
  }
  
  /**
   * Get breadcrumb text
   */
  const getBreadcrumbText = () => {
    const breadcrumbs = {
      terms_and_conditions: t('Terms & Conditions'),
      privacy_policy: t('Privacy Policy')
    }
    
    return breadcrumbs[policyType] || t('Policy')
  }
  
  // Fetch data on mount
  onMounted(() => {
    fetchPolicyData()
  })
  
  return {
    // State
    loading,
    policyData,
    error,
    
    // Methods
    fetchPolicyData,
    getPolicyContent,
    getPageTitle,
    getBreadcrumbText
  }
}
