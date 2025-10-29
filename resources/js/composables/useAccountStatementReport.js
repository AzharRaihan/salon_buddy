import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useAccountStatementReport() {
    // State
    const accountStatementData = ref(null)
    const isLoading = ref(false)
    const statements = ref([])
    const totalRecords = ref(0)
    const summary = ref({
        totalDebit: 0,
        totalCredit: 0,
        openingBalance: 0,
        closingBalance: 0
    })

    // Filter states
    const branchId = ref('')
    const paymentMethodId = ref('')
    
    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref('')
    const dateTo = ref('')

    // Filter options
    const branches = ref([])
    const paymentMethods = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/account-statement-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches || []
            paymentMethods.value = response.data.paymentMethods || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchAccountStatementReport = async () => {
        // Don't fetch if payment method is not selected
        if (!paymentMethodId.value) {
            statements.value = []
            totalRecords.value = 0
            summary.value = {
                totalDebit: 0,
                totalCredit: 0,
                openingBalance: 0,
                closingBalance: 0
            }
            return
        }

        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                payment_method_id: paymentMethodId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/account-statement-report', {
                method: 'GET',
                query: queryParams,
            })
            accountStatementData.value = response.data
            statements.value = response.data.statements || []
            totalRecords.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching account statement report:', error)
            toast('Failed to load account statement report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        paymentMethodId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchAccountStatementReport()
    }

   
    // Watch for changes in filters
    watch([branchId, paymentMethodId, dateFrom, dateTo], () => {
        fetchAccountStatementReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        // Don't fetch on mount - wait for payment method to be selected
    })

    return {
        // State
        accountStatementData,
        isLoading,
        branchId,
        paymentMethodId,
        dateFrom,
        dateTo,
        branches,
        paymentMethods,
        
        // Methods
        fetchFilterOptions,
        fetchAccountStatementReport,
        resetFilters,
        
        // Computed
        statements,
        totalRecords,
        summary,
    }
}

