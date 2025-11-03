<template>
    <div class="staff-payout-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Staff Payout Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Employee: {{ selectedEmployeeName }}
                        </div>
                    </div>
                </div>

                <!-- Payout Table -->
                <VDataTable
                    :items="payouts"
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
                                No payout records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Date formatting -->
                    <template #[`item.date`]="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.date) }}
                        </span>
                    </template>

                    <!-- Reference number -->
                    <template #[`item.reference_no`]="{ item }">
                        <span class="font-weight-medium">
                            {{ item.reference_no || 'N/A' }}
                        </span>
                    </template>

                    <!-- Employee name -->
                    <template #[`item.employee.name`]="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.employee?.name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Payment method name -->
                    <template #[`item.payment_method.name`]="{ item }">
                        <VChip 
                            :color="item.payment_method?.name ? 'primary' : 'default'" 
                            variant="tonal" 
                            size="small"
                        >
                            {{ item.payment_method?.name || 'N/A' }}
                        </VChip>
                    </template>

                    <!-- Amount formatting -->
                    <template #[`item.amount`]="{ item }">
                        <span class="font-weight-medium">
                            {{ formatAmount(item.amount) }}
                        </span>
                    </template>

                    <!-- Note truncation -->
                    <template #[`item.note`]="{ item }">
                        <span :title="item.note">
                            {{ item.note ? (item.note.length > 50 ? item.note.substring(0, 50) + '...' : item.note) : 'N/A' }}
                        </span>
                    </template>

                    <!-- Summary Row -->
                    <template #body.append>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-h6 font-weight-bold text-end">
                                Summary
                            </td>
                            <td class="text-h6 font-weight-bold">
                                {{ formatAmount(calculateTotal('amount')) }}
                            </td>
                            <td></td>
                        </tr>
                    </template>
                </VDataTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount, fetchCompanySettings } = useCompanyFormatters()
onMounted(async () => {
    await fetchCompanySettings()
})

const props = defineProps({
    payouts: {
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
    selectedEmployeeName: {
        type: String,
        default: 'All Employees'
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
    return props.payouts.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.staff-payout-report-table {
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
