<template>
    <div class="staff-evaluation-details-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Staff Evaluation Details Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Employee: {{ selectedEmployeeName }}
                        </div>
                    </div>
                </div>

                <!-- Evaluation Details Table -->
                <VDataTable
                    :items="evaluationDetails"
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

                    <!-- Customer Name with Phone -->
                    <template #[`item.customer.name`]="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.customer?.name || 'N/A' }}
                            <span v-if="item.customer?.phone" class="text-medium-emphasis">
                                ({{ item.customer.phone }})
                            </span>
                        </span>
                    </template>

                    <!-- Item Name -->
                    <template #[`item.item.name`]="{ item }">
                        <VChip 
                            color="primary" 
                            variant="tonal" 
                            size="small"
                        >
                            {{ item.item?.name || 'N/A' }}
                        </VChip>
                    </template>

                    <!-- Rating Number -->
                    <template #[`item.rating_number`]="{ item }">
                        <span class="font-weight-medium text-primary">
                            {{ item.rating }}
                        </span>
                    </template>

                    <!-- Rating with Stars (Visual Rating) -->
                    <template #[`item.rating`]="{ item }">
                        <div class="d-flex align-center">
                            <VRating
                                :model-value="item.rating"
                                color="warning"
                                half-increments
                                readonly
                                density="compact"
                                size="small"
                            />
                        </div>
                    </template>

                    <!-- Created Date formatting -->
                    <template #[`item.created_at`]="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.rating_date) }}
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
                                        Total Ratings
                                    </th>
                                    <th colspan="2">
                                        Average Rating
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
                                        {{ evaluationDetails.length }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary" colspan="2">
                                        {{ calculateAvgRating() }}
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
const { formatDate } = useCompanyFormatters()

const props = defineProps({
    evaluationDetails: {
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

const calculateAvgRating = () => {
    if (props.evaluationDetails.length === 0) return '0.00'
    const totalRating = props.evaluationDetails.reduce((sum, item) => {
        return sum + (parseFloat(item.rating) || 0)
    }, 0)
    return (totalRating / props.evaluationDetails.length).toFixed(2)
}
</script>

<style lang="scss" scoped>
.staff-evaluation-details-report-table {
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

