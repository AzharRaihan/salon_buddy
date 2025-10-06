import { ref, computed } from 'vue'


export function useSearch() {
  const searchQuery = ref('')
  const searchResults = ref([])
  const isSearching = ref(false)
  const searchError = ref(null)
  const searchType = ref('services') // Default to services for the service page
  const searchPagination = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  })

  // Debounced search function
  let searchTimeout = null
  const performSearch = async (query, type = 'services', page = 1, perPage = 12) => {
    if (!query || query.trim().length < 1) {
      searchResults.value = []
      searchPagination.value = {
        current_page: 1,
        per_page: perPage,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      }
      return
    }

    isSearching.value = true
    searchError.value = null

    try {
      const response = await $api('/search', {
        params: {
          q: query.trim(),
          type: type,
          page: page,
          per_page: perPage
        }
      })

      if (response.success) {
        searchResults.value = response.data.results || []
        searchPagination.value = {
          current_page: response.data.current_page,
          per_page: response.data.per_page,
          total: response.data.total,
          last_page: response.data.last_page,
          from: response.data.from,
          to: response.data.to
        }
      } else {
        searchError.value = response.message || 'Search failed'
        searchResults.value = []
      }
    } catch (error) {
      console.error('Search error:', error)
      searchError.value = error.response?.message || 'Search failed'
      searchResults.value = []
    } finally {
      isSearching.value = false
    }
  }

  // Debounced search with delay
  const debouncedSearch = (query, type = 'services', page = 1, perPage = 12, delay = 500) => {
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }

    // Don't search for very short queries
    if (query && query.trim().length < 2) {
      searchResults.value = []
      return
    }

    searchTimeout = setTimeout(() => {
      performSearch(query, type, page, perPage)
    }, delay)
  }

  // Clear search
  const clearSearch = () => {
    searchQuery.value = ''
    searchResults.value = []
    searchError.value = null
    searchPagination.value = {
      current_page: 1,
      per_page: 12,
      total: 0,
      last_page: 1,
      from: 0,
      to: 0
    }
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
  }

  // Check if we have search results
  const hasSearchResults = computed(() => {
    return searchResults.value.length > 0
  })

  // Check if search is active
  const isSearchActive = computed(() => {
    return searchQuery.value.trim().length > 0
  })

  // Get search result count
  const searchResultCount = computed(() => {
    return searchPagination.value.total
  })

  return {
    // State
    searchQuery,
    searchResults,
    isSearching,
    searchError,
    searchType,
    searchPagination,
    
    // Computed
    hasSearchResults,
    isSearchActive,
    searchResultCount,
    
    // Methods
    performSearch,
    debouncedSearch,
    clearSearch
  }
}