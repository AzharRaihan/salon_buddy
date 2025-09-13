import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import { saveAs } from 'file-saver'

export function usePurchaseReport() {
    // State
    const purchaseData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const branchId = ref('')
    const supplierId = ref('')
    const purchases = ref([])
    const totalPurchases = ref(0)
    const summary = ref({
        total_amount: 0,
        total_paid: 0,
        total_due: 0,
        total_items: 0
    })

    const today = new Date()
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(today.getDate() - 7)
    const dateFrom = ref(today.toISOString().split('T')[0])
    const dateTo = ref(today.toISOString().split('T')[0])

    // Filter options
    const branches = ref([])
    const suppliers = ref([])

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/purchase-report-filters', {
                method: 'GET',
            })
            
            branches.value = response.data.branches
            suppliers.value = response.data.suppliers
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', {
                "type": "error",
            })
        }
    }

    const fetchPurchaseReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                branch_id: branchId.value,
                supplier_id: supplierId.value,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }

            const response = await $api('/purchase-report', {
                method: 'GET',
                query: queryParams,
            })
            purchaseData.value = response.data
            purchases.value = response.data.purchases
            totalPurchases.value = response.data.total
            summary.value = response.data.summary
        } catch (error) {
            console.error('Error fetching purchase report:', error)
            toast('Failed to load purchase report', {
                "type": "error",
            })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        branchId.value = ''
        supplierId.value = ''
        dateFrom.value = today.toISOString().split('T')[0]
        dateTo.value = today.toISOString().split('T')[0]
        fetchPurchaseReport()
    }

   
    // Watch for changes in filters
    watch([branchId, supplierId, dateFrom, dateTo], () => {
        fetchPurchaseReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchPurchaseReport()
    })

    return {
        // State
        purchaseData,
        isLoading,
        branchId,
        supplierId,
        dateFrom,
        dateTo,
        branches,
        suppliers,
        
        // Methods
        fetchFilterOptions,
        fetchPurchaseReport,
        resetFilters,
        
        // Computed
        purchases,
        totalPurchases,
        summary,
    }
}