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
const isConfirmDialogOpen = ref(false)
const selectedItemId = ref(null)
const itemData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Type = computed(() => t('Type'))
const Code = computed(() => t('Code'))
const SalePrice = computed(() => t('Sale Price'))
const PurchasePrice = computed(() => t('Purchase Price'))
const Item = computed(() => t('Item'))
const Status = computed(() => t('Status'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: Name,
        key: 'name',
        sortable: true,
    },
    {
        title: Type,
        key: 'type',
        sortable: true,
    },
    {
        title: Code,
        key: 'code',
        sortable: true,
    },
    {
        title: PurchasePrice,
        key: 'purchase_price',
        sortable: true,
    },
    {
        title: SalePrice,
        key: 'sale_price',
        sortable: true,
    },
    {
        title: Item,
        key: 'photo_url',
        sortable: true,
    },
    {
        title: Status,
        key: 'status',
        sortable: true,
    },
    {
        title: Action,
        key: 'action',
        sortable: false,
        align: 'center',
    },
]

const updateOptions = options => {
    sortBy.value = options.sortBy?.[0]?.key
    orderBy.value = options.sortBy?.[0]?.order
}

const fetchItems = async () => {
    const response = await $api('/items', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    itemData.value = response.data
}

// Initial fetch
await fetchItems()

const items = computed(() => {
    const data = itemData.value?.items || []
    return data.map((item, index) => ({
        ...item,
        serial_number: getSerialNumber(index, totalItems.value, page.value, itemsPerPage.value),
    }))
})
const totalItems = computed(() => itemData.value?.total || 0)

const openConfirmDialog = (itemId) => {
    isConfirmDialogOpen.value = true
    selectedItemId.value = itemId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;
    try {
        await $api(`/items/${selectedItemId.value}`, {
            method: 'DELETE',
        })
        selectedItemId.value = null
        isConfirmDialogOpen.value = false
        await fetchItems()
        toast(t('Item deleted successfully'), {
            type: 'success',
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedItemId.value = null
        console.error('Error deleting item:', error)
        toast(t('Failed to delete item'), {
            type: 'error',
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchItems() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchItems()
})

onMounted(async () => {
    await fetchCompanySettings()
})
const getTypeColor = (type) => {
  switch (type) {
    case 'Product':
      return 'primary'
    case 'Service':
      return 'info'
    case 'Package':
      return 'warning'
    default:
      return 'secondary'
  }
}
</script>

<template>
    <div>
        <VCard :title="t('List Item')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Item')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'item-create' }">
                            {{ t('Add Item') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />
                        <ExportTable 
                            :data="items" 
                            :headers="exportHeaders" 
                            filename="item-report"
                            :title="$t('List Item')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="items" item-value="id"
                :headers="headers" :items-length="totalItems" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Type badge -->
                <template #item.type="{ item }">
                    <VChip
                        :color="getTypeColor(item.type)"
                        variant="tonal"
                        size="small"
                    >
                        {{ item.type }}
                    </VChip>
                </template>

                <!-- Amount -->
                <template #item.purchase_price="{ item }">
                    {{ item.type == 'Package' ? 'N/A' : formatAmount(item.purchase_price) }}
                </template>
                <template #item.sale_price="{ item }">
                    {{ formatAmount(item.sale_price) }}
                </template>

                <!-- Photo column -->
                <template #item.photo_url="{ item }">
                    <VImg :src="item.photo_url" width="50" height="50" cover class="rounded" />
                </template>

                <!-- Status badge -->
                <template #item.status="{ item }">
                    <VChip
                        :color="item.status == 'Enable' ? 'success' : 'error'"
                        size="small"
                    >
                        {{ item.status }}
                    </VChip>
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No items found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'item-edit', query: { id: item.id } })"
                            >
                            <VIcon size="22" icon="tabler-edit" />
                        </VBtn>

                        <VBtn icon variant="text" color="error" size="small" @click="openConfirmDialog(item.id)">
                            <VIcon size="22" icon="tabler-trash" />
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
        </VCard>

        <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="t('Are you sure you want to delete this item?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Item has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Item Deletion Cancelled!')" @confirm="handleDelete" />
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

