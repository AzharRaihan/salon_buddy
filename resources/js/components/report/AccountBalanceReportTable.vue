<template>
    <div class="account-balance-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Account Balance Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Account Balance Table -->
                <VDataTable
                    :items="accounts"
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
                                No account records found
                            </div>
                        </div>
                    </template>

                    <!-- SN -->
                    <template #item.sn="{ item }">
                        <span class="font-weight-medium">
                            {{ item.sn }}
                        </span>
                    </template>

                    <!-- Account Name -->
                    <template #item.account_name="{ item }">
                        <span class="text-high-emphasis font-weight-medium">
                            {{ item.account_name }}
                        </span>
                    </template>

                    <!-- Balance formatting -->
                    <template #item.balance="{ item }">
                        <span 
                            class="font-weight-bold"
                            :class="item.balance >= 0 ? 'text-success' : 'text-error'"
                        >
                            {{ formatAmount(item.balance) }}
                        </span>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="2">
                                        Summary
                                    </th>
                                    <th>
                                        Total Balance
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary-row">
                                    <td class="text-h6 font-weight-bold text-primary" colspan="2">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-calculator" class="me-2" />
                                            Total Summary
                                        </span>
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary" colspan="1">
                                        {{ formatAmount(calculateTotal('balance')) }}
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
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount } = useCompanyFormatters()

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    selectedBranchName: {
        type: String,
        default: 'All Outlets'
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
const calculateTotal = (field) => {
    return props.accounts.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.account-balance-report-table {
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

