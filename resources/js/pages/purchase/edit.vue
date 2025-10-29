<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useCompanyFormatters } from '@/composables/useCompanyFormatters';
const { formatNumberPrecision } = useCompanyFormatters()


const { t } = useI18n();
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const suppliers = ref([])
const items = ref([])
const paymentMethods = ref([])
const branch_info = useCookie("branch_info").value || 0;
const previewModal = ref(false)
const previewUrl = ref('')
const isImage = ref(false)
const selectedAccountBalance = ref(0)
const isInitialLoad = ref(true)


const form = ref({
    reference_no: '',
    supplier_invoice_no: '',
    supplier_id: null,
    date: '',
    grand_total: 0,
    paid_amount: 0,
    old_paid_amount: 0,
    old_payment_method_id: null,
    due_amount: 0,
    attachment: null,
    attachment_url: null,
    note: null,
    payment_method_id: null,
    items: [],
    branch_id: branch_info.id
})

const referenceNoError = ref('')
const supplierIdError = ref('')
const dateError = ref('')
const paymentMethodIdError = ref('')
const paidAmountError = ref('')
const attachmentError = ref('')
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

    if(Number(form.value.old_paid_amount) >= Number(paidAmount) && Number(form.value.old_payment_method_id) == Number(form.value.payment_method_id)) {
        paidAmountError.value = ''
        return true
    }

    if (form.value.payment_method_id && paidAmount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(paidAmount) > bal) {
            paidAmountError.value = t(`Insufficient balance. Remaining balance is: ${formatNumberPrecision(bal)}, Previous Paid Amount: ${formatNumberPrecision(form.value.old_paid_amount)}`)
            return false
        }
    }

    paidAmountError.value = ''
    return true
}

const validatePaymentMethodId = (paymentMethodId) => {
    if (form.value.paid_amount > 0 && !paymentMethodId) {
        paymentMethodIdError.value = t('Payment account is required when paid amount is greater than 0')
        return false
    }
    paymentMethodIdError.value = ''
    return true
}

const validateAttachment = (file) => {
    if (!file) return true // Skip validation if no file

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    
    if (!allowedTypes.includes(file.type)) {
        attachmentError.value = t('File must be jpg, jpeg, png, pdf, xls, xlsx, doc or docx')
        return false
    }

    attachmentError.value = ''
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


watch([() => form.value.paid_amount, () => form.value.grand_total], ([newPaidAmount, newGrandTotal]) => {

    if(Number(form.value.old_paid_amount) >= Number(newPaidAmount) && Number(form.value.old_payment_method_id) == Number(form.value.payment_method_id)) {
        paidAmountError.value = ''
        return true
    }

    if (!isInitialLoad.value && form.value.payment_method_id && newPaidAmount > 0) {
        const bal = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(newPaidAmount) > bal) {
            paidAmountError.value = t(`Insufficient balance. Remaining balance is: ${formatNumberPrecision(bal)}, Previous Paid Amount: ${formatNumberPrecision(form.value.old_paid_amount)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatNumberPrecision(bal)}, Previous Paid Amount: ${formatNumberPrecision(form.value.old_paid_amount)}`), { type: 'error' })
        } else {
            paidAmountError.value = ''
        }
    }
    form.value.due_amount = parseFloat(newGrandTotal) - parseFloat(newPaidAmount)
})

watch(() => form.value.payment_method_id, (newMethodId) => {
    if (!newMethodId) {
        selectedAccountBalance.value = 0
        if (!isInitialLoad.value) paidAmountError.value = ''
        return
    }
    const method = paymentMethods.value.find(m => m.id == newMethodId)
    if (!method) {
        selectedAccountBalance.value = 0
        if (!isInitialLoad.value) paidAmountError.value = ''
        return
    }

    if(Number(form.value.old_paid_amount) >= Number(form.value.paid_amount) && Number(form.value.old_payment_method_id) == Number(form.value.payment_method_id)) {
        paidAmountError.value = ''
        return true
    }
    

    const bal = method.account_blanace
    // coerce to number when possible
    selectedAccountBalance.value = bal ? parseFloat(bal) : 0
    
    // Validate paid amount if already entered (but not on initial load)
    if (!isInitialLoad.value && form.value.paid_amount > 0) {
        const currentBalance = parseFloat(selectedAccountBalance.value) || 0
        if (parseFloat(form.value.paid_amount) > currentBalance) {
            paidAmountError.value = t(`Insufficient balance. Remaining balance is: ${formatNumberPrecision(currentBalance)}, Previous Paid Amount: ${formatNumberPrecision(form.value.old_paid_amount)}`)
            toast(t(`Insufficient balance. Remaining balance is: ${formatNumberPrecision(currentBalance)}, Previous Paid Amount: ${formatNumberPrecision(form.value.old_paid_amount)}`), { type: 'error' })
        } else {
            paidAmountError.value = ''
        }
    }
})


watch(previewModal, (isOpen) => {
    if (!isOpen && previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = ''
    }
})


const previewAttachment = () => {
  const fileUrl = form.value.attachment_url;
  console.log('File URL:', fileUrl);

  if (!fileUrl || typeof fileUrl !== 'string') return;

  // Get the file extension
  const extension = fileUrl.split('.').pop().toLowerCase();
  const imageExtensions = ['jpg', 'jpeg', 'png'];

  // Set isImage based on extension
  isImage.value = imageExtensions.includes(extension);

  // Just use the URL directly — do NOT use createObjectURL
  previewUrl.value = fileUrl;
  previewModal.value = true;
};


// Fetch purchase data and other required data when component mounts
onMounted(async () => {
    try {
        // Check if route.query.id exists
        if (!route.query.id) {
            throw new Error(t('Purchase ID is missing'))
        }

        const [purchaseResponse, suppliersResponse, itemsResponse, paymentMethodsResponse] = await Promise.all([
            $api(`/purchases/${route.query.id}`),
            $api('/get-all-suppliers'),
            $api('/get-product-type-item-list'),
            $api('/get-all-payment-methods')
        ])

        // Check if responses are valid
        if (!purchaseResponse?.data) {
            throw new Error(t('Invalid purchase data received'))
        }

        const purchase = purchaseResponse.data
        form.value = {
            reference_no: purchase.reference_no,
            supplier_invoice_no: purchase.supplier_invoice_no,
            supplier_id: purchase.supplier?.id,
            date: purchase.date,
            grand_total: parseFloat(purchase.grand_total),
            paid_amount: parseFloat(purchase.paid_amount),
            old_paid_amount: parseFloat(purchase.paid_amount),
            due_amount: parseFloat(purchase.due_amount),
            attachment: purchase.attachment,
            attachment_url: purchase.attachment_url,
            note: purchase.note ?? '',
            payment_method_id: purchase.payment_method?.id,
            old_payment_method_id: purchase.payment_method?.id,
            branch_id: branch_info.id,
            items: purchase.purchase_details?.map(item => ({
                item_id: item.item?.id,
                quantity: Number(item.quantity),
                unit_price: parseFloat(item.unit_price),
                total: parseFloat(item.total_price)
            })) || []
        }

        suppliers.value = [...suppliersResponse.data]
        items.value = [...itemsResponse.data]
        paymentMethods.value = [...paymentMethodsResponse.data]

        // Set initial load to false after data is loaded
        setTimeout(() => {
            isInitialLoad.value = false
        }, 100)

    } catch (error) {
        console.error('Error fetching data:', error)
        toast(error.message || 'Failed to load data', {
            type: 'error'
        })
        router.push({ name: 'purchase' })
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
  const qty = Number(item.quantity) || 0
  const price = Number(item.unit_price) || 0
  form.value.items[index] = {
    ...item,
    total: qty * price,
  }
  calculateGrandTotal()
}

const calculateGrandTotal = () => {
    form.value.grand_total = form.value.items.reduce((sum, item) => sum + parseFloat(item.total), 0)
    // Recalculate due amount whenever grand total changes
    if (validatePaidAmount(form.value.paid_amount)) {
        form.value.due_amount = parseFloat(form.value.grand_total) - parseFloat(form.value.paid_amount)
    }
}

const handleItemSelect = (index, itemId) => {
  const existingIndex = form.value.items.findIndex((item, i) => 
    i !== index && item.item_id == itemId // allow number/string match
  )
  if (existingIndex !== -1) {
    toast(t('This item is already in the cart. Please update the existing entry.'), { type: 'error' })
    form.value.items[index].item_id = ''
    return
  }
  const selectedItem = items.value.find(i => i.id == itemId)
  if (selectedItem) {
    form.value.items[index].unit_price = Number(selectedItem.purchase_price) || 0
    calculateItemTotal(index)
    calculateGrandTotal()
  }
}



const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (validateAttachment(file)) {
        form.value.attachment = file
    } else {
        event.target.value = '' // Clear the file input
        form.value.attachment = null
    }
}

const resetForm = () => {
    router.push({ name: 'purchase' })
}

const validateForm = () => {
    let isValid = true
    
    if (!validateReferenceNo(form.value.reference_no)) isValid = false
    if (!validateSupplierId(form.value.supplier_id)) isValid = false
    if (!validateDate(form.value.date)) isValid = false
    if (!validatePaymentMethodId(form.value.payment_method_id)) isValid = false
    if (!validatePaidAmount(form.value.paid_amount)) isValid = false
    
    // Only validate attachment if one is provided
    if (form.value.attachment && !validateAttachment(form.value.attachment)) {
        isValid = false
    }
    
    if (form.value.items.length == 0) {
        toast('At least one item is required', { type: 'error' })
        isValid = false
    }
    
    form.value.items.forEach(item => {
        if (!validateItem(item)) isValid = false
    })
    
    return isValid
}

const updatePurchase = async () => {
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
                formData.append('items', JSON.stringify(form.value.items))
            } else if (key == 'attachment') {
                // Only append attachment if a new file is selected
                if (form.value[key] instanceof File) {
                    formData.append('attachment', form.value[key])
                }
            } else {
                // Only append if value is not null
                if (form.value[key] !== null) {
                    formData.append(key, form.value[key])
                }
            }
        })

        // Add _method field for PUT request
        formData.append('_method', 'PUT')

        const res = await $api(`/purchases/${route.query.id}`, {
            method: 'POST', // Actually sends as PUT due to _method field
            body: formData,
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                return Promise.reject(response._data)
            },
        })

        const { status, message } = res

        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            return
        }

        toast(message, {
            type: "success",
        })
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
    }
    finally {
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
            <VCard :title="$t('Edit Purchase')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updatePurchase">
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
                                    @update:model-value="validateDate"
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
                                    :error-messages="attachmentError"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" v-if="form.attachment" class="mt-17-px">
                                <VBtn color="primary" @click="previewAttachment">
                                    {{ $t('View Attachment') }}
                                </VBtn>
                            </VCol>

                            

                            <!-- Note -->
                            <VCol cols="12">
                                <AppTextField v-model="form.note" :label="$t('Note')" type="text"
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

                                <!-- Payment Account -->
                                <AppAutocomplete class="mt-4" v-model="form.payment_method_id" 
                                    :label="form.paid_amount > 0 ? $t('Payment Account') : $t('Payment Account')" :required="form.paid_amount > 0"
                                    :placeholder="$t('Select Payment Account')" :items="paymentMethods" item-title="name" item-value="id"
                                    :error-messages="paymentMethodIdError" @update:model-value="validatePaymentMethodId"
                                    clearable
                                    />
                            </VCol>
         
                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" @click="router.push({ name: 'purchase' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
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


                        <!-- Attachment Preview Modal -->
                        <VDialog v-model="previewModal" max-width="600px">
                            <VCard>
                                <VCardTitle>{{ t('Attachment Preview') }}</VCardTitle>
                                <VCardText>
                                    <div v-if="isImage">
                                        <img :src="previewUrl" alt="Preview" style="max-width: 100%;" />
                                    </div>
                                    <div v-else>
                                        <a :href="previewUrl" download target="_blank">
                                        Click here to download the file
                                        </a>
                                    </div>
                                </VCardText>
                                <VCardActions>
                                <VSpacer />
                                <VBtn color="error" variant="tonal" @click="previewModal = false">{{ t('Close') }}</VBtn>
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