<script setup>
import { computed, ref } from 'vue'
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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Staff Evaluation Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
    dateRange: 'All Time',
    generatedOn: '',
    generatedBy: 'N/A',
    employeeName: 'All Employees'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedBranch = computed(() => {
    if (!branchId.value) return null
    return branches.value.find(b => b.id == branchId.value) || null
})

const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    return selectedBranch.value?.name || 'All Outlets'
})

const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.name || 'All Employees'
})

// employee phone
const selectedEmployeePhone = computed(() => {
    if (!employeeId.value) return 'N/A'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.phone || 'N/A'
})

// employee address
const selectedEmployeeAddress = computed(() => {
    if (!employeeId.value) return 'N/A'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.address || 'N/A'
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

const isFilterOptionsOpen = ref(false)

const toggleFilterOptions = () => {
  isFilterOptionsOpen.value = !isFilterOptionsOpen.value
}

</script>

<template>
    <div>
        

        <!-- Action Buttons -->
        <div class="table-action action mb-4 d-flex justify-end gap-4">
            <VBtn 
                :prepend-icon="isFilterOptionsOpen ? 'tabler-filter-off' : 'tabler-filter'" 
                variant="outlined"
                @click="toggleFilterOptions"
            >
                {{ isFilterOptionsOpen ? 'Hide Filters' : 'Filters' }}
            </VBtn>

            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                color="error"
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableStaffEvaluationReport 
                :data="evaluations" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="staff-evaluation-report"
                title="Staff Evaluation Report"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
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

        <!-- Staff Evaluation Report Table -->
        <StaffEvaluationReportTable
            :evaluations="evaluations"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-branch="selectedBranch"
            :selected-employee-name="selectedEmployeeName"
            :selected-employee-phone="selectedEmployeePhone"
            :selected-employee-address="selectedEmployeeAddress"
            :is-loading="isLoading"
            :export-headers="exportHeaders"
            @update:header-data="handleHeaderDataUpdate"
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
