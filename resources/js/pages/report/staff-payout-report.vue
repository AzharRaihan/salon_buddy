<script setup>
import { computed } from 'vue'
import { useStaffPayoutReport } from '@/composables/useStaffPayoutReport'
import StaffPayoutReportFilters from '@/components/report/StaffPayoutReportFilters.vue'
import StaffPayoutReportTable from '@/components/report/StaffPayoutReportTable.vue'
import ExportTableStaffPayoutReport from '@/components/ExportTableStaffPayoutReport.vue'

// Use the staff payout report composable
const {
    // State
    payoutData,
    isLoading,
    branchId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchPayoutReport,
    resetFilters,
    
    // Computed
    payouts,
    totalPayouts,
    summary,
} = useStaffPayoutReport()

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
    { title: 'Reference No', key: 'reference_no' },
    { title: 'Employee', key: 'employee.name' },
    { title: 'Payment Account', key: 'payment_method.name' },
    { title: 'Amount', key: 'amount' },
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
                <StaffPayoutReportFilters
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

            <ExportTableStaffPayoutReport 
                :data="payouts" 
                :headers="exportHeaders" 
                :summary-data="summary"
                filename="staff-payout-report"
                title="Staff Payout Report"
            />
        </div>

        <!-- Staff Payout Report Table -->
        <StaffPayoutReportTable
            :payouts="payouts"
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
