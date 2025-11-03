<script setup>
import { computed, ref } from 'vue'
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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Staff Payout Report',
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

            <ExportTableStaffPayoutReport 
                :data="payouts" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="staff-payout-report"
                title="Staff Payout Report"
            />
        </div>


        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
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

        <!-- Staff Payout Report Table -->
        <StaffPayoutReportTable
            :payouts="payouts"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-branch="selectedBranch"
            :selected-employee-name="selectedEmployeeName"
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
