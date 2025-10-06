import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useStaffEarningReport() {
    // State
    const earningData = ref(null)
    const isLoading = ref(false)
    const earnings = ref([])
    const totalEarnings = ref(0)
    const summary = ref({
        totalEarnings: 0,
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
            const response = await $api('/staff-earning-report-filters', {
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

    const fetchEarningReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/staff-earning-report', {
                method: 'GET',
                query: queryParams,
            })
            earningData.value = response.data
            earnings.value = response.data.earnings
            totalEarnings.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching staff earning report:', error)
            toast('Failed to load staff earning report', {
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
        fetchEarningReport()
    }

   
    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchEarningReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchEarningReport()
    })

    return {
        // State
        earningData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchEarningReport,
        resetFilters,
        
        // Computed
        earnings,
        totalEarnings,
        summary,
    }
}
