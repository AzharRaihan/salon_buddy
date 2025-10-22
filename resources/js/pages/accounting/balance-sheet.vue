<script setup>
import { computed } from 'vue'
import { useBalanceSheetReport } from '@/composables/useBalanceSheetReport'
import BalanceSheetReportFilters from '@/components/report/BalanceSheetReportFilters.vue'
import BalanceSheetSummaryCards from '@/components/report/BalanceSheetSummaryCards.vue'
import BalanceSheetReportTable from '@/components/report/BalanceSheetReportTable.vue'
import ExportTableBalanceSheetReport from '@/components/ExportTableBalanceSheetReport.vue'

// Use the balance sheet report composable
const {
    // State
    balanceSheetData,
    isLoading,
    branchId,
    dateFrom,
    dateTo,
    branches,
    
    // Methods
    resetFilters,
    
    // Computed
    assets,
    liabilities,
    summary,
} = useBalanceSheetReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const handleResetFilters = () => {
    resetFilters()
}
</script>

<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
            <VCardText>
                <BalanceSheetReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    :branches="branches"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <VCard class="mb-4">
            <VCardText>
                <BalanceSheetSummaryCards
                    :summary="summary"
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

            <ExportTableBalanceSheetReport 
                :assets="assets"
                :liabilities="liabilities"
                filename="balance-sheet-report"
                title="Balance Sheet Report"
                :summary-data="summary"
            />
        </div>

        <!-- Balance Sheet Report Table -->
        <BalanceSheetReportTable
            :assets="assets"
            :liabilities="liabilities"
            :date-from="dateFrom"
            :date-to="dateTo"
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
