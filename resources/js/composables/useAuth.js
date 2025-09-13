import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

export function useAuth() {
  const user = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Get access token from cookie
  const accessToken = computed(() => {
    return useCookie('accessToken').value
  })

  // Get user data from cookie
  const userData = computed(() => {
    return useCookie('userData').value
  })

  // Check if user is authenticated
  const isAuthenticated = computed(() => {
    return !!(accessToken.value && userData.value)
  })

  // Initialize user data if available
  const initializeUser = () => {
    if (userData.value) {
      user.value = userData.value
    }
  }

  // Login function
  const login = async (credentials) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $api('/login', {
        method: 'POST',
        body: credentials
      })

      if (response.success) {
        // Store tokens and user data
        const accessTokenCookie = useCookie('accessToken', { httpOnly: false })
        const userDataCookie = useCookie('userData', { httpOnly: false })

        accessTokenCookie.value = response.data.token
        userDataCookie.value = response.data.user
        user.value = response.data.user

        return { success: true, data: response.data }
      } else {
        error.value = response.message || 'Login failed'
        return { success: false, message: response.message }
      }
    } catch (err) {
      console.error('Login error:', err)
      error.value = err.data?.message || 'Something went wrong. Please try again.'
      return { success: false, message: error.value }
    } finally {
      isLoading.value = false
    }
  }

  // Social login function
  const socialLogin = async (provider, returnUrl = null) => {
    try {
      const response = await $api('/social-auth-urls')
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
      console.error('Social login error:', err)
      return false
    }
  }

  // Logout function
  const logout = async () => {
    isLoading.value = true
    
    try {
      await $api('/logout', { method: 'POST' })
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear cookies regardless of API response
      const accessTokenCookie = useCookie('accessToken')
      const userDataCookie = useCookie('userData')
      
      accessTokenCookie.value = null
      userDataCookie.value = null
      user.value = null
      
      isLoading.value = false
    }
  }

  // Get current user info
  const getCurrentUser = () => {
    if (userData.value) {
      user.value = userData.value
      return userData.value
    }
    return null
  }

  // Handle social login callback
  const handleSocialCallback = () => {
    const urlParams = new URLSearchParams(window.location.search)
    const token = urlParams.get('token')
    const status = urlParams.get('status')
    const userInfo = urlParams.get('user')

    if (status === 'success' && token) {
      // Store token and user data
      const accessTokenCookie = useCookie('accessToken', { httpOnly: false })
      const userDataCookie = useCookie('userData', { httpOnly: false })
      
      accessTokenCookie.value = token
      
      if (userInfo) {
        try {
          const decodedUser = JSON.parse(decodeURIComponent(userInfo))
          userDataCookie.value = decodedUser
          user.value = decodedUser
        } catch (e) {
          console.error('Error parsing user info:', e)
        }
      }

      // Clean URL parameters
      const url = new URL(window.location)
      url.searchParams.delete('token')
      url.searchParams.delete('status')
      url.searchParams.delete('user')
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
  initializeUser()

  return {
    // State
    user,
    isLoading,
    error,
    isAuthenticated,
    accessToken,
    userData,

    // Methods
    login,
    socialLogin,
    logout,
    getCurrentUser,
    handleSocialCallback,
    initializeUser
  }
} 