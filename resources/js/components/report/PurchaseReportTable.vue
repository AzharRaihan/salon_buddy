<template>
    <div class="purchase-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Purchase Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Supplier: {{ selectedSupplierName }}
                        </div>
                    </div>
                </div>

                <!-- Purchase Table -->
                <VDataTable
                    :items="purchases"
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
                                No purchase records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Reference No -->
                    <template #item.reference_no="{ item }">
                        <span class="font-weight-medium">
                            {{ item.reference_no }}
                        </span>
                    </template>

                    <!-- Date formatting -->
                    <template #item.date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.date) }}
                        </span>
                    </template>

                    <!-- Supplier name -->
                    <template #item.supplier.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.supplier?.name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Supplier invoice -->
                    <template #item.supplier_invoice_no="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.supplier_invoice_no || 'N/A' }}
                        </span>
                    </template>

                    <!-- Grand total -->
                    <template #item.grand_total="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ formatAmount(item.grand_total) }}
                        </span>
                    </template>

                    <!-- Paid amount -->
                    <template #item.paid_amount="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatAmount(item.paid_amount) }}
                        </span>
                    </template>

                    <!-- Due amount -->
                    <template #item.due_amount="{ item }">
                        <span class="font-weight-medium text-warning">
                            {{ formatAmount(item.due_amount) }}
                        </span>
                    </template>

                    <!-- Payment status -->
                    <template #item.payment_method.name="{ item }">
                        {{ (item.payment_method?.name || 'N/A') }}
                    </template>

                    <!-- Note truncation -->
                    <template #item.note="{ item }">
                        <span :title="item.note">
                            {{ item.note ? (item.note.length > 50 ? item.note.substring(0, 50) + '...' : item.note) : 'N/A' }}
                        </span>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th>
                                        Summary
                                    </th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>
                                        Grand Total
                                    </th>
                                    <th>
                                        Paid Amount
                                    </th>
                                    <th>
                                        Due Amount
                                    </th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary-row">
                                    <td class="text-h6 font-weight-bold text-primary">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-calculator" class="me-2" />
                                            Total Summary
                                        </span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(calculateTotal('grand_total')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-success">
                                        {{ formatAmount(calculateTotal('paid_amount')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-warning">
                                        {{ formatAmount(calculateTotal('due_amount')) }}
                                    </td>
                                    <td></td>
                                    <td></td>
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
    purchases: {
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
    selectedSupplierName: {
        type: String,
        default: 'All Suppliers'
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
    return props.purchases.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.purchase-report-table {
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
