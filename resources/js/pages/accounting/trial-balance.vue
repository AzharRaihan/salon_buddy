<script setup>
import { computed, ref } from 'vue'
import { useTrialBalanceReport } from '@/composables/useTrialBalanceReport'
import TrialBalanceReportFilters from '@/components/report/TrialBalanceReportFilters.vue'
import TrialBalanceReportTable from '@/components/report/TrialBalanceReportTable.vue'
import ExportTableTrialBalanceReport from '@/components/ExportTableTrialBalanceReport.vue'

// Use the trial balance report composable
const {
    // State
    trialBalanceData,
    isLoading,
    branchId,
    branches,
    
    // Methods
    resetFilters,
    
    // Computed
    trialBalance,
    totalRecords,
    summary,
} = useTrialBalanceReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Trial Balance Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
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

            <ExportTableTrialBalanceReport 
                :data="trialBalance" 
                :headers="exportHeaders" 
                filename="trial-balance-report"
                :header-data="reportHeaderData"
                :summary-data="summary"
            />
        </div>

        <!-- Trial Balance Report Table -->
        <TrialBalanceReportTable
            :trial-balance="trialBalance"
            :export-headers="exportHeaders"
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
