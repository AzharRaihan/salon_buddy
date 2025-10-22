import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useAccountBalanceReport() {
    // State
    const accountData = ref(null)
    const isLoading = ref(false)
    const accounts = ref([])
    const totalAccounts = ref(0)
    const summary = ref({
        totalBalance: 0
    })

    // Filter states
    const branchId = ref('')

    // Filter options
    const branches = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/account-balance-report-filters', {
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

    const fetchAccountBalanceReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
            }

            const response = await $api('/account-balance-report', {
                method: 'GET',
                query: queryParams,
            })
            accountData.value = response.data
            accounts.value = response.data.accounts
            totalAccounts.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching account balance report:', error)
            toast('Failed to load account balance report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        fetchAccountBalanceReport()
    }

   
    // Watch for changes in filters
    watch([branchId], () => {
        fetchAccountBalanceReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchAccountBalanceReport()
    })

    return {
        // State
        accountData,
        isLoading,
        branchId,
        branches,
        
        // Methods
        fetchFilterOptions,
        fetchAccountBalanceReport,
        resetFilters,
        
        // Computed
        accounts,
        totalAccounts,
        summary,
    }
}

