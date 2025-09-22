import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { toast } from 'vue3-toastify'

export function useEmployeeRating() {
  const { isCustomerAuthenticated } = useCustomerAuth()
  
  const isLoading = ref(false)
  const error = ref(null)
  const ratings = ref([])
  const ratingSummary = ref({
    average_rating: 0,
    total_ratings: 0
  })
  const canRate = ref(false)
  const ratingEligibility = ref(null)

  /**
   * Check if customer can rate an employee
   */
  const checkRatingEligibility = async (employeeId) => {
    isLoading.value = true
    error.value = null
    const customerData = useCookie('customerData').value
    if(!customerData) {
      canRate.value = false
      isLoading.value = false

      toast('Please login to rate this employee', { type: 'error' })
      return { success: false, message: 'Please login to rate this employee' }
    }

    try {
      // send post request
      const response = await $api(`/employee/can-rate`, {
        method: 'POST',
        body: {
          employee_id: employeeId,
          customer_id: customerData.id
        },
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('customerAccessToken').value}`
        }
      })

      if (response.success) {
        canRate.value = response.data.can_rate
        ratingEligibility.value = response.data
        
        return response.data
      } else {
        const errorMessage = response.message || 'Failed to check rating eligibility'
        toast(errorMessage, { type: 'error' })
        error.value = errorMessage
        return null
      }
    } catch (err) {
      console.error('Rating eligibility check error:', err)
      const errorMessage = err.data?.message || 'Something went wrong. Please try again.'
      toast(errorMessage, { type: 'error' })
      error.value = errorMessage
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Submit employee rating
   */
  const submitRating = async (employeeId, ratingData) => {
    if (!isCustomerAuthenticated.value) {
      error.value = 'Please login to submit a rating'
      return { success: false, message: 'Authentication required' }
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await $api(`/customer/employee/${employeeId}/rating`, {
        method: 'POST',
        body: ratingData,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${useCookie('customerAccessToken').value}`
        }
      })

      if (response.success) {
        // Refresh ratings after successful submission
        await fetchEmployeeRatings(employeeId)
        await checkRatingEligibility(employeeId)
        
        return { success: true, data: response.data }
      } else {
        const errorMessage = response.message || 'Failed to submit rating'
        toast(errorMessage, { type: 'error' })
        error.value = errorMessage
        return { success: false, message: errorMessage }
      }
    } catch (err) {
      console.error('Rating submission error:', err)
      const errorMessage = err.data?.message || 'Something went wrong. Please try again.'
      toast(errorMessage, { type: 'error' })
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch employee ratings
   */
  const fetchEmployeeRatings = async (employeeId, page = 1, perPage = 10) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await $api(`/employee/${employeeId}/ratings?page=${page}&per_page=${perPage}`)
      
      if (response.success) {
        ratings.value = response.data.reviews.data
        ratingSummary.value = response.data.summary
        
        return {
          success: true,
          reviews: response.data.reviews,
          summary: response.data.summary
        }
      } else {
        const errorMessage = response.message || 'Failed to fetch ratings'
        toast(errorMessage, { type: 'error' })
        error.value = errorMessage
        return { success: false, message: errorMessage }
      }
    } catch (err) {
      console.error('Fetch ratings error:', err)
      const errorMessage = err.data?.message || 'Something went wrong. Please try again.'
      toast(errorMessage, { type: 'error' })
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Get appropriate message for rating eligibility
   */
  const getEligibilityMessage = (eligibility) => {
    if (!eligibility) return ''
    
    switch (eligibility.reason) {
      case 'authentication_required':
        return 'Please login to rate this employee'
      case 'already_rated':
        return 'You have already rated this employee'
      case 'no_service_received':
        return 'You can only rate employees who have provided you service'
      case 'eligible':
        return 'You can rate this employee'
      default:
        return ''
    }
  }

  /**
   * Get eligibility message type for styling
   */
  const getEligibilityMessageType = (eligibility) => {
    if (!eligibility) return 'info'
    
    switch (eligibility.reason) {
      case 'authentication_required':
        return 'warning'
      case 'already_rated':
        return 'info'
      case 'no_service_received':
        return 'error'
      case 'eligible':
        return 'success'
      default:
        return 'info'
    }
  }

  /**
   * Generate star rating display
   */
  const generateStarRating = (rating) => {
    const stars = []
    const fullStars = Math.floor(rating)
    const hasHalfStar = rating % 1 !== 0
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
      stars.push('full')
    }
    
    // Half star
    if (hasHalfStar) {
      stars.push('half')
    }
    
    // Empty stars
    const emptyStars = 5 - Math.ceil(rating)
    for (let i = 0; i < emptyStars; i++) {
      stars.push('empty')
    }
    
    return stars
  }

  /**
   * Clear error message
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Reset all data
   */
  const reset = () => {
    ratings.value = []
    ratingSummary.value = {
      average_rating: 0,
      total_ratings: 0
    }
    canRate.value = false
    ratingEligibility.value = null
    error.value = null
    isLoading.value = false
  }

  return {
    // State
    isLoading,
    error,
    ratings,
    ratingSummary,
    canRate,
    ratingEligibility,
    isCustomerAuthenticated,

    // Methods
    checkRatingEligibility,
    submitRating,
    fetchEmployeeRatings,
    getEligibilityMessage,
    getEligibilityMessageType,
    generateStarRating,
    clearError,
    reset
  }
}
