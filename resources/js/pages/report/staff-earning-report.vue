<script setup>
import { computed } from 'vue'
import { useStaffEarningReport } from '@/composables/useStaffEarningReport'
import StaffEarningReportFilters from '@/components/report/StaffEarningReportFilters.vue'
import StaffEarningSummaryCards from '@/components/report/StaffEarningSummaryCards.vue'
import StaffEarningReportTable from '@/components/report/StaffEarningReportTable.vue'
import ExportTableStaffEarningReport from '@/components/ExportTableStaffEarningReport.vue'

// Use the staff earning report composable
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
    fetchEarningReport,
    resetFilters,
    
    // Computed
    earnings,
    totalEarnings,
    summary,
} = useStaffEarningReport()

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
    { title: 'Done Service', key: 'quantity' },
    { title: 'Total Earning', key: 'subtotal' },
    { title: 'Commission Rate', key: 'commission_rate' },
    { title: 'Staff Earning', key: 'commission' },
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
                <StaffEarningReportFilters
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
                <StaffEarningSummaryCards
                    :summary="summary"
                    :total-filtered="totalEarnings"
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

            <ExportTableStaffEarningReport 
                :data="earnings" 
                :headers="exportHeaders" 
                :summary-data="summary"
                filename="staff-earning-report"
                title="Staff Earning Report"
            />
        </div>

        <!-- Staff Earning Report Table -->
        <StaffEarningReportTable
            :earnings="earnings"
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
