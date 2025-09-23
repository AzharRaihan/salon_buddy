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

// Offcanvas state for status editing
const showOffcanvas = ref(false)
const isEditingStatus = ref(false)
const editingBookingId = ref(null)
const editingBooking = ref(null)

// Form data for status editing
const statusForm = ref({
    status: '',
    send_sms: false,
    send_email: false,
    send_whatsapp: false
})

// Form errors
const statusFormErrors = ref({
    status: ''
})


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

// Offcanvas functions for status editing
const openStatusOffcanvas = (booking) => {
    // Prevent editing if status is Completed
    if (booking.status === 'Completed') {
        toast(t('Cannot edit completed bookings'), { type: 'error' })
        return
    }
    
    isEditingStatus.value = true
    editingBookingId.value = booking.id
    editingBooking.value = booking
    statusForm.value = {
        status: booking.status,
        send_sms: false,
        send_email: false,
        send_whatsapp: false
    }
    resetStatusFormErrors()
    showOffcanvas.value = true
}

const closeStatusOffcanvas = () => {
    showOffcanvas.value = false
    isEditingStatus.value = false
    editingBookingId.value = null
    editingBooking.value = null
    resetStatusForm()
}

const resetStatusForm = () => {
    statusForm.value = {
        status: '',
        send_sms: false,
        send_email: false,
        send_whatsapp: false
    }
    resetStatusFormErrors()
}

const resetStatusFormErrors = () => {
    statusFormErrors.value = {
        status: ''
    }
}

const validateStatusForm = () => {
    let isValid = true
    resetStatusFormErrors()

    if (!statusForm.value.status) {
        statusFormErrors.value.status = t('Status is required')
        isValid = false
    }

    return isValid
}

const updateBookingStatus = async () => {
    if (!validateStatusForm()) {
        toast(t('Please fix the errors in the form'), { type: 'error' })
        return
    }

    try {
        const response = await $api(`/bookings/${editingBookingId.value}/status`, {
            method: 'PUT',
            body: JSON.stringify({
                status: statusForm.value.status,
                send_sms: statusForm.value.send_sms,
                send_email: statusForm.value.send_email,
                send_whatsapp: statusForm.value.send_whatsapp
            }),
            headers: { 'Content-Type': 'application/json' },
        })

        if (response.success) {
            toast(response.message || t('Booking status updated successfully'), {
                type: 'success'
            })
            resetStatusForm()
            closeStatusOffcanvas()
            await fetchBookings()
        } else {
            toast(response.message || t('Operation failed'), { type: 'error' })
        }
    } catch (error) {
        console.error('Error updating booking status:', error)
        if (error.errors) {
            // Handle validation errors
            Object.keys(error.errors).forEach(key => {
                if (statusFormErrors.value.hasOwnProperty(key)) {
                    statusFormErrors.value[key] = error.errors[key][0]
                }
            })
        } else {
            toast(t('An error occurred while updating status'), { type: 'error' })
        }
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

                        <VBtn 
                            icon 
                            variant="text" 
                            color="warning" 
                            size="small"
                            @click="openStatusOffcanvas(item)"
                            :disabled="item.status === 'Completed'"
                        >
                            <VIcon size="22" icon="tabler-settings" />
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

        <!-- Offcanvas for Status Editing -->
        <VNavigationDrawer
            v-model="showOffcanvas"
            location="end"
            temporary
            width="500"
        >
            <VCard flat>
                <VCardTitle class="d-flex justify-space-between align-center pa-4">
                    <span>{{ t('Update Booking Status') }}</span>
                    <VBtn icon variant="text" @click="closeStatusOffcanvas">
                        <VIcon icon="tabler-x" />
                    </VBtn>
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-4">
                    <!-- Booking Info Display -->
                    <div v-if="editingBooking" class="mb-4 pa-3 bg-grey-lighten-5 rounded">
                        <div class="text-subtitle-2 mb-2">{{ t('Booking Details') }}</div>
                        <div class="text-body-2">
                            <div><strong>{{ t('Reference') }}:</strong> {{ editingBooking.reference_no }}</div>
                            <div><strong>{{ t('Customer') }}:</strong> {{ editingBooking.customer_name }}</div>
                            <div><strong>{{ t('Date') }}:</strong> {{ editingBooking.formatted_date }}</div>
                            <div><strong>{{ t('Current Status') }}:</strong> 
                                <VChip 
                                    :color="editingBooking.status == 'Pending' ? 'warning' : editingBooking.status == 'Rejected' ? 'error' : editingBooking.status == 'Completed' ? 'success' : 'info'"
                                    size="small"
                                    class="ml-2"
                                >
                                    {{ editingBooking.status }}
                                </VChip>
                            </div>
                        </div>
                    </div>

                    <VForm @submit.prevent="updateBookingStatus">
                        <VRow>
                            <!-- Status Selection -->
                            <VCol cols="12">
                                <AppAutocomplete
                                    v-model="statusForm.status"
                                    :items="['Pending', 'Accepted', 'Rejected']"
                                    :label="t('New Status')"
                                    :placeholder="t('Select Status')"
                                    :error-messages="statusFormErrors.status"
                                    clearable
                                    required
                                />
                            </VCol>

                            <!-- Notification Options -->
                            <VCol cols="12">
                                <div class="text-subtitle-2 mb-3">{{ t('Send Notifications') }}</div>
                                <VCheckbox
                                    v-model="statusForm.send_sms"
                                    :label="t('Send SMS')"
                                    class="mb-2"
                                />
                                <VCheckbox
                                    v-model="statusForm.send_email"
                                    :label="t('Send Email')"
                                    class="mb-2"
                                />
                                <VCheckbox
                                    v-model="statusForm.send_whatsapp"
                                    :label="t('Send WhatsApp Message')"
                                />
                            </VCol>
                        </VRow>

                        <!-- Form Actions -->
                        <VRow class="mt-4">
                            <VCol cols="12" class="d-flex gap-3">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="false"
                                    block
                                >
                                    <VIcon start icon="tabler-check" />
                                    {{ t('Update Status') }}
                                </VBtn>
                                <VBtn
                                    type="button"
                                    color="secondary"
                                    variant="outlined"
                                    @click="closeStatusOffcanvas"
                                    block
                                >
                                    <VIcon start icon="tabler-x" />
                                    {{ t('Cancel') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VNavigationDrawer>
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
