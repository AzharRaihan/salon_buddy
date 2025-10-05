import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const searchQuery = ref('')
const searchResults = ref([])
const isLoading = ref(false)
const searchType = ref('all') // 'all', 'products', 'services', 'packages'
const currentPage = ref(1)
const totalPages = ref(1)
const totalResults = ref(0)

export function useSearch() {
  const router = useRouter()

  // Perform search
  const performSearch = async (query, type = 'all', page = 1) => {
    if (!query.trim()) {
      searchResults.value = []
      return
    }

    isLoading.value = true
    searchQuery.value = query
    searchType.value = type
    currentPage.value = page

    try {

      const response = await $api('/search', {
        params: {
          q: query,
          type: type,
          page: page,
          per_page: 12
        }
      })


      if (response.success) {
        console.log(response.data.results)
        searchResults.value = response.data.results
        currentPage.value = response.data.current_page
        totalPages.value = response.data.last_page
        totalResults.value = response.data.total
      } else {
        searchResults.value = []
        totalResults.value = 0
      }
    } catch (error) {
      console.error('Search error:', error)
      searchResults.value = []
      totalResults.value = 0
    } finally {
      isLoading.value = false
    }
  }

  // Navigate to search results page
  const navigateToSearch = (query) => {
    if (!query.trim()) return
    
    router.push({
      name: 'search-result',
      query: { q: query }
    })
  }

  // Clear search
  const clearSearch = () => {
    searchQuery.value = ''
    searchResults.value = []
    currentPage.value = 1
    totalPages.value = 1
    totalResults.value = 0
    searchType.value = 'all'
  }

  // Load more results (pagination)
  const loadMore = () => {
    if (currentPage.value < totalPages.value && !isLoading.value) {
      performSearch(searchQuery.value, searchType.value, currentPage.value + 1)
    }
  }

  // Computed properties
  const hasResults = computed(() => searchResults.value.length > 0)
  const hasMorePages = computed(() => currentPage.value < totalPages.value)
  const searchSummary = computed(() => {
    if (totalResults.value === 0) return 'No results found'
    return `Found ${totalResults.value} result${totalResults.value === 1 ? '' : 's'} for "${searchQuery.value}"`
  })

  return {
    // State
    searchQuery,
    searchResults,
    isLoading,
    searchType,
    currentPage,
    totalPages,
    totalResults,
    
    // Computed
    hasResults,
    hasMorePages,
    searchSummary,
    
    // Methods
    performSearch,
    navigateToSearch,
    clearSearch,
    loadMore
  }
}
