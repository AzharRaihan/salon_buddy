import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import { saveAs } from 'file-saver'

export function useProfitLossReport() {
    // State
    const profitLossData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const costingMethod = ref('last_purchase') // 'last_purchase' or 'avg_purchase'

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)

    const dateFrom = ref(sevenDaysAgo.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const branches = ref([])
    const costingMethods = ref([
        { title: 'Last Purchase Price', value: 'last_purchase' },
        { title: 'Last 3 Purchase Average', value: 'avg_purchase' }
    ])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/profit-loss-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchProfitLossReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
                costing_method: costingMethod.value,
            }

            const response = await $api('/profit-loss-report', {
                method: 'GET',
                query: queryParams,
            })
            profitLossData.value = response.data
        } catch (error) {
            console.error('Error fetching profit loss report:', error)
            toast('Failed to load profit loss report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        costingMethod.value = 'last_purchase'
        fetchProfitLossReport()
    }

    const exportReport = async (format = 'pdf') => {
        const params = {
            branch_id: branchId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            costing_method: costingMethod.value,
            format,
        }

        if (format === 'pdf' || format === 'excel' || format === 'csv') {
            try {
                const response = await $api('/profit-loss-report-export', {
                    method: 'GET',
                    query: params,
                    responseType: 'blob',
                })
                const ext = format === 'pdf' ? 'pdf' : format === 'excel' ? 'xlsx' : 'csv'
                saveAs(new Blob([response.data]), `profit-loss-report.${ext}`)
            } catch (error) {
                toast('Failed to export report', { type: 'error' })
            }
        }
    }

    // Computed properties
    const reportData = computed(() => profitLossData.value || {})

    // Watch for changes in filters
    watch([branchId, dateFrom, dateTo, costingMethod], () => {
        fetchProfitLossReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchProfitLossReport()
    })

    return {
        // State
        profitLossData,
        isLoading,
        branchId,
        dateFrom,
        dateTo,
        costingMethod,
        branches,
        costingMethods,
        
        // Methods
        fetchFilterOptions,
        fetchProfitLossReport,
        resetFilters,
        exportReport,
        
        // Computed
        reportData,
    }
}
