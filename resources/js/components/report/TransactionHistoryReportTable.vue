<template>
    <div class="transaction-history-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Transaction History Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Payment Account: {{ selectedPaymentMethodName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Transaction History Table -->
                <VDataTable
                    :items="transactions"
                    :headers="exportHeaders"
                    class="text-no-wrap"
                    :loading="isLoading"
                    hide-default-footer
                    :items-per-page="-1"
                >
                    <!-- Loading state -->
                    <template #loading>
                        <VSkeletonLoader type="table-row" :rows="10" />
                    </template>

                    <!-- No data state -->
                    <template #no-data>
                        <div class="d-flex align-center justify-center pa-4">
                            <VIcon icon="tabler-alert-circle" class="me-2" />
                            <div>
                                No transactions found. Please select a payment account.
                            </div>
                        </div>
                    </template>

                    <!-- SN -->
                    <template #item.sn="{ item }">
                        <span class="font-weight-medium">
                            {{ item.sn }}
                        </span>
                    </template>

                    <!-- Date -->
                    <template #item.date="{ item }">
                        {{ formatDate(item.date) }}
                    </template>

                    <!-- Reference No -->
                    <template #item.reference_no="{ item }">
                        <span class="text-high-emphasis font-weight-medium">
                            {{ item.reference_no }}
                        </span>
                    </template>

                    <!-- Type -->
                    <template #item.type="{ item }">
                        <VChip
                            :color="getTypeColor(item.type)"
                            size="small"
                            class="font-weight-medium"
                        >
                            {{ item.type }}
                        </VChip>
                    </template>

                    <!-- Payment Account -->
                    <template #item.payment_account="{ item }">
                        {{ item.payment_account }}
                    </template>

                    <!-- Amount -->
                    <template #item.amount="{ item }">
                        <span class="font-weight-bold text-success">
                            {{ formatAmount(item.amount) }}
                        </span>
                    </template>

                    <!-- Added By -->
                    <template #item.added_by="{ item }">
                        {{ item.added_by }}
                    </template>

                    <!-- Added Date Time -->
                    <template #item.added_date_time="{ item }">
                        {{ item.added_date_time }}
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="5">Summary</th>
                                    <th>Total Amount</th>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary-row">
                                    <td class="text-h6 font-weight-bold text-primary" colspan="5">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-calculator" class="me-2" />
                                            Total Summary
                                        </span>
                                    </td>
                                    <td class="text-h6 font-weight-bold text-success">
                                        {{ formatAmount(calculateTotal('amount')) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </VTable>
                    </template>
                </VDataTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount } = useCompanyFormatters()

const props = defineProps({
    transactions: {
        type: Array,
        default: () => []
    },
    dateFrom: {
        type: String,
        default: ''
    },
    dateTo: {
        type: String,
        default: ''
    },
    selectedBranchName: {
        type: String,
        default: 'All Outlets'
    },
    selectedPaymentMethodName: {
        type: String,
        default: 'All Payment Methods'
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    exportHeaders: {
        type: Array,
        default: () => []
    }
})

// Helper functions
const formatDateRange = (from, to) => {
    if (!from && !to) return 'All Time'
    if (!from) return `Until ${formatDate(to)}`
    if (!to) return `From ${formatDate(from)}`
    return `${formatDate(from)} - ${formatDate(to)}`
}

const calculateTotal = (field) => {
    return props.transactions.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

const getTypeColor = (type) => {
    const typeColors = {
        'Sale': 'success',
        'Customer Receive': 'info',
        'Deposit': 'success',
        'Purchase': 'warning',
        'Supplier Payment': 'warning',
        'Expense': 'error',
        'Staff Payment': 'error',
        'Withdraw': 'error',
    }
    return typeColors[type] || 'default'
}
</script>

<style lang="scss" scoped>
.transaction-history-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
.summary-row {
    background-color: rgba(var(--v-theme-primary), 0.05);
    border-top: 2px solid rgb(var(--v-theme-primary));
    
    td {
        padding: 16px 12px;
        border-top: 2px solid rgb(var(--v-theme-primary));
    }
}
</style>

