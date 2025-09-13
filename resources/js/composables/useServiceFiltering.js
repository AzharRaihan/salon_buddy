import { ref, computed } from 'vue'

export function useServiceFiltering() {
  // Reactive state
  const allServices = ref([])
  const selectedCategories = ref([])
  const sortBy = ref('Price: Low to High')
  const currentPage = ref(1)
  const perPage = ref(9)
  const loading = ref(false)

  // Sorting options
  const sortOptions = [
    'Price: Low to High',
    'Price: High to Low',
    'Name: A to Z',
    'Name: Z to A',
    'Rating: High to Low'
  ]

  // Computed properties
  const filteredAndSortedServices = computed(() => {
    let services = [...allServices.value]
    
    // Filter by selected categories
    if (selectedCategories.value.length > 0) {
      services = services.filter(service => 
        selectedCategories.value.includes(service.category_id)
      )
    }
    
    // Sort services
    switch (sortBy.value) {
      case 'Price: Low to High':
        services.sort((a, b) => parseFloat(a.price) - parseFloat(b.price))
        break
      case 'Price: High to Low':
        services.sort((a, b) => parseFloat(b.price) - parseFloat(a.price))
        break
      case 'Name: A to Z':
        services.sort((a, b) => a.name.localeCompare(b.name))
        break
      case 'Name: Z to A':
        services.sort((a, b) => b.name.localeCompare(a.name))
        break
      case 'Rating: High to Low':
        services.sort((a, b) => parseFloat(b.rating) - parseFloat(a.rating))
        break
      default:
        break
    }
    
    return services
  })

  // Paginated services
  const paginatedServices = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredAndSortedServices.value.slice(start, end)
  })

  // Total pages
  const totalPages = computed(() => {
    return Math.ceil(filteredAndSortedServices.value.length / perPage.value)
  })

  // Total results
  const totalResults = computed(() => {
    return filteredAndSortedServices.value.length
  })

  // Methods
  const handleCategoryChange = (categoryId) => {
    const index = selectedCategories.value.indexOf(categoryId)
    if (index > -1) {
      selectedCategories.value.splice(index, 1)
    } else {
      selectedCategories.value.push(categoryId)
    }
    currentPage.value = 1 // Reset to first page when filtering
  }

  const handleSortChange = (newSortBy) => {
    sortBy.value = newSortBy
    currentPage.value = 1 // Reset to first page when sorting
  }

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  const resetFilters = () => {
    selectedCategories.value = []
    sortBy.value = 'Price: Low to High'
    currentPage.value = 1
  }

  return {
    // State
    allServices,
    selectedCategories,
    sortBy,
    currentPage,
    perPage,
    loading,
    sortOptions,
    
    // Computed
    filteredAndSortedServices,
    paginatedServices,
    totalPages,
    totalResults,
    
    // Methods
    handleCategoryChange,
    handleSortChange,
    goToPage,
    resetFilters
  }
} 