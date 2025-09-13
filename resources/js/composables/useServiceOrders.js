import { ref, computed, watch } from 'vue'
import { $api } from '@/utils/api'
import { useCustomerAuth } from './useCustomerAuth'

export function useServiceOrders() {
  const { customerAccessToken, isCustomerAuthenticated } = useCustomerAuth()
  
  // Reactive data
  const serviceOrders = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  
  // Pagination
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const totalPages = ref(0)
  
  // Search
  const searchTerm = ref('')
  
  // Fetch service orders
  const fetchServiceOrders = async (page = 1, search = '') => {
    try {
      isLoading.value = true
      error.value = null

      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      const response = await $api('/customer/service-orders', {
        method: 'GET',
        query: {
          page: page,
          per_page: perPage.value,
          search: search
        },
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (response && response.success) {
        const paginationData = response.data
        
        serviceOrders.value = paginationData.data || []
        totalItems.value = paginationData.total || 0
        totalPages.value = paginationData.last_page || 0
        currentPage.value = paginationData.current_page || page
      } else {
        throw new Error(response?.message || 'Failed to fetch service orders')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch service orders'
      console.error('Service orders error:', err)
      
      // Reset data on error
      serviceOrders.value = []
      totalItems.value = 0
      totalPages.value = 0
      currentPage.value = 1
    } finally {
      isLoading.value = false
    }
  }
  
  // Search functionality
  const searchOrders = async (searchValue) => {
    searchTerm.value = searchValue
    currentPage.value = 1 // Reset to first page when searching
    await fetchServiceOrders(1, searchValue)
  }
  
  // Pagination functionality
  const goToPage = async (page) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
      await fetchServiceOrders(page, searchTerm.value)
    }
  }
  
  const nextPage = async () => {
    if (currentPage.value < totalPages.value) {
      await goToPage(currentPage.value + 1)
    }
  }
  
  const prevPage = async () => {
    if (currentPage.value > 1) {
      await goToPage(currentPage.value - 1)
    }
  }
  
  // Initialize service orders (with authentication check)
  const initializeServiceOrders = async () => {
    if (!isCustomerAuthenticated.value) {
      error.value = 'Please login to view service orders'
      return
    }
    
    await fetchServiceOrders()
  }

  // Get status class for styling
  const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'status-pending'
      case 'accepted':
        return 'status-accepted'
      case 'rejected':
        return 'status-rejected'
      case 'completed':
        return 'status-completed'
      default:
        return 'status-pending'
    }
  }
  
  // Computed properties
  const hasError = computed(() => !!error.value)
  const hasOrders = computed(() => serviceOrders.value && serviceOrders.value.length > 0)
  const canGoPrev = computed(() => currentPage.value > 1)
  const canGoNext = computed(() => currentPage.value < totalPages.value)
  
  // Pagination info
  const paginationInfo = computed(() => {
    const start = ((currentPage.value - 1) * perPage.value) + 1
    const end = Math.min(currentPage.value * perPage.value, totalItems.value)
    return {
      start,
      end,
      total: totalItems.value,
      currentPage: currentPage.value,
      totalPages: totalPages.value,
      perPage: perPage.value
    }
  })
  
  // Fetch service order details
  const fetchServiceOrderDetails = async (orderId) => {
    try {
      isLoading.value = true
      error.value = null
      
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }
      
      const response = await $api(`/customer/service-orders/${orderId}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (response && response.success) {
        return response.data
      } else {
        throw new Error(response?.message || 'Failed to fetch service order details')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch service order details'
      console.error('Service order details error:', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    // Data
    serviceOrders,
    isLoading,
    error,
    searchTerm,
    
    // Pagination
    currentPage,
    perPage,
    totalItems,
    totalPages,
    
    // Computed
    hasError,
    hasOrders,
    canGoPrev,
    canGoNext,
    paginationInfo,
    
    // Methods
    fetchServiceOrders,
    searchOrders,
    goToPage,
    nextPage,
    prevPage,
    getStatusClass,
    fetchServiceOrderDetails,
    initializeServiceOrders
  }
} 