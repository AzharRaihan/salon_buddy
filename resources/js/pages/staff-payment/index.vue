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
const selectedStaffPaymentId = ref(null)
const staffPaymentData = ref(null)
const branch_info = useCookie("branch_info").value || 0;


// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const ReferenceNo = computed(() => t('Reference No'))
const Date = computed(() => t('Date'))
const Amount = computed(() => t('Amount'))
const Employee = computed(() => t('Employee'))
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
        title: Employee,
        key: 'employee_name',
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

const fetchStaffPayments = async () => {
    const response = await $api('/staff-payments', {
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
    staffPaymentData.value = response.data
}

// Initial fetch
await fetchStaffPayments()

const staffPayments = computed(() => {
    const data = staffPaymentData.value?.staff_payments || []
    return data.map((staffPayment, index) => ({
        ...staffPayment,
        serial_number: getSerialNumber(index, totalStaffPayments.value, page.value, itemsPerPage.value),
    }))
})
const totalStaffPayments = computed(() => staffPaymentData.value?.total || 0)

const openConfirmDialog = (staffPaymentId) => {
    isConfirmDialogOpen.value = true
    selectedStaffPaymentId.value = staffPaymentId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/staff-payments/${selectedStaffPaymentId.value}`, {
            method: 'DELETE',
        })
        selectedStaffPaymentId.value = null
        isConfirmDialogOpen.value = false
        await fetchStaffPayments()
        toast(t('Staff payment deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedStaffPaymentId.value = null
        console.error('Error deleting staff payment:', error)
        toast(t('Failed to delete staff payment'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchStaffPayments() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchStaffPayments()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="t('List Staff Payment')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search Staff Payment')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'staff-payment-create' }">
                            {{ t('Add Staff Payment') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="staffPayments" 
                            :headers="exportHeaders" 
                            filename="staff-payment-report"
                            :title="$t('List Staff Payment')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="staffPayments" item-value="id"
                :headers="headers" :items-length="totalStaffPayments" class="text-no-wrap" @update:options="updateOptions">

                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <!-- Employee name with mobile number -->
                <template #item.employee_name="{ item }">
                    {{ item.employee_name }}
                    <span class="text-muted" v-if="item.employee_phone">
                        ({{ item.employee_phone }})
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
                        {{ t('No staff payments found') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'staff-payment-edit', query: { id: item.id } })">
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
            :confirmation-question="$t('Are you sure you want to delete this staff payment?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Staff payment has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Staff payment Deletion Cancelled!')" @confirm="handleDelete" />
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
