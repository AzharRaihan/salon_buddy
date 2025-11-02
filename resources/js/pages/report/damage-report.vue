<script setup>
import { computed, ref } from 'vue'
import { useDamageReport } from '@/composables/useDamageReport'
import DamageReportFilters from '@/components/report/DamageReportFilters.vue'
import DamageReportTable from '@/components/report/DamageReportTable.vue'
import ExportTableDamageReport from '@/components/ExportTableDamageReport.vue'

// Use the damage report composable
const {
    // State
    damageData,
    isLoading,
    branchId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    employees,
    
    // Methods
    fetchFilterOptions,
    fetchDamageReport,
    resetFilters,
    
    // Computed
    damages,
    totalDamages,
    summary,
} = useDamageReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Damage Report',
    outletName: 'All Outlets',
    phone: null,
    address: null,
    responsiblePersonName: null,
    responsiblePersonPhone: null,
    dateRange: 'N/A',
    generatedOn: '',
    generatedBy: 'N/A'
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
    { title: 'Reference No', key: 'reference_no', sortable: true },
    { title: 'Date', key: 'date' },
    { title: 'Employee', key: 'employee.name' },
    { title: 'Damage Items', key: 'damage_items' },
    { title: 'Total Loss', key: 'total_loss' },
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
                <DamageReportFilters
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

            <ExportTableDamageReport 
                :data="damages" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="damage-report"
                title="Damage Report"
            />
        </div>

        <!-- Damage Report Table -->
        <DamageReportTable
            :damages="damages"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch="selectedBranch"
            :selected-branch-name="selectedBranchName"
            :selected-employee="selectedEmployee"
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
