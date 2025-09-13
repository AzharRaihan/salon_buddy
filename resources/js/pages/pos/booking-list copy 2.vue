<template>
    <div class="container">
        <VCard class="booking-list-card" :title="t('Booking List')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search by name, phone, or date...')" @input="handleSearch" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <div class="booking-list-header">
                            <button class="btn btn-primary" @click="router.push('/pos')">
                                <VIcon icon="tabler-arrow-left" />
                                {{ t('Back to POS') }}
                            </button>
                        </div>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="bookingData" 
                            :headers="exportHeaders" 
                            filename="booking-report"
                            :title="$t('Booking List')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="bookingData" item-value="id"
                :headers="headers" :items-length="totalItems" class="text-no-wrap" @update:options="updateOptions">

                <template #item.serial_number="{ item }">
                    {{ item.serial_number }}
                </template>

                <template #item.customer_name="{ item }">
                    {{ item.customer_name }}
                </template>

                <template #item.customer_phone="{ item }">
                    {{ item.customer_phone }}
                </template>

                <!-- Formatted Date -->
                <template #item.date="{ item }">
                    {{ formatDate(item.date) }}
                </template>

                <!-- Status with chip -->
                <template #item.status="{ item }">
                    <VChip
                        :color="getStatusClass(item.status)"
                        variant="tonal"
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
                        <VIcon icon="tabler-calendar-off" class="me-2" />
                        {{ searchQuery ? t('No bookings found matching your search') : t('No bookings available') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="primary" size="small" 
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" 
                            aria-controls="offcanvasRight" @click.stop="selectBooking(item)">
                            <VIcon size="22" icon="tabler-eye" />
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
        </VCard>

        <!-- Right Panel - Booking Details -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" >
            <div class="offcanvas-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="offcanvas-title" id="offcanvasRightLabel">{{ t('Booking Details') }}</h5>
                <button type="button" class="btn btn-sm btn-primary" variant="tonal" data-bs-dismiss="offcanvas" aria-label="Close">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="offcanvas-body" :class="{'d-flex justify-content-center align-items-center': loadingBookingDetails}">
                <div v-if="loadingBookingDetails" class="text-center py-4">
                    <VProgressCircular indeterminate color="primary" />
                    <p class="mt-2">{{ t('Loading booking details...') }}</p>
                </div>
                <div v-else>
                    <div class="booking-details-section" v-if="selectedBookingDetails">
                        <div class="booking-summary-wrap">
                            <!-- Customer Info -->
                            <div class="customer-info">
                                <h3>{{ t('Customer Info') }}</h3>
                                <div class="info-item" v-if="selectedBookingDetails.customer.name">
                                    <span class="label">{{ t('Name') }}:</span>
                                    <span class="value">{{ selectedBookingDetails.customer.name }}</span>
                                </div>
                                <div class="info-item" v-if="selectedBookingDetails.customer.phone">
                                    <span class="label">{{ t('Phone') }}:</span>
                                    <span class="value">{{ selectedBookingDetails.customer.phone }}</span>
                                </div>
                                <div class="info-item" v-if="selectedBookingDetails.customer.email">
                                    <span class="label">{{ t('Email') }}:</span>
                                    <span class="value">{{ selectedBookingDetails.customer.email }}</span>
                                </div>
                                <div class="info-item" v-if="selectedBookingDetails.customer.address">
                                    <span class="label">{{ t('Address') }}:</span>
                                    <span class="value">{{ selectedBookingDetails.customer.address }}</span>
                                </div>
                            </div>

                            <!-- Booking Summary -->
                            <div class="booking-summary">
                                <h3>{{ t('Booking Summary') }}</h3>
                                <div class="info-item" v-if="selectedBookingDetails.reference_no">
                                    <span class="label">{{ t('Booking Code') }}:</span>
                                    <span class="value">{{ selectedBookingDetails.reference_no }}</span>
                                </div>
                                <div class="info-item" v-if="selectedBookingDetails.date">
                                    <span class="label">{{ t('Booking Date') }}:</span>
                                    <span class="value">{{ formatDate(selectedBookingDetails.date) }}</span>
                                </div>
                                <div class="info-item" v-if="selectedBookingDetails.status">
                                    <span class="label">{{ t('Status') }}:</span>
                                    <VChip
                                        :color="getStatusClass(selectedBookingDetails.status)"
                                        variant="tonal"
                                        size="small"
                                    >
                                        {{ selectedBookingDetails.status }}
                                    </VChip>
                                </div>
                            </div>
                        </div>
                        

                        <!-- Services Table -->
                        <h3>{{ t('Services') }}</h3>
                        <div class="services-table table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="w-10 text-center">{{ t('SN') }}</th>
                                        <th class="w-35 text-left">{{ t('Service') }}</th>
                                        <th class="w-20 text-center">{{ t('Price') }}</th>
                                        <th class="w-10 text-center">{{ t('Qty') }}</th>
                                        <th class="w-25 text-right">{{ t('Subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(service, index) in selectedBookingDetails.services" :key="service.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-left">{{ service.name }}</td>
                                        <td class="text-center">{{ service.price }}</td>
                                        <td class="text-center">{{ service.quantity }}</td>
                                        <td class="text-right">{{ service.subtotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="totals-section">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><span class="label">{{ t('Subtotal') }}:</span></td>
                                        <td class="text-right"><span class="value">{{ subtotal.toFixed(2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="label">{{ t('Tax') }}:</span></td>
                                        <td class="text-right"><span class="value">{{ taxAmount.toFixed(2) }}</span></td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td colspan="2">
                                            <div class="d-flex justify-content-between">
                                                <span class="label">{{ t('Grand Total') }}:</span>
                                                <span class="value">{{ grandTotal.toFixed(2) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Button -->
                        <div class="action-section">
                            <button class="btn btn-primary btn-lg" @click="placeInCart">
                                <VIcon icon="tabler-shopping-cart-plus" /> {{ t('Place In Cart') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTaxCalculation } from '../../composables/useTaxCalculation'
import { useBookingList } from '../../composables/useBookingList'
import { useCompanyFormatters } from '../../composables/useCompanyFormatters'
import ExportTable from '@/components/ExportTable.vue'
import AppTextField from '@/@core/components/app-form-elements/AppTextField.vue'
import AppSelect from '@/@core/components/app-form-elements/AppSelect.vue'

const { t } = useI18n()

definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})

const router = useRouter()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Company formatters
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Computed properties for i18n translations
const SN = computed(() => t('SN'))
const Name = computed(() => t('Name'))
const Phone = computed(() => t('Phone'))
const Date = computed(() => t('Date'))
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
        key: 'customer_name',
        sortable: true,
    },
    {
        title: Phone,
        key: 'customer_phone',
        sortable: true,
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

// Computed headers for export (converts computed refs to strings)
const exportHeaders = computed(() => {
    return headers.map(header => ({
        ...header,
        title: typeof header.title == 'object' && header.title.value !== undefined 
            ? header.title.value 
            : header.title
    }))
})

const updateOptions = options => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

// Initialize tax calculation
const {
    companyInfo,
    fetchCompanyTaxSettings,
    items,
    fetchItemsWithTax,
    customerInfo,
    getCustomerState,
    calculateItemTax,
} = useTaxCalculation()

// Initialize booking list with pagination
const {
    totalItems,
    currentPage,
    perPage,
    bookings,
    selectedBooking,
    selectedBookingDetails,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    fetchBookings,
    selectBooking,
    handleSearch,
    handlePageChange,
    loadingBookingDetails
} = useBookingList()

// Computed booking data with serial numbers
const bookingData = computed(() => {
    const data = bookings.value || []
    return data.map((item, index) => ({
        ...item,
        serial_number: getSerialNumber(index, totalItems.value, currentPage.value, perPage.value),
    }))
})

// Calculate tax for booking details
const taxAmount = computed(() => {
  if (!selectedBookingDetails.value?.services) return 0
  
  let totalTax = 0
  selectedBookingDetails.value.services.forEach(item => {
    const taxResult = calculateItemTax(item.id, item.quantity, item.price, getCustomerState.value)
    totalTax += taxResult.totalTax
  })
  return totalTax
})

const subtotal = computed(() => {
  if (!selectedBookingDetails.value?.services) return 0
  return selectedBookingDetails.value.services.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const grandTotal = computed(() => subtotal.value + taxAmount.value)

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchBookings()
})

// Methods
const showBookingDetails = async (booking) => {
    try {
        const response = await $api(`/booking-details-pos/${booking.id}`, {
            method: 'GET'
        })
        
        if (response.success) {
            selectedBookingDetails.value = response.data
            selectedBooking.value = booking
        }
    } catch (error) {
        console.error('Error fetching booking details:', error)
    }
}

const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'accepted':
            return 'primary'
        case 'pending':
            return 'warning'
        case 'rejected':
            return 'error'
        default:
        return 'secondary'
    }
}

const placeInCart = () => {
    if (!selectedBookingDetails.value) return
    
    // Navigate back to POS with booking data
    router.push({
        path: '/pos',
        query: {
            booking_id: selectedBookingDetails.value.id,
            booking_data: JSON.stringify(selectedBookingDetails.value)
        }
    })
}

// Initial fetch
onMounted(async () => {
    await fetchCompanySettings()
    await fetchCompanyTaxSettings()
    await fetchItemsWithTax()
    fetchBookings()
})
</script>

<style lang="scss" scoped>
.booking-list-card {
    margin-top: 100px;
}

.offcanvas {
    width: 992px !important;
}

.offcanvas-body {
    background: #eeeeee;
}

.offcanvas-footer {
    padding: 1rem;
}

@media (max-width: 768px) {
    .offcanvas {
        width: 100% !important;
    }
}

.booking-summary-wrap {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.customer-info,
.booking-summary {
    flex: 1;
}

.info-item {
    margin-bottom: 0.5rem;
}

.label {
    font-weight: bold;
    margin-right: 0.5rem;
}

.value {
    color: #666;
}

.services-table {
    margin-bottom: 2rem;
}

.totals-section {
    margin-bottom: 2rem;
}

.grand-total {
    font-weight: bold;
    font-size: 1.1em;
}

.action-section {
    text-align: center;
    margin-top: 2rem;
}
</style>