<script setup>
import { computed } from 'vue'
import { useStaffEvaluationDetailsReport } from '@/composables/useStaffEvaluationDetailsReport'
import StaffEvaluationReportFilters from '@/components/report/StaffEvaluationReportFilters.vue'
import StaffEvaluationDetailsReportTable from '@/components/report/StaffEvaluationDetailsReportTable.vue'
import ExportTableStaffEvaluationDetailsReport from '@/components/ExportTableStaffEvaluationDetailsReport.vue'

// Use the staff evaluation details report composable
const {
    // State
    evaluationDetailsData,
    isLoading,
    employeeId,
    dateFrom,
    dateTo,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchEvaluationDetailsReport,
    resetFilters,
    
    // Computed
    evaluationDetails,
    totalEvaluationDetails,
    summary,
} = useStaffEvaluationDetailsReport()

// Computed properties
const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.name || 'All Employees'
})

// Export headers for ExportTable component
const exportHeaders = computed(() => [
    { title: 'Staff Name', key: 'employee.name' },
    { title: 'Customer Name', key: 'customer.name' },
    { title: 'Item Name', key: 'item.name' },
    { title: 'Rating', key: 'rating_number' },
    { title: 'Rating Visual', key: 'rating' },
    { title: 'Created Date', key: 'created_at' },
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
                <StaffEvaluationReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:employee-id="employeeId"
                    :employees="employees"
                    :hide-branch="true"
                    :employee-required="true"
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

            <ExportTableStaffEvaluationDetailsReport 
                :data="evaluationDetails" 
                :headers="exportHeaders" 
                :summary-data="summary"
                filename="staff-evaluation-details-report"
                title="Staff Evaluation Details Report"
            />
        </div>

        <!-- Staff Evaluation Details Report Table -->
        <StaffEvaluationDetailsReportTable
            :evaluation-details="evaluationDetails"
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

