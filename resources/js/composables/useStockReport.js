import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

export function useStockReport() {
    // State
    const stockData = ref(null)
    const stockSummary = ref(null)
    const isLoading = ref(false)

    // Filter states
    const supplierId = ref('')
    const itemId = ref('')
    const categoryId = ref('')
    const totalItems = ref(0)
    const summary = ref(null)

    // Filter options
    const suppliers = ref([])
    const items = ref([])
    const categories = ref([])

    // Company formatters
    const { fetchCompanySettings, formatDate, formatAmount, formatNumber, getSerialNumber } = useCompanyFormatters()

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/stock-report-filters', {
                method: 'GET',
            })
            
            suppliers.value = response.data.suppliers || []
            items.value = response.data.items || []
            categories.value = response.data.categories || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchStockReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                supplier_id: supplierId.value,
                item_id: itemId.value,
                category_id: categoryId.value,
            }

            const response = await $api('/stock-report', {
                method: 'GET',
                query: queryParams,
            })
            stockData.value = response.data
            totalItems.value = response.data.total
        } catch (error) {
            console.error('Error fetching stock report:', error)
            toast('Failed to load stock report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const fetchStockSummary = async () => {
        try {
            const response = await $api('/stock-summary', {
                method: 'GET',
                query: {
                    item_id: itemId.value,
                    supplier_id: supplierId.value,
                    category_id: categoryId.value,
                },
            })
            summary.value = response.data
        } catch (error) {
            console.error('Error fetching stock summary:', error)
        }
    }

    const resetFilters = () => {
        supplierId.value = ''
        itemId.value = ''
        categoryId.value = ''
        fetchStockReport()
        fetchStockSummary()
    }

    
    // Computed properties
    const stocks = computed(() => {
        const data = stockData.value?.items || []
        return data.map((item, index) => ({
            ...item,
            serial_number: getSerialNumber(index, totalItems.value, 1, data.length),
            // Calculate total price using last_purchase_price like in stock.vue
            total_price: item?.last_purchase_price * item.stock || 0
        }))
    })
    
   

    // Watch for changes in filters
    watch([supplierId, itemId, categoryId], () => {
        fetchStockReport()
        fetchStockSummary()
    })

    // Initialize data
    onMounted(async () => {
        await fetchCompanySettings()
        await fetchFilterOptions()
        fetchStockReport()
        fetchStockSummary()
    })

    return {
        // State
        stockData,
        stockSummary,
        isLoading,
        supplierId,
        itemId,
        categoryId,
        suppliers,
        items,
        categories,
        
        // Methods
        fetchFilterOptions,
        fetchStockReport,
        fetchStockSummary,
        resetFilters,
        
        // Computed
        stocks,
        totalItems,
        summary,
        
        // Formatters
        formatAmount,
        formatNumber,
        getSerialNumber,
    }
}
