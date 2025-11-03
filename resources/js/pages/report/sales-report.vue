<script setup>
import { computed } from 'vue'
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
// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
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

</script>
<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
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

        <!-- Action Buttons -->
        <div class="table-action action mb-4 d-flex justify-end gap-4">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableSalesReport 
                :data="sales" 
                :headers="exportHeaders" 
                filename="sales-report"
                title="Sales Report"
                :summary-data="summary"
            />
        </div>


        <!-- Sale Report Table -->
        <SaleReportTable
            :sales="sales"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-customer-name="selectedCustomerName"
            :is-loading="isLoading"
            :export-headers="exportHeaders"
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
