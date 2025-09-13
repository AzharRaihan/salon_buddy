import { ref, computed } from 'vue'

export function usePagination(options = {}) {
  const {
    initialPage = 1,
    initialPerPage = 10,
    initialSearchQuery = '',
    debounceMs = 300
  } = options

  // Reactive state
  const currentPage = ref(initialPage)
  const perPage = ref(initialPerPage)
  const totalItems = ref(0)
  const totalPages = ref(0)
  const searchQuery = ref(initialSearchQuery)
  const isLoading = ref(false)
  const error = ref(null)

  // Computed properties
  const hasData = computed(() => totalItems.value > 0)
  const canGoPrev = computed(() => currentPage.value > 1)
  const canGoNext = computed(() => currentPage.value < totalPages.value)
  
  const paginationInfo = computed(() => {
    const start = totalItems.value > 0 ? ((currentPage.value - 1) * perPage.value) + 1 : 0
    const end = Math.min(currentPage.value * perPage.value, totalItems.value)
    
    return {
      start,
      end,
      total: totalItems.value,
      currentPage: currentPage.value,
      totalPages: totalPages.value,
      perPage: perPage.value,
      hasData: hasData.value
    }
  })

  // Methods
  const updatePagination = (data) => {
    if (data) {
      totalItems.value = data.total || 0
      totalPages.value = data.last_page || Math.ceil(totalItems.value / perPage.value)
      currentPage.value = data.current_page || 1
    }
  }

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  const nextPage = () => {
    if (canGoNext.value) {
      currentPage.value++
    }
  }

  const prevPage = () => {
    if (canGoPrev.value) {
      currentPage.value--
    }
  }

  const resetPagination = () => {
    currentPage.value = 1
    totalItems.value = 0
    totalPages.value = 0
  }

  const setSearchQuery = (query) => {
    searchQuery.value = query
    resetPagination()
  }

  const clearSearch = () => {
    searchQuery.value = ''
    resetPagination()
  }

  const setLoading = (loading) => {
    isLoading.value = loading
  }

  const setError = (err) => {
    error.value = err
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    isLoading,
    error,

    // Computed
    hasData,
    canGoPrev,
    canGoNext,
    paginationInfo,

    // Methods
    updatePagination,
    goToPage,
    nextPage,
    prevPage,
    resetPagination,
    setSearchQuery,
    clearSearch,
    setLoading,
    setError,
    clearError
  }
}
