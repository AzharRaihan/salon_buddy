<template>
    <div class="balance-sheet-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Balance Sheet Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Assets Section -->
                <div class="mb-6">
                    <h5 class="text-h5 mb-4 text-success">Assets</h5>
                    <VDataTable
                        :items="assets"
                        :headers="assetsHeaders"
                        class="text-no-wrap mb-4"
                        :loading="isLoading"
                        hide-default-footer
                        :items-per-page="-1"
                    >
                        <!-- Loading state -->
                        <template #loading>
                            <VSkeletonLoader type="table-row" :rows="5" />
                        </template>

                        <!-- No data state -->
                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No asset records found</div>
                            </div>
                        </template>

                        <!-- SN -->
                        <template #item.sn="{ item }">
                            <span class="font-weight-medium">{{ item.sn }}</span>
                        </template>

                        <!-- Title -->
                        <template #item.title="{ item }">
                            <span class="text-high-emphasis font-weight-medium">
                                {{ item.title }}
                            </span>
                        </template>

                        <!-- Amount -->
                        <template #item.amount="{ item }">
                            <span class="font-weight-bold text-success">
                                {{ formatAmount(item.amount) }}
                            </span>
                        </template>

                        <!-- Summary Row -->
                        <template #bottom>
                            <VTable>
                                <tbody>
                                    <tr class="summary-row">
                                        <td class="text-h6 font-weight-bold text-success" colspan="2">
                                            <span class="d-flex align-center">
                                                <VIcon icon="tabler-sum" class="me-2" />
                                                Total Assets
                                            </span>
                                        </td>
                                        <td class="text-h6 font-weight-bold text-success">
                                            {{ formatAmount(calculateTotal(assets, 'amount')) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </VTable>
                        </template>
                    </VDataTable>
                </div>

                <!-- Liabilities Section -->
                <div class="mb-6">
                    <h5 class="text-h5 mb-4 text-error">Liabilities</h5>
                    <VDataTable
                        :items="liabilities"
                        :headers="liabilitiesHeaders"
                        class="text-no-wrap mb-4"
                        :loading="isLoading"
                        hide-default-footer
                        :items-per-page="-1"
                    >
                        <!-- Loading state -->
                        <template #loading>
                            <VSkeletonLoader type="table-row" :rows="5" />
                        </template>

                        <!-- No data state -->
                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No liability records found</div>
                            </div>
                        </template>

                        <!-- SN -->
                        <template #item.sn="{ item }">
                            <span class="font-weight-medium">{{ item.sn }}</span>
                        </template>

                        <!-- Title -->
                        <template #item.title="{ item }">
                            <span class="text-high-emphasis font-weight-medium">
                                {{ item.title }}
                            </span>
                        </template>

                        <!-- Amount -->
                        <template #item.amount="{ item }">
                            <span class="font-weight-bold text-error">
                                {{ formatAmount(item.amount) }}
                            </span>
                        </template>

                        <!-- Summary Row -->
                        <template #bottom>
                            <VTable>
                                <tbody>
                                    <tr class="summary-row">
                                        <td class="text-h6 font-weight-bold text-error" colspan="2">
                                            <span class="d-flex align-center">
                                                <VIcon icon="tabler-sum" class="me-2" />
                                                Total Liabilities
                                            </span>
                                        </td>
                                        <td class="text-h6 font-weight-bold text-error">
                                            {{ formatAmount(calculateTotal(liabilities, 'amount')) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </VTable>
                        </template>
                    </VDataTable>
                </div>

                <!-- Net Worth Summary -->
                <VCard variant="outlined" class="net-worth-card">
                    <VCardText>
                        <div class="d-flex justify-space-between align-center">
                            <div>
                                <div class="text-h5 font-weight-bold mb-2">
                                    Net Worth (Assets - Liabilities)
                                </div>
                                <div class="text-body-2 text-medium-emphasis">
                                    Overall financial position
                                </div>
                            </div>
                            <div 
                                class="text-h4 font-weight-bold"
                                :class="netWorth >= 0 ? 'text-success' : 'text-error'"
                            >
                                {{ formatAmount(netWorth) }}
                            </div>
                        </div>
                    </VCardText>
                </VCard>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
const { formatDate, formatAmount } = useCompanyFormatters()

const props = defineProps({
    assets: {
        type: Array,
        default: () => []
    },
    liabilities: {
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
    isLoading: {
        type: Boolean,
        default: false
    }
})

const assetsHeaders = [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Assets', key: 'title', sortable: false },
    { title: 'Amount', key: 'amount', sortable: false }
]

const liabilitiesHeaders = [
    { title: 'SN', key: 'sn', sortable: false },
    { title: 'Liability', key: 'title', sortable: false },
    { title: 'Amount', key: 'amount', sortable: false }
]

// Helper functions
const formatDateRange = (from, to) => {
    if (!from && !to) return 'All Time'
    if (!from) return `Until ${formatDate(to)}`
    if (!to) return `From ${formatDate(from)}`
    return `${formatDate(from)} - ${formatDate(to)}`
}

const calculateTotal = (items, field) => {
    return items.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}

const netWorth = computed(() => {
    const totalAssets = calculateTotal(props.assets, 'amount')
    const totalLiabilities = calculateTotal(props.liabilities, 'amount')
    return totalAssets - totalLiabilities
})
</script>

<style lang="scss" scoped>
.balance-sheet-report-table {
    .v-data-table {
        .v-data-table__wrapper {
            border-radius: 8px;
        }
    }
}
.summary-row {
    background-color: rgba(var(--v-theme-primary), 0.05);
    border-top: 2px solid rgb(var(--v-theme-primary));
    
    td {
        padding: 16px 12px;
        border-top: 2px solid rgb(var(--v-theme-primary));
    }
}

.net-worth-card {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-surface), 1) 100%);
    border: 2px solid rgb(var(--v-theme-primary));
}
</style>

