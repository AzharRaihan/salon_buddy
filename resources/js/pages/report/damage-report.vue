<script setup>
import { computed } from 'vue'
import { useDamageReport } from '@/composables/useDamageReport'
import DamageReportFilters from '@/components/report/DamageReportFilters.vue'
import DamageSummaryCards from '@/components/report/DamageSummaryCards.vue'
import DamageReportTable from '@/components/report/DamageReportTable.vue'

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
    exportReport,
    
    // Computed
    damages,
    totalDamages,
    summary,
} = useDamageReport()

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
                    @reset-filters="resetFilters"
                    @export-report="exportReport"
                />
            </VCardText>
        </VCard>

        <!-- Summary Cards -->
        <VCard class="mb-4">
            <VCardText>
                <DamageSummaryCards
                    :summary="summary"
                    :total-filtered="totalDamages"
                />
            </VCardText>
        </VCard>

        <!-- Damage Report Table -->
        <DamageReportTable
            :damages="damages"
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
</style>
