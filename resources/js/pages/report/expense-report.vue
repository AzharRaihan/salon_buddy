<script setup>
import { computed } from 'vue'
import { useExpenseReport } from '@/composables/useExpenseReport'
import ExpenseReportFilters from '@/components/report/ExpenseReportFilters.vue'
import ExpenseSummaryCards from '@/components/report/ExpenseSummaryCards.vue'
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

// Computed properties
const selectedBranchName = computed(() => {
    if (!branchId.value) return 'All Outlets'
    const branch = branches.value.find(b => b.id == branchId.value)
    return branch?.name || 'All Outlets'
})

const selectedCategoryName = computed(() => {
    if (!categoryId.value) return 'All Categories'
    const category = categories.value.find(c => c.id == categoryId.value)
    return category?.name || 'All Categories'
})

const selectedEmployeeName = computed(() => {
    if (!employeeId.value) return 'All Employees'
    const employee = employees.value.find(e => e.id == employeeId.value)
    return employee?.name || 'All Employees'
})

// Export headers for ExportTable component
const exportHeaders = computed(() => [
    { title: 'Date', key: 'date' },
    { title: 'Branch', key: 'branch.branch_name' },
    { title: 'Category', key: 'category.name' },
    { title: 'Payment Method', key: 'payment_method.name' },
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

        <!-- Summary Cards -->
        <VCard class="mb-4">
            <VCardText>
                <ExpenseSummaryCards
                    :summary="summary"
                    :total-filtered="totalExpenses"
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
                filename="expense-report"
                title="Expense Report"
            />
        </div>

        <!-- Expense Report Table -->
        <ExpenseReportTable
            :expenses="expenses"
            :date-from="dateFrom"
            :date-to="dateTo"
            :selected-branch-name="selectedBranchName"
            :selected-category-name="selectedCategoryName"
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
