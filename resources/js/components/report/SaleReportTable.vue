<template>
    <div class="sale-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Sale Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Customer: {{ selectedCustomerName }}
                        </div>
                    </div>
                </div>

                <!-- Sale Table -->
                <VDataTable
                    :items="sales"
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
                                No sale records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Sale No -->
                    <template #item.invoice_no="{ item }">
                        <span class="font-weight-medium">
                            {{ (item.reference_no) }}
                        </span>
                    </template>

                    <!-- Date formatting -->
                    <template #item.date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.order_date) }}
                        </span>
                    </template>

                    <!-- Customer name -->
                    <template #item.customer.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.customer?.name || 'Walk-in Customer' }}
                        </span>
                    </template>

                    <!-- Order From -->
                    <template #item.order_from="{ item }">
                        <VChip class="text-high-emphasis" :color="item.order_from == 'Website' ? 'primary' : 'secondary'" variant="tonal" size="small">
                            {{ item.order_from || 'POS' }}
                        </VChip>
                    </template>

                    <!-- Order Status -->
                    <template #item.order_status="{ item }">
                        <VChip 
                        :color="getOrderStatusColor(item.order_status)" 
                        variant="tonal" 
                        size="small"
                        >
                            {{ item.order_status || 'Pending' }}
                        </VChip>
                    </template>

                    <!-- Total payable -->
                    <template #item.total_payable="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ formatAmount(item.total_payable) }}
                        </span>
                    </template>

                    <!-- Total paid -->
                    <template #item.total_paid="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatAmount(item.total_paid) }}
                        </span>
                    </template>

                    <!-- Total due -->
                    <template #item.total_due="{ item }">
                        <span class="font-weight-medium text-warning">
                            {{ formatAmount(item.total_due) }}
                        </span>
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
                                    <th colspan="5">
                                        Summary
                                    </th>
                                    <th>
                                        Total Payable
                                    </th>
                                    <th>
                                        Total Paid
                                    </th>
                                    <th>
                                        Total Due
                                    </th>
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
                                    <td class="text-h6 font-weight-bold text-primary" colspan="1">
                                        {{ formatAmount(calculateTotal('total_payable')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-success">
                                        {{ formatAmount(calculateTotal('total_paid')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-warning">
                                        {{ formatAmount(calculateTotal('total_due')) }}
                                    </td>
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
const getOrderStatusColor = (status) => {
    switch (status) {
        case 'Completed':
            return 'success'
        case 'Confirmed':
            return 'info'
        case 'Pending':
            return 'warning'
        case 'Cancelled':
            return 'error'
        default:
            return 'default'
    }
}
const props = defineProps({
    sales: {
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
    selectedCustomerName: {
        type: String,
        default: 'All Customers'
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
    return props.sales.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.sale-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
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
}
</style>
