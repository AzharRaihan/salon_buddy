<script setup>
import { computed } from 'vue'
import { useProfitLossReport } from '@/composables/useProfitLossReport'
import ProfitLossReportFilters from '@/components/report/ProfitLossReportFilters.vue'
import ProfitLossReportTable from '@/components/report/ProfitLossReportTable.vue'

// Use the profit loss report composable
const {
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
} = useProfitLossReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id === branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedCostingMethod = computed(() => {
    const method = costingMethods.value.find(m => m.value === costingMethod.value)
    return method?.title || 'Last Purchase Price'
})
</script>

<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
            <VCardText>
                <ProfitLossReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:costing-method="costingMethod"
                    :branches="branches"
                    :costing-methods="costingMethods"
                    @reset-filters="resetFilters"
                    @export-report="exportReport"
                />
            </VCardText>
        </VCard>

        <!-- Profit & Loss Report Table -->
        <ProfitLossReportTable
            :report-data="reportData"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-costing-method="selectedCostingMethod"
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
