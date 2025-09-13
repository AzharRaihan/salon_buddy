<template>
    <div class="container booking-view">

        <div class="booking-list-section">
            <!-- Back Button -->
            <div class="booking-list-header">
                <button class="btn btn-primary" @click="router.push('/pos')">
                    <VIcon icon="tabler-arrow-left" />
                    {{ t('Back to POS') }}
                </button>
            </div>
            <div class="booking-list-header">
                <h3>{{ t('Booking List') }}</h3>
                <div class="search-booking">
                    <input 
                        type="text" 
                        class="form-control" 
                        :placeholder="t('Search by name, phone, or date...')" 
                        v-model="searchQuery"
                        @input="handleSearch"
                    />
                    <VIcon icon="tabler-search" class="search-icon" />
                </div>
            </div>

            <!-- Error State -->
            <div v-if="error" class="text-center py-4">
                <VIcon icon="tabler-alert-circle" color="error" size="large" />
                <p class="mt-2 text-error">{{ error }}</p>
                <VBtn @click="fetchBookings" color="primary" class="mt-2">
                    <VIcon icon="tabler-refresh" class="me-2" />
                    {{ t('Retry') }}
                </VBtn>
            </div>

            <!-- Data Table -->
            <div v-else class="booking-table table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">{{ t('SN') }}</th>
                            <th class="w-35 text-left">{{ t('Name') }}</th>
                            <th class="w-15 text-center">{{ t('Phone') }}</th>
                            <th class="w-15 text-center">{{ t('Date') }}</th>
                            <th class="w-15 text-center">{{ t('Status') }}</th>
                            <th class="w-10 text-center">{{ t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(booking, index) in bookings" :key="booking.id" 
                            :class="{ 'selected': selectedBooking?.id == booking.id }">
                            <td class="text-center">{{ getSerialNumber(index, totalItems, currentPage, perPage) }}</td>
                            <td class="text-left">{{ booking.customer_name }}</td>
                            <td class="text-center">{{ booking.customer_phone }}</td>
                            <td class="text-center">{{ formatDate(booking.date) }}</td>
                            <td class="text-center">
                                <VChip
                                    :color="getStatusClass(booking.status)"
                                    variant="tonal"
                                    size="small"
                                >
                                    {{ booking.status }}
                                </VChip>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" @click.stop="selectBooking(booking)">
                                    <VIcon icon="tabler-eye" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="!isLoading && bookings.length == 0" class="text-center py-4">
                    <VIcon icon="tabler-calendar-off" color="disabled" size="large" />
                    <p class="mt-2 text-disabled">
                        {{ searchQuery ? t('No bookings found matching your search') : t('No bookings available') }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <AppPagination
                :pagination-info="paginationInfo"
                @update:page="handlePageChange"
            />
        </div>

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
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTaxCalculation } from '../../composables/useTaxCalculation';
import { useBookingList } from '../../composables/useBookingList';
import { useCompanyFormatters } from '../../composables/useCompanyFormatters';
import AppPagination from '../../components/AppPagination.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const {
    companyInfo,
    fetchCompanyTaxSettings,
    items,
    fetchItemsWithTax,
    customerInfo,
    getCustomerState,
    calculateItemTax,
} = useTaxCalculation();

const { formatDate, getSerialNumber } = useCompanyFormatters();

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
} = useBookingList();

fetchCompanyTaxSettings();
fetchItemsWithTax();

// Calculate tax for booking details
const taxAmount = computed(() => {
  if (!selectedBookingDetails.value?.services) return 0;
  
  let totalTax = 0;
  selectedBookingDetails.value.services.forEach(item => {
    const taxResult = calculateItemTax(item.id, item.quantity, item.price, getCustomerState.value);
    totalTax += taxResult.totalTax;
  });
  return totalTax;
});

const subtotal = computed(() => {
  if (!selectedBookingDetails.value?.services) return 0;
  return selectedBookingDetails.value.services.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const grandTotal = computed(() => subtotal.value + taxAmount.value);

// Page configuration
definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})

const router = useRouter();

// Methods
const showBookingDetails = async (booking) => {
    try {
        const response = await $api(`/booking-details-pos/${booking.id}`, {
            method: 'GET'
        });
        
        if (response.success) {
            selectedBookingDetails.value = response.data;
            selectedBooking.value = booking;
        }
    } catch (error) {
        console.error('Error fetching booking details:', error);
    }
};

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
};

const placeInCart = () => {
    if (!selectedBookingDetails.value) return;
    
    // Navigate back to POS with booking data
    router.push({
        path: '/pos',
        query: {
            booking_id: selectedBookingDetails.value.id,
            booking_data: JSON.stringify(selectedBookingDetails.value)
        }
    });
};

// Lifecycle
onMounted(() => {
    fetchBookings();
});
</script>

<style scoped>
.booking-view .booking-summary-wrap .table tbody tr {
    cursor: none !important;
}

.search-booking {
    position: relative;
    max-width: 300px;
}

.search-booking input {
    padding-right: 40px;
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.booking-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
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
    .booking-list-header {
        flex-direction: column;
        align-items: stretch;
    }
    .search-package {
        max-width: 100%;
    }
    .offcanvas {
        width: 100% !important;
    }
}
</style>