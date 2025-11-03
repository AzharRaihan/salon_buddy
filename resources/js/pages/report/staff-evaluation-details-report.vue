<script setup>
import { computed, ref } from 'vue'
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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Staff Evaluation Details Report',
    employeeName: 'All Employees',
    employeePhone: null,
    dateRange: 'All Time',
    generatedOn: '',
    generatedBy: 'N/A'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedEmployee = computed(() => {
    if (!employeeId.value) return null
    return employees.value.find(e => e.id == employeeId.value) || null
})

const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    return selectedEmployee.value?.name || 'All Employees'
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

            <ExportTableStaffEvaluationDetailsReport 
                :data="evaluationDetails" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="staff-evaluation-details-report"
                title="Staff Evaluation Details Report"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
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

        <!-- Staff Evaluation Details Report Table -->
        <StaffEvaluationDetailsReportTable
            :evaluation-details="evaluationDetails"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-employee-name="selectedEmployeeName"
            :selected-employee="selectedEmployee"
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

