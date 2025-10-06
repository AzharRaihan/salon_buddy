import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useStaffPayoutReport() {
    // State
    const payoutData = ref(null)
    const isLoading = ref(false)
    const payouts = ref([])
    const totalPayouts = ref(0)
    const summary = ref({
        totalPayouts: 0,
        totalAmount: 0,
        avgAmount: 0
    })

    // Filter states
    const branchId = ref('')
    const employeeId = ref('')

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const branches = ref([])
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/staff-payout-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches || []
            employees.value = response.data.employees || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchPayoutReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/staff-payout-report', {
                method: 'GET',
                query: queryParams,
            })
            payoutData.value = response.data
            payouts.value = response.data.payouts
            totalPayouts.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching staff payout report:', error)
            toast('Failed to load staff payout report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        employeeId.value = ''
        dateFrom.value = today.toISOString().split('T')[0]
        dateTo.value = today.toISOString().split('T')[0]
        fetchPayoutReport()
    }

   
    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchPayoutReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchPayoutReport()
    })

    return {
        // State
        payoutData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchPayoutReport,
        resetFilters,
        
        // Computed
        payouts,
        totalPayouts,
        summary,
    }
}
