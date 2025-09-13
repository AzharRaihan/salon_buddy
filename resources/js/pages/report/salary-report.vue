<script setup>
import { computed } from 'vue'
import { useSalaryReport } from '@/composables/useSalaryReport'
import SalaryReportFilters from '@/components/report/SalaryReportFilters.vue'
import SalarySummaryCards from '@/components/report/SalarySummaryCards.vue'
import SalaryReportTable from '@/components/report/SalaryReportTable.vue'
import ExportTable from '@/components/ExportTable.vue'

// Use the salary report composable
const {
    // State
    salaryData,
    isLoading,
    branchId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchSalaryReport,
    resetFilters,
    exportReport,
    
    // Computed
    salaries,
    totalSalaries,
    summary,
} = useSalaryReport()

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
    { title: 'Date', key: 'date' },
    { title: 'Branch', key: 'branch.name' },
    { title: 'Basic Salary', key: 'basic_salary' },
    { title: 'Allowances', key: 'allowances' },
    { title: 'Deductions', key: 'deductions' },
    { title: 'Net Salary', key: 'net_salary' },
    { title: 'Payment Status', key: 'payment_status' },
])
</script>

<template>
    <div>
        <!-- Filter Section -->
        <VCard class="mb-4">
            <VCardText>
                <SalaryReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:employee-id="employeeId"
                    :branches="branches"
                    :employees="employees"
                    @reset-filters="resetFilters"
                    @export-report="exportReport"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <!-- <VCard class="mb-4">
            <VCardText>
                <SalarySummaryCards
                    :summary="summary"
                    :total-filtered="totalSalaries"
                />
            </VCardText>
        </VCard> -->

        <!-- Action Buttons -->
        <div class="table-action action">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="resetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTable 
                :data="salaries" 
                :headers="exportHeaders" 
                filename="salary-report"
                title="Salary Report"
            />
        </div>

        <!-- Salary Report Table -->
        <SalaryReportTable
            :salaries="salaries"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-employee-name="selectedEmployeeName"
            :is-loading="isLoading"
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

.table-action {
    display: flex;
    justify-content: end;
    gap: 10px;
    padding-right: 24px;
    padding-bottom: 24px;
}
</style>
