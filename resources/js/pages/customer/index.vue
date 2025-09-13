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
const selectedCustomerId = ref(null)
const customerData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Phone = computed(() => t('Phone'))
const Address = computed(() => t('Address'))
const Action = computed(() => t('Action'))
const Photo = computed(() => t('Photo'))


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
        title: Photo,
        key: 'photo',
        sortable: true,
    },
    {
        title: Phone,
        key: 'phone',
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

const fetchCustomers = async () => {
    const response = await $api('/customers', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    customerData.value = response.data
}

// Initial fetch
await fetchCustomers()

const customers = computed(() => {
    const data = customerData.value?.customers || []
    return data.map((customer, index) => ({
        ...customer,
        serial_number: getSerialNumber(index, totalCustomers.value, page.value, itemsPerPage.value),
    }))
})
const totalCustomers = computed(() => customerData.value?.total || 0)

const openConfirmDialog = (customerId) => {
    isConfirmDialogOpen.value = true
    selectedCustomerId.value = customerId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/customers/${selectedCustomerId.value}`, {
            method: 'DELETE',
        })
        selectedCustomerId.value = null
        isConfirmDialogOpen.value = false
        await fetchCustomers()
        toast(t('Customer deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedCustomerId.value = null
        toast(t('Failed to delete customer'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchCustomers() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchCustomers()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="$t('List Customer')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Customer')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'customer-create' }">
                            {{ $t('Add Customer') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="customers" 
                            :headers="exportHeaders" 
                            filename="customer-report"
                            :title="$t('List Customer')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="customers" item-value="id"
                :headers="headers" :items-length="totalCustomers" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Photo -->
                <template #item.photo="{ item }">
                    <img :src="item.photo_url" alt="Photo" class="img-fluid" style="width: 50px; height: 50px;">
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No customers found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1" v-if="item.name !== 'Walk-in Customer'">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'customer-edit', query: { id: item.id } })">
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
            :confirmation-question="$t('Are you sure you want to delete this customer?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Customer has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Customer Deletion Cancelled!')" @confirm="handleDelete" />
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
