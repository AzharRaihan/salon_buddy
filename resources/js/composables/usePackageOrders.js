import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useCustomerAuth } from './useCustomerAuth'

export function usePackageOrders() {
  const { customerAccessToken, isCustomerAuthenticated } = useCustomerAuth()
  
  // Reactive data
  const packageOrders = ref([])
  const packageDetails = ref(null)
  const isLoading = ref(false)
  const isLoadingDetails = ref(false)
  const error = ref(null)
  const detailsError = ref(null)
  
  // Pagination
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const totalPages = ref(0)
  
  // Search
  const searchTerm = ref('')
  
  // Fetch package orders
  const fetchPackageOrders = async (page = 1, search = '') => {
    try {
      isLoading.value = true
      error.value = null

      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      const response = await $api('/customer/package-orders', {
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
        
        packageOrders.value = paginationData.data || []
        totalItems.value = paginationData.total || 0
        totalPages.value = paginationData.last_page || 0
        currentPage.value = paginationData.current_page || page
      } else {
        throw new Error(response?.message || 'Failed to fetch package orders')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch package orders'
      console.error('Package orders error:', err)
      
      // Reset data on error
      packageOrders.value = []
      totalItems.value = 0
      totalPages.value = 0
      currentPage.value = 1
    } finally {
      isLoading.value = false
    }
  }
  
  // Fetch package details
  const fetchPackageDetails = async (packageId) => {
    try {
      isLoadingDetails.value = true
      detailsError.value = null
      
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }
      
      const response = await $api(`/customer/package-orders/${packageId}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (response && response.success) {
        packageDetails.value = response.data
      } else {
        throw new Error(response?.message || 'Failed to fetch package details')
      }
      
    } catch (err) {
      detailsError.value = err.message || 'Failed to fetch package details'
      console.error('Package details error:', err)
    } finally {
      isLoadingDetails.value = false
    }
  }
  
  // Search functionality
  const searchOrders = async (searchValue) => {
    searchTerm.value = searchValue
    currentPage.value = 1 // Reset to first page when searching
    await fetchPackageOrders(1, searchValue)
  }
  
  // Pagination functionality
  const goToPage = async (page) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
      await fetchPackageOrders(page, searchTerm.value)
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

  // Initialize package orders (with authentication check)
  const initializePackageOrders = async () => {
    if (!isCustomerAuthenticated.value) {
      error.value = 'Please login to view package orders'
      return
    }
    
    await fetchPackageOrders()
  }
  
  // Get status class for styling
  const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
      case 'active':
        return 'status-active'
      case 'pending':
        return 'status-pending'
      case 'expired':
      case 'cancelled':
        return 'status-cancel'
      default:
        return 'status-pending'
    }
  }
  
  // Clear package details
  const clearPackageDetails = () => {
    packageDetails.value = null
    detailsError.value = null
  }
  
  // Computed properties
  const hasError = computed(() => !!error.value)
  const hasOrders = computed(() => packageOrders.value && packageOrders.value.length > 0)
  const hasDetailsError = computed(() => !!detailsError.value)
  const hasPackageDetails = computed(() => !!packageDetails.value)
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
  
  return {
    // Data
    packageOrders,
    packageDetails,
    isLoading,
    isLoadingDetails,
    error,
    detailsError,
    searchTerm,
    
    // Pagination
    currentPage,
    perPage,
    totalItems,
    totalPages,
    
    // Computed
    hasError,
    hasOrders,
    hasDetailsError,
    hasPackageDetails,
    canGoPrev,
    canGoNext,
    paginationInfo,
    
    // Methods
    fetchPackageOrders,
    fetchPackageDetails,
    searchOrders,
    goToPage,
    nextPage,
    prevPage,
    getStatusClass,
    clearPackageDetails,
    initializePackageOrders
  }
} 