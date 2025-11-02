<script setup>
import { computed, ref } from 'vue'
import { useExpenseReport } from '@/composables/useExpenseReport'
import ExpenseReportFilters from '@/components/report/ExpenseReportFilters.vue'
import ExpenseReportTable from '@/components/report/ExpenseReportTable.vue'
import ExportTableExpenseReport from '@/components/ExportTableExpenseReport.vue'

// Use the expense report composable
const {
    // State
    expenseData,
    isLoading,
    branchId,
    categoryId,
    employeeId,
    dateFrom,
    dateTo,
    branches,
    categories,
    employees,
    // Methods
    resetFilters,
    
    // Computed
    expenses,
    totalExpenses,
    summary,
} = useExpenseReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: 'Expense Report',
    outletName: 'All Outlets',
    phone: null,
    address: null,
    responsiblePersonName: null,
    responsiblePersonPhone: null,
    categoryName: null,
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

const selectedCategoryName = computed(() => {
    if (!categoryId.value) return 'All Categories'
    const category = categories.value.find(c => c.id == categoryId.value)
    return category?.name || 'All Categories'
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
    { title: 'Date', key: 'date' },
    { title: 'Branch', key: 'branch.branch_name' },
    { title: 'Category', key: 'category.name' },
    { title: 'Payment Account', key: 'payment_method.name' },
    { title: 'Employee', key: 'employee.name' },
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
                <ExpenseReportFilters
                    v-model:date-from="dateFrom"
                    v-model:date-to="dateTo"
                    v-model:branch-id="branchId"
                    v-model:category-id="categoryId"
                    v-model:employee-id="employeeId"
                    :branches="branches"
                    :categories="categories"
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

            <ExportTableExpenseReport 
                :data="expenses" 
                :headers="exportHeaders" 
                :summary-data="summary"
                :header-data="reportHeaderData"
                filename="expense-report"
                title="Expense Report"
            />
        </div>

        <!-- Expense Report Table -->
        <ExpenseReportTable
            :expenses="expenses"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch="selectedBranch"
            :selected-branch-name="selectedBranchName"
            :selected-category-name="selectedCategoryName"
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
