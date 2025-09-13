import { ref, computed } from 'vue'
import { $api } from '@/utils/api'
import { useCustomerAuth } from './useCustomerAuth'

export function useProductOrders() {
  const { customerAccessToken, isCustomerAuthenticated } = useCustomerAuth()
  
  // Reactive data
  const productOrders = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  
  // Pagination
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const totalPages = ref(0)
  
  // Search
  const searchTerm = ref('')
  
  // Fetch product orders
  const fetchProductOrders = async (page = 1, search = '') => {
    try {
      isLoading.value = true
      error.value = null

      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      const response = await $api('/customer/product-orders', {
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
        
        productOrders.value = paginationData.data || []
        totalItems.value = paginationData.total || 0
        totalPages.value = paginationData.last_page || 0
        currentPage.value = paginationData.current_page || page
      } else {
        throw new Error(response?.message || 'Failed to fetch product orders')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch product orders'
      console.error('Product orders error:', err)
      
      // Reset data on error
      productOrders.value = []
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
    await fetchProductOrders(1, searchValue)
  }
  
  // Pagination functionality
  const goToPage = async (page) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
      await fetchProductOrders(page, searchTerm.value)
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
  
  // Initialize product orders (with authentication check)
  const initializeProductOrders = async () => {
    if (!isCustomerAuthenticated.value) {
      error.value = 'Please login to view product orders'
      return
    }
    
    await fetchProductOrders()
  }

  const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return 'status-confirmed'
      case 'pending':
        return 'status-pending'
      case 'cancelled':
        return 'status-cancel'
      case 'completed':
        return 'status-completed'
      default:
        return 'status-pending'
    }
  }

  // Fetch product order details
  const fetchProductOrderDetails = async (orderId) => {
    try {
      isLoading.value = true
      error.value = null
      
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }
      
      const response = await $api(`/customer/product-orders/${orderId}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (response && response.success) {
        return response.data
      } else {
        throw new Error(response?.message || 'Failed to fetch product order details')
      }
      
    } catch (err) {
      error.value = err.message || 'Failed to fetch product order details'
      console.error('Product order details error:', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Test route function to debug URL issues
  const testRoute = async () => {
    try {
      const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
      let testUrl
      if (apiBaseUrl.startsWith('http')) {
        testUrl = `${apiBaseUrl}/customer/product-orders/test/route`
      } else {
        const baseUrl = window.location.origin
        testUrl = `${baseUrl}${apiBaseUrl}/customer/product-orders/test/route`
      }
      
      console.log('Testing URL:', testUrl)
      console.log('Current location:', window.location.href)
      console.log('Origin:', window.location.origin)
      console.log('API Base URL:', apiBaseUrl)
      
      const response = await fetch(testUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Accept': 'application/json'
        }
      })
      
      console.log('Test response status:', response.status)
      const result = await response.json()
      console.log('Test response:', result)
      
      return result
    } catch (err) {
      console.error('Test route error:', err)
      throw err
    }
  }

  // Test PDF generation function
  const testPdfGeneration = async () => {
    try {
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
      
      let testUrl
      if (apiBaseUrl.startsWith('http')) {
        testUrl = `${apiBaseUrl}/customer/product-orders/test/pdf`
      } else {
        const baseUrl = window.location.origin
        testUrl = `${baseUrl}${apiBaseUrl}/customer/product-orders/test/pdf`
      }
      
      console.log('=== TESTING PDF GENERATION ===')
      console.log('Test URL:', testUrl)
      console.log('Auth Token:', customerAccessToken.value ? 'Present' : 'Missing')
      
      const response = await fetch(testUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Accept': 'application/pdf'
        }
      })
      
      console.log('Test PDF Response Status:', response.status)
      console.log('Test PDF Response Headers:', Object.fromEntries(response.headers.entries()))
      
      const contentType = response.headers.get('content-type')
      console.log('Test PDF Content-Type:', contentType)
      
      if (response.ok) {
        if (contentType && contentType.includes('application/pdf')) {
          const blob = await response.blob()
          console.log('Test PDF Blob Size:', blob.size, 'bytes')
          
          // Create download link
          const url = window.URL.createObjectURL(blob)
          const link = document.createElement('a')
          link.href = url
          link.download = 'test-invoice.pdf'
          link.style.display = 'none'
          
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
          
          window.URL.revokeObjectURL(url)
          
          console.log('✅ Test PDF generated and downloaded successfully')
          return { success: true, message: 'Test PDF generated successfully', size: blob.size }
        } else {
          // Not a PDF, let's see what it is
          const text = await response.text()
          console.log('❌ Test PDF: Response is not a PDF. Content:', text.substring(0, 500) + '...')
          return { success: false, message: `Test PDF: Expected PDF but got: ${contentType}`, content: text }
        }
      } else {
        const errorText = await response.text()
        console.log('❌ Test PDF: Request failed:', errorText)
        return { success: false, message: `Test PDF: HTTP ${response.status}: ${response.statusText}`, content: errorText }
      }
      
    } catch (err) {
      console.error('Test PDF error:', err)
      return { success: false, message: err.message, error: err }
    }
  }

  // Debug invoice endpoint - test what the server returns
  const debugInvoiceEndpoint = async (orderId) => {
    try {
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
      
      let debugUrl
      if (apiBaseUrl.startsWith('http')) {
        debugUrl = `${apiBaseUrl}/customer/product-orders/${orderId}/invoice`
      } else {
        const baseUrl = window.location.origin
        debugUrl = `${baseUrl}${apiBaseUrl}/customer/product-orders/${orderId}/invoice`
      }
      
      console.log('=== DEBUGGING INVOICE ENDPOINT ===')
      console.log('URL:', debugUrl)
      console.log('Order ID:', orderId)
      console.log('Auth Token:', customerAccessToken.value ? 'Present' : 'Missing')
      
      const response = await fetch(debugUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Accept': 'application/pdf'
        }
      })
      
      console.log('Response Status:', response.status)
      console.log('Response Status Text:', response.statusText)
      console.log('Response Headers:', Object.fromEntries(response.headers.entries()))
      
      const contentType = response.headers.get('content-type')
      console.log('Content-Type:', contentType)
      
      if (response.ok) {
        if (contentType && contentType.includes('application/pdf')) {
          const blob = await response.blob()
          console.log('PDF Blob Size:', blob.size, 'bytes')
          console.log('✅ Response appears to be a valid PDF')
          return { success: true, message: 'PDF response received successfully', size: blob.size }
        } else {
          // Not a PDF, let's see what it is
          const text = await response.text()
          console.log('❌ Response is not a PDF. Content:', text.substring(0, 500) + '...')
          return { success: false, message: `Expected PDF but got: ${contentType}`, content: text }
        }
      } else {
        const errorText = await response.text()
        console.log('❌ Request failed:', errorText)
        return { success: false, message: `HTTP ${response.status}: ${response.statusText}`, content: errorText }
      }
      
    } catch (err) {
      console.error('Debug error:', err)
      return { success: false, message: err.message, error: err }
    }
  }

  // Download product order invoice
  const downloadProductOrderInvoice = async (orderId) => {
    try {
      // Check if customer is authenticated
      if (!isCustomerAuthenticated.value || !customerAccessToken.value) {
        throw new Error('Customer authentication required')
      }

      // Use the API base URL from environment or construct it
      const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
      
      // Construct the download URL
      let downloadUrl
      if (apiBaseUrl.startsWith('http')) {
        // Full URL provided
        downloadUrl = `${apiBaseUrl}/customer/product-orders/${orderId}/invoice`
      } else {
        // Relative URL - use current origin
        const baseUrl = window.location.origin
        downloadUrl = `${baseUrl}${apiBaseUrl}/customer/product-orders/${orderId}/invoice`
      }
      
      console.log('Attempting to download from:', downloadUrl)
      
      const response = await fetch(downloadUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`,
          'Accept': 'application/pdf'
        }
      })
      
      console.log('Response status:', response.status)
      console.log('Response headers:', Object.fromEntries(response.headers.entries()))
      
      if (!response.ok) {
        // Log the response for debugging
        const errorText = await response.text()
        console.error('Response error:', response.status, errorText)
        throw new Error(`HTTP error! status: ${response.status} - ${errorText}`)
      }
      
      // Check if the response is actually a PDF
      const contentType = response.headers.get('content-type')
      console.log('Content-Type:', contentType)
      
      if (!contentType || !contentType.includes('application/pdf')) {
        // If it's not a PDF, log the actual content
        const responseText = await response.text()
        console.error('Expected PDF but got:', contentType)
        console.error('Response content:', responseText)
        throw new Error(`Expected PDF but received: ${contentType}. The server might be returning an error page.`)
      }
      
      // Get the blob from response
      const blob = await response.blob()
      
      // Verify the blob is not empty
      if (blob.size === 0) {
        throw new Error('Received empty PDF file')
      }
      
      console.log('PDF blob size:', blob.size, 'bytes')
      
      // Create object URL and trigger download
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `product-order-invoice-${orderId}.pdf`
      link.style.display = 'none'
      
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      window.URL.revokeObjectURL(url)
      
      console.log('PDF download triggered successfully')
      
    } catch (err) {
      error.value = err.message || 'Failed to download invoice'
      console.error('Download invoice error:', err)
      throw err
    }
  }
  
  // Computed properties
  const hasError = computed(() => !!error.value)
  const hasOrders = computed(() => productOrders.value && productOrders.value.length > 0)
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
    productOrders,
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
    fetchProductOrders,
    searchOrders,
    goToPage,
    nextPage,
    prevPage,
    getStatusClass,
    fetchProductOrderDetails,
    initializeProductOrders,
    downloadProductOrderInvoice,
    testRoute,
    testPdfGeneration,
    debugInvoiceEndpoint
  }
} 