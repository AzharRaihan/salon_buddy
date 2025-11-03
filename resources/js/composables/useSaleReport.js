import { ref, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useSaleReport() {
    // State
    const saleData = ref(null)
    const isLoading = ref(false)

    const sales = ref([])
    const totalSales = ref(0)
    const summary = ref({
        total_amount: 0,
        total_paid: 0,
        total_due: 0,
        total_items: 0
    })

    // Filter states
    const branchId = ref('')
    const customerId = ref('')


    // Filter options
    const branches = ref([])
    const customers = ref([])

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(sevenDaysAgo.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/sale-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches
            customers.value = response.data.customers
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchSaleReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                customer_id: customerId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/sales-report', {
                method: 'GET',
                query: queryParams,
            })
            saleData.value = response.data
            sales.value = response.data.sales
            totalSales.value = response.data.total
            summary.value = response.data.summary
        } catch (error) {
            console.error('Error fetching sale report:', error)
            toast('Failed to load sale report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        customerId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchSaleReport()
    }

    // Watch for changes in filters
    watch([branchId, customerId, dateFrom, dateTo], () => {
        fetchSaleReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchSaleReport()
    })

    return {
        // State
        saleData,
        isLoading,
        branchId,
        customerId,
        dateFrom,
        dateTo,
        branches,
        customers,
        
        // Methods
        fetchFilterOptions,
        fetchSaleReport,
        resetFilters,
        
        // Computed
        sales,
        totalSales,
        summary,
    }
}
