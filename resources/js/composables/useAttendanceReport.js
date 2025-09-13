import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useAttendanceReport() {
    // State
    const attendanceData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const employeeId = ref('')

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(sevenDaysAgo.toISOString().split('T')[0])
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

    const exportReport = async (format = 'pdf') => {
        const params = {
            employee_id: employeeId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            format,
        }

        if (format === 'pdf' || format === 'excel' || format === 'csv') {
            try {
                const response = await $api('/attendance-report-export', {
                    method: 'GET',
                    query: params,
                    responseType: 'blob',
                })
                const ext = format === 'pdf' ? 'pdf' : format === 'excel' ? 'xlsx' : 'csv'
                saveAs(new Blob([response.data]), `attendance-report.${ext}`)
            } catch (error) {
                toast('Failed to export report', { type: 'error' })
            }
        }
    }

    // Computed properties
    const attendances = computed(() => attendanceData.value?.attendances || [])
    const totalAttendances = computed(() => attendanceData.value?.total_attendances || 0)
    const summary = computed(() => attendanceData.value?.summary || {
        total_records: 0,
        total_work_days: 0,
        total_absent_days: 0,
        total_late_days: 0,
        avg_working_hours: 0
    })

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
        exportReport,
        
        // Computed
        attendances,
        totalAttendances,
        summary,
    }
}