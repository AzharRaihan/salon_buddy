<script setup>
import { computed } from 'vue'
import { useTrialBalanceReport } from '@/composables/useTrialBalanceReport'
import TrialBalanceReportFilters from '@/components/report/TrialBalanceReportFilters.vue'
import TrialBalanceSummaryCards from '@/components/report/TrialBalanceSummaryCards.vue'
import TrialBalanceReportTable from '@/components/report/TrialBalanceReportTable.vue'
import ExportTableTrialBalanceReport from '@/components/ExportTableTrialBalanceReport.vue'

// Use the trial balance report composable
const {
    // State
    trialBalanceData,
    isLoading,
    branchId,
    dateFrom,
    dateTo,
    branches,
    
    // Methods
    resetFilters,
    
    // Computed
    trialBalance,
    totalRecords,
    summary,
} = useTrialBalanceReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const exportHeaders = computed(() => [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Title', key: 'title', sortable: false },
    { title: 'Debit', key: 'debit', sortable: false },
    { title: 'Credit', key: 'credit', sortable: false },
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
                <TrialBalanceReportFilters
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
                <TrialBalanceSummaryCards
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

            <ExportTableTrialBalanceReport 
                :data="trialBalance" 
                :headers="exportHeaders" 
                filename="trial-balance-report"
                title="Trial Balance Report"
                :summary-data="summary"
            />
        </div>

        <!-- Trial Balance Report Table -->
        <TrialBalanceReportTable
            :trial-balance="trialBalance"
            :export-headers="exportHeaders"
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
