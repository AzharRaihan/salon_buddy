<template>
    <div class="employee-commission-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Employee Commission Report</h4>
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

                <!-- Commission Table -->
                <VDataTable
                    :items="commissions"
                    :headers="headers"
                    class="text-no-wrap"
                    :loading="isLoading"
                    hide-default-footer
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
                                No commission records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Date formatting -->
                    <template #item.order_date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.order_date) }}
                        </span>
                    </template>

                    <!-- Employee name -->
                    <template #item.employee.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.employee?.name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Service/Item name -->
                    <template #item.item.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.item?.name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Subtotal -->
                    <template #item.subtotal="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatAmount(item.subtotal) }}
                        </span>
                    </template>

                    <!-- Commission Rate -->
                    <template #item.commission_rate="{ item }">
                        <span class="font-weight-medium text-info">
                            {{ item.commission_rate }}%
                        </span>
                    </template>

                    <!-- Commission Amount -->
                    <template #item.commission_amount="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ formatAmount(item.commission_amount) }}
                        </span>
                    </template>

                    <!-- Order Status -->
                    <template #item.order_status="{ item }">
                        <VChip 
                            :color="getOrderStatusColor(item.order_status)" 
                            variant="tonal" 
                            size="small"
                        >
                            {{ item.order_status }}
                        </VChip>
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
    commissions: {
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
    }
})

// Table headers
const headers = [
    { title: 'Date', key: 'order_date', sortable: true },
    { title: 'Employee', key: 'employee.name', sortable: false },
    { title: 'Service/Item', key: 'item.name', sortable: false },
    { title: 'Subtotal', key: 'subtotal', sortable: true },
    { title: 'Commission Rate', key: 'commission_rate', sortable: true },
    { title: 'Commission Amount', key: 'commission_amount', sortable: true },
    { title: 'Status', key: 'order_status', sortable: false },
]

// Helper functions
const formatDateRange = (from, to) => {
    if (!from && !to) return 'All Time'
    if (!from) return `Until ${formatDate(to)}`
    if (!to) return `From ${formatDate(from)}`
    return `${formatDate(from)} - ${formatDate(to)}`
}

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
</script>

<style lang="scss" scoped>
.employee-commission-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
</style>
