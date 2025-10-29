<script setup>
import { computed, ref } from 'vue'
import { useBalanceSheetReport } from '@/composables/useBalanceSheetReport'
import BalanceSheetReportFilters from '@/components/report/BalanceSheetReportFilters.vue'
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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Balance Sheet Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
    dateRange: 'All Time',
    generatedOn: '',
    generatedBy: 'N/A'
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
                :header-data="reportHeaderData"
                :summary-data="summary"
            />
        </div>

        <!-- Balance Sheet Report Table -->
        <BalanceSheetReportTable
            :assets="assets"
            :liabilities="liabilities"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch="selectedBranch"
            :selected-branch-name="selectedBranchName"
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
