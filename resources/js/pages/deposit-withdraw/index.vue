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
const selectedDepositWithdrawId = ref(null)
const depositWithdrawData = ref(null)
const branch_info = useCookie("branch_info").value || 0;

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Date = computed(() => t('Date'))
const Amount = computed(() => t('Amount'))
const Note = computed(() => t('Note'))
const Action = computed(() => t('Action'))
const PaymentMethod = computed(() => t('Payment Account'))

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
        title: Amount,
        key: 'amount',
        sortable: true,
    },
    {
        title: PaymentMethod,
        key: 'payment_method_name',
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

const fetchDepositWithdraws = async () => {
    const response = await $api('/deposit-withdraws', {
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
    depositWithdrawData.value = response.data
}

// Initial fetch
await fetchDepositWithdraws()

const depositWithdraws = computed(() => {
    const data = depositWithdrawData.value?.depositWithdraws || []
    return data.map((depositWithdraw, index) => ({
        ...depositWithdraw,
        serial_number: getSerialNumber(index, totalDepositWithdraws.value, page.value, itemsPerPage.value),
    }))
})
const totalDepositWithdraws = computed(() => depositWithdrawData.value?.total || 0)

const openConfirmDialog = (depositWithdrawId) => {
    isConfirmDialogOpen.value = true
    selectedDepositWithdrawId.value = depositWithdrawId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/deposit-withdraws/${selectedDepositWithdrawId.value}`, {
            method: 'DELETE',
        })
        selectedDepositWithdrawId.value = null
        isConfirmDialogOpen.value = false
        await fetchDepositWithdraws()
        toast(t('Deposit Withdraw deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedDepositWithdrawId.value = null
        console.error('Error deleting deposit withdraw:', error)
        toast(t('Failed to delete deposit withdraw'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchDepositWithdraws() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchDepositWithdraws()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Deposit Withdraw')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Deposit Withdraw')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'deposit-withdraw-create' }">
                            {{ t('Add Deposit Withdraw') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="depositWithdraws" 
                            :headers="exportHeaders" 
                            filename="deposit-withdraw-report"
                            :title="$t('List Deposit Withdraw')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="depositWithdraws" item-value="id"
                :headers="headers" :items-length="totalDepositWithdraws" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Payment Account -->
                <template #item.payment_method_name="{ item }">
                    {{ item.payment_method_name }}
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
                        {{ t('No deposit withdraws found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'deposit-withdraw-edit', query: { id: item.id } })">
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
            :confirmation-question="$t('Are you sure you want to delete this deposit withdraw?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Deposit Withdraw has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Deposit Withdraw Deletion Cancelled!')" @confirm="handleDelete" />
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
