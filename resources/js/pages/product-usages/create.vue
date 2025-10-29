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
const items = ref([])
const employees = ref([])
const branch_info = useCookie("branch_info").value || 0;

const form = ref({
    reference_no: '',
    date: '',
    note: '',
    items: [],
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const dateError = ref('')
const itemErrors = ref({
    item_id: '',
    quantity: '',
    note: '',
    employee_id: ''
})

const validateReferenceNo = (referenceNo) => {
    if (!referenceNo) {
        referenceNoError.value = t('Reference no is required')
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

    if (item.note.length > 255) {
        itemErrors.value.note = t('Note must be less than 255 characters')
        isValid = false
    }


    if (!item.employee_id) {
        itemErrors.value.employee_id = t('Employee is required')
        isValid = false
    }
    
    return isValid
}

// Fetch reference number and data when component mounts
onMounted(async () => {
    try {
        const [refResponse, itemsResponse, employeesResponse] = await Promise.all([
            $api('/generate-product-usage-reference-no'),
            $api('/get-product-type-item-list'),
            $api('/get-all-employees')
        ])
        form.value.reference_no = refResponse.data
        items.value = [...itemsResponse.data]
        employees.value = [...employeesResponse.data]
    } catch (error) {
        console.error('Error fetching data:', error)
        toast(t('Failed to load data'), {
            type: 'error'
        })
    }
})

const addItem = () => {
    const newItem = {
        item_id: null,
        quantity: 1,
        note: '',
        employee_id: null,
    }
    
    form.value.items = [...form.value.items, newItem]
}

const removeItem = (index) => {
    form.value.items.splice(index, 1)
}

const handleItemSelect = (index, itemId) => {
    const existingIndex = form.value.items.findIndex((item, i) => 
        i !== index && item.item_id == itemId
    )
    if (existingIndex !== -1) {
        toast(t('This item is already in the list. Please update the existing entry.'), {
            type: 'error'
        })
        form.value.items[index].item_id = ''
        return
    }
    const selectedItem = items.value.find(i => i.id == itemId)
}

const resetForm = () => {
    const currentRefNo = form.value.reference_no
    form.value = {
        reference_no: currentRefNo,
        date: '',
        note: '',
        items: []
    }
    referenceNoError.value = ''
    dateError.value = ''
    itemErrors.value = {
        item_id: '',
        quantity: '',
        note: '',
        employee_id: ''
    }
}

const validateForm = () => {
    let isValid = true
    
    if (!validateReferenceNo(form.value.reference_no)) isValid = false
    if (!validateDate(form.value.date)) isValid = false
    
    if (form.value.items.length == 0) {
        toast(t('At least one item is required'), { type: 'error' })
        isValid = false
    }
    
    form.value.items.forEach(item => {
        if (!validateItem(item)) isValid = false
    })
    
    return isValid
}

const createUsage = async () => {
    loadings.value = true
    
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const payload = {
            reference_no: form.value.reference_no,
            date: form.value.date,
            note: form.value.note,
            branch_id: form.value.branch_id,
            items: JSON.stringify(form.value.items)
        }

        const res = await $api('/product-usages', {
            method: 'POST',
            body: JSON.stringify(payload),
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

        toast(res.message || t('Product usage created successfully'), {
            type: "success",
        })
        
        resetForm()
        router.push({ name: 'product-usages' })

    } catch (err) {
        if (err.errors) {
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
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
            <VCard :title="$t('Add Product Usage')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createUsage">
                        <VRow>
                            <!-- Reference No -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.reference_no" :label="$t('Reference No')" :required="true" type="text" readonly
                                    :error-messages="referenceNoError" />
                            </VCol>

                            <!-- Date -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date"
                                    :label="$t('Date')" :required="true"
                                    :placeholder="$t('Select date')" 
                                    :error-messages="dateError"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12">
                                <AppTextField v-model="form.note" :label="$t('Note')" type="text"
                                    :placeholder="$t('Enter note')" />
                            </VCol>

                            <!-- Usage Items -->
                            <VCol cols="12">
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <h6 class="text-h6">{{ $t('Usage Items') }} <span class="text-error">*</span></h6>
                                </div>

                                <VTable class="repeter-form">
                                    <thead>
                                        <tr>
                                            <th class="w-5">{{ $t('SN') }}</th>
                                            <th class="w-25">{{ $t('Item') }} <span class="text-error">*</span></th>
                                            <th class="w-15">{{ $t('Quantity') }} <span class="text-error">*</span></th>
                                            <th class="w-20">{{ $t('Employee') }} <span class="text-error">*</span></th>
                                            <th class="w-15">{{ $t('Note') }}</th>
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
                                                />
                                            </td>
                                            <td>
                                                <AppAutocomplete
                                                    v-model="item.employee_id"
                                                    :items="employees"
                                                    item-title="name"
                                                    item-value="id"
                                                    :placeholder="$t('Select Employee')"
                                                    density="compact"
                                                    hide-details
                                                    :error-messages="itemErrors.employee_id"
                                                    clearable
                                                />
                                            </td>
                                            <td>
                                                <AppTextarea
                                                    v-model="item.note"
                                                    type="text"
                                                    density="compact"
                                                    hide-details
                                                    :error-messages="itemErrors.note"
                                                />
                                            </td>
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
         
                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'product-usages' })" color="primary" variant="tonal">
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

