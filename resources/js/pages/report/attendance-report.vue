<script setup>
import { computed } from 'vue'
import { useAttendanceReport } from '@/composables/useAttendanceReport'
import AttendanceReportFilters from '@/components/report/AttendanceReportFilters.vue'
import AttendanceReportTable from '@/components/report/AttendanceReportTable.vue'
import ExportTableAttendanceReport from '@/components/ExportTableAttendanceReport.vue'

// Use the attendance report composable
const {
    // State
    attendanceData,
    isLoading,
    employeeId,
    dateFrom,
    dateTo,
    employees,
    
    // Methods
    resetFilters,
    
    // Computed
    attendances,
    totalAttendances,
    summary,
} = useAttendanceReport()

// Computed properties
const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.name || 'All Employees'
})

// Export headers for ExportTable component
const exportHeaders = computed(() => [
    { title: 'Date', key: 'date' },
    { title: 'Employee', key: 'user.name' },
    { title: 'In Time', key: 'in_time' },
    { title: 'Out Time', key: 'out_time' },
    { title: 'Total Time', key: 'total_time' },
    { title: 'Note', key: 'note' },
])

const handleResetFilters = () => {
    resetFilters()
}

</script>

<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
            <VCardText>
                <AttendanceReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:employee-id="employeeId"
                    :employees="employees"
                />
            </VCardText>
        </VCard>

        <!-- Action Buttons -->
        <div class="table-action action mb-4 d-flex justify-end gap-4">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableAttendanceReport 
                :data="attendances" 
                :headers="exportHeaders" 
                filename="attendance-report"
                title="Attendance Report"
                :summary-data="summary"
            />
        </div>

        <!-- Attendance Report Table -->
        <AttendanceReportTable
            :attendances="attendances"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-employee-name="selectedEmployeeName"
            :is-loading="isLoading"
            :export-headers="exportHeaders"
        />
    </div>
</template>

<style lang="scss" scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;

    &:hover {
        color: rgba(var(--v-theme-primary), 0.8);
    }
}
</style>
