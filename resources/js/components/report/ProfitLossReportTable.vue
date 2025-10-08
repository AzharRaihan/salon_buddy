<template>
    <div class="profit-loss-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Profit & Loss Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date Range: {{ formatDateRange(dateFrom, dateTo) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-body-2 text-medium-emphasis">
                            Costing Method: {{ selectedCostingMethod }}
                        </div>
                    </div>
                </div>

                <!-- Profit & Loss Table -->
                <VTable class="profit-loss-table">
                    <tbody>
                        <!-- 1. Total Sales (Paid & Unpaid) (Incl. Tax & Discount) -->
                        <tr>
                            <td class="font-weight-medium">1.</td>
                            <td class="font-weight-medium">Total Sales (Paid & Unpaid) (Incl. Tax & Discount)</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.total_sales || 0) }}
                            </td>
                        </tr>

                        <!-- 2. Total Cost of Sale -->
                        <tr>
                            <td class="font-weight-medium">2.</td>
                            <td class="font-weight-medium">Total Cost of Sale</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.total_cost_of_sale || 0) }}
                            </td>
                        </tr>

                        <!-- 3. Tax -->
                        <tr>
                            <td class="font-weight-medium">3.</td>
                            <td class="font-weight-medium">Tax</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.tax || 0) }}
                            </td>
                        </tr>

                        <!-- 4. Discount -->
                        <tr>
                            <td class="font-weight-medium">4.</td>
                            <td class="font-weight-medium">Discount</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.discount || 0) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="font-weight-medium">5.</td>
                            <td class="font-weight-medium">Tips</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.total_tips || 0) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="font-weight-medium">6.</td>
                            <td class="font-weight-medium">Delivery Charge</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.delivery_charge || 0) }}
                            </td>
                        </tr>

                        <tr class="gross-profit-row">
                            <td class="font-weight-bold">7.</td>
                            <td class="font-weight-bold">
                                Gross Profit (1) - (2+3+4+5+6)
                            </td>
                            <td class="text-right">
                                {{ formatAmount(reportData.gross_profit || 0) }}
                            </td>
                        </tr>


                        <tr>
                            <td class="font-weight-medium">8.</td>
                            <td class="font-weight-medium">Total Salaries</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.total_salaries || 0) }}
                            </td>
                        </tr>

                        <!-- 7. Expense -->
                        <tr>
                            <td class="font-weight-medium">9.</td>
                            <td class="font-weight-medium">Expense</td>
                            <td class="text-right">
                                {{ formatAmount(reportData.expense || 0) }}
                            </td>
                        </tr>

                        <!-- 8. Net Profit (5) - (6+7) -->
                        <tr class="net-profit-row">
                            <td class="font-weight-bold">10.</td>
                            <td class="font-weight-bold">
                                Net Profit (7) - (8+9)
                            </td>
                            <td class="text-right">
                                {{ formatAmount(reportData.net_profit || 0) }}
                            </td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { formatDate, formatAmount, formatNumber, formatNumberPrecision } = useCompanyFormatters();
const props = defineProps({
    reportData: {
        type: Object,
        default: () => ({})
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
    selectedCostingMethod: {
        type: String,
        default: 'Last Purchase Price'
    }
})

// Computed properties
const grossProfitClass = computed(() => {
    const profit = props.reportData.gross_profit || 0
    return profit >= 0 ? 'text-success' : 'text-error'
})

const netProfitClass = computed(() => {
    const profit = props.reportData.net_profit || 0
    return profit >= 0 ? 'text-success' : 'text-error'
})



const formatDateRange = (from, to) => {
    if (!from && !to) return 'All Time'
    if (!from) return `Until ${formatDate(to)}`
    if (!to) return `From ${formatDate(from)}`
    return `${formatDate(from)} - ${formatDate(to)}`
}

</script>

<style lang="scss" scoped>
.profit-loss-report-table {
    .profit-loss-table {
        .gross-profit-row,
        .net-profit-row {
            background-color: rgba(var(--v-theme-primary), 0.1);
            
            td {
                border-top: 2px solid rgb(var(--v-theme-primary));
                border-bottom: 2px solid rgb(var(--v-theme-primary));
            }
        }
        
        td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.12);
            
            &:first-child {
                width: 40px;
                text-align: center;
            }
            
            &:nth-child(2) {
                width: auto;
            }
            
            &:last-child {
                width: 150px;
                text-align: right;
            }
        }
    }
}
</style>
