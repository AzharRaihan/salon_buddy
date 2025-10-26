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
const suppliers = ref([])
const items = ref([])
const paymentMethods = ref([])
const branch_info = useCookie("branch_info").value || 0;

const form = ref({
    reference_no: '',
    supplier_invoice_no: '',
    supplier_id: null,
    date: '',
    grand_total: 0,
    paid_amount: 0,
    due_amount: 0,
    attachment: null,
    note: '',
    payment_method_id: null,
    items: [],
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const supplierIdError = ref('')
const dateError = ref('')
const paymentMethodIdError = ref('')
const paidAmountError = ref('')
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

const validateSupplierId = (supplierId) => {
    if (!supplierId) {
        supplierIdError.value = t('Supplier is required')
        return false
    }
    supplierIdError.value = ''
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

const validatePaidAmount = (paidAmount) => {
    if (paidAmount < 0) {
        paidAmountError.value = t('Paid amount cannot be negative')
        return false
    }
    if (paidAmount > form.value.grand_total) {
        paidAmountError.value = t('Paid amount cannot exceed grand total')
        return false
    }

    paidAmountError.value = ''
    return true
}

const validatePaymentMethodId = (paymentMethodId) => {
    if (form.value.paid_amount > 0 && !paymentMethodId) {
        paymentMethodIdError.value = t('Payment method is required when paid amount is greater than 0')
        return false
    }
    paymentMethodIdError.value = ''
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

// Watch for changes in paid_amount and grand_total
watch([() => form.value.paid_amount, () => form.value.grand_total], ([newPaidAmount, newGrandTotal]) => {
    if (validatePaidAmount(newPaidAmount)) {
        form.value.due_amount = parseFloat(newGrandTotal) - parseFloat(newPaidAmount)
    }
})

// Fetch reference number and suppliers when component mounts
onMounted(async () => {
    try {
        const [refResponse, suppliersResponse, itemsResponse, paymentMethodsResponse] = await Promise.all([
            $api('/generate-purchase-reference-no'),
            $api('/get-all-suppliers'),
            $api('/get-product-type-item-list'),
            $api('/get-all-payment-methods')
        ])
        form.value.reference_no = refResponse.data
        suppliers.value = [...suppliersResponse.data]
        items.value = [...itemsResponse.data]
        paymentMethods.value = [...paymentMethodsResponse.data]
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
    form.value.grand_total = form.value.items.reduce((sum, item) => sum + item.total, 0)
    // Recalculate due amount whenever grand total changes
    if (validatePaidAmount(form.value.paid_amount)) {
        form.value.due_amount = parseFloat(form.value.grand_total) - parseFloat(form.value.paid_amount)
    }
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


const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.value.attachment = file
    }
}

const resetForm = () => {
    const currentRefNo = form.value.reference_no
    form.value = {
        reference_no: currentRefNo,
        supplier_invoice_no: '',
        supplier_id: null,
        date: '',
        grand_total: 0,
        paid_amount: 0,
        due_amount: 0,
        attachment: null,
        note: '',
        payment_method_id: null,
        items: []
    }
    referenceNoError.value = ''
    supplierIdError.value = ''
    dateError.value = ''
    paymentMethodIdError.value = ''
    paidAmountError.value = ''
    itemErrors.value = {
        item_id: '',
        quantity: '',
        unit_price: ''
    }
}

const validateForm = () => {
    let isValid = true
    
    if (!validateReferenceNo(form.value.reference_no)) isValid = false
    if (!validateSupplierId(form.value.supplier_id)) isValid = false
    if (!validateDate(form.value.date)) isValid = false
    if (!validatePaidAmount(form.value.paid_amount)) isValid = false
    if (!validatePaymentMethodId(form.value.payment_method_id)) isValid = false
    
    if (form.value.items.length == 0) {
        toast(t('At least one item is required'), { type: 'error' })
        isValid = false
    }

    if (form.value.paid_amount > 0 && !form.value.payment_method_id) {
        toast(t('Payment method is required when paid amount is greater than 0'), { type: 'error' })
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
            } else if (key == 'attachment' && form.value[key]) {
                formData.append('attachment', form.value[key])
            } else if (form.value[key] !== null && form.value[key] !== undefined) {
                formData.append(key, form.value[key].toString())
            }
        })


        const res = await $api('/purchases', {
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

        toast(res.message || t('Purchase created successfully'), {
            type: "success",
        })
        
        resetForm()
        router.push({ name: 'purchase' })

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


const fetchSupplierList = async () => {
    try {
        const res = await $api('/get-all-suppliers')
        suppliers.value = res.data
    } catch (err) {
        console.error('Error fetching category list:', err)
        toast('Error fetching category list', {
            type: 'error'
        })
    }
}

// Add modal state and logic for category/unit
const showSupplierModal = ref(false)
const newSupplierName = ref('')
const newSupplierContactPerson = ref('')
const newSupplierPhone = ref('')
const newSupplierEmail = ref('')
const newSupplierAddress = ref('')

const newSupplierNameError = ref('')
const newSupplierContactPersonError = ref('')
const newSupplierPhoneError = ref('')
const newSupplierEmailError = ref('')
const newSupplierAddressError = ref('')

const validateNewSupplierName = (name) => {
    if (!name) {
        newSupplierNameError.value = t('Name is required')
        return false
    }
    newSupplierNameError.value = ''
    return true
}

const validateNewSupplierContactPerson = (contactPerson) => {
    if (!contactPerson) {
        newSupplierContactPersonError.value = t('Contact person is required')
        return false
    }
    newSupplierContactPersonError.value = ''
    return true
}

const validateNewSupplierPhone = (phone) => {
    if (!phone) {
        newSupplierPhoneError.value = t('Phone is required')
        return false
    }
    newSupplierPhoneError.value = ''
    return true
}

const validateNewSupplierEmail = (email) => {
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        newSupplierEmailError.value = t('Invalid email address')
        return false
    }
    newSupplierEmailError.value = ''
    return true
}

const validateNewSupplierAddress = (address) => {
    if (address && address.length > 255) {
        newSupplierAddressError.value = t('Address cannot exceed 255 characters')
        return false
    }
    newSupplierAddressError.value = ''
    return true
}

const validateSupplierForm = () => {
    let isValidSupplier = true
    
    if (!validateNewSupplierName(newSupplierName.value)) isValidSupplier = false
    if (!validateNewSupplierContactPerson(newSupplierContactPerson.value)) isValidSupplier = false
    if (!validateNewSupplierPhone(newSupplierPhone.value)) isValidSupplier = false
    if (!validateNewSupplierEmail(newSupplierEmail.value)) isValidSupplier = false
    if (!validateNewSupplierAddress(newSupplierAddress.value)) isValidSupplier = false
    return isValidSupplier
}
const addSupplier = async () => {
    if (!validateSupplierForm()) return
    try {

        const res = await $api('/suppliers', {
            method: 'POST',
            body: JSON.stringify({ name: newSupplierName.value, contact_person: newSupplierContactPerson.value, phone: newSupplierPhone.value, email: newSupplierEmail.value, address: newSupplierAddress.value }),
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

        await fetchSupplierList()
        const last = res.data
        form.value.supplier_id = last.id
        showSupplierModal.value = false
        newSupplierName.value = ''
        newSupplierContactPerson.value = ''
        newSupplierPhone.value = ''
        newSupplierEmail.value = ''
        newSupplierAddress.value = ''
        toast(t('Supplier added successfully'), { type: 'success' })

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
            <VCard :title="$t('Add Purchase')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createPurchase">
                        <VRow>
                            <!-- Reference No -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.reference_no" :label="$t('Reference No')" :required="true" type="text" readonly
                                    :error-messages="referenceNoError" />
                            </VCol>

                            <!-- Supplier Invoice No -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.supplier_invoice_no" :label="$t('Supplier Invoice No')" type="text"
                                    :placeholder="$t('Enter supplier invoice no')" />
                            </VCol>

                            <VCol cols="12" md="6" lg="4" class="d-flex align-center">
                                <div class="flex-grow-1">
                                    <AppAutocomplete
                                        v-model="form.supplier_id"
                                        :items="suppliers"
                                        :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                                        item-value="id"
                                        :label="t('Supplier')" :required="true"
                                        :placeholder="t('Select Supplier')"
                                        :error-messages="supplierIdError"
                                        clearable
                                    />
                                </div>
                                <div>
                                    <VBtn icon size="small" color="primary" :class="['ms-2', supplierIdError ? 'mt-minus-4-px' : 'mt-17-px']" @click="showSupplierModal = true">
                                        <VIcon icon="tabler-plus" />
                                    </VBtn>
                                </div>
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

                            <!-- Attachment -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    :label="$t('Attachment')" 
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx,.doc,.docx"
                                    @change="handleFileUpload"
                                />
                            </VCol>

                            <!-- Note -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.note" :label="$t('Note')" type="text"
                                    :placeholder="$t('Enter note')" />
                            </VCol>

                            <!-- Purchase Items -->
                            <VCol cols="12">
                                <div class="d-flex align-center justify-space-between mb-3">
                                    <h6 class="text-h6">{{ $t('Purchase Items') }} <span class="text-error">*</span></h6>
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
                                <AppTextField :model-value="formatNumberPrecision(form.grand_total)" :label="$t('Grand Total')" type="number"
                                    :placeholder="$t('Grand total')" readonly />

                                <AppTextField class="mt-4" v-model="form.paid_amount" :label="$t('Paid Amount')" type="number"
                                    :placeholder="$t('Enter paid amount')" :error-messages="paidAmountError" @focus="validatePaidAmount($event.target.select())" />
                                    
                                <AppTextField class="mt-4" :model-value="formatNumberPrecision(form.due_amount)" :label="$t('Due Amount')" type="number"
                                    :placeholder="$t('Due amount')" readonly />

                                <!-- Payment Method -->
                                <AppAutocomplete class="mt-4" v-model="form.payment_method_id" 
                                    :label="form.paid_amount > 0 ? $t('Payment Method') : $t('Payment Method')" :required="form.paid_amount > 0 ? true : false"
                                    :placeholder="$t('Select Payment Method')" :items="paymentMethods" item-title="name" item-value="id"
                                    :error-messages="paymentMethodIdError" @update:model-value="validatePaymentMethodId"
                                    clearable
                                    />
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
                        <!-- Supplier Modal -->
                        <VDialog v-model="showSupplierModal" class="supplier-modal">
                            <VCard class="modal-card modal-card-md">
                                <VCardTitle>
                                    {{ t('Add Supplier') }}
                                </VCardTitle>
                                <VCardText>
                                    <VRow>
                                        <VCol md="12" lg="6">
                                            <AppTextField v-model="newSupplierName" :label="t('Name')" :required="true" :placeholder="t('Enter Name')" :error-messages="newSupplierNameError" />
                                        </VCol>
                                        <VCol md="12" lg="6">
                                            <AppTextField v-model="newSupplierContactPerson" :label="t('Contact Person')" :required="true" :placeholder="t('Enter Contact Person')" :error-messages="newSupplierContactPersonError" />
                                        </VCol>
                                        <VCol md="12" lg="6">
                                            <AppTextField v-model="newSupplierPhone" :label="t('Phone')" :required="true" :placeholder="t('Enter Phone')" :error-messages="newSupplierPhoneError" />
                                        </VCol>
                                        <VCol md="12" lg="6">
                                            <AppTextField v-model="newSupplierEmail" :label="t('Email')" :placeholder="t('Enter Email')" :error-messages="newSupplierEmailError" />
                                        </VCol>
                                        <VCol md="12" lg="6">
                                            <AppTextarea v-model="newSupplierAddress" :label="t('Address')" :placeholder="t('Enter Address')" :error-messages="newSupplierAddressError" />
                                        </VCol>
                                    </VRow>
                                </VCardText>
                                <VCardActions>
                                    <VBtn color="primary" variant="tonal" @click="addSupplier">
                                        <VIcon start icon="tabler-checkbox" />
                                        {{ t('Submit') }}
                                    </VBtn>
                                    <VBtn color="error" variant="tonal" @click="showSupplierModal = false">
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
.supplier-modal {
    width: 800px;
}
@media screen and (max-width: 575.98px) {
    .supplier-modal {
        width: 95%;
    }
}
</style>