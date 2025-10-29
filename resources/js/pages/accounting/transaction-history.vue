<script setup>
import { computed } from 'vue'
import { useTransactionHistory } from '@/composables/useTransactionHistory'
import TransactionHistoryReportFilters from '@/components/report/TransactionHistoryReportFilters.vue'
import TransactionHistorySummaryCards from '@/components/report/TransactionHistorySummaryCards.vue'
import TransactionHistoryReportTable from '@/components/report/TransactionHistoryReportTable.vue'
import ExportTableTransactionHistory from '@/components/ExportTableTransactionHistory.vue'

// Use the transaction history report composable
const {
    // State
    transactionHistoryData,
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
    transactions,
    totalRecords,
    summary,
} = useTransactionHistory()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedPaymentMethodName = computed(() => {
    if (!paymentMethodId.value) return 'All Payment Accounts'
    const paymentMethod = paymentMethods.value.find(p => p.id == paymentMethodId.value)
    return paymentMethod?.name || 'All Payment Accounts'
})

const exportHeaders = computed(() => [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Date', key: 'date', sortable: false },
    { title: 'Reference No', key: 'reference_no', sortable: false },
    { title: 'Type', key: 'type', sortable: false },
    { title: 'Payment Account', key: 'payment_account', sortable: false },
    { title: 'Amount', key: 'amount', sortable: false },
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
                <TransactionHistoryReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:payment-method-id="paymentMethodId"
                    v-model:branch-id="branchId"
                    :branches="branches"
                    :payment-methods="paymentMethods"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <VCard v-if="paymentMethodId && transactions.length > 0" class="mb-4">
            <VCardText>
                <TransactionHistorySummaryCards
                    :summary="summary"
                />
            </VCardText>
        </VCard>

        <!-- Action Buttons -->
        <div v-if="paymentMethodId && transactions.length > 0" class="table-action action mb-4 d-flex justify-end gap-4">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableTransactionHistory 
                :data="transactions" 
                :headers="exportHeaders" 
                filename="transaction-history-report"
                title="Transaction History Report"
                :summary-data="summary"
            />
        </div>

        <!-- Transaction History Report Table -->
        <TransactionHistoryReportTable
            :transactions="transactions"
            :export-headers="exportHeaders"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-payment-method-name="selectedPaymentMethodName"
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
