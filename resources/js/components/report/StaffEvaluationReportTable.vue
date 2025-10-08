<template>
    <div class="staff-evaluation-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Staff Evaluation Report</h4>
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

                <!-- Evaluation Table -->
                <VDataTable
                    :items="evaluations"
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
                                No evaluation records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Staff Name with Phone -->
                    <template #[`item.employee.name`]="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.employee?.name || 'N/A' }}
                            <span v-if="item.employee?.phone" class="text-medium-emphasis">
                                ({{ item.employee.phone }})
                            </span>
                        </span>
                    </template>

                    <!-- Total Ratings -->
                    <template #[`item.total_ratings`]="{ item }">
                        <span class="font-weight-medium">
                            {{ formatNumber(item.total_ratings) }}
                        </span>
                    </template>

                    <!-- Average Rating -->
                    <template #[`item.avg_rating`]="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ item.avg_rating }}
                        </span>
                    </template>

                    <!-- Rating with Stars -->
                    <template #[`item.rating`]="{ item }">
                        <div class="d-flex align-center">
                            <VRating
                                :model-value="item.avg_rating"
                                color="warning"
                                half-increments
                                readonly
                                density="compact"
                                size="small"
                            />
                        </div>
                    </template>

                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th>
                                        Summary
                                    </th>
                                    <th>
                                        Total Ratings
                                    </th>
                                    <th>
                                        Average Rating
                                    </th>
                                    <th>
                                        
                                    </th>
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
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatNumber(calculateTotal('total_ratings')) }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ calculateAvgRating() }}
                                    </td>
                                    <td>
                                        
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
const { formatDate, formatNumber } = useCompanyFormatters()

const props = defineProps({
    evaluations: {
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
    return props.evaluations.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

const calculateAvgRating = () => {
    if (props.evaluations.length === 0) return '0.00'
    const totalAvg = props.evaluations.reduce((sum, item) => {
        return sum + (parseFloat(item.avg_rating) || 0)
    }, 0)
    return (totalAvg / props.evaluations.length).toFixed(2)
}
</script>

<style lang="scss" scoped>
.staff-evaluation-report-table {
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

