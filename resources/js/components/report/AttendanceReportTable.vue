<template>
    <div class="attendance-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Attendance Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Employee: {{ selectedEmployeeName }}
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <VDataTable
                    :items="attendances"
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
                            <div v-if="!selectedEmployeeName || selectedEmployeeName === 'All Employees'">
                                Please select an employee to view attendance records
                            </div>
                            <div v-else>
                                No attendance records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Date formatting -->
                    <template #item.date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.date) }}
                        </span>
                    </template>

                    <!-- Employee name -->
                    <template #item.user.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.user?.name }}  {{ item.user?.phone ? `(${item.user?.phone})` : '' }}
                        </span>
                    </template>

                    <!-- In time -->
                    <template #item.in_time="{ item }">
                        <span class="text-high-emphasis">
                            {{ formatTime(item.in_time) }} 
                        </span>
                    </template>

                    <!-- Out time -->
                    <template #item.out_time="{ item }">
                        <span class="text-high-emphasis">
                            {{ formatTime(item.out_time) }} 
                        </span>
                    </template>

                    <!-- Total time -->
                    <template #item.total_time="{ item }">
                        <span class="font-weight-medium text-success">
                            {{ formatTotalTime(item.total_time) }} h
                        </span>
                    </template>

                    <!-- Status -->
                    <template #item.status="{ item }">
                        <VChip 
                            :color="getAttendanceStatus(item).color" 
                            variant="tonal" 
                            size="small"
                        >
                            {{ getAttendanceStatus(item).text }}
                        </VChip>
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
                                        Total Time
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
                                        {{ (calculateTotal('total_time')) }} h
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
    attendances: {
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

const formatTime = (time) => {
    if (!time) return 'N/A'
    return time
}

const formatTotalTime = (time) => {
    if (!time) return 'N/A'
    return time
}

const getAttendanceStatus = (item) => {
    if (!item.in_time) return { text: 'Absent', color: 'error' }
    if (item.in_time > '09:00:00') return { text: 'Late', color: 'warning' }
    return { text: 'Present', color: 'success' }
}

const calculateTotal = (field) => {
    return props.attendances.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

</script>

<style lang="scss" scoped>
.attendance-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
</style>
