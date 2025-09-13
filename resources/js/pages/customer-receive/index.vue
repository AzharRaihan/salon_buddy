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
const selectedCustomerReceiveId = ref(null)
const customerReceiveData = ref(null)
const branch_info = useCookie("branch_info").value || 0;


// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Customer = computed(() => t('Customer'))
const Date = computed(() => t('Date'))
const Amount = computed(() => t('Amount'))
const Note = computed(() => t('Note'))
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
        title: Customer,
        key: 'customer_name',
        sortable: true,
    },
    {
        title: Date,
        key: 'date',
        sortable: true,
    },
    {
        title: Amount,
        key: 'amount',
        sortable: true,
    },
    {
        title: Note,
        key: 'note',
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

const fetchCustomerReceives = async () => {
    const response = await $api('/customer-receives', {
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
    customerReceiveData.value = response.data
}

// Initial fetch
await fetchCustomerReceives()

const customerReceives = computed(() => {
    const data = customerReceiveData.value?.customer_receives || []
    return data.map((customerReceive, index) => ({
        ...customerReceive,
        serial_number: getSerialNumber(index, totalCustomerReceives.value, page.value, itemsPerPage.value),
    }))
})
const totalCustomerReceives = computed(() => customerReceiveData.value?.total || 0)

const openConfirmDialog = (customerReceiveId) => {
    isConfirmDialogOpen.value = true
    selectedCustomerReceiveId.value = customerReceiveId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/customer-receives/${selectedCustomerReceiveId.value}`, {
            method: 'DELETE',
        })
        selectedCustomerReceiveId.value = null
        isConfirmDialogOpen.value = false
        await fetchCustomerReceives()
        toast(t('Customer receive deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedCustomerReceiveId.value = null
        console.error('Error deleting customer receive:', error)
        toast(t('Failed to delete customer receive'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchCustomerReceives() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchCustomerReceives()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Customer Receive')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Customer Receive')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'customer-receive-create' }">
                            {{ t('Add Customer Receive') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="customerReceives" 
                            :headers="exportHeaders" 
                            filename="customer-receive-report"
                            :title="$t('List Customer Receive')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="customerReceives" item-value="id"
                :headers="headers" :items-length="totalCustomerReceives" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Customer name with mobile number -->
                <template #item.customer_name="{ item }">
                    {{ item.customer_name }}
                    <span class="text-muted" v-if="item.customer_phone">
                        ({{ item.customer_phone }})
                    </span>
                </template>

                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ formatDate(item.date) }}
                </template>

                <!-- Amount -->
                <template #item.amount="{ item }">
                    {{ formatAmount(item.amount) }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ t('No customer receives found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'customer-receive-edit', query: { id: item.id } })"
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
            :confirmation-question="$t('Are you sure you want to delete this customer receive?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Customer receive has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Customer Receive Deletion Cancelled!')" @confirm="handleDelete" />
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
