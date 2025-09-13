<template>
    <div class="damage-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Damage Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Employee: {{ selectedEmployeeName }}
                        </div>
                    </div>
                </div>

                <!-- Damage Table -->
                <VDataTable
                    :items="damages"
                    :headers="headers"
                    class="text-no-wrap"
                    :loading="isLoading"
                    hide-default-footer
                >
                    <!-- Loading state -->
                    <template #loading>
                        <VSkeletonLoader type="table-row" :rows="10" />
                    </template>

                    <!-- No data state -->
                    <template #no-data>
                        <div class="d-flex align-center justify-center pa-4">
                            <VIcon icon="tabler-alert-circle" class="me-2" />
                            <div>
                                No damage records found with current filters
                            </div>
                        </div>
                    </template>

                    <!-- Reference No -->
                    <template #item.reference_no="{ item }">
                        <span class="font-weight-medium">
                            {{ item.reference_no || 'N/A' }}
                        </span>
                    </template>

                    <!-- Date formatting -->
                    <template #item.date="{ item }">
                        <span class="font-weight-medium">
                            {{ formatDate(item.date) }}
                        </span>
                    </template>

                    <!-- Employee name -->
                    <template #item.employee.name="{ item }">
                        <span class="text-high-emphasis">
                            {{ item.employee?.name || 'N/A' }}
                        </span>
                    </template>


                    <!-- Due amount -->
                    <template #item.total_loss="{ item }">
                        <span class="font-weight-medium text-error">
                            {{ formatAmount(item.total_loss) }}
                        </span>
                    </template>


                    <!-- Note truncation -->
                    <template #item.note="{ item }">
                        <span :title="item.note">
                            {{ item.note ? (item.note.length > 50 ? item.note.substring(0, 50) + '...' : item.note) : 'N/A' }}
                        </span>
                    </template>
                </VDataTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount } = useCompanyFormatters()

const props = defineProps({
    damages: {
        type: Array,
        default: () => []
    },
    dateFrom: {
        type: String,
        default: ''
    },
    dateTo: {
        type: String,
        default: ''
    },
    selectedBranchName: {
        type: String,
        default: 'All Outlets'
    },
    selectedEmployeeName: {
        type: String,
        default: 'All Employees'
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

// Table headers
const headers = [
    { title: 'Reference No', key: 'reference_no', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Employee', key: 'employee.name', sortable: false },
    { title: 'Total Loss', key: 'total_loss', sortable: true },
    { title: 'Note', key: 'note', sortable: false },
]

// Helper functions
const formatDateRange = (from, to) => {
    if (!from && !to) return 'All Time'
    if (!from) return `Until ${formatDate(to)}`
    if (!to) return `From ${formatDate(from)}`
    return `${formatDate(from)} - ${formatDate(to)}`
}

</script>

<style lang="scss" scoped>
.damage-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
</style>
