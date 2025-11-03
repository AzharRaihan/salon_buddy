<script setup>
import { computed } from 'vue'
import { usePurchaseReport } from '@/composables/usePurchaseReport'
import PurchaseReportFilters from '@/components/report/PurchaseReportFilters.vue'
import PurchaseReportTable from '@/components/report/PurchaseReportTable.vue'
import ExportTablePurchaseReport from '@/components/ExportTablePurchaseReport.vue'

// Use the purchase report composable
const {
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
    resetFilters,
    
    // Computed
    purchases,
    totalPurchases,
    summary,
} = usePurchaseReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedSupplierName = computed(() => {
    if (!supplierId.value) return 'All Suppliers'
    const supplier = suppliers.value.find(s => s.id == supplierId.value)
    return supplier?.name || 'All Suppliers'
})


const exportHeaders = computed(() => [
    { title: 'Reference No', key: 'reference_no', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Supplier', key: 'supplier.name', sortable: false },
    { title: 'Supplier Invoice', key: 'supplier_invoice_no', sortable: false },
    { title: 'Grand Total', key: 'grand_total', sortable: true },
    { title: 'Paid Amount', key: 'paid_amount', sortable: true },
    { title: 'Due Amount', key: 'due_amount', sortable: true },
    { title: 'Payment Status', key: 'payment_method.name', sortable: false },
    { title: 'Note', key: 'note', sortable: false },
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
                <PurchaseReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:supplier-id="supplierId"
                    :branches="branches"
                    :suppliers="suppliers"
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

            <ExportTablePurchaseReport 
                :data="purchases" 
                :headers="exportHeaders" 
                filename="purchase-report"
                title="Purchase Report"
                :summary-data="summary"
            />
        </div>


        <!-- Purchase Report Table -->
        <PurchaseReportTable
            :purchases="purchases"
            :export-headers="exportHeaders"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-supplier-name="selectedSupplierName"
            :is-loading="isLoading"
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
