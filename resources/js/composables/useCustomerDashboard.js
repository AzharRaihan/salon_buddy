import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useAuthState } from './useAuthState'

export function useCustomerDashboard() {
  const { customerAuthState } = useAuthState()
  
  // Reactive data
  const dashboardStats = ref({
    complete_order: 0,
    service_booking: 0,
    total_buy_package: 0
  })
  
  const orderHistory = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  // Fetch dashboard data from API
  const fetchDashboardData = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      // Check if customer is authenticated
      if (!customerAuthState.value.isAuthenticated) {
        throw new Error('Customer authentication required')
      }

      const response = await $api('/customer/dashboard', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${useCookie('customerAccessToken').value}`,
          'Content-Type': 'application/json'
        }
      })


      if (response && response.success) {
        dashboardStats.value = {
          complete_order: response.data?.stats?.complete_order || 0,
          service_booking: response.data?.stats?.service_booking || 0,
          total_buy_package: response.data?.stats?.total_buy_package || 0
        }
        orderHistory.value = response.data?.orderHistory || []
      } else {
        throw new Error(response?.message || 'Failed to fetch dashboard data')
      }
    } catch (err) {
      console.error('Dashboard data error:', err)
      // Reset to default values on error
      dashboardStats.value = {
        complete_order: 0,
        service_booking: 0,
        total_buy_package: 0
      }
      orderHistory.value = []
    } finally {
      isLoading.value = false
    }
  }

  // Initialize dashboard data
  const initializeDashboard = async () => {
    if (!customerAuthState.value.isAuthenticated) {
      error.value = 'Please login to view dashboard data'
      return
    }
    
    await fetchDashboardData()
  }

  // Computed properties
  const hasError = computed(() => !!error.value)
  
  return {
    // Data
    dashboardStats,
    orderHistory,
    isLoading,
    error,
    
    // Computed
    hasError,
    
    // Methods
    fetchDashboardData,
    initializeDashboard
  }
} 