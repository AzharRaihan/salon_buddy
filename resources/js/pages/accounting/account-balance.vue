<script setup>
import { computed } from 'vue'
import { useAccountBalanceReport } from '@/composables/useAccountBalanceReport'
import AccountBalanceReportFilters from '@/components/report/AccountBalanceReportFilters.vue'
import AccountBalanceSummaryCards from '@/components/report/AccountBalanceSummaryCards.vue'
import AccountBalanceReportTable from '@/components/report/AccountBalanceReportTable.vue'
import ExportTableAccountBalanceReport from '@/components/ExportTableAccountBalanceReport.vue'

// Use the account balance report composable
const {
    // State
    accountData,
    isLoading,
    branchId,
    branches,
    
    // Methods
    resetFilters,
    
    // Computed
    accounts,
    totalAccounts,
    summary,
} = useAccountBalanceReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const exportHeaders = computed(() => [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Account Name', key: 'account_name', sortable: false },
    { title: 'Balance', key: 'balance', sortable: false },
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
                <AccountBalanceReportFilters
                    v-model:branch-id="branchId"
                    :branches="branches"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <VCard class="mb-4">
            <VCardText>
                <AccountBalanceSummaryCards
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

            <ExportTableAccountBalanceReport 
                :data="accounts" 
                :headers="exportHeaders" 
                filename="account-balance-report"
                title="Account Balance Report"
                :summary-data="summary"
            />
        </div>

        <!-- Account Balance Report Table -->
        <AccountBalanceReportTable
            :accounts="accounts"
            :export-headers="exportHeaders"
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
