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
                            {{ item.user?.name || 'N/A' }}
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
                            {{ formatTotalTime(item.total_time) }}
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
                </VDataTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate } = useCompanyFormatters()

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
    }
})

// Table headers
const headers = [
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Employee', key: 'user.name', sortable: false },
    { title: 'In Time', key: 'in_time', sortable: false },
    { title: 'Out Time', key: 'out_time', sortable: false },
    { title: 'Total Time', key: 'total_time', sortable: false },
    { title: 'Status', key: 'status', sortable: false },
    { title: 'Note', key: 'note', sortable: false },
]

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
