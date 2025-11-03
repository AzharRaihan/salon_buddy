import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useExpenseReport() {
    // State
    const expenseData = ref(null)
    const isLoading = ref(false)
    const expenses = ref([])
    const totalExpenses = ref(0)
    const summary = ref({
        totalExpenses: 0,
        totalAmount: 0,
        avgAmount: 0
    })

    // Filter states
    const branchId = ref('')
    const categoryId = ref('')
    const employeeId = ref('')

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const branches = ref([])
    const categories = ref([])
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/expense-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches || []
            categories.value = response.data.categories || []
            employees.value = response.data.employees || []
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchExpenseReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                category_id: categoryId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
                employee_id: employeeId.value,
            }

            const response = await $api('/expense-report', {
                method: 'GET',
                query: queryParams,
            })
            expenseData.value = response.data
            expenses.value = response.data.expenses
            totalExpenses.value = response.data.total
            summary.value = response.data.summary


        } catch (error) {
            console.error('Error fetching expense report:', error)
            toast('Failed to load expense report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        categoryId.value = ''
        employeeId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchExpenseReport()
    }

   
    // Watch for changes in filters
    watch([branchId, categoryId, employeeId, dateFrom, dateTo], () => {
        fetchExpenseReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchExpenseReport()
    })

    return {
        // State
        expenseData,
        isLoading,
        branchId,
        categoryId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        categories,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchExpenseReport,
        resetFilters,
        
        // Computed
        expenses,
        totalExpenses,
        summary,
    }
}