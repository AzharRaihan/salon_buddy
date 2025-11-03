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
                            {{ item.employee?.phone ? `(${item.employee?.phone})` : '' }}
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
                        <span class="font-weight-medium">
                            {{ formatAmount(item.subtotal) }}
                        </span>
                    </template>

                    <!-- Commission Rate -->
                    <template #item.commission_rate="{ item }">
                        <span class="font-weight-medium">
                            {{ item.commission_rate }}%
                        </span>
                    </template>

                    <!-- Commission Amount -->
                    <template #item.commission_amount="{ item }">
                        <span class="font-weight-medium">
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
                                {{ formatAmount(calculateTotal('subtotal')) }}
                            </td>
                            <td class="text-h6 font-weight-bold">
                                {{ (calculateTotal('commission_rate')) }}
                            </td>
                            <td class="text-h6 font-weight-bold">
                                {{ formatAmount(calculateTotal('commission_amount')) }}
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

const calculateTotal = (field) => {
    return props.commissions.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
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
