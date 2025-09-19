<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import ExportTable from '@/components/ExportTable.vue';
// Computed headers for export (converts computed refs to strings)
const exportHeaders = computed(() => {
    return headers.map(header => ({
        ...header,
        title: typeof header.title == 'object' && header.title.value !== undefined 
            ? header.title.value 
            : header.title
    }))
})
const { t } = useI18n()
const router = useRouter()
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const stockData = ref(null)
const suppliers = ref([])
const categories = ref([])
const itemsList = ref([])
const itemId = ref(null)
const supplierId = ref(null)
const categoryId = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, formatNumber, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ItemNameCode = computed(() => t('Item Name & Code'))
const Category = computed(() => t('Category'))
const TotalStockQTY = computed(() => t('Total Stock QTY'))
const PurchasePrice = computed(() => t('Purchase Price'))
const TotalPrice = computed(() => t('Total Price'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: ItemNameCode,
        key: 'name',
        sortable: true,
    },
    {
        title: Category,
        key: 'category',
        sortable: true,
    },
    {
        title: TotalStockQTY,
        key: 'stock',
        sortable: true,
    },
    {
        title: PurchasePrice,
        key: 'last_three_purchase_avg',
        sortable: true,
    },
    {
        title: TotalPrice,
        key: 'total_price',
        sortable: false,
    },
]
const resetFilters = () => {
    itemId.value = null
    supplierId.value = null
    categoryId.value = null
    searchQuery.value = ''
    page.value = 1
    fetchStock()
}
const filterStock = ref(false)

const updateOptions = options => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchStock = async () => {
    const response = await $api('/alert-stock', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
            item_id: itemId.value,
            supplier_id: supplierId.value,
            category_id: categoryId.value,
        },
    })
    stockData.value = response.data
    suppliers.value = response.data.suppliers
    categories.value = response.data.categories
    itemsList.value = response.data.itemsList
}
// Initial fetch
await fetchStock()

const items = computed(() => {
    const data = stockData.value?.items || []
    return data.map((item, index) => ({
        ...item,
        serial_number: getSerialNumber(index, totalItems.value, page.value, itemsPerPage.value),
    }))
})
const totalItems = computed(() => stockData.value?.total || 0)

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchStock() 
})
// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchStock()
})
watch([itemId, supplierId, categoryId], () => {
    page.value = 1 // Reset to first page
    fetchStock()
})
onMounted(async () => {
    await fetchCompanySettings()
})


</script>

<template>
    <div>
        <VCard :title="t('List Alert Stock')">
            <!-- Filter Button -->
            <VCardText class="d-flex justify-end">
                <VBtn color="primary" variant="tonal" @click="filterStock = !filterStock">
                    <VIcon icon="tabler-filter" class="me-2" />
                    {{ filterStock ? t('Hide Filters') : t('Filter') }}
                </VBtn>
            </VCardText>
            <!-- Filter Option -->
            <VCardText v-if="filterStock" class="mb-5">
                <VRow>
                    <VCol cols="12" sm="6" md="4" lg="3">
                        <AppAutocomplete
                            v-model="itemId"
                            :items="itemsList"
                            item-title="name"
                            item-value="id"
                            :label="t('Item')" :required="true"
                            :placeholder="t('Select Item')"
                            clearable
                            />
                    </VCol>
                    <VCol cols="12" sm="6" md="4" lg="3">
                        <AppAutocomplete
                            v-model="supplierId"
                            :items="suppliers"
                            :item-title="item => item.phone ? item.name + ' (' + item.phone + ')' : item.name"
                            item-value="id"
                            :label="t('Supplier')" :required="true"
                            :placeholder="t('Select Supplier')"
                            clearable
                            />
                    </VCol>
                    <VCol cols="12" sm="6" md="4" lg="3">
                        <AppAutocomplete
                            v-model="categoryId"
                            :items="categories"
                            item-title="name"
                            item-value="id"
                            :label="t('Category')" :required="true"
                            :placeholder="t('Select Category')"
                            clearable
                            />
                    </VCol>
                    <VCol cols="12" sm="6" md="4" lg="3" class="mt-6">
                        <VBtn color="primary" variant="tonal" @click="resetFilters">
                            <VIcon icon="tabler-refresh" class="me-2" />
                            {{ t('Reset') }}
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>

            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Product')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="items" 
                            :headers="exportHeaders" 
                            filename="alert-stock-report"
                            :title="$t('List Alert Stock')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="items" item-value="id"
                :headers="headers" :items-length="totalItems" class="text-no-wrap" @update:options="updateOptions">

                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <template #item.name="{ item }">
                    {{ item?.name }}
                    <span class="text-body-2 text-medium-emphasis">({{ item?.code || '-' }})</span>
                </template>

                <template #item.category="{ item }">
                    {{ item.category?.name || '-' }}
                </template>

                <template #item.stock="{ item }">
                    <VChip 
                        :color="item.stock == 0 ? 'error' : item.stock <= 10 ? 'warning' : 'success'"
                        size="small"
                    >
                        {{ (item.stock) }} {{ item.unit?.name || '-' }}
                    </VChip>
                </template>

                <template #header.last_three_purchase_avg="{ column }">
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
                        <span>{{ t('This is the last three purchase average price of the product.') }}</span>
                        </VTooltip>
                    </div>
                </template>

                <template #item.total_price="{ item }">
                    {{ formatAmount(item?.last_three_purchase_avg * item.stock) || formatAmount(0) }}
                </template>
                
                
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No products found') }}   
                    </div>
                </template>
            </VDataTableServer>
        </VCard>
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
.main_card_wrap {
    display: flex;
    flex-grow: 1;
    gap: 16px;
    margin-bottom: 16px;
}
</style>
