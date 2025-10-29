<script setup>
import { computed, ref } from 'vue'
import { useAccountStatementReport } from '@/composables/useAccountStatementReport'
import AccountStatementReportFilters from '@/components/report/AccountStatementReportFilters.vue'
import AccountStatementReportTable from '@/components/report/AccountStatementReportTable.vue'
import ExportTableAccountStatementReport from '@/components/ExportTableAccountStatementReport.vue'

// Use the account statement report composable
const {
    // State
    accountStatementData,
    isLoading,
    branchId,
    paymentMethodId,
    dateFrom,
    dateTo,
    branches,
    paymentMethods,
    
    // Methods
    resetFilters,
    
    // Computed
    statements,
    totalRecords,
    summary,
} = useAccountStatementReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Account Statement Report',
    outletName: 'All Outlets',
    dateRange: 'All Time',
    address: 'N/A',
    phone: 'N/A',
    generatedOn: '',
    generatedBy: 'N/A',
    paymentAccountName: 'All Payment Account'
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

const selectedPaymentMethodName = computed(() => {
    if (!paymentMethodId.value) return 'All Payment Account'
    const paymentMethod = paymentMethods.value.find(p => p.id == paymentMethodId.value)
    return paymentMethod?.name || 'All Payment Account'
})

const exportHeaders = computed(() => [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Date', key: 'date', sortable: false },
    { title: 'Title', key: 'title', sortable: false },
    { title: 'Debit', key: 'debit', sortable: false },
    { title: 'Credit', key: 'credit', sortable: false },
    { title: 'Balance', key: 'balance', sortable: false },
    { title: 'Added By', key: 'added_by', sortable: false },
    { title: 'Added Date Time', key: 'added_date_time', sortable: false },
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
                <AccountStatementReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:payment-method-id="paymentMethodId"
                    v-model:branch-id="branchId"
                    :branches="branches"
                    :payment-methods="paymentMethods"
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

            <ExportTableAccountStatementReport 
                :data="statements" 
                :headers="exportHeaders" 
                filename="account-statement-report"
                :header-data="reportHeaderData"
                :summary-data="summary"
            />
        </div>

        <!-- Account Statement Report Table -->
        <AccountStatementReportTable
            :statements="statements"
            :export-headers="exportHeaders"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch="selectedBranch"
            :selected-branch-name="selectedBranchName"
            :selected-payment-method-name="selectedPaymentMethodName"
            :is-loading="isLoading"
            :summary-data="summary"
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
