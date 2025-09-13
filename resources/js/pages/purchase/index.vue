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
const { t } = useI18n();
const router = useRouter()
const searchQuery = ref('')
const branch_info = useCookie("branch_info").value || 0;

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const isConfirmDialogOpen = ref(false)
const selectedPurchaseId = ref(null)
const purchaseData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Date = computed(() => t('Date'))
const SupplierName = computed(() => t('Supplier Name'))
const GrandTotal = computed(() => t('Grand Total'))
const Action = computed(() => t('Action'))


// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: ReferenceNo,
        key: 'reference_no',
        sortable: true,
    },
    {
        title: Date,
        key: 'date',
        sortable: true,
    },
    {
        title: SupplierName,
        key: 'supplier_name',
        sortable: true,
    },
    {
        title: GrandTotal,
        key: 'grand_total',
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
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchPurchases = async () => {
    const response = await $api('/purchases', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            branch_id: branch_info.id,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    purchaseData.value = response.data
}

// Initial fetch
await fetchPurchases()


const purchases = computed(() => {
    const purchases = purchaseData.value?.purchases || [];
    return purchases.map((purchase, index) => ({
        ...purchase,
        date: formatDate(purchase.date),
        serial_number: getSerialNumber(index, totalPurchases.value, page.value, itemsPerPage.value),
    }));
})
const totalPurchases = computed(() => purchaseData.value?.total || 0)

const openConfirmDialog = (purchaseId) => {
    isConfirmDialogOpen.value = true
    selectedPurchaseId.value = purchaseId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/purchases/${selectedPurchaseId.value}`, {
            method: 'DELETE',
        })
        selectedPurchaseId.value = null
        isConfirmDialogOpen.value = false
        await fetchPurchases()
        toast('Purchase deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedPurchaseId.value = null
        console.error('Error deleting purchase:', error)
        toast('Failed to delete purchase', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchPurchases() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchPurchases()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Purchase')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Purchase')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'purchase-create' }">
                            {{ t('Add Purchase') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="purchases" 
                            :headers="exportHeaders" 
                            filename="purchase-report"
                            :title="$t('List Purchase')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="purchases" item-value="id"
                :headers="headers" :items-length="totalPurchases" class="text-no-wrap" @update:options="updateOptions">
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ formatDate(item.date) }}
                </template>

                <!-- Supplier name with mobile number -->
                <template #item.supplier_name="{ item }">
                    {{ item.supplier_name }}
                    <span class="text-muted" v-if="item.supplier_phone">
                        ({{ item.supplier_phone }})
                    </span>
                </template>

                <!-- Amount -->
                <template #item.grand_total="{ item }">
                    {{ formatAmount(item.grand_total) }}
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No purchases found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'purchase-edit', query: { id: item.id } })"
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
            :confirmation-question="t('Are you sure you want to delete this purchase?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Purchase has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Purchase Deletion Cancelled!')" @confirm="handleDelete" />
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
