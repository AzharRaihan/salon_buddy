<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, computed } from 'vue';
import defaultAvater from '@images/system-config/default-picture.png';
import DemoEditorCustomEditor from '@core/components/editor/DemoEditorCustomEditor.vue';
import { useI18n } from 'vue-i18n';

// Image Cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
const showCropperModal = ref(false)
const imageSrc = ref(null)
const croppedImage = ref(null) 
const cropPreview = ref(null)
const refInputEl = ref()
const previewImage = ref(defaultAvater)
let cropperRef = null
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
const MAX_FILE_SIZE = 1 * 1024 * 1024 // 1MB
const MIN_WIDTH = 250
const MIN_HEIGHT = 250
function onCrop({ canvas }) {
  cropperRef = canvas
  cropPreview.value = canvas.toDataURL()
}
function getCroppedImage() {
  if (cropperRef) {
    croppedImage.value = cropperRef.toDataURL()
    previewImage.value = croppedImage.value
    form.value.photo = convertDataURLtoFile(croppedImage.value, 'cropped.jpg')
    showCropperModal.value = false
  }
}
function convertDataURLtoFile(dataURL, filename) {
  const arr = dataURL.split(',')
  const mime = arr[0].match(/:(.*?);/)[1]
  const bstr = atob(arr[1])
  let  n = bstr.length
  const u8arr = new Uint8Array(n)
  while(n--) {
    u8arr[n] = bstr.charCodeAt(n)
  }
  return new File([u8arr], filename, {type: mime})
}
function cancelCrop() {
  showCropperModal.value = false
}
function validateImageFile(file) {
  if (!ALLOWED_TYPES.includes(file.type)) {
    toast(t('Only JPEG, JPG, PNG, and WEBP formats are allowed.'), { type: 'error' })
    return false
  }
  if (file.size > MAX_FILE_SIZE) {
    toast(t('File size must be less than or equal to 1 MB.'), { type: 'error' })
    return false
  }
  return true
}

function validateImageDimensions(img) {
    return true;
  if (img.width < MIN_WIDTH || img.height < MIN_HEIGHT) {
    toast(t(`Image dimensions must be at least ${MIN_WIDTH}px × ${MIN_HEIGHT}px.`), { type: 'error' })
    return false
  }
  return true
}
const changeImage = (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (!validateImageFile(file)) {
    event.target.value = ''
    return
  }
  const reader = new FileReader()
  reader.onload = e => {
    const img = new Image()
    img.onload = () => {
      if (!validateImageDimensions(img)) {
        event.target.value = ''
        return
      }
      imageSrc.value = e.target.result
      showCropperModal.value = true
    }
    img.onerror = () => {
      toast(t('Invalid image file.'), { type: 'error' })
      event.target.value = ''
    }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}
const resetImage = () => {
  form.value.photo = defaultAvater
  previewImage.value = defaultAvater
  if (refInputEl.value) {
    refInputEl.value.value = ''
  }
  imageSrc.value = null
  showCropperModal.value = false
}
// Cropper End
    
const { t } = useI18n();
const router = useRouter()
const route = useRoute()
const loadings = ref(false)
const editorContent = ref('')
const subtotal = ref(0)
const packageTotal = ref(0)
const packageDiscount = ref(0)

const form = ref({
    name: '',
    code: '',
    type: null,
    duration: 0,
    duration_type: null,
    purchase_price: '',
    sale_price: '',
    profit_margin: '',
    loyalty_point: '',
    description: '',
    status: null,
    photo: defaultAvater,
    item_id: null,
    category_id: null,
    supplier_id: null,
    unit_id: null,
    taxes: [],
    subtotal: 0,
    package_total: 0,
    package_discount: 0
})

const itemRows = ref([])
const nameError = ref('')
const codeError = ref('')
const typeError = ref('')
const durationError = ref('')
const durationTypeError = ref('')
const purchasePriceError = ref('')
const salePriceError = ref('')
const profitMarginError = ref('')
const loyaltyPointError = ref('')
const descriptionError = ref('')
const statusError = ref('')
const categoryError = ref('')
const unitError = ref('')
const rowErrors = ref([])
const unitList = ref([])
const categoryList = ref([])
const itemList = ref([])
const supplierList = ref([])

const fetchItemDetails = async () => {
    try {
        const res = await $api(`/items/${route.query.id}`)
        const item = res.data.item

        // Populate form with item details
        form.value = {
            name: item.name,
            code: item.code,
            type: item.type,
            duration: item.duration ?? 0,
            duration_type: item.duration_type ?? null,
            purchase_price: item.purchase_price ?? 0,
            sale_price: item.sale_price ?? 0,
            profit_margin: item.profit_margin ?? 0,
            loyalty_point: item.loyalty_point ?? 0,
            description: item.description || '',
            status: item.status,
            photo: item.photo || defaultAvater,
            category_id: item.category?.id,
            supplier_id: item.supplier?.id,
            unit_id: item.unit?.id,
            taxes: item.tax_information ? JSON.parse(item.tax_information) : [],
            subtotal: item.subtotal ?? 0,
            package_total: item.package_total ?? 0,
            package_discount: item.package_discount ?? 0
        }

        // Set preview image
        previewImage.value = item.photo_url || defaultAvater
        imageSrc.value = item.photo_url || defaultAvater
        
        // Set editor content
        editorContent.value = item.description || ''

        // Populate item rows if service or package
        if (item.item_details && item.item_details.length > 0) {
            itemRows.value = item.item_details.map(row => {
                let mappedRow;
                if (item.type == 'Package') {
                    mappedRow = {
                        item_id: row.items?.id,
                        itemName: row.items.name,
                        price: row.price,
                        quantity: row.quantity,
                        discount: row.discount,
                        total_price: row.total_price
                    }
                    subtotal.value += parseFloat(mappedRow.total_price)
                    packageTotal.value += (parseFloat(mappedRow.price) * parseFloat(mappedRow.quantity) ) 
                    packageDiscount.value += parseFloat(mappedRow.discount)
                    return mappedRow
                }
            })
            // Initialize row errors
            rowErrors.value = itemRows.value.map(() => ({}))
        }

    } catch (err) {
        console.error('Error fetching item details:', err)
        toast(t('Error fetching item details'), {
            type: 'error'
        })
    }
}

const fetchTaxDetails = async () => {
    try {
        const res = await $api('/get-tax-details')
        const taxInformation = res.data
        form.value.taxes = taxInformation;
    } catch (err) {
        console.error('Error fetching tax details:', err)
        toast('Error fetching tax details', {
            type: 'error'
        })
    }
}

const fetchUnitList = async () => {
    try {
        const res = await $api('/get-unit-list')
        unitList.value = res.data.map(unit => ({
            id: unit.id,
            name: unit.name
        }))
    } catch (err) {
        console.error('Error fetching unit list:', err)
        toast('Error fetching unit list', {
            type: 'error'
        })
    }
}

const fetchCategoryList = async () => {
    try {
        if(form.value.type == 'Service') {
            const res = await $api('/get-service-category-list')
            categoryList.value = res.data
        
        } else if(form.value.type == 'Product') {
            const res = await $api('/get-product-category-list')
            categoryList.value = res.data
        } else {
            const res = await $api('/get-category-list')
            categoryList.value = res.data
        }
    } catch (err) {
        console.error('Error fetching category list:', err)
        toast('Error fetching category list', {
            type: 'error'
        })
    }
}
const fetchItemList = async () => {
    try {
        const res = await $api('/get-item-and-service-list')
        itemList.value = res.data
    } catch (err) {
        console.error('Error fetching item list:', err)
        toast('Error fetching item list', {
            type: 'error'
        })
    }
}
const fetchSupplierList = async () => {
    try {
        const res = await $api('/get-all-suppliers')
        supplierList.value = res.data
    } catch (err) {
        console.error('Error fetching supplier list:', err)
        toast('Error fetching supplier list', {
            type: 'error'
        })
    }
}

const fetchServiceProductItems = async () => {
  try {
    const res = await $api('/get-product-type-item-list')
    itemList.value = res.data
  } catch (err) {
    console.error('Error fetching service product items:', err)
    toast('Error fetching service product items', {
      type: 'error'
    })
  }
}
watch(
  () => [form.value.type],
  async ([type]) => {
    if (type == 'Package') {
      await fetchItemList() // this already points to /get-item-and-service-list
    } else {
      itemList.value = [] // clear the list when type is not relevant
    }
  },
  { immediate: true }
)

onMounted(() => {
    fetchItemDetails()
    fetchUnitList()
    fetchCategoryList()
    fetchItemList()
    // fetchTaxDetails()
    fetchSupplierList()
})

// Watch for changes in item_id and add row to table
const handleItemChange = async () => {
    if (!form.value.item_id) return

    const selectedItem = itemList.value.find(i => i.id == form.value.item_id)
    if (!selectedItem) return

    // Check if item already exists in table
    const existingItem = itemRows.value.find(row => row.item_id == selectedItem.id)
    if (existingItem) {
        toast('This item is already added', {
            type: 'error'
        })
        form.value.item_id = null
        return
    }

    if (form.value.type == 'Package') {
        const salePrice = Number((parseFloat(selectedItem.sale_price) || 0).toFixed(3))
        itemRows.value.push({
            item_id: selectedItem.id,
            itemName: selectedItem.name,
            price: salePrice,
            quantity: 1,
            discount: 0,
            total_price: salePrice
        })
        rowErrors.value.push({
            price: '',
            quantity: ''
        })
    }

    form.value.item_id = null
    calculateTotal()
}

const calculateTotal = () => {
    let totalSum = 0
    let packageTotalVal = 0
    let packageDiscountVal = 0
    
    itemRows.value.forEach((row, index) => {
        if (form.value.type == 'Package') {
            row.total_price = Number(((row.price * row.quantity) - (row.discount || 0)).toFixed(3))
            packageTotalVal += Number(((row.price * row.quantity)).toFixed(3))
            packageDiscountVal += Number(row.discount || 0)
        }
        totalSum += row.total_price

    })
    subtotal.value = parseFloat(totalSum.toFixed(3))
    form.value.subtotal = subtotal.value

    packageTotal.value = parseFloat(packageTotalVal.toFixed(3))
    form.value.package_total = packageTotal.value

    packageDiscount.value = parseFloat(packageDiscountVal.toFixed(3))
    form.value.package_discount = packageDiscount.value

    // Corrected formula for Product
    if (form.value.type == 'Product') {
        if (form.value.profit_margin) {
            const profitMargin = parseFloat(form.value.profit_margin) / 100;
            form.value.sale_price = Number((parseFloat(form.value.purchase_price) * (1 + profitMargin)).toFixed(3));
        } else {
            form.value.sale_price = Number(form.value.purchase_price)
        }
    
    } else if (form.value.type == 'Package') {
        if (form.value.profit_margin) {
            const profitMargin = parseFloat(form.value.profit_margin) / 100;
            form.value.sale_price = Number((parseFloat(form.value.subtotal) * (1 + profitMargin)).toFixed(3));
        } else {
            form.value.sale_price = Number(totalSum.toFixed(3))
        }
    } else if (form.value.type == 'Service') {
        if (form.value.profit_margin && form.value.purchase_price) {
            const profitMargin = parseFloat(form.value.profit_margin) / 100;
            form.value.sale_price = Number((parseFloat(form.value.purchase_price) * (1 + profitMargin)).toFixed(3));
        }
    }
}

const displayPackageTotal = computed(() => packageTotal.value.toFixed(3))
const displayPackageDiscount = computed(() => packageDiscount.value.toFixed(3))
const displaySubtotal = computed(() => subtotal.value.toFixed(3))

const removeItemRow = (index) => {
    itemRows.value.splice(index, 1)
    rowErrors.value.splice(index, 1)
    calculateTotal()
}

const validateForm = () => {
    let isValid = true
    nameError.value = form.value.name ? '' : t('Name is required')
    typeError.value = form.value.type ? '' : t('Type is required')
    categoryError.value = form.value.category_id ? '' : t('Category is required')
    statusError.value = form.value.status ? '' : t('Status is required')
    if (form.value.type == 'Product') {
        unitError.value = form.value.unit_id ? '' : t('Unit is required')
        purchasePriceError.value = form.value.purchase_price ? '' : t('Purchase price is required')
        salePriceError.value = form.value.sale_price ? '' : t('Sale price is required')
    }
    if (form.value.type == 'Service') {
        salePriceError.value = form.value.sale_price ? '' : t('Sale price is required')
    }

    if (form.value.type == 'Package') {
        salePriceError.value = form.value.sale_price ? '' : t('Sale price is required')

        if (itemRows.value.length == 0) {
            toast(t('Please add at least one item for the package'), {
                type: 'error'
            })
            isValid = false
        }
    }
    itemRows.value.forEach((row, index) => {
        if (form.value.type == 'Package') {
            rowErrors.value[index] = {
                price: row.price ? '' : t('Required'),
                quantity: row.quantity ? '' : t('Required')
            }
        }
    })
    if (nameError.value || typeError.value || categoryError.value || statusError.value || 
        unitError.value || purchasePriceError.value || salePriceError.value) {
        isValid = false
    }
    if (rowErrors.value.some(error => Object.values(error).some(e => e))) {
        isValid = false
    }
    return isValid
}

const updateItem = async () => {
    if (!validateForm()) {
        toast(t('Please fill all required fields'), {
            type: 'error'
        })
        return
    }

    try {
        loadings.value = true
        const formData = new FormData()
        
        // Append basic form fields
        Object.keys(form.value).forEach(key => {
            if (key == 'photo') {
                if (form.value.photo instanceof File) {
                    formData.append('photo', form.value.photo)
                }
            } else if (key == 'taxes') {
                formData.append('taxes', JSON.stringify(form.value.taxes))
            } else if (key == 'description') {
                formData.append('description', form.value.description)
            } else {
                formData.append(key, form.value[key])
            }
        })

        // Append item rows
        formData.append('itemRows', JSON.stringify(itemRows.value))

        const response = await $api(`/items/${route.query.id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                loadings.value = false
                return Promise.reject(response._data)
            },
        })
        const { status, message } = response


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
        router.push({ name: 'item' })

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

const handleEditorInput = (content) => {
    const htmlContent = content.target?.innerHTML || content
    editorContent.value = htmlContent
    form.value.description = htmlContent
}


// Computed properties for conditional rendering
const isItemType = computed(() => form.value.type == 'Product')
const isServiceType = computed(() => form.value.type == 'Service')
const isPackageType = computed(() => form.value.type == 'Package')
// const showUseConsumption = computed(() => form.value.type == 'Service')
const showItemRepeater = computed(() => {
    return form.value.type == 'Package'
})

// Add modal state and logic for category/unit
const showCategoryModal = ref(false)
const showUnitModal = ref(false)
const newCategoryName = ref('')
const newUnitName = ref('')
const newCategoryType = ref(null)     

const addCategory = async () => {
    if (!newCategoryType.value) {
        toast(t('Category Type is required'), { type: 'error' })
    }
    if (!newCategoryName.value) {
        toast(t('Category Name is required'), { type: 'error' })
    }
    if (!newCategoryType.value || !newCategoryName.value) {
        return
    }
    try {
        const res = await $api('/categories', {
            method: 'POST',
            body: JSON.stringify({ name: newCategoryName.value, status: 'Enabled', type: newCategoryType.value }),
            headers: { 'Content-Type': 'application/json' },
        })
        
        if (res.success) {
            await fetchCategoryList()
            // Find the last added category and select it
            const last = res.data
            form.value.category_id = last.id
            showCategoryModal.value = false
            newCategoryName.value = ''
            toast(t('Category added successfully'), { type: 'success' })
        } else {
            toast(res.message || t('Failed to add category'), { type: 'error' })
        }
    } catch (err) {
        toast(t('Failed to add category'), { type: 'error' })
    }
}

const addUnit = async () => {
    if (!newUnitName.value) {
        toast(t('Unit Name is required'), { type: 'error' })
        return
    }
    try {
        const res = await $api('/units', {
            method: 'POST',
            body: JSON.stringify({ name: newUnitName.value }),
            headers: { 'Content-Type': 'application/json' },
        })
        if (res.success) {
            await fetchUnitList()
            const last = res.data
            form.value.unit_id = last.id
            showUnitModal.value = false
            newUnitName.value = ''
            toast(t('Unit added successfully'), { type: 'success' })
        } else {
            toast(res.message || t('Failed to add unit'), { type: 'error' })
        }
    } catch (err) {
        toast(t('Failed to add unit'), { type: 'error' })
    }
}


// Supplier Validation
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
    if (email.length > 55) {
        newSupplierEmailError.value = t('Email cannot exceed 55 characters')
        return false
    }
    if (email) {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            newSupplierEmailError.value = t('Invalid email format')
            return false
        }
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
            <VCard :title="$t('Edit Item')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updateItem">
                        <VRow>
                            <!-- Type -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.type" :items="['Product', 'Service', 'Package']" :label="$t('Type')" :required="true" placeholder="Select Type" :error-messages="typeError" disabled />
                            </VCol>
                        </VRow>

                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="$t('Name')" :required="true" type="text" :placeholder="$t('Enter Name')"
                                    :error-messages="nameError" />
                            </VCol>
                            <!-- Code -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.code" :label="$t('Code')" :required="true" type="text" :placeholder="$t('Enter Code')"
                                    :error-messages="codeError" />
                            </VCol>
                            <!-- Category with plus icon -->
                            <VCol cols="12" md="6" lg="4" class="d-flex align-center">
                                <div class="flex-grow-1">
                                    <AppAutocomplete
                                        v-model="form.category_id"
                                        :items="categoryList"
                                        item-title="name"
                                        item-value="id"
                                        :label="t('Category')" :required="true"
                                        :placeholder="t('Select Category')"
                                        :error-messages="categoryError"
                                        clearable
                                    />
                                </div>
                                <div>
                                    <VBtn icon size="small" color="primary" :class="['ms-2', categoryError ? 'mt-minus-4-px' : 'mt-17-px']" @click="showCategoryModal = true">
                                        <VIcon icon="tabler-plus" />
                                    </VBtn>
                                </div>
                            </VCol>
                            
                            <!-- Supplier with plus icon -->
                            <VCol cols="12" md="6" lg="4" class="d-flex align-center" v-if="isItemType && form.type == 'Product'">
                                <div class="flex-grow-1">
                                    <AppAutocomplete
                                        v-model="form.supplier_id"
                                        :item-title="item => `${item.name}  ${ item.phone ? `(${item.phone})` : ''}`"
                                        :items="supplierList"
                                        item-value="id"
                                        :label="t('Supplier')"
                                        :placeholder="t('Select Supplier')"
                                        clearable
                                    />
                                </div>
                                <div>
                                    <VBtn icon size="small" color="primary" class="ms-2 mt-17-px" @click="showSupplierModal = true">
                                        <VIcon icon="tabler-plus" />
                                    </VBtn>
                                </div>
                            </VCol>
                            <!-- Item specific fields -->
                            <VCol v-if="isItemType" cols="12" md="6" lg="4" class="d-flex align-center">
                                <AppAutocomplete
                                    v-model="form.unit_id"
                                    :items="unitList"
                                    item-title="name"
                                    item-value="id"
                                    :label="t('Unit')" :required="true"
                                    :placeholder="t('Select Unit')"
                                    :error-messages="unitError"
                                    clearable
                                />
                                <VBtn icon size="small" color="primary" :class="['ms-2', unitError ? 'mt-minus-4-px' : 'mt-17-px']" @click="showUnitModal = true">
                                    <VIcon icon="tabler-plus" />
                                </VBtn>
                            </VCol>
                            <VCol v-if="isItemType" cols="12" md="6" lg="4">
                                <AppTextField v-model="form.purchase_price" :label="t('Purchase Price')" :required="true" type="number"
                                    :placeholder="t('Enter Purchase Price')" min="0" :error-messages="purchasePriceError" @input="calculateTotal" />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" v-if="isItemType">
                                <AppTextField
                                    v-model="form.profit_margin"
                                    :label="t('Profit Margin') + ' (%)'"
                                    type="number"
                                    :placeholder="t('Profit Margin')"
                                    min="0"
                                    @input="calculateTotal"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" v-if="(isServiceType) || isItemType">
                                <AppTextField v-model="form.sale_price" :label="t('Sale Price')" :required="true" type="number"
                                    :placeholder="t('Enter Sale Price')" min="0" :error-messages="salePriceError" />
                            </VCol>
                            <!-- Loyalty Point -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.loyalty_point" :label="$t('Loyalty Point')" type="number"
                                    :placeholder="$t('Enter Loyalty Point')" min="0" />
                            </VCol>
                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.status" :items="['Enable', 'Disable']" :label="$t('Status')"
                                    :placeholder="$t('Select Status')" :error-messages="statusError" clearable />
                            </VCol>
                            <!-- Duration -->
                            <VCol cols="12" md="6" lg="4" v-if="isPackageType || isServiceType">
                                <AppAutocomplete v-model="form.duration_type" :items="['Day', 'Hour', 'Minute']" :label="t('Duration Type')"
                                    :placeholder="t('Select Duration Type')" :error-messages="durationTypeError" clearable />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" v-if="isPackageType || isServiceType">
                                <AppTextField v-model="form.duration" :label="$t('Duration')" type="number" :error-messages="durationError"
                                    :placeholder="$t('Enter Duration')" min="0" />
                            </VCol>
                        </VRow>

                        <!-- Use Consumption Checkbox (only for Service) -->
                        <!-- <VRow v-if="showUseConsumption">
                            <VCol cols="12" md="6" lg="4">
                                <VCheckbox
                                    v-model="form.use_consumption"
                                    :label="$t('Use Consumption')"
                                    @change="() => {
                                        if (!form.use_consumption) {
                                            itemRows.value = []
                                            rowErrors.value = []
                                            form.value.item_id = null
                                            calculateTotal()
                                        }
                                    }"
                                />
                            </VCol>
                        </VRow> -->

                        <!-- Item Selection for Service and Package -->
                        <VRow v-if="showItemRepeater">
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.item_id"
                                    :items="itemList"
                                    item-title="name"
                                    item-value="id"
                                    :label="$t('Select Item')"
                                    :placeholder="$t('Select an item')"
                                    @update:model-value="handleItemChange"
                                    clearable
                                />
                            </VCol>
                        </VRow>

                        <!-- Service specific fields with consumption -->
                        <!-- <VRow v-if="isServiceType && form.use_consumption">
                            <VCol cols="12">
                                <VTable class="repeter-form">
                                    <thead>
                                        <tr>
                                            <th>{{ $t('SN') }}</th>
                                            <th>{{ $t('Item Name') }}</th>
                                            <th>{{ $t('Consumption') }}</th>
                                            <th>{{ $t('Unit') }}</th>
                                            <th>{{ $t('Conversion Rate') }}</th>
                                            <th>{{ $t('Cost Per Unit') }}</th>
                                            <th>{{ $t('Total') }}</th>
                                            <th>{{ $t('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in itemRows" :key="index">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ row.itemName }}</td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.consumption"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Consumption')"
                                                    min="1"
                                                    :error-messages="rowErrors[index]?.consumption"
                                                    @input="calculateTotal"
                                                />
                                            </td>
                                            <td>
                                                <AppAutocomplete
                                                    v-model="row.unit"
                                                    :items="unitList"
                                                    item-title="name"
                                                    item-value="id"
                                                    density="compact"
                                                    :placeholder="$t('Unit')"
                                                    :error-messages="rowErrors[index]?.unit"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.conversion_rate"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Conversion Rate')"
                                                    min="1"
                                                    :error-messages="rowErrors[index]?.conversion_rate"
                                                    @input="calculateTotal"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.cost_per_unit"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Cost Per Unit')"
                                                    min="0"
                                                    :error-messages="rowErrors[index]?.cost_per_unit"
                                                    @input="calculateTotal"
                                                    readonly
                                                />
                                            </td>
                                            <td>{{ row.total_price }}</td>
                                            <td>
                                                <VBtn color="error" icon variant="text" size="small" @click="removeItemRow(index)">
                                                    <VIcon icon="tabler-trash" />
                                                </VBtn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </VCol>
                        </VRow> -->
                        <!-- Package specific fields -->
                        <VRow v-if="isPackageType">
                            <VCol cols="12">
                                <VTable class="repeter-form">
                                    <thead>
                                        <tr>
                                            <th>{{ $t('SN') }}</th>
                                            <th>{{ $t('Item Name') }}</th>
                                            <th>{{ $t('Price') }}</th>
                                            <th>{{ $t('Quantity') }}</th>
                                            <th>{{ $t('Discount Price') }}</th>
                                            <th>{{ $t('Total') }}</th>
                                            <th>{{ $t('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in itemRows" :key="index">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ row.itemName }}</td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.price"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Price')"
                                                    min="0"
                                                    :error-messages="rowErrors[index]?.price"
                                                    @input="calculateTotal"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.quantity"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Quantity')"
                                                    min="1"
                                                    :error-messages="rowErrors[index]?.quantity"
                                                    @input="calculateTotal"
                                                />
                                            </td>
                                            <td>
                                                <AppTextField
                                                    v-model="row.discount"
                                                    type="number"
                                                    density="compact"
                                                    :placeholder="$t('Discount Price')"
                                                    min="0"
                                                    @input="calculateTotal"
                                                />
                                            </td>
                                            <td>{{ row.total_price }}</td>
                                            <td>
                                                <VBtn color="error" icon variant="text" size="small" @click="removeItemRow(index)">
                                                    <VIcon icon="tabler-trash" />
                                                </VBtn>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </VCol>
                        </VRow>
                        <!-- Common fields for Service with consumption and Package -->
                        <!-- <template v-if="(isServiceType && form.use_consumption) || isPackageType">
                            <VRow class="justify-end">
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField
                                        v-model="displaySubtotal"
                                        :label="t('Subtotal')"
                                        type="number"
                                        :placeholder="t('Subtotal')"
                                        min="0"
                                        readonly
                                    />
                                </VCol>
                            </VRow>
                        </template> -->
                        <!-- Always show Profit Margin and Sale Price for Service -->
                        <template v-if="isPackageType">
                            <VRow class="justify-end">
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField
                                        v-model="displayPackageTotal"
                                        :label="t('Package Total')" :required="true"
                                        :placeholder="t('Package Total')"
                                        type="number"
                                        readonly
                                    />
                                </VCol>
                            </VRow>
                            <VRow class="justify-end">
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField
                                        v-model="displayPackageDiscount"
                                        :label="t('Package Discount')"
                                        :placeholder="t('Package Discount')"
                                        type="number"
                                        readonly
                                    />
                                </VCol>
                            </VRow>
                        </template>
                        <!-- Always show Profit Margin and Sale Price for Service -->
                        <!-- <template v-if="(isServiceType && form.use_consumption)">
                            <VRow class="justify-end">
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField
                                        v-model="form.sale_price"
                                        :label="t('Sale Price')" :required="true"
                                        :placeholder="t('Sale Price')"
                                        type="number"
                                    />
                                </VCol>
                            </VRow>
                        </template> -->
                        <!-- Always show Profit Margin and Sale Price for Service -->
                        <template v-if="isPackageType">
                            <VRow class="justify-end">
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField
                                        v-model="form.sale_price"
                                        :label="t('Sale Price') + ' (' + t('Package Total') + ' - ' + t('Package Discount') + ')'" :required="true"
                                        :placeholder="t('Sale Price')"
                                        type="number"
                                        readonly
                                    />
                                </VCol>
                            </VRow>
                        </template>
                        <VRow>
                            <!-- Image Upload -->
                            <VCol cols="12">
                                <VCardText class="d-flex">
                                    <!-- 👉 Image Preview -->
                                    <VAvatar rounded size="100" class="me-6" :image="previewImage" />

                                    <!-- 👉 Upload Image -->
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputEl?.click()" >
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Upload Photo') }}</span>
                                            </VBtn>

                                            <input ref="refInputEl" type="file" name="photo" accept=".jpeg,.png,.jpg" hidden @input="changeImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetImage">
                                                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                            <VBtn  v-if="imageSrc" type="reset" size="small" color="secondary" variant="tonal" @click="showCropperModal = true">
                                                <span class="d-none d-sm-block">{{ $t('Crop Image') }}</span>
                                                <VIcon icon="tabler-crop" class="d-sm-none" />
                                            </VBtn>
                                        </div>

                                        <p class="text-body-1 mb-0">
                                            {{ $t('Allowed JPG, GIF or PNG. Max size of 1 MB Required') }}
                                        </p>
                                        <p class="text-body-1 mb-0">
                                            {{ $t('Recommended size: 250px x 250px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>

                            <!-- Description Editor -->
                            <VCol cols="12">
                                <div title="Item Description">
                                    <DemoEditorCustomEditor 
                                        height="150"
                                        :content="editorContent"
                                        v-model="editorContent"
                                        @input="handleEditorInput" 
                                    />
                                </div>
                            </VCol>
                            
                            <!-- Tax Information -->
                            <VCol cols="12">
                                <h4 class="mt-4" v-if="form.taxes.length > 0">{{ $t('Tax Information') }}</h4>
                                <VRow v-if="form.taxes.length > 0">
                                    <VCol cols="12" md="6" lg="4" v-for="(tax, index) in form.taxes" :key="index">
                                        <AppTextField
                                            v-model="tax.tax_rate"
                                            :label="`${tax.tax} (%)`"
                                            :placeholder="tax.tax_rate"
                                        />
                                    </VCol>
                                </VRow>
                                <p v-else>{{ $t('No tax information available.') }}</p>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" @click="router.push({ name: 'item' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>

                        <!-- Category Modal -->
                        <VDialog v-model="showCategoryModal" max-width="400">
                            <VCard class="modal-card modal-card-sm">
                                <VCardTitle>
                                    {{ t('Add Category') }}
                                </VCardTitle>
                                <VCardText>
                                    <AppAutocomplete
                                        v-model="newCategoryType"
                                        class="mb-3"
                                        :label="$t('Type')" :required="true"
                                        :items="[
                                            { title: 'Service', value: 'Service' },
                                            { title: 'Product', value: 'Product' }
                                        ]"
                                        :placeholder="$t('Select Type')"
                                        clearable
                                    />
                                
                                    <AppTextField v-model="newCategoryName" :label="t('Name')" :required="true" :placeholder="t('Enter Name')" />
                                </VCardText>
                                <VCardActions>
                                    <VBtn color="primary" variant="tonal" @click="addCategory">
                                        <VIcon start icon="tabler-checkbox" />
                                        {{ t('Add') }}
                                    </VBtn>
                                    <VBtn color="error" variant="tonal" @click="showCategoryModal = false">
                                        <VIcon start icon="tabler-x" />
                                        {{ t('Close') }}
                                    </VBtn>
                                </VCardActions>
                            </VCard>
                        </VDialog>
                        <!-- Unit Modal -->
                        <VDialog v-model="showUnitModal" max-width="400">
                            <VCard class="modal-card modal-card-sm">
                                <VCardTitle>{{ t('Add Unit') }}</VCardTitle>
                                <VCardText>
                                    <AppTextField v-model="newUnitName" :label="t('Name')" :required="true" :placeholder="t('Unit Name')" />
                                </VCardText>
                                <VCardActions>
                                    <VBtn color="primary" variant="tonal"  @click="addUnit">
                                        <VIcon start icon="tabler-checkbox" />
                                        {{ t('Add') }}
                                    </VBtn>
                                    <VBtn color="error" variant="tonal" @click="showUnitModal = false">
                                        <VIcon start icon="tabler-x" />
                                        {{ t('Close') }}
                                    </VBtn>
                                </VCardActions>
                            </VCard>
                        </VDialog>

                        
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

        <!-- Image Cropper Modal -->
        <VDialog v-model="showCropperModal" persistent max-width="400px">
            <VCard class="modal-card modal-card-md">
                <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="imageSrc"
                        :stencil-props="{
                            aspectRatio: 250/250,
                        }"
                        :min-size="250"
                        :max-size="250"
                        @change="onCrop"
                    />
                    <!-- Preview section -->
                    <!-- <div v-if="cropPreview" class="mt-4 cropper-preview">
                        <h4>{{ $t('Preview') }}</h4>
                        <img :src="cropPreview" alt="Preview" />
                    </div> -->
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn color="primary" variant="tonal" @click="getCroppedImage">
                        <VIcon start icon="tabler-crop" />
                        {{ $t('Crop & Save') }}
                    </VBtn>
                    <VBtn color="error" variant="tonal" @click="cancelCrop">
                        <VIcon start icon="tabler-x" />
                        {{ $t('Cancel') }}
                    </VBtn>
                    
                </VCardActions>
            </VCard>
        </VDialog>

    </VRow>
</template>
<style scoped>
.cropper {
  height: 260px;
  background: #DDD;
}
/* .v-card-title {
  padding: 25px 25px 0px 25px !important;
}
.v-card-actions {
  padding: 0px 25px 25px 25px !important;
} */
.supplier-modal {
    width: 800px;
}
@media screen and (max-width: 575.98px) {
    .supplier-modal {
        width: 95%;
    }
}
</style>