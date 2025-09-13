<template>
    <div class="stock-report-table">
        <VCard>
            <VCardText>
                <!-- Report Header -->
                <div class="d-flex justify-space-between align-center mb-6">
                    <div>
                        <h4 class="text-h4 mb-2">Stock Report</h4>
                        <div class="text-body-1 text-medium-emphasis">
                            Supplier: {{ selectedSupplierName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Item: {{ selectedItemName }}
                        </div>
                        <div class="text-body-1 text-medium-emphasis">
                            Category: {{ selectedCategoryName }}
                        </div>
                    </div>
                </div>

                <!-- Stock Table -->

                <VDataTable
                    :items="stocks"
                    :headers="exportHeaders"
                    class="text-no-wrap"
                    :loading="isLoading"
                    hide-default-footer
                    :items-per-page="-1"
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
                                {{ t('No products found') }}
                            </div>
                        </div>
                    </template>

                    <!-- Serial Number -->
                    <template #item.serial_number="{ item }">
                        {{ (item.serial_number) }}
                    </template>


                    <!-- Item Name & Code -->
                    <template #item.name="{ item }">
                        {{ item?.name }}
                        <span class="text-body-2 text-medium-emphasis">({{ item?.code || '-' }})</span>
                    </template>

                    <!-- Category -->
                    <template #item.category.name="{ item }">
                        {{ item.category?.name || '-' }}
                    </template>

                    <!-- Stock Quantity -->
                    <template #item.stock="{ item }">
                        <VChip 
                            :color="item.stock === 0 ? 'error' : item.stock <= 10 ? 'warning' : 'success'"
                            size="small"
                        >
                            {{ (item.stock) }} {{ item.unit?.name || '-' }}
                        </VChip>
                    </template>

                    <!-- Purchase Price Header with Tooltip -->
                    <template #header.last_purchase_price="{ column }">
                        <div class="d-flex align-center">
                            <span>{{ column.title }}</span>
                            <VTooltip location="top">
                                <template #activator="{ props }">
                                    <VIcon 
                                    v-bind="props" 
                                    icon="tabler-info-circle" 
                                    size="20" 
                                    color="primary" 
                                    class="ms-1"
                                    />
                                </template>
                                <span>{{ t('This is the last recorded purchase price of the product.') }}</span>
                            </VTooltip>
                        </div>
                    </template>

                    <!-- Purchase Price -->
                    <template #item.last_purchase_price="{ item }">
                        {{ formatAmount(item?.last_purchase_price) || formatAmount(0) }}
                    </template>

                    <!-- Total Price -->
                    <template #item.total_price="{ item }">
                        {{ formatAmount(item?.last_purchase_price * item.stock) || formatAmount(0) }}
                    </template>


                    <!-- Summary Row -->
                    <template #bottom>
                        <VTable>
                            <thead>
                                <tr>
                                    <th colspan="4">
                                        Summary
                                    </th>
                                    <th>
                                        Total Stock QTY
                                    </th>
                                    <th>
                                        Total Stock Value
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary-row">
                                    <td class="text-h6 font-weight-bold text-primary" colspan="4">
                                        <span class="d-flex align-center">
                                            <VIcon icon="tabler-calculator" class="me-2" />
                                            Total Summary
                                        </span>
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ calculateTotal('stock') }}
                                    </td>
                                    <td class="text-h6 font-weight-bold text-primary">
                                        {{ formatAmount(calculateTotal2('last_purchase_price')) }}
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </template>
                </VDataTable>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()

const props = defineProps({
    stocks: {
        type: Array,
        default: () => []
    },
    selectedSupplierName: {
        type: String,
        default: 'All Suppliers'
    },
    selectedItemName: {
        type: String,
        default: 'All Items'
    },
    selectedCategoryName: {
        type: String,
        default: 'All Categories'
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    exportHeaders: {
        type: Array,
        default: () => []
    }
})
const calculateTotal = (field) => {
    return props.stocks.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) || 0)
    }, 0)
}
// Calculate stoc value stock * last_purchase_price
const calculateTotal2 = (field) => {
    // check null or undefined
    if (field === null || field === undefined) {
        return 0
    }
    return props.stocks.reduce((sum, item) => {
        return sum + (parseFloat(item[field]) * item.stock || 0)
    }, 0)
}
</script>

<style lang="scss" scoped>
.stock-report-table {
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
</style>
