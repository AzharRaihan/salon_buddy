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

  // Initialize state
  updateAuthState()
  updateCustomerAuthState()

  return {
    authState: computed(() => authState.value),
    customerAuthState: computed(() => customerAuthState.value),
    updateAuthState,
    updateCustomerAuthState
  }
}
