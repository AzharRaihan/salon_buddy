import { ref } from 'vue'

export function useServiceApi() {
  const serviceCategories = ref([])
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch service categories from API
   * @returns {Promise<void>}
   */
  const fetchServiceCategories = async () => {
    try {
      loading.value = true
      error.value = null
      const response = await $api('/get-service-categories')
      
      if (response.success) {
        serviceCategories.value = response.data
      } else {
        throw new Error(response.message || 'Failed to fetch service categories')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching service categories:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch featured services from API
   * @param {string|null} categoryId - Optional category filter
   * @returns {Promise<Array>}
   */
  const fetchFeaturedServices = async (categoryId = null) => {
    try {
      loading.value = true
      error.value = null
      
      const url = categoryId && categoryId !== 'all' 
        ? `/get-featured-services?category_id=${categoryId}`
        : '/get-featured-services'
        
      const response = await $api(url)
      
      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || 'Failed to fetch services')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching services:', err)
      return []
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch services with pagination
   * @param {Object} params - Pagination and filter parameters
   * @returns {Promise<Object>}
   */
  const fetchServicesPaginated = async (params = {}) => {
    try {
      loading.value = true
      error.value = null
      
      const queryParams = new URLSearchParams({
        page: params.page || 1,
        per_page: params.perPage || 9,
        category_id: params.categoryId || '',
        sort: params.sort || 'price_asc'
      }).toString()
      
      const response = await $api(`/get-featured-services-paginated?${queryParams}`)
      
      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || 'Failed to fetch paginated services')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching paginated services:', err)
      return {
        data: [],
        total: 0,
        current_page: 1,
        last_page: 1
      }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    serviceCategories,
    loading,
    error,
    
    // Methods
    fetchServiceCategories,
    fetchFeaturedServices,
    fetchServicesPaginated
  }
} 