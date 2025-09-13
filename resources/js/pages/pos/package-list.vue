<template>
    <div class="container booking-view">
        <VCard class="package-list-card booking-list-section" :title="t('List of packages sold')">
            <VCardText>
                <div class="d-flex justify-space-between flex-wrap gap-y-4">
                    <AppTextField v-model="searchQuery" style="max-inline-size: 280px; min-inline-size: 280px;"
                        :placeholder="t('Search by customer name, phone, or package...')" @input="handleSearch" />
                    <div class="d-flex flex-row gap-4 align-center flex-wrap">
                        <button class="btn btn-primary d-flex align-item-center gap-1" @click="router.push('/pos')">
                            <VIcon icon="tabler-arrow-left" />
                            {{ t('Back to POS') }}
                        </button>
                        <AppSelect v-model="itemsPerPage" :items="[5, 10, 20, 50, 100]" />

                        <ExportTable 
                            :data="packageData" 
                            :headers="exportHeaders" 
                            filename="package-report"
                            :title="$t('List of packages sold')"
                        />
                    </div>
                </div>
            </VCardText>

            <VDivider />
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page" :items="packageData" item-value="id"
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

                <template #item.package_name="{ item }">
                    {{ item.package_name }} ({{ item.package_code }})
                </template>

                <!-- Formatted Date -->
                <template #item.purchase_date="{ item }">
                    {{ formatDate(item.purchase_date) }}
                </template>
                
                <!-- Loading state -->
                <template #loading>
                    <VSkeletonLoader type="table-row" :rows="itemsPerPage" />
                </template>

                <!-- No data state -->
                <template #no-data>
                    <div class="d-flex align-center justify-center pa-4">
                        <VIcon icon="tabler-package-off" class="me-2" />
                        {{ searchQuery ? t('No packages found matching your search.') : t('No packages available.') }}   
                    </div>
                </template>

                <template #item.action="{ item }">
                    <div class="d-flex justify-center gap-1">
                        <VBtn icon variant="text" color="primary" size="small" 
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" 
                            aria-controls="offcanvasRight" @click="selectPackage(item)">
                            <VIcon size="22" icon="tabler-eye" />
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
        </VCard>

        <!-- Package Details Offcanvas -->
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
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'
import { usePackageList } from '../../composables/usePackageList'
import ExportTable from '@/components/ExportTable.vue'
import AppAutocomplete from '@/@core/components/app-form-elements/AppAutocomplete.vue'
import AppTextField from '@/@core/components/app-form-elements/AppTextField.vue'
import AppSelect from '@/@core/components/app-form-elements/AppSelect.vue'
import AppDate from '@core/components/date-time-picker/DemoDateTimePickerBasic.vue'
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue'

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
const CustomerName = computed(() => t('Customer Name'))
const Phone = computed(() => t('Phone'))
const PackageName = computed(() => t('Package Name (Code)'))
const PurchaseDate = computed(() => t('Purchase Date'))
const Action = computed(() => t('Action'))

// Data table Headers
const headers = [
    {
        title: SN,
        key: 'serial_number',
        sortable: false,
    },
    {
        title: CustomerName,
        key: 'customer_name',
        sortable: true,
    },
    {
        title: Phone,
        key: 'customer_phone',
        sortable: true,
    },
    {
        title: PackageName,
        key: 'package_name',
        sortable: true,
    },
    {
        title: PurchaseDate,
        key: 'purchase_date',
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
} = usePackageList()

// Computed sales data with serial numbers
const packageData = computed(() => {
    const data = packages.value || []
    return data.map((item, index) => ({
        ...item,
        serial_number: getSerialNumber(index, totalItems.value, currentPage.value, perPage.value),
    }))
})

// Watch for changes in pagination
watch([page, itemsPerPage], () => {
    fetchPackages()
})

// Initial fetch
onMounted(async () => {
    await fetchCompanySettings()
    fetchPackages()
})
</script>

<style scoped>
.offcanvas {
    width: 992px !important;
}

.offcanvas-body {
    background: #eeeeee;
}

.offcanvas-footer {
    padding: 1rem;
}
.booking-view .booking-list-section {
    background-color: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px #0000001a;
}
@media (max-width: 768px) {
    .offcanvas {
        width: 100% !important;
    }
}

.package-usage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.footer-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.send-notification-checkbox {
    display: flex;
    gap: 1rem;
}

.summary-section {
    margin-bottom: 2rem;
}

.summary-heading {
    font-weight: bold;
    margin-right: 0.5rem;
}

.summary-value {
    color: #666;
}
</style>

