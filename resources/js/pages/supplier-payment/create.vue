<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber } = useCompanyFormatters()

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)
const suppliers = ref([])
const paymentMethods = ref([])
const branch_info = useCookie("branch_info").value || 0;
const paymentAmount = ref(0)
const selectedAccountBalance = ref(0)



const form = ref({
    reference_no: '',
    date: '',
    amount: '',
    note: '',
    supplier_id: null,
    payment_method_id: null,
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const dateError = ref('')
const amountError = ref('')
const noteError = ref('')
const supplierIdError = ref('')
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

    if (form.value.payment_method_id && amount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(amount) > bal) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}`)
            return false
        }
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

const validateSupplierId = async (supplierId) => {

    
    if (!supplierId) {
        supplierIdError.value = t('Supplier is required')
        return false
    }

    supplierIdError.value = ''
    return true
}


const validatePaymentMethodId = (paymentMethodId) => {
    if (!paymentMethodId) {
        paymentMethodIdError.value = t('Payment account is required')
        return false
    }
    paymentMethodIdError.value = ''
    return true
}

// Fetch reference no on mount
const fetchReferenceNo = async () => {
    try {
        const res = await $api('/supplier-payment-reference-no')
        form.value.reference_no = res.data
    } catch (err) {
        console.error('Error fetching reference no:', err)
        toast(t('Error generating reference no'), {
            type: 'error'
        })
    }
}

watch([() => form.value.amount], ([newAmount]) => {
    if (form.value.payment_method_id && newAmount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(newAmount) > bal) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}`), { type: 'error' })
        } else {
            amountError.value = ''
        }
    }
})

watch(() => form.value.payment_method_id, (newMethodId) => {
    if (!newMethodId) {
        selectedAccountBalance.value = 0
        amountError.value = ''
        return
    }

    // our paymentMethods items use { title, value, account_balance }
    const method = paymentMethods.value.find(m => m.value == newMethodId)
    if (!method) {
        selectedAccountBalance.value = 0
        amountError.value = ''
        return
    }

    const bal = method.account_balance ?? 0
    selectedAccountBalance.value = bal ? parseFloat(bal) : 0
    
    // Validate amount if already entered
    if (form.value.amount > 0) {
        const currentBalance = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(form.value.amount) > currentBalance) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(currentBalance)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatAmount(currentBalance)}`), { type: 'error' })
        } else {
            amountError.value = ''
        }
    }
})


// Fetch supplier list
const fetchSuppliers = async () => {
    try {
        const res = await $api('/get-all-suppliers')
        suppliers.value = [
            ...res.data.map(supplier => ({
                title: supplier.phone ? supplier.name + ' (' +  supplier.phone + ')' : supplier.name,
                value: supplier.id
            }))
        ]
    } catch (err) {
        console.error('Error fetching suppliers:', err)
        toast(t('Error fetching suppliers'), {
            type: 'error'
        })
    }
}

// Fetch payment accounts
const fetchPaymentMethods = async () => {
    try {
        const res = await $api('/get-all-payment-methods')
        paymentMethods.value = [
            ...res.data.map(method => ({
                title: method.name,
                value: method.id,
                account_balance: method.account_blanace
            }))
        ]
    } catch (err) {
        console.error('Error fetching payment accounts:', err)
        toast(t('Error fetching payment accounts'), {
            type: 'error'
        })
    }
}

const fetchSupplierPaymentAmount = async (supplierId) => {
    if (!supplierId) {
        paymentAmount.value = 0
        return
    }
    try {
        const res = await $api(`/supplier-payment/${supplierId}`)
        paymentAmount.value = res.data || 0
    } catch (err) {
        console.error('Error fetching due amount:', err)
        toast('Error fetching supplier payment', { type: 'error' })
        paymentAmount.value = 0
    }
}
watch(() => form.value.supplier_id, (newVal) => {
    fetchSupplierPaymentAmount(newVal)
})

onMounted(() => {
    fetchReferenceNo()
    fetchSuppliers()
    fetchPaymentMethods()
})

const resetForm = () => {
    const currentRefNo = form.value.reference_no
    form.value = {
        reference_no: currentRefNo,
        date: '',
        amount: '',
        note: '',
        supplier_id: null,
        payment_method_id: null
    }
    selectedAccountBalance.value = 0
    referenceNoError.value = ''
    dateError.value = ''
    amountError.value = ''
    noteError.value = ''
    supplierIdError.value = ''
    paymentMethodIdError.value = ''
}

const validateForm = () => {
    const isReferenceNoValid = validateReferenceNo(form.value.reference_no)
    const isDateValid = validateDate(form.value.date)
    const isAmountValid = validateAmount(form.value.amount)
    const isNoteValid = validateNote(form.value.note)
    const isSupplierIdValid = validateSupplierId(form.value.supplier_id)
    const isPaymentMethodIdValid = validatePaymentMethodId(form.value.payment_method_id)

    return isReferenceNoValid && isDateValid && isAmountValid && isNoteValid && 
           isSupplierIdValid && isPaymentMethodIdValid
}

const createSupplierPayment = async () => {
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

        const res = await $api('/supplier-payments', {
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
        router.push({ name: 'supplier-payment' })
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
            <VCard :title="t('Add Supplier Payment')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createSupplierPayment">
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

                            <!-- Supplier -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.supplier_id" 
                                    :items="suppliers"
                                    :label="t('Supplier')" :required="true" 
                                    :placeholder="t('Select Supplier')"
                                    :error-messages="supplierIdError"
                                    @change="validateSupplierId($event)"
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
                                    <div class="mt-2" v-if="paymentAmount < 0">
                                        <span class="text-success">{{ t('Advance Paid to Supplier') + ' : ' + formatAmount(Math.abs(paymentAmount)) }}</span>
                                    </div>
                                    <div class="mt-2" v-if="paymentAmount > 0">
                                        <span class="text-error">{{ t('Payment to Supplier') + ' : ' + formatAmount(paymentAmount) }}</span>
                                    </div>
                            </VCol>
                            
                            <!-- Payment Account -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.payment_method_id"
                                    :items="paymentMethods"
                                    :label="t('Payment Account')" :required="true"
                                    :placeholder="t('Select Payment Account')"
                                    :error-messages="paymentMethodIdError"
                                    @change="validatePaymentMethodId($event)"
                                    clearable
                                    />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12">
                                <AppTextField v-model="form.note" :label="t('Note')" type="text" 
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
                                <VBtn type="button" @click="router.push({ name: 'supplier-payment' })" color="primary" variant="tonal">
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
