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
const selectedBookingId = ref(null)
const bookingData = ref(null)


// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Date = computed(() => t('Date'))
const ReferenceNo = computed(() => t('Reference No'))
const Customer = computed(() => t('Customer'))
const Branch = computed(() => t('Branch'))
const Action = computed(() => t('Action'))
const Status = computed(() => t('Status'))

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
        title: Branch,
        key: 'branch_name',
        sortable: false,
    },
    {
        title: Date,
        key: 'date',
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
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const fetchBookings = async () => {
    const response = await $api('/bookings', {
        method: 'GET',
        query: {
            q: searchQuery.value,
            itemsPerPage: itemsPerPage.value,
            page: page.value,
            sortBy: sortBy.value,
            orderBy: orderBy.value,
        },
    })
    bookingData.value = response.data
}

// Initial fetch
await fetchBookings()

const bookings = computed(() => {
    const data = bookingData.value?.bookings || []
    return data.map((booking, index) => ({
        ...booking,
        serial_number: getSerialNumber(index, totalBookings.value, page.value, itemsPerPage.value),
        formatted_date: formatDate(booking.date)
    }))
})
const totalBookings = computed(() => bookingData.value?.total || 0)

const openConfirmDialog = (bookingId) => {
    isConfirmDialogOpen.value = true
    selectedBookingId.value = bookingId
}

const handleDelete = async (confirmed) => {
    if (!confirmed) return;

    try {
        await $api(`/bookings/${selectedBookingId.value}`, {
            method: 'DELETE',
        })
        selectedBookingId.value = null
        isConfirmDialogOpen.value = false
        await fetchBookings()
        toast(t('Booking deleted successfully'), {
            "type": "success",
        });
    } catch (error) {
        isConfirmDialogOpen.value = false
        selectedBookingId.value = null
        console.error('Error deleting booking:', error)
        toast(t('Failed to delete booking'), {
            "type": "error",
        });
    }
}

// Watch for changes in search query
watch(searchQuery, () => {
    page.value = 1 // Reset to first page when searching
    fetchBookings() 
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchBookings()
})

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings()
})

</script>

<template>
    <div>
        <VCard :title="$t('List Booking')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="$t('Search Booking')" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <VBtn prepend-icon="tabler-plus" :to="{ name: 'booking-create' }">
                            {{ $t('Add Booking') }}
                        </VBtn>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="bookings" 
                            :headers="exportHeaders" 
                            filename="booking-report"
                            :title="$t('Booking List')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="bookings" item-value="id"
                :headers="headers" :items-length="totalBookings" class="text-no-wrap" @update:options="updateOptions">
                <!-- Serial Number -->
                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>


                <!-- Customer name with phone number -->
                <template #item.customer_name="{ item }">
                    {{ item.customer_name }}
                    <span class="text-muted" v-if="item.customer_phone">
                        ({{ item.customer_phone }})
                    </span>
                </template>
                
                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ item.formatted_date }}
                </template>
                
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-alert-circle" class="me-2" />
                        {{ $t('No bookings found') }}
                    </div>
                </template>

                <!-- Status column -->
                <template #item.status="{ item }">
                    <VChip
                        :color="item.status == 'Pending' ? 'warning' : item.status == 'Rejected' ? 'error' : item.status == 'Completed' ? 'success' : 'info'"
                        size="small"
                    >
                        {{ item.status }}
                    </VChip>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="info" size="small"
                            @click="$router.push({ name: 'booking-edit', query: { id: item.id } })">
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
            :confirmation-question="$t('Are you sure you want to delete this booking?')" :confirm-title="$t('Deleted!')"
            :confirm-msg="$t('Booking has been deleted successfully.')" :cancel-title="$t('Cancelled')"
            :cancel-msg="$t('Booking Deletion Cancelled!')" @confirm="handleDelete" />
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
