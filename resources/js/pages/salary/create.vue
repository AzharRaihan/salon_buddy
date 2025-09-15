<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)
const employees = ref([])
const paymentMethods = ref([])
const branch_info = useCookie("branch_info").value || 0;
const { fetchCompanySettings, formatDate, formatAmount, getSerialNumber, formatNumberPrecision } = useCompanyFormatters()

const form = ref({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    generated_date: '',

    total_amount: 0,
    items: [],
    payments: []
})

const yearError = ref('')
const monthError = ref('')
const dateError = ref('')
const paymentMethodError = ref('')
const itemErrors = ref({
    employee_id: '',
    salary_amount: '',
    overtime_rate: '',
    overtime_hour: '',
    additional_amount: '',
    deduction_amount: '',
    absent_day: '',
    absent_day_amount: '',
    advance_taken: '',
    net_salary: ''
})

const validateYear = (year) => {
    if (!year) {
        yearError.value = t('Year is required')
        return false
    }
    yearError.value = ''
    return true
}

const validateMonth = (month) => {
    if (!month) {
        monthError.value = t('Month is required')
        return false
    }
    monthError.value = ''
    return true
}

const validateDate = (date) => {
    if (!date) {
        dateError.value = t('Generated date is required')
        return false
    }
    dateError.value = ''
    return true
}

const validatePaymentMethod = (paymentMethods) => {
    if (!paymentMethods || paymentMethods.length == 0) {
        paymentMethodError.value = t('At least one payment method is required')
        return false
    }
    paymentMethodError.value = ''
    return true
}
    
const validateItem = (item) => {
    let isValid = true
    
    if (!item.employee_id) {
        itemErrors.value.employee_id = t('Employee is required')
        isValid = false
    }
    
    if (!item.salary_amount || item.salary_amount < 0) {
        itemErrors.value.salary_amount = t('Valid salary amount is required')
        isValid = false
    }

    return isValid
}

// Fetch employees and payment methods when component mounts
onMounted(async () => {
    try {
        const [employeesResponse, paymentMethodsResponse] = await Promise.all([
            $api('/get-all-employees'),
            $api('/get-all-payment-methods')
        ])
        employees.value = [...employeesResponse.data]
        paymentMethods.value = [...paymentMethodsResponse.data]

        // Initialize form items with all employees
        form.value.items = employees.value.map(employee => ({
            employee_id: employee.id,
            employee_name: employee.name,
            salary_amount: formatNumberPrecision(employee.salary) || 0,
            overtime_rate: formatNumberPrecision(employee.overtime_hour_rate) || 0,
            overtime_hour: 0,
            additional_amount: 0,
            deduction_amount: 0,
            absent_day: 0,
            absent_day_amount: 0,
            advance_taken: 0,
            net_salary: formatNumberPrecision(employee.salary) || 0,
            note: ''
        }))

        // Calculate initial total
        calculateTotalAmount()

    } catch (error) {
        console.error('Error fetching data:', error)
        toast(t('Failed to load data'), {
            type: 'error'
        })
    }
})

const addPaymentMethod = (methodId) => {
    if (!methodId) return

    const method = paymentMethods.value.find(m => m.id == methodId)
    if (!method) return

    // Check if payment method already exists
    const exists = form.value.payments.some(p => p.payment_method_id == method.id)
    if (exists) {
        toast(t('Payment method already added'), { type: 'error' })
        return
    }

    form.value.payments.push({
        payment_method_id: method.id,
        name: method.name,
        amount: 0
    })
}

const removePaymentMethod = (index) => {
    form.value.payments.splice(index, 1)
}

const calculateNetSalary = (item) => {
    // Calculate overtime amount
    const overtimeAmount = parseFloat(item.overtime_rate || 0) * parseFloat(item.overtime_hour || 0)
    
    // Calculate total additions
    const totalAdditions = overtimeAmount + parseFloat(item.additional_amount || 0)
    
    // Calculate total deductions
    const totalDeductions = parseFloat(item.deduction_amount || 0) + 
                          parseFloat(item.absent_day_amount || 0) + 
                          parseFloat(item.advance_taken || 0)
    
    // Calculate net salary
    item.net_salary = parseFloat(item.salary_amount || 0) + totalAdditions - totalDeductions
    
    calculateTotalAmount()
}

const calculateTotalAmount = () => {
    form.value.total_amount = form.value.items.reduce((sum, item) => sum + parseFloat(item.net_salary || 0), 0)
}

const validateForm = () => {
    let isValid = true
    
    if (!validateYear(form.value.year)) isValid = false
    if (!validateMonth(form.value.month)) isValid = false
    if (!validateDate(form.value.generated_date)) isValid = false
    if (!validatePaymentMethod(form.value.payments)) isValid = false
    if (form.value.items.length == 0) {
        toast(t('At least one employee salary is required'), { type: 'error' })
        isValid = false
    }
    
    form.value.items.forEach(item => {
        if (!validateItem(item)) isValid = false
    })
    
    return isValid
}

const isConfirmDialogOpen = ref(false)
const selectedSalaryId = ref(null)

const openConfirmDialog = (index) => {
    isConfirmDialogOpen.value = true
    selectedSalaryId.value = index
}
const removeItem = () => {
    form.value.items.splice(selectedSalaryId, 1)
    calculateTotalAmount()
    isConfirmDialogOpen.value = false
    selectedSalaryId.value = null
}

const createSalary = async () => {
    loadings.value = true
    
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        // Format payments data
        const formattedPayments = form.value.payments.map(payment => ({
            payment_method_id: payment.payment_method_id,
            amount: payment.amount
        }))

        const payload = {
            ...form.value,
            payments: formattedPayments
        }

        const res = await $api('/salaries', {
            method: 'POST',
            body: JSON.stringify(payload),
        })

        if (!res || !res.success) {
            throw new Error(res?.message || t('Failed to create salary'))
        }

        toast(res.message || t('Salary created successfully'), {
            type: "success",
        })
        
        router.push({ name: 'salary' })
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
    } finally {
        loadings.value = false
    }
}
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Generate Salary')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createSalary">
                        <VRow>
                            <!-- Year -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    v-model="form.year"
                                    :label="t('Year')" :required="true"
                                    type="number"
                                    :error-messages="yearError"
                                />
                            </VCol>

                            <!-- Month -->
                            <VCol cols="12" md="6" lg="4">
                                <AppSelect
                                    v-model="form.month"
                                    :label="t('Month')" :required="true"
                                    :items="[
                                        {title: 'January', value: 1},
                                        {title: 'February', value: 2},
                                        {title: 'March', value: 3},
                                        {title: 'April', value: 4},
                                        {title: 'May', value: 5},
                                        {title: 'June', value: 6},
                                        {title: 'July', value: 7},
                                        {title: 'August', value: 8},
                                        {title: 'September', value: 9},
                                        {title: 'October', value: 10},
                                        {title: 'November', value: 11},
                                        {title: 'December', value: 12}
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="monthError"
                                />
                            </VCol>

                            <!-- Generated Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.generated_date"
                                    :label="t('Generated Date')" :required="true"
                                    :placeholder="t('Select date')" 
                                    :error-messages="dateError"
                                    @update:model-value="validateDate"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                />
                            </VCol>

                            <!-- Salary Items -->
                            <VCol cols="12">
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <h6 class="text-h6">{{ t('Employee Salaries') }} <span class="text-error">*</span></h6>
                                </div>

                                <VRow>
                                    <VCol v-for="(item, index) in form.items" 
                                         :key="item.employee_id"
                                         cols="12">
                                        <VCard>
                                            <VCardText>
                                                <div class="d-flex justify-space-between align-center mb-4">
                                                    <h6 class="text-h6">{{ item.employee_name }}</h6>
                                                    <VBtn
                                                        color="error"
                                                        icon
                                                        variant="text"
                                                        size="small"
                                                        @click="openConfirmDialog(index)"
                                                    >
                                                        <VIcon>tabler-trash</VIcon>
                                                    </VBtn>
                                                </div>

                                                <VRow>
                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="(item.salary_amount)"
                                                            :label="t('Salary Amount')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.overtime_rate"
                                                            :label="t('Overtime Rate')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.overtime_hour"
                                                            :label="t('Overtime Hour')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.additional_amount"
                                                            :label="t('Additional Amount')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.deduction_amount"
                                                            :label="t('Deduction Amount')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.absent_day"
                                                            :label="t('Absent Days')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.absent_day_amount"
                                                            :label="t('Absent Amount')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="6" sm="4" md="3" lg="2">
                                                        <AppTextField
                                                            v-model="item.advance_taken"
                                                            :label="t('Advance Taken')"
                                                            type="number"
                                                            density="compact"
                                                            hide-details
                                                            @input="calculateNetSalary(item)"
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="12" sm="8" md="6" lg="4">
                                                        <AppTextField
                                                            v-model="item.note"
                                                            :label="t('Note')"
                                                            type="text"
                                                            density="compact"
                                                            hide-details
                                                            @focus="$event.target.select()"
                                                        />
                                                    </VCol>

                                                    <VCol cols="12" sm="4" md="4" lg="4" class="d-flex align-end justify-space-between mb-1">
                                                        <div class="d-flex align-center justify-space-between">
                                                            <h3 class="font-weight-bold">{{ t('Net Salary') }}:</h3>
                                                            <span class="text-primary text-h6 ms-2">
                                                                {{ formatNumberPrecision(item.net_salary) }}
                                                            </span>
                                                        </div>
                                                    </VCol>
                                                </VRow>
                                            </VCardText>
                                        </VCard>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Total Amount -->
                            <VCol cols="12" md="6" lg="4" class="ms-auto">
                                <AppTextField 
                                    v-model="form.total_amount" 
                                    :label="t('Total Amount')" 
                                    type="number"
                                    readonly 
                                />
                            </VCol>
                        </VRow>
                        <VRow>
                            <!-- Payment Methods -->
                            <VCol cols="12" md="6" lg="4" class="ms-auto">
                                <AppAutocomplete
                                    :label="t('Add Payment Method')" :required="true"
                                    :items="paymentMethods"
                                    :error-messages="paymentMethodError"
                                    item-title="name"
                                    :placeholder="t('Select Payment Method')"
                                    item-value="id"
                                    @update:model-value="addPaymentMethod"
                                    clearable
                                />
                            </VCol>
                        </VRow>
                        
                        <VRow v-for="(payment, index) in form.payments" 
                        :key="payment.payment_method_id">
                            <VCol cols="12" md="6" lg="4" class="ms-auto">
                                <AppTextField
                                    v-model="payment.amount"
                                    :label="payment.name"
                                    type="number"
                                    density="compact"
                                    hide-details
                                    class="mb-2"
                                >
                                    <template #append>
                                        <VBtn
                                            color="error"
                                            icon
                                            variant="text"
                                            size="small"
                                            @click="removePaymentMethod(index)"
                                        >
                                            <VIcon>tabler-trash</VIcon>
                                        </VBtn>
                                    </template>
                                </AppTextField>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'salary' })" color="primary" variant="tonal">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset">
                                    <VIcon start icon="tabler-refresh" />
                                    {{ t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
            <ConfirmDialog v-model:is-dialog-visible="isConfirmDialogOpen"
            :confirmation-question="t('Are you sure you want to delete this salary?')" :confirm-title="t('Deleted!')"
            :confirm-msg="t('Salary has been deleted successfully.')" :cancel-title="t('Cancelled')"
            :cancel-msg="t('Salary Deletion Cancelled!')" @confirm="removeItem" />
        </VCol>
    </VRow>
</template>
