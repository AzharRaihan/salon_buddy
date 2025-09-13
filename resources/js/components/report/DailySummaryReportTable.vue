<template>
    <div class="daily-summary-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Daily Summary Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Date: {{ formatDate(selectedDate) }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Outlet: {{ selectedBranchName }}
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <VRow class="mb-6">
                    <VCol cols="12" sm="6" md="3">
                        <VCard variant="outlined" class="summary-card">
                            <VCardText class="d-flex align-center justify-space-between">
                                <div>
                                    <div class="text-h6 font-weight-bold text-success">
                                        {{ formatAmount(totalSales) }}
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis">
                                        Total Sales
                                    </div>
                                </div>
                                <VIcon 
                                    icon="tabler-shopping-cart" 
                                    size="32" 
                                    color="success"
                                />
                            </VCardText>
                        </VCard>
                    </VCol>

                    <VCol cols="12" sm="6" md="3">
                        <VCard variant="outlined" class="summary-card">
                            <VCardText class="d-flex align-center justify-space-between">
                                <div>
                                    <div class="text-h6 font-weight-bold text-info">
                                        {{ formatAmount(totalPurchases) }}
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis">
                                        Total Purchases
                                    </div>
                                </div>
                                <VIcon 
                                    icon="tabler-package" 
                                    size="32" 
                                    color="info"
                                />
                            </VCardText>
                        </VCard>
                    </VCol>

                    <VCol cols="12" sm="6" md="3">
                        <VCard variant="outlined" class="summary-card">
                            <VCardText class="d-flex align-center justify-space-between">
                                <div>
                                    <div class="text-h6 font-weight-bold text-warning">
                                        {{ formatAmount(totalSupplierDue) }}
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis">
                                        Supplier Due Payment
                                    </div>
                                </div>
                                <VIcon 
                                    icon="tabler-credit-card" 
                                    size="32" 
                                    color="warning"
                                />
                            </VCardText>
                        </VCard>
                    </VCol>

                    <VCol cols="12" sm="6" md="3">
                        <VCard variant="outlined" class="summary-card">
                            <VCardText class="d-flex align-center justify-space-between">
                                <div>
                                    <div class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(totalCustomerDue) }}
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis">
                                        Customer Due Receive
                                    </div>
                                </div>
                                <VIcon 
                                    icon="tabler-wallet" 
                                    size="32" 
                                    color="primary"
                                />
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>

                <!-- Sales Table -->
                <VCard variant="outlined" class="mb-4">
                    <VCardTitle class="d-flex align-center">
                        <VIcon icon="tabler-shopping-cart" class="me-2" />
                        Today's Sales
                    </VCardTitle>
                    <VDataTable
                        :items="sales"
                        :headers="salesHeaders"
                        class="text-no-wrap"
                        :loading="isLoading"
                        hide-default-footer
                    >
                        <template #item.date="{ item }">
                            <span class="font-weight-medium">
                                {{ formatDate(item.date) }}
                            </span>
                        </template>

                        <template #item.total_payable="{ item }">
                            <span class="font-weight-medium">
                                {{ formatAmount(item.total_payable) }}
                            </span>
                        </template>
                        <template #item.total_paid="{ item }">
                            <span class="font-weight-medium" :class="(item.total_payable.toFixed(formatNumberPrecision) != item.total_paid.toFixed(formatNumberPrecision)) && item.total_paid > 0 ? 'text-warning' : ''">
                                {{ formatAmount(item.total_paid) }}
                            </span>
                        </template>
                        <template #item.total_due="{ item }">
                            <span class="font-weight-medium" :class="item.total_due > 0 ? 'text-error' : ''">
                                {{ formatAmount(item.total_due) }}
                            </span>
                        </template>
                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No sales found for this date</div>
                            </div>
                        </template>
                    </VDataTable>
                </VCard>

                <!-- Purchases Table -->
                <VCard variant="outlined" class="mb-4">
                    <VCardTitle class="d-flex align-center">
                        <VIcon icon="tabler-package" class="me-2" />
                        Today's Purchases
                    </VCardTitle>
                    <VDataTable
                        :items="purchases"
                        :headers="purchasesHeaders"
                        class="text-no-wrap"
                        :loading="isLoading"
                        hide-default-footer
                    >
                        <template #item.date="{ item }">
                            <span class="font-weight-medium">
                                {{ formatDate(item.date) }}
                            </span>
                        </template>

                        <template #item.grand_total="{ item }">
                            <span class="font-weight-medium">
                                {{ formatAmount(item.grand_total) }}
                            </span>
                        </template>
                        <template #item.paid_amount="{ item }">
                            <span class="font-weight-medium" :class="(item.grand_total.toFixed(formatNumberPrecision) != item.paid_amount.toFixed(formatNumberPrecision)) && item.paid_amount > 0 ? 'text-warning' : ''">
                                {{ formatAmount(item.paid_amount) }}
                            </span>
                        </template>
                        <template #item.due_amount="{ item }">
                            <span class="font-weight-medium" :class="item.due_amount > 0 ? 'text-error' : ''">
                                {{ formatAmount(item.due_amount) }}
                            </span>
                        </template>

                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No purchases found for this date</div>
                            </div>
                        </template>
                    </VDataTable>
                </VCard>

                <!-- Supplier Due Payments Table -->
                <VCard variant="outlined" class="mb-4">
                    <VCardTitle class="d-flex align-center">
                        <VIcon icon="tabler-credit-card" class="me-2" />
                        Supplier Due Payments
                    </VCardTitle>
                    <VDataTable
                        :items="supplierDuePayments"
                        :headers="supplierDueHeaders"
                        class="text-no-wrap"
                        :loading="isLoading"
                        hide-default-footer
                    >
                        <template #item.date="{ item }">
                            <span class="font-weight-medium">
                                {{ formatDate(item.date) }}
                            </span>
                        </template>

                        <template #item.amount="{ item }">
                            <span class="font-weight-medium">
                                {{ formatAmount(item.amount) }}
                            </span>
                        </template>

                        <template #item.note="{ item }">
                            <span class="font-weight-medium">
                                {{ item.note }}
                            </span>
                        </template>
                        

                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No supplier due payments found</div>
                            </div>
                        </template>
                    </VDataTable>
                </VCard>

                <!-- Customer Due Receives Table -->
                <VCard variant="outlined">
                    <VCardTitle class="d-flex align-center">
                        <VIcon icon="tabler-wallet" class="me-2" />
                        Customer Due Receives
                    </VCardTitle>
                    <VDataTable
                        :items="customerDueReceives"
                        :headers="customerDueHeaders"
                        class="text-no-wrap"
                        :loading="isLoading"
                        hide-default-footer
                    >
                        <template #item.due_date="{ item }">
                            <span class="font-weight-medium">
                                {{ formatDate(item.date) }}
                            </span>
                        </template>

                        <template #item.amount="{ item }">
                            <span class="font-weight-medium">
                                {{ formatAmount(item.amount) }}
                            </span>
                        </template>

                        <template #item.note="{ item }">
                            <span class="font-weight-medium">
                                {{ item.note }}
                            </span>
                        </template>

                        <template #no-data>
                            <div class="d-flex align-center justify-center pa-4">
                                <VIcon icon="tabler-alert-circle" class="me-2" />
                                <div>No customer due receives found</div>
                            </div>
                        </template>
                    </VDataTable>
                </VCard>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { formatDate, formatAmount, formatNumber, formatNumberPrecision } = useCompanyFormatters();
const props = defineProps({
    sales: {
        type: Array,
        default: () => []
    },
    purchases: {
        type: Array,
        default: () => []
    },
    supplierDuePayments: {
        type: Array,
        default: () => []
    },
    customerDueReceives: {
        type: Array,
        default: () => []
    },
    selectedDate: {
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

// Table headers
const salesHeaders = [
    { title: 'Invoice No', key: 'invoice_no', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Customer', key: 'customer_name', sortable: false },
    { title: 'Total Payable', key: 'total_payable', sortable: true },
    { title: 'Total Paid', key: 'total_paid', sortable: true },
    { title: 'Total Due', key: 'total_due', sortable: true },
]

const purchasesHeaders = [
    { title: 'Invoice No', key: 'invoice_no', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Supplier', key: 'supplier_name', sortable: false },
    { title: 'Total', key: 'grand_total', sortable: true },
    { title: 'Paid', key: 'paid_amount', sortable: true },
    { title: 'Due', key: 'due_amount', sortable: true },
]

const supplierDueHeaders = [
    { title: 'Supplier', key: 'supplier_name', sortable: false },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Amount', key: 'amount', sortable: true },
    { title: 'Note', key: 'note', sortable: false },
]

const customerDueHeaders = [
    { title: 'Customer', key: 'customer_name', sortable: false },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Amount', key: 'amount', sortable: true },
    { title: 'Note', key: 'note', sortable: false },
]

// Computed properties
const totalSales = computed(() => {
    return props.sales.reduce((sum, sale) => sum + (sale.total_payable || 0), 0)
})

const totalPurchases = computed(() => {
    return props.purchases.reduce((sum, purchase) => sum + (purchase.grand_total || 0), 0)
})

const totalSupplierDue = computed(() => {
    return props.supplierDuePayments.reduce((sum, payment) => sum + (payment.amount || 0), 0)
})

const totalCustomerDue = computed(() => {
    return props.customerDueReceives.reduce((sum, receive) => sum + (receive.amount || 0), 0)
})



</script>

<style lang="scss" scoped>
.daily-summary-report-table {
    .summary-card {
        transition: transform 0.2s ease-in-out;
        
        &:hover {
            transform: translateY(-2px);
        }
    }
    
    .v-card-text {
        padding: 1.5rem;
    }
}
</style>
