<template>
    <div class="salary-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Salary Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Salary Table -->
                <VDataTable
                    :items="salaries"
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
                                No salary records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Date formatting -->
                    <template #item.generated_date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.generated_date) }}
                        </span>
                    </template>
                    <!-- Month-Year formatting -->
                    <template #item.month="{ item }">
                        <span class="font-weight-medium">
                            {{ (item.month) }}
                        </span>
                    </template>
                    

                    <!-- Branch name -->
                    <template #item.branch.branch_name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.branch?.branch_name || 'N/A' }}
                        </span>
                    </template>

                    <!-- Total amount -->
                    <template #item.total_amount="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatAmount(item.total_amount) }}
                        </span>
                    </template>

                    <!-- User name -->
                    <template #item.user.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.user?.name || 'N/A' }}
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
                                        Total Amount
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
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(calculateTotal('total_amount')) }}
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
    salaries: {
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
    return props.salaries.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

</script>

<style lang="scss" scoped>
.salary-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
</style>
