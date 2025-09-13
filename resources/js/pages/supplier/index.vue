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
const selectedSupplierId = ref(null)
const supplierData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Phone = computed(() => t('Phone'))
const Address = computed(() => t('Address'))
const Email = computed(() => t('Email'))
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
        title: Phone,
        key: 'phone',
        sortable: true,
    },
    {
        title: Email,
        key: 'email',
        sortable: true,
    },
    {
        title: Address,
        key: 'address',
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

const fetchSuppliers = async () => {
    const response = await $api('/suppliers', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    supplierData.value = response.data
}

// Initial fetch
await fetchSuppliers()

const suppliers = computed(() => {
    const suppliers = supplierData.value?.suppliers || [];
    return suppliers.map((supplier, index) => ({
        ...supplier,
        serial_number: getSerialNumber(index, totalSuppliers.value, page.value, itemsPerPage.value),
    }));
})
const totalSuppliers = computed(() => supplierData.value?.total || 0)

const openConfirmDialog = (supplierId) => {
    isConfirmDialogOpen.value = true
    selectedSupplierId.value = supplierId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/suppliers/${selectedSupplierId.value}`, {
            method: 'DELETE',
        })
        selectedSupplierId.value = null
        isConfirmDialogOpen.value = false
        await fetchSuppliers()
        toast('Supplier deleted successfully', {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedSupplierId.value = null
        console.error('Error deleting supplier:', error)
        toast('Failed to delete supplier', {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchSuppliers() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchSuppliers()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Supplier')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Supplier')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'supplier-create' }">
                            {{ t('Add Supplier') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="suppliers" 
                            :headers="exportHeaders" 
                            filename="supplier-report"
                            :title="$t('List Supplier')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="suppliers" item-value="id"
                :headers="headers" :items-length="totalSuppliers" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>
                

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No suppliers found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'supplier-edit', query: { id: item.id } })"
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
            :confirmation-question="t('Are you sure you want to delete this supplier?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Supplier has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Supplier Deletion Cancelled!')" @confirm="handleDelete" />
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
