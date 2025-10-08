import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useStaffEvaluationReport() {
    // State
    const evaluationData = ref(null)
    const isLoading = ref(false)
    const evaluations = ref([])
    const totalEvaluations = ref(0)
    const summary = ref({
        totalEmployees: 0,
        totalRatings: 0,
        avgRating: 0
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
            const response = await $api('/staff-evaluation-report-filters', {
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

    const fetchEvaluationReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/staff-evaluation-report', {
                method: 'GET',
                query: queryParams,
            })
            evaluationData.value = response.data
            evaluations.value = response.data.evaluations
            totalEvaluations.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching staff evaluation report:', error)
            toast('Failed to load staff evaluation report', {
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
        fetchEvaluationReport()
    }

   
    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchEvaluationReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchEvaluationReport()
    })

    return {
        // State
        evaluationData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchEvaluationReport,
        resetFilters,
        
        // Computed
        evaluations,
        totalEvaluations,
        summary,
    }
}

