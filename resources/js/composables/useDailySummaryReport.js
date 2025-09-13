import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import { saveAs } from 'file-saver'

export function useDailySummaryReport() {
    // State
    const dailySummaryData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const selectedDate = ref(new Date().toISOString().split('T')[0]) // Today's date

    // Filter options
    const branches = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/daily-summary-report-filters', {
                method: 'GET',
            })
            
            branches.value = [
                ...response.data.branches
            ]
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchDailySummaryReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                date: selectedDate.value,
            }

            const response = await $api('/daily-summary-report', {
                method: 'GET',
                query: queryParams,
            })
            dailySummaryData.value = response.data
        } catch (error) {
            console.error('Error fetching daily summary report:', error)
            toast('Failed to load daily summary report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        selectedDate.value = new Date().toISOString().split('T')[0]
        fetchDailySummaryReport()
    }

    const exportReport = async (format = 'pdf') => {
        const params = {
            branch_id: branchId.value,
            date: selectedDate.value,
            format,
        }

        if (format === 'pdf' || format === 'excel' || format === 'csv') {
            try {
                const response = await $api('/daily-summary-report-export', {
                    method: 'GET',
                    query: params,
                    responseType: 'blob',
                })
                const ext = format === 'pdf' ? 'pdf' : format === 'excel' ? 'xlsx' : 'csv'
                saveAs(new Blob([response.data]), `daily-summary-report.${ext}`)
            } catch (error) {
                toast('Failed to export report', { type: 'error' })
            }
        }
    }

    // Computed properties
    const reportData = computed(() => dailySummaryData.value || {})
    const sales = computed(() => reportData.value.sales || [])
    const purchases = computed(() => reportData.value.purchases || [])
    const supplierDuePayments = computed(() => reportData.value.supplier_due_payments || [])
    const customerDueReceives = computed(() => reportData.value.customer_due_receives || [])

    // Watch for changes in filters
    watch([branchId, selectedDate], () => {
        fetchDailySummaryReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchDailySummaryReport()
    })

    return {
        // State
        dailySummaryData,
        isLoading,
        branchId,
        selectedDate,
        branches,
        
        // Methods
        fetchFilterOptions,
        fetchDailySummaryReport,
        resetFilters,
        exportReport,
        
        // Computed
        reportData,
        sales,
        purchases,
        supplierDuePayments,
        customerDueReceives,
    }
}
