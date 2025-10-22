<template>
    <div class="trial-balance-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Trial Balance Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Trial Balance Table -->
                <VDataTable
                    :items="trialBalance"
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
                                No trial balance records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- SN -->
                    <template #item.sn="{ item }">
                        <span class="font-weight-medium">
                            {{ item.sn }}
                        </span>
                    </template>

                    <!-- Title -->
                    <template #item.title="{ item }">
                        <span class="text-high-emphasis font-weight-medium">
                            {{ item.title }}
                        </span>
                    </template>

                    <!-- Debit formatting -->
                    <template #item.debit="{ item }">
                        <span 
                            v-if="item.debit > 0"
                            class="font-weight-bold text-info"
                        >
                            {{ formatAmount(item.debit) }}
                        </span>
                        <span v-else class="text-medium-emphasis">-</span>
                    </template>

                    <!-- Credit formatting -->
                    <template #item.credit="{ item }">
                        <span 
                            v-if="item.credit > 0"
                            class="font-weight-bold text-warning"
                        >
                            {{ formatAmount(item.credit) }}
                        </span>
                        <span v-else class="text-medium-emphasis">-</span>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="2">Summary</th>
                                    <th>Total Debit</th>
                                    <th>Total Credit</th>
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
                                    <td class="text-h6 font-weight-bold text-info">
                                        {{ formatAmount(calculateTotal('debit')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-warning">
                                        {{ formatAmount(calculateTotal('credit')) }}
                                    </td>
                                </tr>
                                <tr class="difference-row">
                                    <td class="text-h6 font-weight-bold" colspan="2">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-minus" class="me-2" />
                                            Difference
                                        </span>
                                    </td>
                                    <td 
                                        class="text-h6 font-weight-bold" 
                                        colspan="2"
                                        :class="difference === 0 ? 'text-success' : 'text-error'"
                                    >
                                        {{ formatAmount(Math.abs(difference)) }}
                                        <VChip 
                                            v-if="difference === 0" 
                                            color="success" 
                                            size="small" 
                                            class="ml-2"
                                        >
                                            Balanced
                                        </VChip>
                                        <VChip 
                                            v-else 
                                            color="error" 
                                            size="small" 
                                            class="ml-2"
                                        >
                                            Not Balanced
                                        </VChip>
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
const { formatDate, formatAmount } = useCompanyFormatters()

const props = defineProps({
    trialBalance: {
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
    return props.trialBalance.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

const difference = computed(() => {
    return calculateTotal('debit') - calculateTotal('credit')
})
</script>

<style lang="scss" scoped>
.trial-balance-report-table {
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

.difference-row {
    background-color: rgba(var(--v-theme-warning), 0.05);
    
    td {
        padding: 16px 12px;
    }
}
</style>

