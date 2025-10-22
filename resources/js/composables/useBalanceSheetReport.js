import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useBalanceSheetReport() {
    // State
    const balanceSheetData = ref(null)
    const isLoading = ref(false)
    const assets = ref([])
    const liabilities = ref([])
    const summary = ref({
        totalAssets: 0,
        totalLiabilities: 0,
        netWorth: 0
    })

    // Filter states
    const branchId = ref('')
    
    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref('')
    const dateTo = ref('')

    // Filter options
    const branches = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/balance-sheet-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchBalanceSheetReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/balance-sheet-report', {
                method: 'GET',
                query: queryParams,
            })
            balanceSheetData.value = response.data
            assets.value = response.data.assets || []
            liabilities.value = response.data.liabilities || []
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching balance sheet report:', error)
            toast('Failed to load balance sheet report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchBalanceSheetReport()
    }

   
    // Watch for changes in filters
    watch([branchId, dateFrom, dateTo], () => {
        fetchBalanceSheetReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchBalanceSheetReport()
    })

    return {
        // State
        balanceSheetData,
        isLoading,
        branchId,
        dateFrom,
        dateTo,
        branches,
        
        // Methods
        fetchFilterOptions,
        fetchBalanceSheetReport,
        resetFilters,
        
        // Computed
        assets,
        liabilities,
        summary,
    }
}

