import { ref, computed } from 'vue'
import { usePagination } from './usePagination'

export function useBookingList() {
  // Initialize pagination
  const {
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    updatePagination,
    goToPage,
    setSearchQuery,
    setLoading,
    setError,
    clearError
  } = usePagination()

  // Reactive data
  const bookings = ref([])
  const selectedBooking = ref(null)
  const selectedBookingDetails = ref(null)
  const loadingBookingDetails = ref(false)
  // Debounced search
  let searchTimeout = null

  // Methods
  const fetchBookings = async () => {
    try {
      setLoading(true)
      clearError()
      
      const response = await $api('/get-booking-list-pos', {
        method: 'GET',
        query: {
          page: currentPage.value,
          itemsPerPage: perPage.value,
          q: searchQuery.value
        }
      })
      
      if (response.success) {
        bookings.value = response.data.bookings || []
        updatePagination({
          total: response.data.total || 0,
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1
        })
      } else {
        setError('Failed to fetch bookings')
      }
    } catch (error) {
      console.error('Error fetching bookings:', error)
      setError('Failed to fetch bookings. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  const showBookingDetails = async (booking) => {
    try {
      setLoading(true)
      const response = await $api(`/booking-details-pos/${booking.id}`, {
        method: 'GET'
      })
      selectedBooking.value = booking
      if (response.success) {
        selectedBookingDetails.value = response.data
        loadingBookingDetails.value = false
      }
    } catch (error) {
      console.error('Error fetching booking details:', error)
    } finally {
      setLoading(false)
      loadingBookingDetails.value = false
    }
  }

  const selectBooking = (booking) => {
    loadingBookingDetails.value = true
    selectedBooking.value = booking
    showBookingDetails(booking)
    document.body.style.paddingRight = '0px'
  }

  const handleSearch = () => {
    // Clear previous timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
      goToPage(1) // Reset to first page when searching
      fetchBookings()
    }, 300)
  }

  const handlePageChange = (page) => {
    goToPage(page)
    fetchBookings()
  }

  const clearSelection = () => {
    selectedBooking.value = null
    selectedBookingDetails.value = null
  }

  // Computed properties
  const hasBookings = computed(() => bookings.value.length > 0)
  const hasSelection = computed(() => !!selectedBooking.value)

  return {
    // State
    bookings,
    selectedBooking,
    selectedBookingDetails,
    
    // Pagination state
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    
    // Computed
    hasBookings,
    hasSelection,
    
    // Methods
    fetchBookings,
    showBookingDetails,
    selectBooking,
    handleSearch,
    handlePageChange,
    clearSelection,
    updatePagination,
    goToPage,
    setSearchQuery,
    setLoading,
    setError,
    clearError,
    loadingBookingDetails
  }
}
