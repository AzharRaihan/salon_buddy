<script setup>
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { useWebsiteSettingsStore } from '@/stores/websiteSetting'
const websiteSettingsStore = useWebsiteSettingsStore()
const taxIsGst = computed(() => websiteSettingsStore.getTaxIsGst)

const { t } = useI18n()
const route = useRoute();
const router = useRouter();
const loadings = ref(false)
const sendSMS = ref(false)
const sendEmail = ref(false)
const sendWhatsapp = ref(false)

const form = ref({
    reference_no: '',
    customer_id: null,
    branch_id: null,
    date: null,
    note: '',
    status: null,
    booking_details: [],
})

const errors = ref({
    customer_id: null,
    branch_id: null,
    date: null,
    status: null,
    booking_details: []
})

const customers = ref([])
const branches = ref([])
const servicePackages = ref([])
const serviceSellers = ref([])

// Fetch initial data
const fetchInitialData = async () => {
    try {
        // Fetch customers
        const customersResponse = await $api('/get-customers-except-walking-customer')
        customers.value = customersResponse.data

        // Fetch branches
        const branchesResponse = await $api('/get-branch-list')
        branches.value = branchesResponse.data

        // Fetch service packages
        const packagesResponse = await $api('/get-service-type-item-list')
        servicePackages.value = packagesResponse.data

        // Fetch service sellers
        const sellersResponse = await $api('/get-all-users')
        serviceSellers.value = sellersResponse.data


        // Fetch booking data
        const bookingResponse = await $api(`/bookings/${route.query.id}`)
        const bookingData = bookingResponse.data.booking


        form.value = {
            reference_no: bookingData?.reference_no,
            customer_id: bookingData.customer?.id,
            branch_id: bookingData.branch?.id,
            date: bookingData?.date,
            note: bookingData?.note,
            status: bookingData?.status,
            booking_details: bookingData?.booking_details?.map(detail => ({
                item_id: detail.items?.id,
                start_time: detail?.start_time,
                end_time: detail?.end_time,
                quantity: detail?.quantity,
                service_seller_id: detail?.service_seller?.id
            }))
        }

    } catch (err) {
        console.error('Error fetching initial data:', err)
        toast('Error loading initial data', {
            type: 'error'
        })
    }
}

onMounted(async () => {
    await websiteSettingsStore.resetSettings()
    fetchInitialData()
})

const fetchCustomerList = async () => {
    const response = await $api('/get-all-customers')
    customers.value = response.data
}

const addBookingDetail = () => {
    form.value.booking_details.push({
        item_id: null,
        start_time: null,
        end_time: null,
        quantity: 1,
        service_seller_id: null
    })

    errors.value.booking_details.push({
        item_id: '',
        start_time: '',
        end_time: '',
        quantity: '',
        service_seller_id: ''
    })
}

const removeBookingDetail = (index) => {
    form.value.booking_details.splice(index, 1)
    errors.value.booking_details.splice(index, 1)
}

const validateForm = () => {
    let isValid = true
    errors.value = {
        customer_id: null,
        branch_id: null,
        date: null,
        status: null,
        booking_details: []
    }

    // Reset booking details errors
    form.value.booking_details.forEach(() => {
        errors.value.booking_details.push({
            item_id: '',
            start_time: '',
            end_time: '',
            quantity: '',
            service_seller_id: ''
        })
    })

    if (!form.value.customer_id) {
        errors.value.customer_id = t('Customer is required')
        isValid = false
    }
    if (!form.value.branch_id) {
        errors.value.branch_id = t('Branch is required')
        isValid = false
    }
    if (!form.value.date) {
        errors.value.date = t('Date is required')
        isValid = false
    }
    if (!form.value.status) {
        errors.value.status = t('Status is required')
        isValid = false
    }

    // Validate booking details
    if (form.value.booking_details.length == 0) {
        toast(t('Please add at least one booking detail'), {
            type: 'error'
        })
        isValid = false
    } else {
        form.value.booking_details.forEach((detail, index) => {
            if (!detail.item_id) {
                errors.value.booking_details[index].item_id = t('Service is required')
                isValid = false
            }
            if (!detail.start_time) {
                errors.value.booking_details[index].start_time = t('Start time is required')
                isValid = false
            }
            if (!detail.end_time) {
                errors.value.booking_details[index].end_time = t('End time is required')
                isValid = false
            }
            if (!detail.quantity || detail.quantity < 1) {
                errors.value.booking_details[index].quantity = t('Valid quantity is required')
                isValid = false
            }
            if (!detail.service_seller_id) {
                errors.value.booking_details[index].service_seller_id = t('Service seller is required')
                isValid = false
            }

            // Validate time range
            if (detail.start_time && detail.end_time) {
                const start = new Date(`2000/01/01 ${detail.start_time}`)
                const end = new Date(`2000/01/01 ${detail.end_time}`)
                
                if (end <= start) {
                    errors.value.booking_details[index].end_time = t('End time must be after start time')
                    isValid = false
                }
            }
        })
    }

    return isValid
}

const updateBooking = async () => {
    loadings.value = true
    
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api(`/bookings/${route.query.id}`, {
            method: 'PUT',
            body: {
                ...form.value,
                send_sms: sendSMS.value,
                send_email: sendEmail.value,
                send_whatsapp: sendWhatsapp.value
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            loadings.value = false
            return
        }

        toast(message, {
            type: "success",
        })
        loadings.value = false
        router.push({ name: 'booking' })
    }
    catch (err) {
        if (err.errors) {
            // Show each validation error as a toast
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            // Show general error if no field-specific errors
            toast(message, {
                type: 'error',
            })
        }
        loadings.value = false
        return
    }
}



// Customer Modal
const showCustomerModal = ref(false)
const newCustomerName = ref('')
const newCustomerPhone = ref('')
const newCustomerEmail = ref('')
const newCustomerAddress = ref('')
const newCustomerGstNumber = ref('')
const newCustomerSameOrDiffState = ref(null)


const newCustomerNameError = ref('')
const newCustomerPhoneError = ref('')
const newCustomerEmailError = ref('')
const newCustomerAddressError = ref('')
const newCustomerGstNumberError = ref('')
const newCustomerSameOrDiffStateError = ref('')

const validateNewCustomerName = (name) => {
    if (!name) {
        newCustomerNameError.value = t('Name is required')
        return false
    }
    newCustomerNameError.value = ''
    return true
}
const validateNewCustomerPhone = (phone) => {
    if (!phone) {
        newCustomerPhoneError.value = t('Phone is required')
        return false
    }
    newCustomerPhoneError.value = ''
    return true
}
const validateNewCustomerEmail = (email) => {
    if (!email) {
        newCustomerEmailError.value = t('Email is required')
        return false
    }
    newCustomerEmailError.value = ''
    return true
}
const validateNewCustomerGstNumber = (gstNumber) => {
    if (!gstNumber) {
        newCustomerGstNumberError.value = t('GST Number is required')
        return false
    }
    newCustomerGstNumberError.value = ''
    return true
}

const validateNewCustomerSameOrDiffState = (sameOrDiffState) => {
    if (!sameOrDiffState) {
        newCustomerSameOrDiffStateError.value = t('State is required')
        return false
    }
    newCustomerSameOrDiffStateError.value = ''
    return true
}

const validateCustomerForm = () => {

    let isValidCustomer = true
    
    if (!validateNewCustomerName(newCustomerName.value)) isValidCustomer = false
    if (!validateNewCustomerPhone(newCustomerPhone.value)) isValidCustomer = false
    if (!validateNewCustomerEmail(newCustomerEmail.value)) isValidCustomer = false

    if (taxIsGst.value == 'Yes') {
        if (!validateNewCustomerGstNumber(newCustomerGstNumber.value)) isValidCustomer = false
        if (!validateNewCustomerSameOrDiffState(newCustomerSameOrDiffState.value)) isValidCustomer = false
    }
    
    return isValidCustomer
}
const addCustomer = async () => {
    if (!validateCustomerForm()) return
    try {

        const res = await $api('/customers', {
            method: 'POST',
            body: JSON.stringify({ name: newCustomerName.value, phone: newCustomerPhone.value, email: newCustomerEmail.value, address: newCustomerAddress.value, gst_number: newCustomerGstNumber.value, same_or_diff_state: newCustomerSameOrDiffState.value }),
            headers: { 'Content-Type': 'application/json' },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            loadings.value = false
            return
        }

        await fetchCustomerList()
        const last = res.data
        form.value.customer_id = last.id
        showCustomerModal.value = false
        newCustomerName.value = ''
        newCustomerPhone.value = ''
        newCustomerEmail.value = ''
        newCustomerAddress.value = ''
        toast(t('Customer added successfully'), { type: 'success' })

    } catch (err) {
        if (err.errors) {
            // Show each validation error as a toast
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            // Show general error if no field-specific errors
            toast(message, {
                type: 'error',
            })
        }
        loadings.value = false
        return
    }
}


</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="$t('Edit Booking')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateBooking">
                        <VRow>
                            <!-- Reference Number -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField
                                    v-model="form.reference_no"
                                    :label="$t('Reference Number')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter reference number')"
                                    readonly
                                />
                            </VCol>

                            <!-- Customer -->
                            <VCol cols="12" md="6" lg="4" class="d-flex align-center">
                                <AppAutocomplete
                                    v-model="form.customer_id"
                                    :items="customers"
                                    :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                                    item-value="id"
                                    :label="t('Customer')" :required="true"
                                    :placeholder="t('Select Customer')"
                                    :error-messages="errors.customer_id"
                                    clearable
                                />
                                <VBtn icon size="small" color="primary" :class="['ms-2', errors.customer_id ? 'mt-0' : 'mt-5']" @click="showCustomerModal = true">
                                    <VIcon icon="tabler-plus" />
                                </VBtn>
                            </VCol>

                            <!-- Branch -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.branch_id"
                                    :items="branches"
                                    item-title="branch_name"
                                    item-value="id"
                                    :label="$t('Branch')" :required="true"
                                    :placeholder="$t('Select Branch')"
                                    :error-messages="errors.branch_id"
                                    clearable
                                />
                            </VCol>

                            <!-- Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date"
                                    :label="$t('Date')" :required="true"
                                    :placeholder="$t('Select date')"
                                    :error-messages="errors.date"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.status"
                                    :items="['Pending', 'Accepted', 'Rejected', 'Completed']"
                                    :label="$t('Status')" :required="true"
                                    :placeholder="$t('Select Status')"
                                    :error-messages="errors.status"
                                    clearable
                                />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea
                                    v-model="form.note"
                                    :label="$t('Note')"
                                    type="textarea"
                                    :placeholder="$t('Enter note')"
                                />
                            </VCol>

                            <!-- Booking Details -->
                            <VCol cols="12">
                                <div class="d-flex justify-space-between align-center">
                                    {{ $t('Booking Details') }}
                                </div>
                                <VTable class="repeter-form">
                                    <thead>
                                        <tr>
                                            <th class="w-20">{{ $t('Service') }}</th>
                                            <th class="w-20">{{ $t('Start Time') }}</th>
                                            <th class="w-20">{{ $t('End Time') }}</th>
                                            <th class="w-15">{{ $t('Quantity') }}</th>
                                            <th class="w-20">{{ $t('Service Seller') }}</th>
                                            <th class="w-5">{{ $t('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="v-align-top" v-for="(detail, index) in form.booking_details" :key="index">
                                            <td>
                                                <AppAutocomplete
                                                    v-model="detail.item_id"
                                                    :items="servicePackages"
                                                    item-title="name"
                                                    item-value="id"
                                                    placeholder="Select Service"
                                                    :error-messages="errors.booking_details[index]?.item_id"
                                                    clearable
                                                />
                                            </td>
                                            <td>
                                                <AppDateTimePicker
                                                    v-model="detail.start_time"
                                                    :placeholder="$t('Select start time')"
                                                    :error-messages="errors.booking_details[index]?.start_time"
                                                    :config="{
                                                        enableTime: true,
                                                        noCalendar: true,
                                                        dateFormat: 'H:i',
                                                        minTime: '08:00',
                                                        maxTime: '18:00'
                                                    }"
                                                />  
                                            </td>
                                            <td>
                                                <AppDateTimePicker
                                                    v-model="detail.end_time"
                                                    :placeholder="$t('Select end time')"
                                                    :error-messages="errors.booking_details[index]?.end_time"
                                                    :config="{
                                                        enableTime: true,
                                                        noCalendar: true,
                                                        dateFormat: 'H:i',
                                                        minTime: '08:00',
                                                        maxTime: '18:00'
                                                    }"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="detail.quantity"
                                                    type="number"
                                                    min="1"
                                                    :placeholder="$t('Enter quantity')"
                                                    :error-messages="errors.booking_details[index]?.quantity"
                                                />
                                            </td>
                                            <td>
                                                <AppAutocomplete
                                                    v-model="detail.service_seller_id"
                                                    :items="serviceSellers"
                                                    item-title="name"
                                                    item-value="id"
                                                    :placeholder="$t('Select Seller')"
                                                    :error-messages="errors.booking_details[index]?.service_seller_id"  
                                                    clearable
                                                />
                                            </td>
                                            <td>
                                                <VBtn
                                                    color="error"
                                                    icon
                                                    variant="text"
                                                    @click="removeBookingDetail(index)"
                                                >
                                                    <VIcon icon="tabler-trash" />
                                                </VBtn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                                <div class="d-flex justify-end mt-3">
                                    <VBtn color="primary" @click="addBookingDetail">
                                        <VIcon start icon="tabler-plus" />
                                        {{ $t('Add Item') }}
                                    </VBtn>
                                </div>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'booking' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
                                </VBtn>
                                <VCheckbox
                                    v-model="sendSMS"
                                    label="Send SMS"
                                    />
                                <VCheckbox
                                    v-model="sendEmail"
                                    label="Send Email"
                                    />
                                <VCheckbox
                                    v-model="sendWhatsapp"
                                    label="Send Whatsapp Message"
                                    />
                            </VCol>
                        </VRow>


                        <!-- Customer Modal -->
                        <VDialog v-model="showCustomerModal" class="customer-modal">
                            <VCard class="modal-card modal-card-md">
                                <VCardTitle>
                                    {{ t('Add Customer') }}
                                </VCardTitle>
                                <VCardText>
                                    <VRow>
                                        <VCol md="6" lg="6">
                                            <AppTextField v-model="newCustomerName" :label="t('Name')" :required="true" :placeholder="t('Enter Name')" :error-messages="newCustomerNameError" />
                                        </VCol>
                                        <VCol md="6" lg="6">
                                            <AppTextField v-model="newCustomerPhone" :label="t('Phone')" :required="true" :placeholder="t('Enter Phone')" :error-messages="newCustomerPhoneError" />
                                        </VCol>
                                        <VCol md="6" lg="6">
                                            <AppTextField v-model="newCustomerEmail" :label="t('Email')" :required="true" :placeholder="t('Enter Email')" :error-messages="newCustomerEmailError"  />
                                        </VCol>
                                        <VCol md="6" lg="6">
                                            <AppTextarea v-model="newCustomerAddress" :label="t('Address')" :placeholder="t('Enter Address')" />
                                        </VCol>
                                        <VCol md="6" lg="6" v-if="taxIsGst == 'Yes'">
                                            <AppTextField v-model="newCustomerGstNumber" :label="t('GST Number')" :required="true" :placeholder="t('Enter GST Number')" :error-messages="newCustomerGstNumberError"  />
                                        </VCol>
                                        <VCol md="6" lg="6" v-if="taxIsGst == 'Yes'">
                                            <AppAutocomplete
                                                v-model="newCustomerSameOrDiffState" 
                                                :label="$t('State Status')" :required="true"
                                                :items="[
                                                    { title: 'Same', value: 'Same' },
                                                    { title: 'Different', value: 'Different' }
                                                ]"
                                                :placeholder="$t('Select state status')"
                                                :error-messages="newCustomerSameOrDiffStateError"
                                                clearable
                                            />
                                        </VCol>
                                    </VRow>
                                </VCardText>
                                <VCardActions>
                                    <VBtn color="primary" variant="tonal" @click="addCustomer">
                                        <VIcon start icon="tabler-checkbox" />
                                        {{ t('Submit') }}
                                    </VBtn>
                                    <VBtn color="error" variant="tonal" @click="showCustomerModal = false">
                                        <VIcon start icon="tabler-x" />
                                        {{ t('Close') }}
                                    </VBtn>
                                </VCardActions>
                            </VCard>
                        </VDialog>

                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>

<style scoped>
.customer-modal {
    width: 800px;
}
@media screen and (max-width: 575.98px) {
    .customer-modal {
        width: 95%;
    }
}
.v-table > .v-table__wrapper > table > thead > tr > th:first-child {
    padding-left: 0px !important;
}

.v-table > .v-table__wrapper > table > tbody > tr > td:first-child {
    padding-left: 0px !important;
}
</style>