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
const selectedPaymentMethodId = ref(null)
const paymentMethodData = ref(null)

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const AccountType = computed(() => t('Account Type'))
const OpeningBalance = computed(() => t('Opening Balance'))
const UseInWebsite = computed(() => t('Enable In Website'))
const Icon = computed(() => t('Icon'))
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
        sortable: false,
    },
    {
        title: AccountType,
        key: 'account_type',
        sortable: false,
    },
    {
        title: OpeningBalance,
        key: 'current_balance',
        sortable: false,
    },
    {
        title: UseInWebsite,
        key: 'use_in_website',
        sortable: false,
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

const fetchPaymentMethods = async () => {
    const response = await $api('/payment-methods', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    paymentMethodData.value = response.data
}

// Initial fetch
await fetchPaymentMethods()

const paymentMethods = computed(() => {
    const data = paymentMethodData.value?.paymentMethods || []
    return data.map((paymentMethod, index) => ({
        ...paymentMethod,
        serial_number: getSerialNumber(index, totalPaymentMethods.value, page.value, itemsPerPage.value),
    }))
})
const totalPaymentMethods = computed(() => paymentMethodData.value?.total || 0)


const openConfirmDialog = (paymentMethodId) => {
    isConfirmDialogOpen.value = true
    selectedPaymentMethodId.value = paymentMethodId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/payment-methods/${selectedPaymentMethodId.value}`, {
            method: 'DELETE',
        })
        selectedPaymentMethodId.value = null
        isConfirmDialogOpen.value = false
        await fetchPaymentMethods()
        toast(t('Payment account deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedPaymentMethodId.value = null // Fixed: Changed selectedExpenseId to selectedPaymentMethodId
        console.error('Error deleting payment account:', error) // Fixed: Changed error message
        toast(t('Failed to delete payment account'), { // Fixed: Changed error message
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchPaymentMethods() // Fixed: Changed fetchExpenses to fetchPaymentMethods
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchPaymentMethods() // Fixed: Changed fetchExpenses to fetchPaymentMethods
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})
</script>

<template>
    <div>
        <VCard :title="$t('List Payment Account')"> <!-- Fixed: Changed title -->
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Payment Account')" /> <!-- Fixed: Changed placeholder -->
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn 
                            prepend-icon="tabler-arrows-sort" 
                            variant="tonal"
                            color="secondary"
                            :to="{ name: 'payment-account-sorting-payment-account' }"
                        >
                            {{ $t('Sort') }}
                        </VBtn>
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'payment-account-create' }"> 
                            {{ $t('Add Payment Account') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="paymentMethods" 
                            :headers="exportHeaders" 
                            filename="payment-account-report"
                            :title="$t('List Payment Account')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="paymentMethods" item-value="id"
                :headers="headers" :items-length="totalPaymentMethods" class="text-no-wrap" @update:options="updateOptions">

                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Amount -->
                <template #item.current_balance="{ item }">
                    {{ formatAmount(item.current_balance) }}
                </template>

                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No payment accounts found') }} <!-- Fixed: Changed message -->
                    </div>
                </template>


                <!-- Status column -->
                <template #item.status="{ item }">
                    <VChip
                        :color="item.status == 'Enabled' ? 'success' : 'error'"
                        size="small"
                    >
                        {{ item.status }}
                    </VChip>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'payment-account-edit', query: { id: item.id } })"
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
            :confirmation-question="$t('Are you sure you want to delete this payment account?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Payment account has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Payment Account Deletion Cancelled!')" @confirm="handleDelete" /> <!-- Fixed: Changed dialog text -->
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
