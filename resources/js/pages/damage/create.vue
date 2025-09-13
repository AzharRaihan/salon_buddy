<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';

const { formatNumberPrecision } = useCompanyFormatters()

const { t } = useI18n()
const router = useRouter()
const loadings = ref(false)
const employees = ref([])
const items = ref([])
const branch_info = useCookie("branch_info").value || 0;

const form = ref({
    reference_no: '',
    employee_id: null,
    date: '',
    total_loss: 0,
    note: '',
    items: [],
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const employeeIdError = ref('')
const dateError = ref('')
const totalLossError = ref('')
const itemErrors = ref({
    item_id: '',
    quantity: '',
    unit_price: ''
})

const validateReferenceNo = (referenceNo) => {
    if (!referenceNo) {
        referenceNoError.value = t('Reference no is required')
        return false
    }
    referenceNoError.value = ''
    return true
}

const validateEmployeeId = (employeeId) => {
    if (!employeeId) {
        employeeIdError.value = t('Employee is required')
        return false
    }
    employeeIdError.value = ''
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



const validateItem = (item) => {
    let isValid = true
    
    if (!item.item_id) {
        itemErrors.value.item_id = t('Item is required')
        isValid = false
    }
    
    if (!item.quantity || item.quantity <= 0) {
        itemErrors.value.quantity = t('Valid quantity is required')
        isValid = false
    }
    
    if (!item.unit_price || item.unit_price <= 0) {
        itemErrors.value.unit_price = t('Valid unit price is required')
        isValid = false
    }
    
    return isValid
}



// Fetch reference number and employees when component mounts
onMounted(async () => {
    try {
        const [refResponse, employeesResponse, itemsResponse] = await Promise.all([
            $api('/generate-damage-reference-no'),
            $api('/get-all-employees'),
            $api('/get-product-type-item-list')
        ])
        form.value.reference_no = refResponse.data
        employees.value = [...employeesResponse.data]
        items.value = [...itemsResponse.data]
    } catch (error) {
        console.error('Error fetching data:', error)
        toast('Failed to load data', {
            type: 'error'
        })
    }
})

const addItem = () => {
    const newItem = {
        item_id: null,
        quantity: 1,
        unit_price: 0,
        total: 0
    }
    
    form.value.items = [...form.value.items, newItem]
}

const removeItem = (index) => {
    form.value.items.splice(index, 1)
    calculateGrandTotal()
}

const calculateItemTotal = (index) => {
    const item = form.value.items[index]
    item.total = item.quantity * item.unit_price
    calculateGrandTotal()
}

const calculateGrandTotal = () => {
    form.value.total_loss = form.value.items.reduce((sum, item) => sum + item.total, 0)
}

const validateTotalLoss = (totalLoss) => {
    if (totalLoss < 0) {
        totalLossError.value = t('Total loss cannot be negative')
        return false
    }
    totalLossError.value = ''
    return true
}


const handleItemSelect = (index, itemId) => {
  const existingIndex = form.value.items.findIndex((item, i) => 
    i !== index && item.item_id == itemId
  )
  if (existingIndex !== -1) {
    toast(t('This item is already in the cart. Please update the existing entry.'), {
      type: 'error'
    })
    form.value.items[index].item_id = ''
    return
  }
  const selectedItem = items.value.find(i => i.id == itemId)
  if (selectedItem) {
    form.value.items[index].unit_price = selectedItem.purchase_price || 0
    calculateItemTotal(index)
  }
}




const resetForm = () => {
    const currentRefNo = form.value.reference_no
    form.value = {
        reference_no: currentRefNo,
        employee_id: null,
        date: '',
        total_loss: 0,
        note: '',
        items: []
    }
    referenceNoError.value = ''
    employeeIdError.value = ''
    dateError.value = ''
    totalLossError.value = ''
    itemErrors.value = {
        item_id: '',
        quantity: '',
        unit_price: ''
    }
}

const validateForm = () => {
    let isValid = true
    
    if (!validateReferenceNo(form.value.reference_no)) isValid = false
    if (!validateEmployeeId(form.value.employee_id)) isValid = false
    if (!validateDate(form.value.date)) isValid = false
    if (!validateTotalLoss(form.value.total_loss)) isValid = false
    
    if (form.value.items.length == 0) {
        toast(t('At least one item is required'), { type: 'error' })
        isValid = false
    }
    
    form.value.items.forEach(item => {
        if (!validateItem(item)) isValid = false
    })
    
    return isValid
}

const createPurchase = async () => {
    loadings.value = true
    
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        // Create FormData object
        const formData = new FormData()
        
        // Append all form fields
        Object.keys(form.value).forEach(key => {
            if (key == 'items') {
                if (form.value.items.length == 0) {
                    throw new Error('At least one item is required')
                }
                formData.append('items', JSON.stringify(form.value.items))
            } else if (form.value[key] !== null && form.value[key] !== undefined) {
                formData.append(key, form.value[key].toString())
            }
        })


        const res = await $api('/damages', {
            method: 'POST',
            body: formData,
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

        toast(res.message || t('Damage created successfully'), {
            type: "success",
        })
        
        resetForm()
        router.push({ name: 'damage' })

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
            <VCard :title="$t('Add Damage')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createPurchase">
                        <VRow>
                            <!-- Reference No -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.reference_no" :label="$t('Reference No')" :required="true" type="text" readonly
                                    :error-messages="referenceNoError" />
                            </VCol>

                            <VCol cols="12" md="6" lg="4" class="d-flex align-center">
                                <div class="flex-grow-1">
                                    <AppAutocomplete
                                        v-model="form.employee_id"
                                        :items="employees"
                                        :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                                        item-value="id"
                                        :label="t('Employee')" :required="true"
                                        :placeholder="t('Select Employee')"
                                        :error-messages="employeeIdError"
                                        clearable
                                    />
                                </div>
                            </VCol>

                            <!-- Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date"
                                    :label="$t('Date')" :required="true"
                                    :placeholder="$t('Select date')" 
                                    :error-messages="dateError"
                                    @update:model-value="validateDate"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                />
                            </VCol>
                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.note" :label="$t('Note')" type="text"
                                    :placeholder="$t('Enter note')" />
                            </VCol>

                            <!-- Damage Items -->
                            <VCol cols="12">
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <h6 class="text-h6">{{ $t('Damage Items') }} <span class="text-error">*</span></h6>
                                </div>

                                <VTable class="repeter-form">
                                    <thead>
                                        <tr>
                                            <th class="w-5">{{ $t('SN') }}</th>
                                            <th class="w-30">{{ $t('Item') }} <span class="text-error">*</span></th>
                                            <th class="w-20">{{ $t('Quantity') }} <span class="text-error">*</span></th>
                                            <th class="w-25">{{ $t('Unit Price') }} <span class="text-error">*</span></th>
                                            <th class="w-15">{{ $t('Total') }}</th>
                                            <th class="w-5">{{ $t('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <AppAutocomplete
                                                    v-model="item.item_id"
                                                    :items="items"
                                                    item-title="name"
                                                    item-value="id"
                                                    :placeholder="$t('Select Item')"
                                                    density="compact"
                                                    hide-details
                                                    :error-messages="itemErrors.item_id"
                                                    @update:model-value="handleItemSelect(index, $event)"
                                                    clearable
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="item.quantity"
                                                    type="number"
                                                    density="compact"
                                                    hide-details
                                                    :error-messages="itemErrors.quantity"
                                                    @input="calculateItemTotal(index)"
                                                    @focus="$event.target.select()"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="item.unit_price"
                                                    type="number"
                                                    density="compact"
                                                    hide-details
                                                    :error-messages="itemErrors.unit_price"
                                                    @input="calculateItemTotal(index)"
                                                    @focus="$event.target.select()"
                                                />
                                            </td>
                                            <td>{{ formatNumberPrecision(item.total) }}</td>
                                            <td>
                                                <VBtn
                                                    color="error"
                                                    icon
                                                    variant="text"
                                                    size="small"
                                                    @click="removeItem(index)"
                                                >
                                                    <VIcon>tabler-trash</VIcon>
                                                </VBtn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                                <div class="d-flex justify-end mt-4">
                                    <VBtn color="primary" variant="tonal" @click="addItem">
                                        <VIcon start icon="tabler-plus" />
                                        {{ $t('Add Item') }}
                                    </VBtn>
                                </div>
                            </VCol>

                            <!-- Grand Total -->
                            <VCol cols="12" md="6" lg="4" class="ms-auto">
                                <AppTextField :model-value="formatNumberPrecision(form.total_loss)" :label="$t('Total Loss')" type="number"
                                    :placeholder="$t('Grand total')" readonly />
                            </VCol>
         
                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'purchase' })" color="primary" variant="tonal">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-refresh" />
                                    {{ $t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>