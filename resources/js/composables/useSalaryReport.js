import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useSalaryReport() {
    // State
    const salaryData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const employeeId = ref('')
    const salaries = ref([])
    const totalSalaries = ref(0)
    

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
            const response = await $api('/salary-report-filters', {
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

    const fetchSalaryReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/salary-report', {
                method: 'GET',
                query: queryParams,
            })
            salaryData.value = response.data

            salaries.value = response.data.salaries
            totalSalaries.value = response.data.total_amount

        } catch (error) {
            console.error('Error fetching salary report:', error)
            toast('Failed to load salary report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        employeeId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchSalaryReport()
    }


    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchSalaryReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchSalaryReport()
    })

    return {
        // State
        salaryData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchSalaryReport,
        resetFilters,

        // Computed
        salaries,
        totalSalaries,
    }
}
