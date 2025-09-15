import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useAttendanceReport() {
    // State
    const attendanceData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const employeeId = ref('')
    const attendances = ref([])
    const totalAttendances = ref(0)
    const summary = ref({
        total_records: 0,
        total_work_days: 0,
        total_absent_days: 0,
        total_late_days: 0,
        avg_working_hours: 0
    })

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const employees = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/attendance-report-filters', {
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

    const fetchAttendanceReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                employee_id: employeeId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/attendance-report', {
                method: 'GET',
                query: queryParams,
            })

            attendanceData.value = response.data
            attendances.value = response.data.attendances
            totalAttendances.value = response.data.total
            summary.value = response.data.summary


        } catch (error) {
            console.error('Error fetching attendance report:', error)
            toast('Failed to load attendance report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        employeeId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        fetchAttendanceReport()
    }


    // Watch for changes in filters
    watch([employeeId, dateFrom, dateTo], () => {
        fetchAttendanceReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchAttendanceReport()
    })

    return {
        // State
        attendanceData,
        isLoading,
        employeeId,
        dateFrom,
        dateTo,
        employees,
        
        // Methods
        fetchFilterOptions,
        fetchAttendanceReport,
        resetFilters,
        
        // Computed
        attendances,
        totalAttendances,
        summary,
    }
}