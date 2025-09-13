import { computed, ref, watch } from 'vue'
import { toast } from 'vue3-toastify'

export function useDataTable(apiEndpoint, options = {}) {
    const {
        searchQuery = ref(''),
        itemsPerPage = ref(10),
        page = ref(1),
        sortBy = ref(),
        orderBy = ref(),
        dataKey = 'data', // Key for the main data array in response
        totalKey = 'total', // Key for total count in response
        additionalQueryParams = {}
    } = options

    const tableData = ref(null)
    const isConfirmDialogOpen = ref(false)
    const selectedItemId = ref(null)
    const isLoading = ref(false)

    const updateOptions = options => {
        sortBy.value = options.sortBy[0]?.key
        orderBy.value = options.sortBy[0]?.order
    }

    const fetchData = async () => {
        isLoading.value = true
        try {
            const response = await $api(apiEndpoint, {
                method: 'GET',
                query: {
                    q: searchQuery.value,
                    itemsPerPage: itemsPerPage.value,
                    page: page.value,
                    sortBy: sortBy.value,
                    orderBy: orderBy.value,
                    ...additionalQueryParams
                },
            })
            
            // Debug: Log the response structure
            console.log('API Response:', response)
            console.log('Response data:', response.data)
            
            tableData.value = response.data
        } catch (error) {
            console.error('Error fetching data:', error)
            toast('Failed to fetch data', { type: 'error' })
        } finally {
            isLoading.value = false
        }
    }

    // Computed properties that handle different response structures
    const items = computed(() => {
        if (!tableData.value) return []
        
        // Try different possible data structures
        if (tableData.value[dataKey]) {
            return tableData.value[dataKey]
        }
        if (Array.isArray(tableData.value)) {
            return tableData.value
        }
        if (tableData.value.data && Array.isArray(tableData.value.data)) {
            return tableData.value.data
        }
        if (tableData.value.items) {
            return tableData.value.items
        }
        if (tableData.value.expenses) {
            return tableData.value.expenses
        }
        
        console.warn('Could not find data array in response:', tableData.value)
        return []
    })

    const total = computed(() => {
        if (!tableData.value) return 0
        
        // Try different possible total structures
        if (tableData.value[totalKey] !== undefined) {
            return tableData.value[totalKey]
        }
        if (tableData.value.total !== undefined) {
            return tableData.value.total
        }
        if (tableData.value.meta?.total !== undefined) {
            return tableData.value.meta.total
        }
        
        console.warn('Could not find total count in response:', tableData.value)
        return 0
    })

    const openConfirmDialog = (itemId) => {
        isConfirmDialogOpen.value = true
        selectedItemId.value = itemId
    }

    const handleDelete = async (confirmed, deleteEndpoint) => {
        if (!confirmed) return

        try {
            await $api(deleteEndpoint, {
                method: 'DELETE',
            })
            selectedItemId.value = null
            isConfirmDialogOpen.value = false
            await fetchData()
            toast('Item deleted successfully', { type: 'success' })
        } catch (error) {
            isConfirmDialogOpen.value = false
            selectedItemId.value = null
            console.error('Error deleting item:', error)
            toast('Failed to delete item', { type: 'error' })
        }
    }

    // Watch for changes in search query
    watch(searchQuery, () => {
        page.value = 1 // Reset to first page when searching
        fetchData()
    })

    // Watch for changes in pagination
    watch([page, itemsPerPage], () => {
        fetchData()
    })

    return {
        // State
        tableData,
        isLoading,
        isConfirmDialogOpen,
        selectedItemId,
        searchQuery,
        itemsPerPage,
        page,
        sortBy,
        orderBy,
        
        // Computed
        items,
        total,
        
        // Methods
        fetchData,
        updateOptions,
        openConfirmDialog,
        handleDelete
    }
} 