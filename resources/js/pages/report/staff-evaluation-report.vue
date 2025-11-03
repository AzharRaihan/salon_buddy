<script setup>
import { computed } from 'vue'
import { useStaffEvaluationReport } from '@/composables/useStaffEvaluationReport'
import StaffEvaluationReportFilters from '@/components/report/StaffEvaluationReportFilters.vue'
import StaffEvaluationReportTable from '@/components/report/StaffEvaluationReportTable.vue'
import ExportTableStaffEvaluationReport from '@/components/ExportTableStaffEvaluationReport.vue'

// Use the staff evaluation report composable
const {
    // State
    evaluationData,
    isLoading,
    branchId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchEvaluationReport,
    resetFilters,
    
    // Computed
    evaluations,
    totalEvaluations,
    summary,
} = useStaffEvaluationReport()

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.name || 'All Employees'
})

// Export headers for ExportTable component
const exportHeaders = computed(() => [
    { title: 'Staff Name', key: 'employee.name' },
    { title: 'Total Ratings', key: 'total_ratings' },
    { title: 'Average Rating', key: 'avg_rating' },
    { title: 'Rating Visual', key: 'rating' },
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
                    v-model:branch-id="branchId"
                    v-model:employee-id="employeeId"
                    :branches="branches"
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

            <ExportTableStaffEvaluationReport 
                :data="evaluations" 
                :headers="exportHeaders" 
                :summary-data="summary"
                filename="staff-evaluation-report"
                title="Staff Evaluation Report"
            />
        </div>

        <!-- Staff Evaluation Report Table -->
        <StaffEvaluationReportTable
            :evaluations="evaluations"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
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
