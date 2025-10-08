import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useStaffEvaluationDetailsReport() {
    // State
    const evaluationDetailsData = ref(null)
    const isLoading = ref(false)
    const evaluationDetails = ref([])
    const totalEvaluationDetails = ref(0)
    const summary = ref({
        totalRatings: 0,
        avgRating: 0,
        totalRatingSum: 0
    })

    // Filter states
    const employeeId = ref('')

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/staff-evaluation-report-filters', {
                method: 'GET',
            })
            
            employees.value = response.data.employees || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchEvaluationDetailsReport = async () => {
        // Only fetch if employee is selected
        if (!employeeId.value) {
            evaluationDetails.value = []
            totalEvaluationDetails.value = 0
            summary.value = {
                totalRatings: 0,
                avgRating: 0,
                totalRatingSum: 0
            }
            return
        }

        isLoading.value = true
        try {
            const queryParams = {
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/staff-evaluation-details-report', {
                method: 'GET',
                query: queryParams,
            })
            evaluationDetailsData.value = response.data
            evaluationDetails.value = response.data.evaluationDetails
            totalEvaluationDetails.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching staff evaluation details report:', error)
            toast('Failed to load staff evaluation details report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        employeeId.value = ''
        dateFrom.value = today.toISOString().split('T')[0]
        dateTo.value = today.toISOString().split('T')[0]
        evaluationDetails.value = []
        totalEvaluationDetails.value = 0
        summary.value = {
            totalRatings: 0,
            avgRating: 0,
            totalRatingSum: 0
        }
    }

   
    // Watch for changes in filters
    watch([employeeId, dateFrom, dateTo], () => {
        fetchEvaluationDetailsReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        // Don't fetch on mount - wait for employee selection
    })

    return {
        // State
        evaluationDetailsData,
        isLoading,
        employeeId,
        dateFrom,
        dateTo,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchEvaluationDetailsReport,
        resetFilters,
        
        // Computed
        evaluationDetails,
        totalEvaluationDetails,
        summary,
    }
}

