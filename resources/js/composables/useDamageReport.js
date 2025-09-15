import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import { saveAs } from 'file-saver'

export function useDamageReport() {
    // State
    const damageData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const employeeId = ref('')
    


    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    const damages = ref([]);
    const totalDamages = ref(0);
    const summary = ref({});
    // Filter options
    const branches = ref([])
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/damage-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches
            employees.value = response.data.employees
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchDamageReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }
            const response = await $api('/damage-report', {
                method: 'GET',
                query: queryParams,
            })
            damageData.value = response.data
            
            damages.value = response.data.damages
            totalDamages.value = response.data.total
            summary.value = response.data.summary

        } catch (error) {
            console.error('Error fetching damage report:', error)
            toast('Failed to load damage report', {
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
        fetchDamageReport()
    }

    // Watch for changes in filters
    watch([branchId, employeeId, dateFrom, dateTo], () => {
        fetchDamageReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchDamageReport()
    })

    return {
        // State
        damageData,
        isLoading,
        branchId,
        employeeId,
        dateFrom,
        dateTo,
        branches,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchDamageReport,
        resetFilters,
        
        // Computed
        damages,
        totalDamages,
        summary,
    }
}