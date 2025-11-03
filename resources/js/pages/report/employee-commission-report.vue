<script setup>
import { computed, ref } from 'vue'
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

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Employee Commission Report',
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

            <ExportTableEmployeeCommissionReport 
                :data="commissions" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="employee-commission-report"
                title="Employee Commission Report"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
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



        <!-- Employee Commission Report Table -->
        <EmployeeCommissionReportTable
            :commissions="commissions"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-branch="selectedBranch"
            :branches="branches"
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
