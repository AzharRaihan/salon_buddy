<script setup>
import { computed, ref } from 'vue'
import { useSaleReport } from '@/composables/useSaleReport'
import SaleReportFilters from '@/components/report/SaleReportFilters.vue'
import SaleReportTable from '@/components/report/SaleReportTable.vue'
import ExportTableSalesReport from '@/components/ExportTableSalesReport.vue'

// Use the sale report composable
const {
    // State
    isLoading,
    branchId,
    customerId,
    dateFrom,
    dateTo,
    branches,
    customers,
    // Methods
    resetFilters,
    // Computed
    sales,
    totalSales,
    summary,
} = useSaleReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Sale Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
    dateRange: 'All Time',
    generatedOn: '',
    generatedBy: 'N/A',
    customerName: 'All Customers'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedBranch = computed(() => {
    if (!branchId.value) return null
    return branches.value.find(b => b.id == branchId.value) || null
})

const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    return selectedBranch.value?.name || 'All Outlets'
})

const selectedCustomerName = computed(() => {
    if (!customerId.value) return 'All Customers'
    const customer = customers.value.find(c => c.id == customerId.value)
    return customer?.name || 'All Customers'
})

const exportHeaders = computed(() => [
    { title: 'Invoice No', key: 'reference_no', sortable: true },
    { title: 'Date', key: 'order_date', sortable: true },
    { title: 'Customer', key: 'customer.name', sortable: false },
    { title: 'Order From', key: 'order_from', sortable: false },
    { title: 'Order Status', key: 'order_status', sortable: false },
    { title: 'Total Payable', key: 'total_payable', sortable: true },
    { title: 'Total Paid', key: 'total_paid', sortable: true },
    { title: 'Total Due', key: 'total_due', sortable: true },
    { title: 'Payment Account', key: 'payment_method.name', sortable: false }
])

const handleResetFilters = () => {
    resetFilters()
}


const isFilterOptionsOpen = ref(false)

const toggleFilterOptions = () => {
  isFilterOptionsOpen.value = !isFilterOptionsOpen.value
}
</script>
<template>
    <div>

        <!-- Action Buttons -->
        <div class="table-action action mb-4 d-flex justify-end gap-4">
            <VBtn 
                :prepend-icon="isFilterOptionsOpen ? 'tabler-filter-off' : 'tabler-filter'" 
                variant="outlined" 
                @click="toggleFilterOptions"
            >
                {{ isFilterOptionsOpen ? 'Hide Filters' : 'Filters' }}
            </VBtn>

            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined"
                color="error"
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableSalesReport 
                :data="sales" 
                :headers="exportHeaders" 
                filename="sales-report"
                title="Sales Report"
                :header-data="reportHeaderData"
                :summary-data="summary"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
            <VCardText>
                <SaleReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:customer-id="customerId"
                    :branches="branches"
                    :customers="customers"
                />
            </VCardText>
        </VCard>



        <!-- Sale Report Table -->
        <SaleReportTable
            :sales="sales"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-branch="selectedBranch"
            :branches="branches"
            :selected-customer-name="selectedCustomerName"
            :is-loading="isLoading"
            :export-headers="exportHeaders"
            @update:header-data="handleHeaderDataUpdate"
        />
    </div>
</template>

<style lang="scss" scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;

    &:hover {
        color: rgba(var(--v-theme-primary), 0.8);
    }
}
</style>
