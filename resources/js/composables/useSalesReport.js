import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'

export function useSalesReport() {
    // Data table options
    const itemsPerPage = ref(10)
    const page = ref(1)
    const sortBy = ref()
    const orderBy = ref()
    const salesData = ref(null)
    const isLoading = ref(false)

    // Filter states
    const customerId = ref('')
    const dateFrom = ref('')
    const dateTo = ref('')

    // Filter options
    const customers = ref([])

    // Data table Headers
    const headers = [
        {
            title: 'Date',
            key: 'date',
            sortable: true,
            align: 'start',
        },
        {
            title: 'Reference No',
            key: 'reference_no',
            sortable: true,
        },
        {
            title: 'Customer',
            key: 'customer.name',
            sortable: true,
        },
        {
            title: 'Order From',
            key: 'order_from',
            sortable: true,
        },
        {
            title: 'Order Status',
            key: 'order_status',
            sortable: true,
        },
        {
            title: 'Total Tax',
            key: 'total_tax',
            sortable: true,
            align: 'end',
        },
        {
            title: 'Total Payable',
            key: 'total_payable',
            sortable: true,
            align: 'end',
        },
        {
            title: 'Payment Method',
            key: 'paymentMethod.name',
            sortable: true,
        },
    ]

    const updateOptions = options => {
        sortBy.value = options.sortBy[0]?.key
        orderBy.value = options.sortBy[0]?.order
    }

    const fetchFilterOptions = async () => {
        try {
            const response = await $api('/sales-report-filters', {
                method: 'GET',
            })
            customers.value = response.data.customers
        } catch (error) {
            console.error('Error fetching filter options:', error)
            toast('Failed to load filter options', { type: 'error' })
        }
    }

    const fetchSalesReport = async () => {
        isLoading.value = true
        try {
            const queryParams = {
                itemsPerPage: itemsPerPage.value,
                page: page.value,
                sortBy: sortBy.value,
                orderBy: orderBy.value,
            }
            if (customerId.value) queryParams.customer_id = customerId.value
            if (dateFrom.value) queryParams.date_from = dateFrom.value
            if (dateTo.value) queryParams.date_to = dateTo.value

            const response = await $api('/sales-report', {
                method: 'GET',
                query: queryParams,
            })
            salesData.value = response.data
        } catch (error) {
            console.error('Error fetching sales report:', error)
            toast('Failed to load sales report', { type: 'error' })
        } finally {
            isLoading.value = false
        }
    }

    const resetFilters = () => {
        customerId.value = ''
        dateFrom.value = ''
        dateTo.value = ''
        page.value = 1
        fetchSalesReport()
    }

    // Computed properties
    const sales = computed(() => salesData.value?.sales || [])
    const totalSales = computed(() => salesData.value?.total || 0)
    const summary = computed(() => salesData.value?.summary || {})

    // Watch for changes in filters
    watch([customerId, dateFrom, dateTo], () => {
        page.value = 1
        fetchSalesReport()
    })

    // Watch for changes in pagination
    watch([page, itemsPerPage], () => {
        fetchSalesReport()
    })

    // Initialize data
    onMounted(async () => {
        await fetchFilterOptions()
        fetchSalesReport()
    })

    return {
        // State
        itemsPerPage,
        page,
        sortBy,
        orderBy,
        salesData,
        isLoading,
        customerId,
        dateFrom,
        dateTo,
        customers,
        headers,
        // Methods
        updateOptions,
        fetchFilterOptions,
        fetchSalesReport,
        resetFilters,
        // Computed
        sales,
        totalSales,
        summary,
    }
} 