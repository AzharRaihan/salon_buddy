<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()
import { useI18n } from 'vue-i18n';
const { t } = useI18n()

const router = useRouter()
const loadings = ref(false)
const customers = ref([])
const paymentMethods = ref([]) // Added payment methods ref
const branch_info = useCookie("branch_info").value || 0;
const dueAmount = ref(0)

const form = ref({
    reference_no: '',
    date: '',
    amount: '',
    note: '',
    customer_id: null,
    payment_method_id: null,
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const dateError = ref('')
const amountError = ref('')
const noteError = ref('')
const customerIdError = ref('')
const paymentMethodIdError = ref('')

const validateReferenceNo = (referenceNo) => {
    if (!referenceNo) {
        referenceNoError.value = t('Reference number is required')
        return false
    }
    referenceNoError.value = ''
    return true
}

const validateDate = (date) => {
    if (!date) {
        dateError.value = t('Date is required')
        return false
    }
    dateError.value = ''
    return true
}

const validateAmount = (amount) => {
    if (!amount) {
        amountError.value = t('Amount is required')
        return false
    }
    if (isNaN(amount) || amount <= 0) {
        amountError.value = t('Amount must be a positive number')
        return false
    }
    amountError.value = ''
    return true
}

const validateNote = (note) => {
    if (note && note.length > 255) {
        noteError.value = t('Note cannot exceed 255 characters')
        return false
    }
    noteError.value = ''
    return true
}

const validateCustomerId = (customerId) => {
    if (!customerId) {
        customerIdError.value = t('Customer is required')
        return false
    }
    customerIdError.value = ''
    return true
}

const validatePaymentMethodId = (paymentMethodId) => {
    if (!paymentMethodId) {
        paymentMethodIdError.value = t('Payment method is required')
        return false
    }
    paymentMethodIdError.value = ''
    return true
}

// Fetch reference no on mount
const fetchReferenceNo = async () => {
    try {
        const res = await $api('/customer-receive-reference-no')
        form.value.reference_no = res.data
    } catch (err) {
        console.error('Error fetching reference no:', err)
        toast('Error generating reference no', {
            type: 'error'
        })
    }
}

// Fetch customer list
const fetchCustomers = async () => {
    try {
        const res = await $api('/customers')
        customers.value = [
            ...res.data.customers.map(customer => ({
                title: customer.phone ? customer.name + ' (' +  customer.phone + ')' : customer.name,
                value: customer.id
            }))
        ]
    } catch (err) {
        console.error('Error fetching customers:', err)
        toast('Error fetching customers', {
            type: 'error'
        })
    }
}

// Fetch payment methods
const fetchPaymentMethods = async () => {
    try {
        const res = await $api('/get-all-payment-methods')
        paymentMethods.value = [
            ...res.data.map(method => ({
                title: method.name,
                value: method.id
            }))
        ]
    } catch (err) {
        console.error('Error fetching payment methods:', err)
        toast('Error fetching payment methods', {
            type: 'error'
        })
    }
}

const fetchCustomerDue = async (customerId) => {
    if (!customerId) {
        dueAmount.value = 0
        return
    }
    try {
        const res = await $api(`/customer-due/${customerId}`)
        dueAmount.value = res.data || 0
    } catch (err) {
        console.error('Error fetching due amount:', err)
        toast('Error fetching customer due amount', { type: 'error' })
        dueAmount.value = 0
    }
}
watch(() => form.value.customer_id, (newVal) => {
    fetchCustomerDue(newVal)
})

onMounted(() => {
    fetchReferenceNo()
    fetchCustomers()
    fetchPaymentMethods()
})

const resetForm = () => {
    const currentRefNo = form.value.reference_no
    form.value = {
        reference_no: currentRefNo,
        date: '',
        amount: '',
        note: '',
        customer_id: null,
        payment_method_id: null
    }
    referenceNoError.value = ''
    dateError.value = ''
    amountError.value = ''
    noteError.value = ''
    customerIdError.value = ''
    paymentMethodIdError.value = ''
}

const validateForm = () => {
    const isReferenceNoValid = validateReferenceNo(form.value.reference_no)
    const isDateValid = validateDate(form.value.date)
    const isAmountValid = validateAmount(form.value.amount)
    const isNoteValid = validateNote(form.value.note)
    const isCustomerIdValid = validateCustomerId(form.value.customer_id)
    const isPaymentMethodIdValid = validatePaymentMethodId(form.value.payment_method_id)

    return isReferenceNoValid && isDateValid && isAmountValid && isNoteValid && 
           isCustomerIdValid && isPaymentMethodIdValid
}

const createCustomerReceive = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        Object.keys(form.value).forEach(key => {
            formData.append(key, form.value[key])
        })

        const res = await $api('/customer-receives', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
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
        router.push({ name: 'customer-receive' })
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
            toast(err.message, {
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
            <VCard :title="t('Add Customer Receive')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createCustomerReceive">
                        <VRow>
                            <!-- Reference No -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.reference_no" :label="t('Reference No')" :required="true" type="text" 
                                    :placeholder="t('Enter reference number')"
                                    :error-messages="referenceNoError" 
                                    @input="validateReferenceNo($event.target.value)"
                                    readonly
                                    />
                            </VCol>

                            <!-- Customer -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.customer_id" 
                                    :items="customers"
                                    :label="t('Customer')" :required="true" 
                                    :placeholder="t('Select Customer')"
                                    :error-messages="customerIdError"
                                    @change="validateCustomerId($event)"
                                    clearable
                                    />
                            </VCol>

                            <!-- Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date"
                                    :label="t('Date')" :required="true"
                                    :placeholder="t('Select date')"
                                    :error-messages="dateError"
                                    @update:model-value="validateDate"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d',
                                        maxDate: new Date()
                                    }"
                                />
                            </VCol>

                            <!-- Amount -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.amount" :label="t('Amount')" :required="true" type="number" 
                                    :placeholder="t('Enter Amount')"
                                    :error-messages="amountError" 
                                    @input="validateAmount($event.target.value)" />
                                    <div class="mt-2" v-if="dueAmount < 0">
                                        <span class="text-success">{{ t('Advance Paid by Customer') + ' : ' + formatAmount(Math.abs(dueAmount)) }}</span>
                                    </div>
                                    <div class="mt-2" v-if="dueAmount > 0">
                                        <span class="text-error">{{ t('Receive from Customer') + ' : ' + formatAmount(dueAmount) }}</span>
                                    </div>
                            </VCol>

                            <!-- Payment Method -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.payment_method_id"
                                    :items="paymentMethods"
                                    :label="t('Payment Method')" :required="true"
                                    :placeholder="t('Select Payment Method')"
                                    :error-messages="paymentMethodIdError"
                                    @change="validatePaymentMethodId($event)"
                                    clearable
                                    />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.note" :label="t('Note')" type="text" 
                                    :placeholder="t('Enter note')"
                                    :error-messages="noteError" 
                                    @input="validateNote($event.target.value)" />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'customer-receive' })" color="primary" variant="tonal">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-refresh" />
                                    {{ t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>
