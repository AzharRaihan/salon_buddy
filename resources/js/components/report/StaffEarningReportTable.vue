<template>
    <div class="staff-earning-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Staff Earning Report</h4>
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

                <!-- Earning Table -->
                <VDataTable
                    :items="earnings"
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
                                No earning records found with current filters
                            </div>
                        </div>
                    </template>


                    <!-- Employee name -->
                    <template #[`item.employee.name`]="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.employee?.name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Subtotal formatting -->
                    <template #[`item.subtotal`]="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ formatAmount(item.subtotal) }}
                        </span>
                    </template>

                    <!-- Quantity formatting -->
                    <template #[`item.quantity`]="{ item }">
                        <span class="font-weight-medium">
                            {{ formatNumber(item.quantity) }}
                        </span>
                    </template>

                    <!-- Commission formatting -->
                    <template #[`item.commission`]="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatAmount(item.commission) }}
                        </span>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="3">
                                        Summary
                                    </th>
                                    <th>
                                        Total Subtotal
                                    </th>
                                    <th>
                                        Total Services
                                    </th>
                                    <th>
                                        Total Commission
                                    </th>
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
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(calculateTotal('subtotal')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatNumber(calculateTotal('quantity')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(calculateTotal('commission')) }}
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
const { formatDate, formatAmount, formatNumber } = useCompanyFormatters()

const props = defineProps({
    earnings: {
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
    return props.earnings.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.staff-earning-report-table {
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
