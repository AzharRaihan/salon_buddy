import { ref, computed } from 'vue'

/**
 * Service Management Composable
 * Handles service filtering, sorting, and pagination with server-side support
 */
export function useServiceManagement() {
  // State
  const allServices = ref([])
  const selectedCategories = ref([])
  const sortBy = ref('Price: Low to High')
  const currentPage = ref(1)
  const perPage = ref(9)
  const loading = ref(false)
  const totalPages = ref(1)
  const totalResults = ref(0)

  // Sort options
  const sortOptions = [
    'Price: Low to High',
    'Price: High to Low',
    'Name: A to Z',
    'Name: Z to A',
    'Rating: High to Low'
  ]

  // Computed
  const paginatedServices = computed(() => allServices.value)

  // Sort mapping for API
  const sortMapping = {
    'Price: Low to High': 'price_asc',
    'Price: High to Low': 'price_desc',
    'Name: A to Z': 'name_asc',
    'Name: Z to A': 'name_desc',
    'Rating: High to Low': 'rating_desc'
  }

  // Methods
  const loadServices = async (fetchServicesPaginated) => {
    try {
      loading.value = true
      
      const params = {
        page: currentPage.value,
        perPage: perPage.value,
        categoryIds: selectedCategories.value.length > 0 ? selectedCategories.value : null,
        sort: sortMapping[sortBy.value] || 'price_asc'
      }
      
      const result = await fetchServicesPaginated(params)
      
      allServices.value = result.data || []
      totalPages.value = result.last_page || 1
      totalResults.value = result.total || 0
      
    } catch (error) {
      console.error('Error loading services:', error)
      allServices.value = []
      totalPages.value = 1
      totalResults.value = 0
    } finally {
      loading.value = false
    }
  }

  const handleCategoryChange = async (categoryId, fetchServicesPaginated) => {
    const index = selectedCategories.value.indexOf(categoryId)
    if (index > -1) {
      // Remove category if already selected
      selectedCategories.value.splice(index, 1)
    } else {
      // Add category if not selected (multiple selection)
      selectedCategories.value.push(categoryId)
    }
    currentPage.value = 1
    await loadServices(fetchServicesPaginated)
  }

  const handleSortChange = async (newSortBy, fetchServicesPaginated) => {
    sortBy.value = newSortBy
    currentPage.value = 1
    await loadServices(fetchServicesPaginated)
  }

  const goToPage = async (page, fetchServicesPaginated) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
      await loadServices(fetchServicesPaginated)
    }
  }

  const resetFilters = async (fetchServicesPaginated) => {
    selectedCategories.value = []
    sortBy.value = 'Price: Low to High'
    currentPage.value = 1
    await loadServices(fetchServicesPaginated)
  }

  const initializeServices = async (fetchServicesPaginated) => {
    await loadServices(fetchServicesPaginated)
  }

  return {
    // State
    allServices,
    selectedCategories,
    sortBy,
    currentPage,
    perPage,
    loading,
    totalPages,
    totalResults,
    sortOptions,
    
    // Computed
    paginatedServices,
    
    // Methods
    loadServices,
    handleCategoryChange,
    handleSortChange,
    goToPage,
    resetFilters,
    initializeServices
  }
}
