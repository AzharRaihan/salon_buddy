<template>
    <div class="account-statement-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Account Statement Report</h4>
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

                <!-- Account Statement Table -->
                <VDataTable
                    :items="statements"
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
                                No statement records found with current filters
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
                        <span class="font-weight-medium">
                            {{ formatDate(item.date) }}
                        </span>
                    </template>

                    <!-- Title with multiline support -->
                    <template #item.title="{ item }">
                        <div class="text-high-emphasis" style="white-space: pre-line; line-height: 1.5;">
                            {{ item.title }}
                        </div>
                    </template>

                    <!-- Debit formatting -->
                    <template #item.debit="{ item }">
                        <span 
                            v-if="item.debit > 0"
                            class="font-weight-bold text-success"
                        >
                            {{ formatAmount(item.debit) }}
                        </span>
                        <span v-else class="text-medium-emphasis">-</span>
                    </template>

                    <!-- Credit formatting -->
                    <template #item.credit="{ item }">
                        <span 
                            v-if="item.credit > 0"
                            class="font-weight-bold text-error"
                        >
                            {{ formatAmount(item.credit) }}
                        </span>
                        <span v-else class="text-medium-emphasis">-</span>
                    </template>

                    <!-- Balance formatting -->
                    <template #item.balance="{ item }">
                        <span 
                            class="font-weight-bold"
                            :class="item.balance >= 0 ? 'text-info' : 'text-warning'"
                        >
                            {{ formatAmount(item.balance) }}
                        </span>
                    </template>

                    <!-- Added By -->
                    <template #item.added_by="{ item }">
                        <VChip 
                            size="small" 
                            variant="tonal"
                            color="primary"
                        >
                            {{ item.added_by || 'N/A' }}
                        </VChip>
                    </template>

                    <!-- Added Date Time -->
                    <template #item.added_date_time="{ item }">
                        <span class="text-medium-emphasis">
                            {{ formatDateWithTime(item.added_date_time) }}
                        </span>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="3">Summary</th>
                                    <th>Total Debit</th>
                                    <th>Total Credit</th>
                                    <th>Closing Balance</th>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary-row">
                                    <td class="text-h6 font-weight-bold text-primary" colspan="3">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-calculator" class="me-2" />
                                            Total Summary
                                        </span>
                                    </td>
                                    <td class="text-h6 font-weight-bold text-success">
                                        {{ formatAmount(calculateTotal('debit')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-error">
                                        {{ formatAmount(calculateTotal('credit')) }}
                                    </td>
                                    <td 
                                        class="text-h6 font-weight-bold"
                                        :class="closingBalance >= 0 ? 'text-info' : 'text-warning'"
                                        colspan="3"
                                    >
                                        {{ formatAmount(closingBalance) }}
                                    </td>
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
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount, formatDateWithTime } = useCompanyFormatters()

const props = defineProps({
    statements: {
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
    return props.statements.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

const closingBalance = computed(() => {
    if (props.statements.length === 0) return 0
    return props.statements[props.statements.length - 1]?.balance || 0
})
</script>

<style lang="scss" scoped>
.account-statement-report-table {
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

