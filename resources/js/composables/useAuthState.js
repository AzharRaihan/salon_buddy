import { ref, computed } from 'vue'

// Global authentication state
const authState = ref({
  isAuthenticated: false,
  userData: null,
  isLoading: false
})

// Customer authentication state
const customerAuthState = ref({
  isAuthenticated: false,
  customerData: null,
  isLoading: false
})

export function useAuthState() {
  // Update auth state based on cookies
  const updateAuthState = () => {
    const accessToken = useCookie('accessToken').value
    const userData = useCookie('userData').value
    
    authState.value.isAuthenticated = !!(accessToken && userData)
    authState.value.userData = userData
  }

  const updateCustomerAuthState = () => {
    const customerAccessToken = useCookie('customerAccessToken').value
    const customerData = useCookie('customerData').value
    
    customerAuthState.value.isAuthenticated = !!(customerAccessToken && customerData)
    customerAuthState.value.customerData = customerData
  }

  // Clear conflicting auth states when switching between admin and customer
  const clearConflictingAuth = (isCustomer = false) => {
    if (isCustomer) {
      // Clear admin auth when logging in as customer
      const accessTokenCookie = useCookie('accessToken')
      const userDataCookie = useCookie('userData')
      accessTokenCookie.value = null
      userDataCookie.value = null
      updateAuthState()
    } else {
      // Clear customer auth when logging in as admin
      const customerAccessTokenCookie = useCookie('customerAccessToken')
      const customerDataCookie = useCookie('customerData')
      customerAccessTokenCookie.value = null
      customerDataCookie.value = null
      updateCustomerAuthState()
    }
  }

  // Initialize state
  updateAuthState()
  updateCustomerAuthState()

  return {
    authState: computed(() => authState.value),
    customerAuthState: computed(() => customerAuthState.value),
    updateAuthState,
    updateCustomerAuthState,
    clearConflictingAuth
  }
}
