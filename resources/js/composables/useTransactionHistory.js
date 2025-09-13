import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useCustomerAuth } from './useCustomerAuth'

export function useTransactionHistory() {
  const { customerAccessToken, isCustomerAuthenticated } = useCustomerAuth()
  
  // Reactive data
  const transactions = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  
  // Pagination
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const totalPages = ref(0)
  
  // Search
  const searchTerm = ref('')
  
  // Fetch transaction history
  const fetchTransactionHistory = async (page = 1, search = '') => {
    try {
      isLoading.value = true
      error.value = null
      
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }
      
      const response = await $api('/customer/transaction-history', {
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
        
        transactions.value = paginationData.data || []
        totalItems.value = paginationData.total || 0
        totalPages.value = paginationData.last_page || 0
        currentPage.value = paginationData.current_page || page
      } else {
        throw new Error(response?.message || 'Failed to fetch transaction history')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch transaction history'
      console.error('Transaction history error:', err)
      
      // Reset data on error
      transactions.value = []
      totalItems.value = 0
      totalPages.value = 0
      currentPage.value = 1
    } finally {
      isLoading.value = false
    }
  }
  
  // Search functionality
  const searchTransactions = async (searchValue) => {
    searchTerm.value = searchValue
    currentPage.value = 1 // Reset to first page when searching
    await fetchTransactionHistory(1, searchValue)
  }
  
  // Pagination functionality
  const goToPage = async (page) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
      await fetchTransactionHistory(page, searchTerm.value)
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
  
  // Fetch transaction details
  const fetchTransactionDetails = async (transactionId, sourceType = 'booking', type = 'Service') => {
    try {
      isLoading.value = true
      error.value = null
      
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }
      
      const response = await $api(`/customer/transaction-history/${transactionId}`, {
        method: 'GET',
        query: {
          source_type: sourceType,
          type: type
        },
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (response && response.success) {
        return response.data
      } else {
        throw new Error(response?.message || 'Failed to fetch transaction details')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch transaction details'
      console.error('Transaction details error:', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }
  
  // Initialize transaction history (with authentication check)
  const initializeTransactionHistory = async () => {
    if (!isCustomerAuthenticated.value) {
      error.value = 'Please login to view transaction history'
      return
    }
    
    await fetchTransactionHistory()
  }
  
  // Get status class for styling
  const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
      case 'completed':
        return 'status-active'
      case 'pending':
        return 'status-pending'
      case 'failed':
      case 'cancelled':
        return 'status-cancel'
      default:
        return 'status-pending'
    }
  }
  
  // Get transaction type class for styling
  const getTypeClass = (type) => {
    switch (type.toLowerCase()) {
      case 'service':
        return 'transaction-service'
      case 'product':
        return 'transaction-product'
      default:
        return 'transaction-default'
    }
  }
  
  // Format amount with proper sign
  const formatAmount = (amount) => {
    const formattedAmount = Math.abs(amount).toFixed(2)
    return amount < 0 ? `-$${formattedAmount}` : `$${formattedAmount}`
  }
  
  // Computed properties
  const hasError = computed(() => !!error.value)
  const hasTransactions = computed(() => transactions.value && transactions.value.length > 0)
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
    transactions,
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
    hasTransactions,
    canGoPrev,
    canGoNext,
    paginationInfo,
    
    // Methods
    fetchTransactionHistory,
    searchTransactions,
    goToPage,
    nextPage,
    prevPage,
    getStatusClass,
    getTypeClass,
    formatAmount,
    fetchTransactionDetails,
    initializeTransactionHistory
  }
} 