<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    form: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    customers: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Array,
        default: () => []
    },
    servicePackages: {
        type: Array,
        default: () => []
    },
    serviceSellers: {
        type: Array,
        default: () => []
    },
    isCompleted: {
        type: Boolean,
        default: false
    },
    isReadonly: {
        type: Boolean,
        default: false
    }
})


const emit = defineEmits(['add-detail', 'remove-detail'])

const addBookingDetail = () => {
    emit('add-detail')
}

const removeBookingDetail = (index) => {
    emit('remove-detail', index)
}

</script>

<template>
    <VForm class="mt-3">
        <VRow>
            <!-- Reference Number -->
            <VCol cols="12" md="6" lg="4">
                <AppTextField
                    v-model="form.reference_no"
                    :label="$t('Reference Number')"
                    :title="$t('Reference Number')"
                    :required="true"
                    type="text"
                    :placeholder="$t('Enter reference number')"
                    readonly
                />
            </VCol>

            <!-- Customer -->
            <VCol cols="12" md="6" lg="4">
                <AppAutocomplete
                    v-model="form.customer_id"
                    :items="customers"
                    :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                    item-value="id"
                    :title="$t('Customer')"
                    :label="$t('Customer')" 
                    :required="true"
                    :placeholder="$t('Select Customer')"
                    :error-messages="errors.customer_id"
                    :readonly="isReadonly"
                    clearable
                />
            </VCol>

            <!-- Branch -->
            <VCol cols="12" md="6" lg="4">
                <AppAutocomplete
                    v-model="form.branch_id"
                    :items="branches"
                    item-title="branch_name"
                    item-value="id"
                    :label="$t('Branch')" 
                    :title="$t('Branch')"
                    :required="true"
                    :placeholder="$t('Select Branch')"
                    :error-messages="errors.branch_id"
                    :readonly="isReadonly"
                    clearable
                />
            </VCol>

            <!-- Date -->
            <VCol cols="12" md="6" lg="4">
                <AppDateTimePicker
                    v-model="form.date"
                    :label="$t('Date')" 
                    :required="true"
                    :placeholder="$t('Select date')"
                    :error-messages="errors.date"
                    :readonly="isReadonly"
                    :config="{
                        enableTime: false,
                        dateFormat: 'Y-m-d',
                        ...(props.form.customer_id ? {} : { minDate: new Date().toISOString().split('T')[0] })
                    }"
                />
            </VCol>

            <!-- Status -->
            <VCol cols="12" md="6" lg="4">
                <AppAutocomplete
                    v-model="form.status"
                    :items="['Pending', 'Accepted', 'Completed', 'Rejected']"
                    :label="$t('Status')" 
                    :required="true"
                    :placeholder="$t('Select Status')"
                    :error-messages="errors.status"
                    :readonly="isReadonly"
                    clearable
                />
            </VCol>

            <!-- Note -->
            <VCol cols="12">
                <AppTextField
                    v-model="form.note"
                    :label="$t('Note')"
                    type="textarea"
                    :placeholder="$t('Enter note')"
                    :readonly="isReadonly"
                />
            </VCol>

            <!-- Booking Details -->
            <VCol cols="12">
                <div class="d-flex justify-space-between align-center">
                    <h3>{{ $t('Booking Details') }}</h3>
                </div>
                <VTable class="repeter-form">
                    <thead>
                        <tr>
                            <th class="w-20">{{ $t('Service') }}</th>
                            <th class="w-20">{{ $t('Start Time') }}</th>
                            <th class="w-20">{{ $t('End Time') }}</th>
                            <th class="w-15">{{ $t('Quantity') }}</th>
                            <th class="w-20">{{ $t('Service Seller') }}</th>
                            <th v-if="!isCompleted && !isReadonly" class="w-5">{{ $t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(detail, index) in form.booking_details" :key="index">
                            <td>
                                <AppAutocomplete
                                    v-model="detail.item_id"
                                    :items="servicePackages"
                                    item-title="name"
                                    item-value="id"
                                    :placeholder="$t('Select Service')"
                                    :readonly="isReadonly"
                                    :error-messages="errors.booking_details[index]?.item_id"
                                    clearable
                                />
                            </td>
                            <td>
                                <AppDateTimePicker
                                    v-model="detail.start_time"
                                    :placeholder="$t('Select start time')"
                                    :readonly="isReadonly"
                                    :title="$t('Start Time')"
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
                                    :readonly="isReadonly"
                                    :title="$t('End Time')"
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
                                    :readonly="isReadonly"
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
                                    :readonly="isReadonly"
                                    :error-messages="errors.booking_details[index]?.service_seller_id"
                                    clearable
                                />
                            </td>
                            <td v-if="!isCompleted && !isReadonly">
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
                    <VBtn 
                        v-if="!isCompleted && !isReadonly" 
                        color="primary" 
                        @click="addBookingDetail"
                    >
                        <VIcon start icon="tabler-plus" />
                        {{ $t('Add Item') }}
                    </VBtn>
                </div>
            </VCol>
        </VRow>
    </VForm>
</template>

<style scoped>
.v-table > .v-table__wrapper > table > thead > tr > th:first-child {
    padding-left: 0px !important;
}
.v-table > .v-table__wrapper > table > tbody > tr > td:first-child {
    padding-left: 0px !important;
}
</style> 