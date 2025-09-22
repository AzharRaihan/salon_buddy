import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export function useCustomerAuth() {
  const customer = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Get access token from cookie (separate from admin auth)
  const customerAccessToken = computed(() => {
    return useCookie('customerAccessToken').value
  })

  // Get customer data from cookie (separate from admin auth)
  const customerData = computed(() => {
    return useCookie('customerData').value
  })

  // Check if customer is authenticated
  const isCustomerAuthenticated = computed(() => {
    return !!(customerAccessToken.value && customerData.value)
  })

  // Initialize customer data if available
  const initializeCustomer = () => {
    if (customerData.value) {
      customer.value = customerData.value
    }
  }

  // Customer login function
  const customerLogin = async (credentials) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $api('/customer/login', {
        method: 'POST',
        body: credentials
      })

      if (response.success) {
        // Clear any conflicting admin authentication
        const { clearConflictingAuth } = useAuthState()
        clearConflictingAuth(true)

        // Store tokens and customer data
        const customerAccessTokenCookie = useCookie('customerAccessToken', { httpOnly: false })
        const customerDataCookie = useCookie('customerData', { httpOnly: false })

        customerAccessTokenCookie.value = response.data.token
        customerDataCookie.value = response.data.customer
        customer.value = response.data.customer

        return { success: true, data: response.data }
      } else {
        error.value = response.message || 'Login failed'
        return { success: false, message: response.message }
      }
    } catch (err) {
      console.error('Customer login error:', err)
      error.value = err.data?.message || 'Something went wrong. Please try again.'
      return { success: false, message: error.value }
    } finally {
      isLoading.value = false
    }
  }

  // Customer registration function
  const customerRegister = async (registrationData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $api('/customer/register', {
        method: 'POST',
        body: registrationData
      })

      if (response.success) {
        // Clear any conflicting admin authentication
        const { clearConflictingAuth } = useAuthState()
        clearConflictingAuth(true)

        // Store tokens and customer data
        const customerAccessTokenCookie = useCookie('customerAccessToken', { httpOnly: false })
        const customerDataCookie = useCookie('customerData', { httpOnly: false })

        customerAccessTokenCookie.value = response.data.token
        customerDataCookie.value = response.data.customer
        customer.value = response.data.customer

        return { success: true, data: response.data }
      } else {
        error.value = response.message || 'Registration failed'
        return { success: false, message: response.message, errors: response.errors }
      }
    } catch (err) {
      console.error('Customer registration error:', err)
      error.value = err.data?.message || 'Something went wrong. Please try again.'
      return { success: false, message: error.value, errors: err.data?.errors }
    } finally {
      isLoading.value = false
    }
  }

  // Customer social login function
  const customerSocialLogin = async (provider, returnUrl = null) => {
    try {
      const response = await $api('/customer/social-auth-urls')
      if (response.success && response.data[provider]) {
        // Use provided return URL or current page
        const finalReturnUrl = returnUrl || window.location.pathname
        
        // Append return_url parameter to the social auth URL
        const authUrl = new URL(response.data[provider])
        authUrl.searchParams.set('return_url', finalReturnUrl)
        
        window.location.href = authUrl.toString()
        return true
      }
      return false
    } catch (err) {
      console.error('Customer social login error:', err)
      return false
    }
  }

  // Customer logout function
  const customerLogout = async () => {
    isLoading.value = true
    
    try {
      // Call logout API if customer is authenticated
      if (customerAccessToken.value) {
        await $api('/customer/logout', { 
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${customerAccessToken.value}`
          }
        })
      }
    } catch (err) {
      console.error('Customer logout error:', err)
    } finally {
      // Clear cookies regardless of API response
      const customerAccessTokenCookie = useCookie('customerAccessToken')
      const customerDataCookie = useCookie('customerData')
      
      customerAccessTokenCookie.value = null
      customerDataCookie.value = null
      customer.value = null
      
      // Clear any OAuth-related data from localStorage/sessionStorage
      if (typeof window !== 'undefined') {
        localStorage.removeItem('oauth_state')
        sessionStorage.removeItem('oauth_state')
        localStorage.removeItem('social_auth_data')
        sessionStorage.removeItem('social_auth_data')
      }
      
      isLoading.value = false
    }
  }

  // Get current customer info
  const getCurrentCustomer = () => {
    if (customerData.value) {
      customer.value = customerData.value
      return customerData.value
    }
    return null
  }

  // Update customer profile
  const updateCustomerProfile = async (profileData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $api('/customer/profile', {
        method: 'POST',
        body: profileData,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${customerAccessToken.value}`
        }
      })

      if (response.success) {
        // Update customer data
        const customerDataCookie = useCookie('customerData', { httpOnly: false })
        customerDataCookie.value = response.data.customer
        customer.value = response.data.customer

        return { success: true, data: response.data }
      } else {
        error.value = response.message || 'Profile update failed'
        return { success: false, message: response.message, errors: response.errors }
      }
    } catch (err) {
      console.error('Customer profile update error:', err)
      error.value = err.data?.message || 'Something went wrong. Please try again.'
      return { success: false, message: error.value, errors: err.data?.errors }
    } finally {
      isLoading.value = false
    }
  }

  // Handle customer social login callback
  const handleCustomerSocialCallback = () => {
    const urlParams = new URLSearchParams(window.location.search)
    const token = urlParams.get('token')
    const status = urlParams.get('status')
    const customerInfo = urlParams.get('customer')

    if (status === 'success' && token) {
      // Clear any conflicting admin authentication
      const { clearConflictingAuth } = useAuthState()
      clearConflictingAuth(true)

      // Store token and customer data
      const customerAccessTokenCookie = useCookie('customerAccessToken', { httpOnly: false })
      const customerDataCookie = useCookie('customerData', { httpOnly: false })
      
      customerAccessTokenCookie.value = token
      
      if (customerInfo) {
        try {
          const decodedCustomer = JSON.parse(decodeURIComponent(customerInfo))
          customerDataCookie.value = decodedCustomer
          customer.value = decodedCustomer
        } catch (e) {
          console.error('Error parsing customer info:', e)
        }
      }

      // Clean URL parameters
      const url = new URL(window.location)
      url.searchParams.delete('token')
      url.searchParams.delete('status')
      url.searchParams.delete('customer')
      url.searchParams.delete('message')
      window.history.replaceState({}, document.title, url.pathname + url.search)

      return { success: true }
    } else if (status === 'error') {
      const errorMessage = urlParams.get('message')
      error.value = errorMessage ? decodeURIComponent(errorMessage) : 'Social login failed'
      
      // Clean URL parameters
      const url = new URL(window.location)
      url.searchParams.delete('status')
      url.searchParams.delete('message')
      window.history.replaceState({}, document.title, url.pathname + url.search)

      return { success: false, message: error.value }
    }

    return { success: false }
  }

  // Initialize on composable creation
  initializeCustomer()

  return {
    // State
    customer,
    isLoading,
    error,
    isCustomerAuthenticated,
    customerAccessToken,
    customerData,

    // Methods
    customerLogin,
    customerRegister,
    customerSocialLogin,
    customerLogout,
    getCurrentCustomer,
    updateCustomerProfile,
    handleCustomerSocialCallback,
    initializeCustomer
  }
} 