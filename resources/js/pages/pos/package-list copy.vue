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
                <h3>{{ t('List of packages sold') }}</h3>
                <div class="search-package">
                    <input 
                        type="text" 
                        class="form-control" 
                        :placeholder="t('Search by customer name, phone, or package...')" 
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
                <VBtn @click="fetchPackages" color="primary" class="mt-2">
                    <VIcon icon="tabler-refresh" class="me-2" />
                    {{ t('Retry') }}
                </VBtn>
            </div>

            <!-- Data Table -->
            <div v-else class="booking-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">{{ t('SN') }}</th>
                            <th class="w-25 text-left">{{ t('Customer Name') }}</th>
                            <th class="w-15 text-center">{{ t('Phone') }}</th>
                            <th class="w-25 text-left">{{ t('Package Name (Code)') }}</th>
                            <th class="w-15 text-center">{{ t('Purchase Date') }}</th>
                            <th class="w-10 text-center">{{ t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(pkg, index) in packages" :key="pkg.id" 
                            :class="{ 'selected': selectedPackage?.id == pkg.id }">
                            <td class="w-10 text-center">{{ getSerialNumber(index, totalItems, currentPage, perPage) }}</td>
                            <td class="w-25 text-left">{{ pkg.customer_name }}</td>
                            <td class="w-15 text-center">{{ pkg.customer_phone }}</td>
                            <td class="w-25 text-left">{{ pkg.package_name }} ({{ pkg.package_code }})</td>
                            <td class="w-15 text-center">{{ formatDate(pkg.purchase_date) }}</td>
                            <td class="w-10 text-center">
                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" @click="selectPackage(pkg)">
                                    <VIcon icon="tabler-eye" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="!isLoading && packages.length == 0" class="text-center py-4">
                    <VIcon icon="tabler-package-off" color="disabled" size="large" />
                    <p class="mt-2 text-disabled">
                        {{ searchQuery ? t('No packages found matching your search.') : t('No packages available.') }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <AppPagination
                :pagination-info="paginationInfo"
                @update:page="handlePageChange"
            />
        </div>

        <!-- Package Details -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" >
            <div class="offcanvas-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="offcanvas-title" id="offcanvasRightLabel">{{ t('Package Details') }}</h5>
                <button type="button" class="btn btn-sm btn-primary" variant="tonal" data-bs-dismiss="offcanvas" aria-label="Close">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            
            <div class="offcanvas-body" :class="{'d-flex justify-content-center align-items-center': loadingPackageDetails}">
                <div v-if="loadingPackageDetails" class="text-center py-4">
                    <VProgressCircular indeterminate color="primary" />
                    <p class="mt-2">{{ t('Loading package details...') }}</p>
                </div>
                <div v-else>
                    <div class="booking-list-section" v-if="packageDetails && packages.some(pkg => pkg.id == selectedPackage?.id) && !loadingPackageDetails" >
                        <div class="row">
                            <div class="col-md-6 summary-section">
                                <h3>{{ t('Package Summary') }}</h3>
                                <p v-if="packageDetails.package_summary?.package_name">
                                    <span class="summary-heading">{{ t('Package Name') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.package_name }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.package_code">
                                    <span class="summary-heading">{{ t('Package Code') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.package_code }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.duration > 0">
                                    <span class="summary-heading">{{ t('Package Duration') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.duration }} days</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.purchase_date">
                                    <span class="summary-heading">{{ t('Purchase Date') }}:</span> 
                                    <span class="summary-value">{{ formatDate(packageDetails.package_summary?.purchase_date) }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.end_date">
                                    <span class="summary-heading">{{ t('End Date') }}:</span> 
                                    <span class="summary-value">{{ formatDate(packageDetails.package_summary?.end_date) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 summary-section">
                                <h3>{{ t('Customer Info') }}</h3>
                                <p v-if="packageDetails.package_summary?.customer?.name">
                                    <span class="summary-heading">{{ t('Customer Name') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.customer?.name }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.customer?.phone">
                                    <span class="summary-heading">{{ t('Customer Phone') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.customer?.phone }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.customer?.email">
                                    <span class="summary-heading">{{ t('Customer Email') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.customer?.email }}</span>
                                </p>
                                <p v-if="packageDetails.package_summary?.customer?.address">
                                    <span class="summary-heading">{{ t('Customer Address') }}:</span> 
                                    <span class="summary-value">{{ packageDetails.package_summary?.customer?.address }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="booking-list-header">
                            <h3>{{ t('Package Service & Item') }}</h3>
                        </div>
                        <div class="booking-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="w-15 text-center">{{ t('SN') }}</th>
                                        <th class="w-40 text-left">{{ t('Service') }}</th>
                                        <th class="w-15 text-center">{{ t('Package Qty') }}</th>
                                        <th class="w-15 text-center">{{ t('Taken Qty') }}</th>
                                        <th class="w-15 text-center">{{ t('Remaining Qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in packageDetails.included_items" :key="item.id">
                                        <td class="w-15 text-center">{{ index + 1 }}</td>
                                        <td class="w-40 text-left">{{ item.service_name }}</td>
                                        <td class="w-15 text-center">{{ item.package_qty }}</td>
                                        <td class="w-15 text-center">{{ item.taken }}</td>
                                        <td class="w-15 text-center">{{ item.remaining }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row pt-5" v-if="packageDetails.usages_summary.length > 0">
                            <div class="col-12">
                                <h3 class="mb-3">{{ t('Usages Summary') }}</h3>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="w-10 text-center">{{ t('SN') }}</th>
                                            <th class="w-35 text-left">{{ t('Service') }}</th>
                                            <th class="w-20 text-center">{{ t('Usages Date') }}</th>
                                            <th class="w-20 text-center">{{ t('Usages Time') }}</th>
                                            <th class="w-15 text-center">{{ t('Usages Qty') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in packageDetails.usages_summary" :key="item.id">
                                            <td class="w-10 text-center">{{ index + 1 }}</td>
                                            <td class="w-35 text-left">{{ item.service_item }}</td>
                                            <td class="w-20 text-center">{{ formatDate(item.usages_date) }}</td>
                                            <td class="w-20 text-center">{{ item.usages_time }}</td>
                                            <td class="w-15 text-center">{{ item.taken_qty }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row pt-5">
                            <div class="col-12">
                                <div class="package-usage-header">
                                    <h3>{{ t('Usage Package Item') }}</h3>
                                    <button 
                                        class="btn btn-primary d-flex align-items-center" 
                                        @click="addServiceToUsage"
                                        :disabled="!canAddMoreServices"
                                        :title="!canAddMoreServices ? 'No more services available to add' : 'Add service usage'"
                                    >
                                        <VIcon icon="tabler-plus" class="me-2" />
                                        <span>{{ t('Add Usage') }}</span>
                                    </button>
                                </div>
                                <div class="table-wrapper">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="w-10 text-center">{{ t('SN') }}</th>
                                                <th class="w-25 text-left">{{ t('Service') }}</th>
                                                <th class="w-20 text-center">{{ t('Usages Date') }}</th>
                                                <th class="w-20 text-center">{{ t('Usages Time') }}</th>
                                                <th class="w-15 text-center">{{ t('Usages Qty') }}</th>
                                                <th class="w-10 text-center">{{ t('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in usageRows" :key="index">
                                                <td class="w-10 text-center">{{ index + 1 }}</td>
                                                <td class="w-25 text-left">
                                                    <AppAutocomplete 
                                                        v-model="item.service"
                                                        :items="[
                                                            { title: 'Select Service', value: null, disabled: false },
                                                                ...packageDetails.included_items.map(service => ({
                                                                title: `${service.service_name} (Remaining: ${service.remaining})`,
                                                                value: service,
                                                                disabled: service.remaining <= 0
                                                            }))
                                                        ]"
                                                        
                                                        :placeholder="'Select Service'"
                                                        @update:modelValue="onServiceChange($event, index)"
                                                        clearable
                                                        />
                                                    
                                                </td>
                                                <td class="w-20 text-center">
                                                    <AppDate
                                                        v-model="item.date"
                                                        label=""
                                                        placeholder="Select date"
                                                        :config="{ 
                                                            dateFormat: 'Y-m-d',
                                                        }"
                                                    />
                                                </td>
                                                <td class="w-20 text-center">
                                                    <AppDateTimePicker
                                                        v-model="item.time"
                                                        label=""
                                                        placeholder="Select time"
                                                        :config="{ 
                                                            enableTime: true,
                                                            noCalendar: true,
                                                            dateFormat: 'h:i K',
                                                            time_24hr: false
                                                        }"
                                                    />
                                                </td>
                                                <td class="w-15 text-center">
                                                    <AppTextField v-model="item.qty" min="1"
                                                        label=""
                                                        type="number"
                                                        placeholder="Enter Quantity"
                                                        :disabled="!item.service"
                                                        :max="getMaxQty(item.service)"
                                                        @input="validateQty($event, index)"
                                                        @change="validateQty($event, index)"
                                                    />
                                                </td>
                                                <td class="w-10 text-center">
                                                    <VIcon @click="removeUsage(index)" icon="tabler-trash" class="text-danger" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="offcanvas-footer border-top" v-if="usageRows.length > 0">
                <div class="footer-actions">
                    <button class="btn btn-primary" @click="submitUsage">
                        <VIcon icon="tabler-send" class="me-2" />
                        <span>{{ t('Submit') }}</span>
                    </button>
                    <div class="send-notification-checkbox">
                        <VCheckbox v-model="sendSMS" label="SMS" />
                        <VCheckbox v-model="sendEmail" label="Email" />
                        <VCheckbox v-model="sendWhatsapp" label="Whatsapp Message" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue3-toastify'
import AppDate from '@core/components/date-time-picker/DemoDateTimePickerBasic.vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
import { usePackageList } from '../../composables/usePackageList';
import AppPagination from '../../components/AppPagination.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const branch_info = useCookie("branch_info").value || 0;

definePage({
    meta: {
        layout: 'pos',
        public: true,
    },
})

const router = useRouter();

// Initialize package list with pagination
const {
    totalItems,
    currentPage,
    perPage,
    packages,
    selectedPackage,
    packageDetails,
    usageRows,
    sendSMS,
    sendEmail,
    sendWhatsapp,
    searchQuery,
    isLoading,
    error,
    paginationInfo,
    canAddMoreServices,
    fetchPackages,
    selectPackage,
    handleSearch,
    handlePageChange,
    addServiceToUsage,
    removeUsage,
    getMaxQty,
    onServiceChange,
    validateQty,
    submitUsage,
    loadingPackageDetails
} = usePackageList();

const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

// Initialize company settings on mount
onMounted(async () => {
    await fetchCompanySettings(),
    fetchPackages()
})
</script>

<style scoped>
.search-package {
    position: relative;
    max-width: 300px;
}

.search-package input {
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
.v-pagination-root ul {
    margin-bottom: 0 !important;
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