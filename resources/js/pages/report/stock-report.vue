<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStockReport } from '@/composables/useStockReport'
import StockReportFilters from '@/components/report/StockReportFilters.vue'
import StockReportTable from '@/components/report/StockReportTable.vue'
import ExportTableStockReport from '@/components/ExportTableStockReport.vue'

const { t } = useI18n()

// Use the stock report composable
const {
    // State
    isLoading,
    supplierId,
    itemId,
    categoryId,
    suppliers,
    items,
    categories,
    
    // Methods
    resetFilters,
    
    // Computed
    stocks,
    totalItems,
    summary,

} = useStockReport()

// Ref to store report header data from table component
const reportHeaderData = ref({
    reportTitle: t('Stock Report'),
    itemName: null,
    categoryName: null,
    generatedOn: '',
    generatedBy: 'N/A',
    supplierName: 'All Suppliers'
})

// Handle header data updates from table component
const handleHeaderDataUpdate = (headerData) => {
    reportHeaderData.value = headerData
}

// Computed properties
const selectedSupplierName = computed(() => {
    if (!supplierId.value) return 'All Suppliers'
    const supplier = suppliers.value.find(s => s.id == supplierId.value)
    return supplier?.name || 'All Suppliers'
})

const selectedItemName = computed(() => {
    if (!itemId.value) return 'All Items'
    const item = items.value.find(i => i.id == itemId.value)
    return item?.name || 'All Items'
})

const selectedCategoryName = computed(() => {
    if (!categoryId.value) return 'All Categories'
    const category = categories.value.find(c => c.id == categoryId.value)
    return category?.name || 'All Categories'
})

// Export headers for ExportTable component
const exportHeaders = computed(() => [
    { title: t('SN'), key: 'serial_number' },
    { title: t('Item Name & Code'), key: 'name' },
    { title: t('Category'), key: 'category.name' },
    { title: t('Total Stock QTY'), key: 'stock' },
    { title: t('Unit'), key: 'unit.name' },
    { title: t('Purchase Price'), key: 'last_purchase_price' },
    { title: t('Total Price'), key: 'total_price' },
])

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

            <ExportTableStockReport
                :data="stocks" 
                :headers="exportHeaders" 
                filename="stock-report"
                :title="t('Stock Report')"
                :header-data="reportHeaderData"
                :summary-data="summary"
            />
        </div>


        <!-- Filter Section -->
        <VCard class="mb-4" v-if="isFilterOptionsOpen">
            <VCardText>
                <StockReportFilters
                    v-model:supplier-id="supplierId"
                    v-model:item-id="itemId"
                    v-model:category-id="categoryId"
                    :suppliers="suppliers"
                    :items="items"
                    :categories="categories"
                />
            </VCardText>
        </VCard>

        

        <!-- Stock Report Table -->
        <StockReportTable
            :stocks="stocks"
            :selected-supplier-name="selectedSupplierName"
            :selected-item-name="selectedItemName"
            :selected-category-name="selectedCategoryName"
            :item-id="itemId"
            :category-id="categoryId"
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
