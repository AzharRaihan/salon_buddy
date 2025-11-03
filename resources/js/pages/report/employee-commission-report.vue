<script setup>
import { computed } from 'vue'
import { useEmployeeCommissionReport } from '@/composables/useEmployeeCommissionReport'
import EmployeeCommissionReportFilters from '@/components/report/EmployeeCommissionReportFilters.vue'
import EmployeeCommissionReportTable from '@/components/report/EmployeeCommissionReportTable.vue'
import ExportTableEmployeeCommissionReport from '@/components/ExportTableEmployeeCommissionReport.vue'

// Use the employee commission report composable
const {
    // State
    commissionData,
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
} = useEmployeeCommissionReport()

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
    { title: 'Invoice No', key: 'reference_no' },
    { title: 'Employee', key: 'employee.name' },
    { title: 'Service/Item', key: 'item.name' },
    { title: 'Subtotal', key: 'subtotal' },
    { title: 'Commission Rate', key: 'commission_rate' },
    { title: 'Commission Amount', key: 'commission_amount' },
    { title: 'Status', key: 'order_status' },
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


        <!-- Action Buttons -->
        <div class="table-action action mb-4 d-flex justify-end gap-4">
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
</style>
