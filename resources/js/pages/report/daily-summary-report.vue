<script setup>
import { computed, ref } from 'vue'
import { useDailySummaryReport } from '@/composables/useDailySummaryReport'
import DailySummaryReportFilters from '@/components/report/DailySummaryReportFilters.vue'
import DailySummaryReportTable from '@/components/report/DailySummaryReportTable.vue'
import ExportTableDailySummaryReport from '@/components/ExportTableDailySummaryReport.vue'

// Use the daily summary report composable
const {
    // State
    dailySummaryData,
    isLoading,
    branchId,
    selectedDate,
    branches,
    
    // Methods
    fetchFilterOptions,
    fetchDailySummaryReport,
    resetFilters,
    exportReport,
    
    // Computed
    reportData,
    sales,
    purchases,
    supplierDuePayments,
    customerDueReceives,
} = useDailySummaryReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Daily Summary Report',
    outletName: 'All Outlets',
    address: 'N/A',
    phone: 'N/A',
    date: 'N/A',
    generatedOn: '',
    generatedBy: 'N/A'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedBranch = computed(() => {
    if (!branchId.value) return null
    return branches.value.find(b => b.id == branchId.value) || null
})

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

            <ExportTableDailySummaryReport 
                :sales="sales"
                :purchases="purchases"
                :supplier-due-payments="supplierDuePayments"
                :customer-due-receives="customerDueReceives"
                filename="daily-summary-report"
                title="Daily Summary Report"
                :header-data="reportHeaderData"
            />
        </div>

        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
            <VCardText>
                <DailySummaryReportFilters
                    v-model:selected-date="selectedDate"
                    v-model:branch-id="branchId"
                    :branches="branches"
                    @reset-filters="resetFilters"
                    @export-report="exportReport"
                />
            </VCardText>
        </VCard>

        <!-- Daily Summary Report Table -->
        <DailySummaryReportTable
            :sales="sales"
            :purchases="purchases"
            :supplier-due-payments="supplierDuePayments"
            :customer-due-receives="customerDueReceives"
            :selected-date="selectedDate"
            :selected-branch-name="selectedBranchName"
            :selected-branch="selectedBranch"
            :branches="branches"
            :is-loading="isLoading"
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
