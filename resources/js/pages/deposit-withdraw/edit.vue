<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

const { formatAmount } = useCompanyFormatters()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const paymentMethods = ref([])
const branch_info = useCookie("branch_info").value || 0;
const selectedAccountBalance = ref(0)
const isInitialLoad = ref(true)

const form = ref({
    reference_no: '',
    date: '',
    amount: '',
    old_amount: '',
    note: '',
    payment_method_id: null,
    type: 'Deposit',
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const dateError = ref('')
const amountError = ref('')
const noteError = ref('')
const paymentMethodIdError = ref('')
const typeError = ref('')

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

    if(Number(form.value.old_amount) >= Number(amount)) {
        amountError.value = ''
        return true
    }
    
    // Only validate balance for Withdraw type
    if (form.value.type == 'Withdraw' && form.value.payment_method_id && amount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(amount) > bal) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`)
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

const validatePaymentMethodId = (paymentMethodId) => {
    if (!paymentMethodId) {
        paymentMethodIdError.value = t('Payment account is required')
        return false
    }
    paymentMethodIdError.value = ''
    return true
}

const validateType = (type) => {
    if (!type) {
        typeError.value = t('Type is required')
        return false
    }
    typeError.value = ''
    return true
}


// Fetch deposit withdraw data
const fetchDepositWithdrawData = async () => {
    try {
        const res = await $api(`/deposit-withdraws/${route.query.id}`)
        const depositWithdraw = res.data
        form.value = {
            reference_no: depositWithdraw.reference_no,
            date: depositWithdraw.date,
            amount: depositWithdraw.amount,
            old_amount: depositWithdraw.amount,
            note: depositWithdraw.note ?? '',
            payment_method_id: depositWithdraw.payment_method?.id,
            type: depositWithdraw.type,
            branch_id: branch_info.id
        }
    } catch (err) {
        console.error('Error fetching deposit withdraw:', err)
        toast('Error loading deposit withdraw data', {
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
        toast('Error loading payment accounts', {
            type: 'error'
        })
    }
}

// Watch for amount changes
watch(() => form.value.amount, (newAmount) => {
    if (isInitialLoad.value) return

    if(Number(form.value.old_amount) >= Number(newAmount)) {
        amountError.value = ''
        return true
    }
    if (form.value.type == 'Withdraw' && form.value.payment_method_id && newAmount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(newAmount) > bal) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`), { type: 'error' })
        } else {
            amountError.value = ''
        }
    }
})

// Watch for payment account changes
watch(() => form.value.payment_method_id, (newMethodId) => {
    if (!newMethodId) {
        selectedAccountBalance.value = 0
        if (!isInitialLoad.value) amountError.value = ''
        return
    }

    const method = paymentMethods.value.find(m => m.value == newMethodId)
    if (!method) {
        selectedAccountBalance.value = 0
        if (!isInitialLoad.value) amountError.value = ''
        return
    }

    const bal = method.account_balance ?? 0
    selectedAccountBalance.value = bal ? parseFloat(bal) : 0

    if(Number(form.value.old_amount) >= Number(form.value.amount)) {
        amountError.value = ''
        return true
    }
    
    // Validate amount if already entered and type is Withdraw (but not on initial load)
    if (!isInitialLoad.value && form.value.type == 'Withdraw' && form.value.amount > 0) {
        const currentBalance = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(form.value.amount) > currentBalance) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(currentBalance)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatAmount(currentBalance)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`), { type: 'error' })
        } else {
            amountError.value = ''
        }
    }
})

// Watch for type changes
watch(() => form.value.type, (newType) => {
    if (isInitialLoad.value) return
    
    // Clear amount error when switching between Deposit and Withdraw
    if (newType === 'Deposit') {
        amountError.value = ''
    } else if (newType == 'Withdraw' && form.value.payment_method_id && form.value.amount > 0) {
        // Re-validate when switching to Withdraw
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(form.value.amount) > bal) {
            amountError.value = t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatAmount(bal)}, Previous Withdraw Amount: ${formatAmount(form.value.old_amount)}`), { type: 'error' })
        }
    }
})

onMounted(async () => {
    await Promise.all([
        fetchDepositWithdrawData(),
        fetchPaymentMethods()
    ])
    
    // Set initial load to false after data is loaded
    setTimeout(() => {
        isInitialLoad.value = false
    }, 100)
})

const resetForm = () => {
    router.push({ name: 'deposit-withdraw' })
}

const validateForm = () => {
    const isReferenceNoValid = validateReferenceNo(form.value.reference_no)
    const isDateValid = validateDate(form.value.date)
    const isAmountValid = validateAmount(form.value.amount)
    const isNoteValid = validateNote(form.value.note)
    const isPaymentMethodIdValid = validatePaymentMethodId(form.value.payment_method_id)
    const isTypeValid = validateType(form.value.type)

    return isReferenceNoValid && isDateValid && isAmountValid && isNoteValid && 
           isPaymentMethodIdValid && isTypeValid
}

const updateDepositWithdraw = async () => {
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
        formData.append('_method', 'PUT')

        const res = await $api(`/deposit-withdraws/${route.query.id}`, {
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
        router.push({ name: 'deposit-withdraw' })
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
            <VCard :title="t('Edit Deposit Withdraw')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateDepositWithdraw">
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

                            <!-- Type -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.type"
                                    :items="[
                                        { title: 'Deposit', value: 'Deposit' },
                                        { title: 'Withdraw', value: 'Withdraw' }
                                    ]"
                                    :label="t('Type')" :required="true"
                                    :placeholder="t('Select Type')"
                                    :error-messages="typeError"
                                    @update:model-value="validateType"
                                    clearable
                                    />
                            </VCol>

                            <!-- Amount -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.amount" :label="t('Amount')" :required="true" type="number" 
                                    :placeholder="t('Enter Amount')"
                                    :error-messages="amountError" 
                                    @input="validateAmount($event.target.value)" />
                            </VCol>

                            <!-- Payment Account -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.payment_method_id"
                                    :items="paymentMethods"
                                    :label="t('Payment Account')" :required="true"
                                    :placeholder="t('Select Payment Account')"
                                    :error-messages="paymentMethodIdError"
                                    @update:model-value="validatePaymentMethodId"
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
                                    {{ t('Update') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'deposit-withdraw' })" color="primary" variant="tonal">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>