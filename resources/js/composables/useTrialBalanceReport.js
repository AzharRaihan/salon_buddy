import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useTrialBalanceReport() {
    // State
    const trialBalanceData = ref(null)
    const isLoading = ref(false)
    const trialBalance = ref([])
    const totalRecords = ref(0)
    const summary = ref({
        totalDebit: 0,
        totalCredit: 0,
        difference: 0
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
            const response = await $api('/trial-balance-report-filters', {
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

    const fetchTrialBalanceReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/trial-balance-report', {
                method: 'GET',
                query: queryParams,
            })
            trialBalanceData.value = response.data
            trialBalance.value = response.data.trialBalance || []
            totalRecords.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching trial balance report:', error)
            toast('Failed to load trial balance report', {
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
        fetchTrialBalanceReport()
    }

   
    // Watch for changes in filters
    watch([branchId, dateFrom, dateTo], () => {
        fetchTrialBalanceReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchTrialBalanceReport()
    })

    return {
        // State
        trialBalanceData,
        isLoading,
        branchId,
        dateFrom,
        dateTo,
        branches,
        
        // Methods
        fetchFilterOptions,
        fetchTrialBalanceReport,
        resetFilters,
        
        // Computed
        trialBalance,
        totalRecords,
        summary,
    }
}

