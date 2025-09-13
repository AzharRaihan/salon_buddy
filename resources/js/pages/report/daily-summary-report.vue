<script setup>
import { computed } from 'vue'
import { useDailySummaryReport } from '@/composables/useDailySummaryReport'
import DailySummaryReportFilters from '@/components/report/DailySummaryReportFilters.vue'
import DailySummaryReportTable from '@/components/report/DailySummaryReportTable.vue'

// Use the daily summary report composable
const {
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
} = useDailySummaryReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})
</script>

<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
            <VCardText>
                <DailySummaryReportFilters
                    v-model:selected-date="selectedDate"
                    v-model:branch-id="branchId"
                    :branches="branches"
                    @reset-filters="resetFilters"
                    @export-report="exportReport"
                />
            </VCardText>
        </VCard>

        <!-- Daily Summary Report Table -->
        <DailySummaryReportTable
            :sales="sales"
            :purchases="purchases"
            :supplier-due-payments="supplierDuePayments"
            :customer-due-receives="customerDueReceives"
            :selected-date="selectedDate"
            :selected-branch-name="selectedBranchName"
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
