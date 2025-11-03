<script setup>
import { computed, ref } from 'vue'
import { useProfitLossReport } from '@/composables/useProfitLossReport'
import ProfitLossReportFilters from '@/components/report/ProfitLossReportFilters.vue'
import ProfitLossReportTable from '@/components/report/ProfitLossReportTable.vue'
import ExportTableProfitLossReport from '@/components/ExportTableProfitLossReport.vue'

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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Profit & Loss Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
    dateRange: 'All Time',
    generatedOn: '',
    generatedBy: 'N/A',
    costingMethod: 'Last Purchase Price'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id === branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedBranch = computed(() => {
    if (!branchId.value) return null
    return branches.value.find(b => b.id == branchId.value) || null
})

const selectedCostingMethod = computed(() => {
    const method = costingMethods.value.find(m => m.value === costingMethod.value)
    return method?.title || 'Last Purchase Price'
})

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

            <ExportTableProfitLossReport 
                :report-data="reportData" 
                filename="profit-loss-report"
                title="Profit & Loss Report"
                :header-data="reportHeaderData"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
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
            :selected-branch="selectedBranch"
            :branches="branches"
            :selected-costing-method="selectedCostingMethod"
            :is-loading="isLoading"
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
