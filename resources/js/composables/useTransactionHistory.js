import { ref, computed, watch, onMounted } from 'vue'

/**
 * Composable for Transaction History Report
 * Manages state and data fetching for transaction history report
 */
export function useTransactionHistory() {
  // State
  const transactionHistoryData = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  
  // Filters
  const branchId = ref(null)
  const paymentMethodId = ref(null)
  const dateFrom = ref(null)
  const dateTo = ref(null)
  
  // Filter Options
  const branches = ref([])
  const paymentMethods = ref([])
  
  // Computed properties
  const transactions = computed(() => {
    return transactionHistoryData.value?.transactions || []
  })
  
  const totalRecords = computed(() => {
    return transactionHistoryData.value?.total || 0
  })
  
  const summary = computed(() => {
    return transactionHistoryData.value?.summary || {
      totalTransactions: 0,
      totalAmount: 0,
    }
  })
  
  /**
   * Fetch transaction history data
   */
  const fetchTransactionHistory = async () => {
    // Payment method is required
    if (!paymentMethodId.value) {
      transactionHistoryData.value = null
      return
    }
    
    isLoading.value = true
    error.value = null
    
    try {
      const response = await $api('/transaction-history-report', {
        method: 'GET',
        query: {
          branch_id: branchId.value,
          payment_method_id: paymentMethodId.value,
          date_from: dateFrom.value,
          date_to: dateTo.value,
        },
      })
      
      transactionHistoryData.value = response.data
    } catch (err) {
      console.error('Error fetching transaction history:', err)
      error.value = err.message || 'Failed to fetch transaction history'
      transactionHistoryData.value = null
    } finally {
      isLoading.value = false
    }
  }
  
  /**
   * Fetch filter options
   */
  const fetchFilterOptions = async () => {
    try {
      const response = await $api('/transaction-history-report-filters', {
        method: 'GET',
      })
      
      branches.value = response.data.branches || []
      paymentMethods.value = response.data.paymentMethods || []
    } catch (err) {
      console.error('Error fetching filter options:', err)
    }
  }
  
  /**
   * Reset all filters
   */
  const resetFilters = () => {
    branchId.value = null
    paymentMethodId.value = null
    dateFrom.value = null
    dateTo.value = null
    transactionHistoryData.value = null
  }
  
  // Watch for filter changes
  watch([branchId, paymentMethodId, dateFrom, dateTo], () => {
    fetchTransactionHistory()
  })
  
  // Fetch filter options on mount
  onMounted(async () => {
    await fetchFilterOptions()
  })
  
  return {
    // State
    transactionHistoryData,
    isLoading,
    error,
    branchId,
    paymentMethodId,
    dateFrom,
    dateTo,
    branches,
    paymentMethods,
    
    // Methods
    fetchTransactionHistory,
    fetchFilterOptions,
    resetFilters,
    
    // Computed
    transactions,
    totalRecords,
    summary,
  }
}
