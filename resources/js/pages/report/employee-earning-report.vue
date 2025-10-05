<script setup>
import { computed } from 'vue'
import { useEmployeeEarningReport } from '@/composables/useEmployeeEarningReport'
import EmployeeCommissionReportFilters from '@/components/report/EmployeeCommissionReportFilters.vue'
import EmployeeCommissionSummaryCards from '@/components/report/EmployeeCommissionSummaryCards.vue'
import EmployeeCommissionReportTable from '@/components/report/EmployeeCommissionReportTable.vue'
import ExportTableEmployeeCommissionReport from '@/components/ExportTableEmployeeCommissionReport.vue'

// Use the employee commission report composable
const {
    // State
    earningData,
    isLoading,
    branchId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchCommissionReport,
    resetFilters,
    
    // Computed
    commissions,
    totalCommissions,
    summary,
} = useEmployeeEarningReport()

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
    { title: 'Date', key: 'order_date' },
    { title: 'Employee', key: 'employee.name' },
    { title: 'Subtotal', key: 'subtotal' },
    { title: 'Quantity', key: 'quantity' },
    { title: 'Commission', key: 'commission' },

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
                <EmployeeCommissionReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:employee-id="employeeId"
                    :branches="branches"
                    :employees="employees"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <VCard class="mb-4">
            <VCardText>
                <EmployeeCommissionSummaryCards
                    :summary="summary"
                    :total-filtered="totalCommissions"
                />
            </VCardText>
        </VCard>

        <!-- Action Buttons -->
        <div class="table-action action">
            <VBtn 
                prepend-icon="tabler-refresh" 
                variant="outlined" 
                @click="handleResetFilters"
            >
                Reset Filters
            </VBtn>

            <ExportTableEmployeeCommissionReport 
                :data="commissions" 
                :headers="exportHeaders" 
                :summary-data="summary"
                filename="employee-commission-report"
                title="Employee Commission Report"
            />
        </div>

        <!-- Employee Commission Report Table -->
        <EmployeeCommissionReportTable
            :commissions="commissions"
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

.table-action {
    display: flex;
    justify-content: end;
    gap: 10px;
    padding-right: 24px;
    padding-bottom: 24px;
}
</style>
