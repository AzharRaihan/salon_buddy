import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useEmployeeCommissionReport() {
    // State
    const commissionData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const employeeId = ref('')

    const commissions = ref([]);
    const totalCommissions = ref(0);
    const summary = ref({});

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(sevenDaysAgo.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const branches = ref([])
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/employee-commission-report-filters', {
                method: 'GET',
            })
            branches.value = response.data.branches || []
            employees.value = [...response.data.employees]
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchCommissionReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/employee-commission-report', {
                method: 'GET',
                query: queryParams,
            })
            commissionData.value = response.data

            commissions.value = response.data.commissions
            totalCommissions.value = response.data.total_commissions
            summary.value = response.data.summary
        } catch (error) {
            console.error('Error fetching commission report:', error)
            toast('Failed to load commission report', {
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
        fetchCommissionReport()
    }


    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchCommissionReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchCommissionReport()
    })

    return {
        // State
        commissionData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchCommissionReport,
        resetFilters,
        
        // Computed
        commissions,
        totalCommissions,
        summary,
    }
}