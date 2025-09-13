import { ref } from 'vue'


export function useTestimonials() {
  const testimonials = ref([])
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch testimonials from the API
   */
  const fetchTestimonials = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await $api('/get-testimonials')
      testimonials.value = response.data || []
    } catch (err) {
      error.value = err.message || 'Failed to fetch testimonials'
      console.error('Error fetching testimonials:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Get star display data for a rating
   * @param {number} rating - The rating value (0-5)
   * @returns {Array} Array of star objects with fill percentage
   */
  const getStarDisplay = (rating) => {
    const stars = []
    const fullStars = Math.floor(rating)
    const hasHalfStar = rating % 1 !== 0
    
    for (let i = 1; i <= 5; i++) {
      if (i <= fullStars) {
        stars.push({ index: i, fillPercentage: 100, isFilled: true })
      } else if (i === fullStars + 1 && hasHalfStar) {
        const halfStarPercentage = Math.round((rating % 1) * 100)
        stars.push({ index: i, fillPercentage: halfStarPercentage, isFilled: false })
      } else {
        stars.push({ index: i, fillPercentage: 0, isFilled: false })
      }
    }
    
    return stars
  }

  /**
   * Initialize testimonials data
   */
  const initTestimonials = () => {
    fetchTestimonials()
  }

  return {
    testimonials,
    loading,
    error,
    fetchTestimonials,
    getStarDisplay,
    initTestimonials
  }
}
