<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import defaultAvater from '@images/system-config/default-picture.png';
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

const { t } = useI18n()

const router = useRouter()
const route = useRoute()
const loadings = ref(false)

const form = ref({
    name: '',
    account_type: null,
    description: '',
    current_balance: 0,
    status: null,
    use_in_website: null,
    photo: defaultAvater,
    photo_url: defaultAvater
})

const nameError = ref('')
const accountTypeError = ref('')
const descriptionError = ref('')
const currentBalanceError = ref('')
const statusError = ref('')
const useInWebsiteError = ref('')

const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    if (name.length > 55) {
        nameError.value = t('Name cannot exceed 55 characters')
        return false
    }
    nameError.value = ''
    return true
}

const validateAccountType = (accountType) => {
    if (!accountType) {
        accountTypeError.value = t('Account type is required')
        return false
    }
    if (accountType.length > 25) {
        accountTypeError.value = t('Account type cannot exceed 25 characters')
        return false
    }
    accountTypeError.value = ''
    return true
}

const validateDescription = (description) => {
    if (description && description.length > 255) {
        descriptionError.value = t('Description cannot exceed 255 characters')
        return false
    }
    descriptionError.value = ''
    return true
}

const validateCurrentBalance = (balance) => {
    if (balance == '') {
        currentBalanceError.value = t('Opening balance is required')
        return false
    }
    if (isNaN(balance)) {
        currentBalanceError.value = t('Opening balance must be a number')
        return false
    }
    currentBalanceError.value = ''
    return true
}

const validateStatus = (status) => {
    if (!status) {
        statusError.value = t('Status is required')
        return false
    }
    statusError.value = ''
    return true
}

const validateUseInWebsite = (useInWebsite) => {
    if (!useInWebsite) {
        useInWebsiteError.value = t('Use in website is required')
        return false
    }
    useInWebsiteError.value = ''
    return true
}

const resetForm = () => {
    router.push({ name: 'payment-method' })
}

const validateForm = () => {
    const isNameValid = validateName(form.value.name)
    const isAccountTypeValid = validateAccountType(form.value.account_type)
    const isDescriptionValid = validateDescription(form.value.description)
    const isCurrentBalanceValid = validateCurrentBalance(form.value.current_balance)
    const isStatusValid = validateStatus(form.value.status)
    const isUseInWebsiteValid = validateUseInWebsite(form.value.use_in_website)

    return isNameValid && isAccountTypeValid && isDescriptionValid && isCurrentBalanceValid && isStatusValid && isUseInWebsiteValid
}

const fetchPaymentMethod = async () => {
    try {
        const res = await $api(`/payment-methods/${route.query.id}`)
        form.value = res.data
        previewImage.value = res.data.payment_method_icon_url || defaultAvater
        imageSrc.value = res.data.payment_method_icon_url || defaultAvater
    } catch (err) {
        console.error(err)
        toast(t('Error fetching payment method'), {
            type: 'error'
        })
        router.push({ name: 'payment-method' })
    }
}

const updatePaymentMethod = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        // Create FormData object to handle file upload
        const formData = new FormData()
        
        // Append all form fields except photo_url
        Object.keys(form.value).forEach(key => {
            if (key == 'photo') {
                if (form.value[key] !== defaultAvater) { // Only append if icon has changed
                    formData.append('photo', form.value[key])
                }
            } else if (key !== 'photo_url') {
                // Only append if value is not null
                if (form.value[key] !== null) {
                    formData.append(key, form.value[key])
                }
            }
        })
        formData.append('_method', 'PUT') // For Laravel PUT method

        const res = await $api(`/payment-methods/${route.query.id}`, {
            method: 'POST', // Using POST with _method: PUT for file upload
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
        router.push({ name: 'payment-method' })
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

onMounted(() => {
    fetchPaymentMethod()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="$t('Edit Payment Method')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updatePaymentMethod">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" 
                                    :label="$t('Name')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter payment method name')"
                                    :error-messages="nameError"
                                    @input="validateName($event.target.value)"
                                />
                            </VCol>

                            <!-- Account Type -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.account_type"
                                    :label="$t('Account Type')" :required="true"
                                    :items="[
                                        'Cash',
                                        'Bank Account',
                                        'Paypal',
                                        'Stripe',
                                        'Loyalty Point',
                                        'Razorpay',
                                        'Paystack',
                                        'Paytm',
                                        'Mobile Banking',
                                        'Cash On Delivery',
                                    ]"
                                    :placeholder="$t('Select account type')"
                                    :error-messages="accountTypeError"
                                    @input="validateAccountType($event)"
                                    clearable
                                />
                            </VCol>

                            <!-- Opening Balance -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.current_balance"
                                    :label="$t('Opening Balance')"
                                    type="number"
                                    step="0.01"
                                    :placeholder="$t('Enter opening balance')"
                                    :error-messages="currentBalanceError"
                                    @input="validateCurrentBalance($event.target.value)"
                                />
                            </VCol>

                            <!-- Description -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.description"
                                    :label="$t('Description')"
                                    :placeholder="$t('Enter Description')"
                                    :error-messages="descriptionError"
                                    @input="validateDescription($event.target.value)"
                                />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.status"
                                    :label="$t('Status')" :required="true"
                                    :items="[
                                        'Enable',
                                        'Disable'
                                    ]"
                                    :placeholder="$t('Select status')"
                                    :error-messages="statusError"
                                    @input="validateStatus($event)"
                                    clearable
                                />
                            </VCol>

                            <!-- User in website -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.use_in_website"
                                    :label="$t('Use in website')" :required="true"
                                    :items="[
                                        'Yes',
                                        'No'
                                    ]"
                                    :placeholder="$t('Select use in website')"
                                    :error-messages="useInWebsiteError"
                                    @input="validateUseInWebsite($event)"
                                    clearable
                                />
                            </VCol>

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

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ $t('Update') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'payment-method' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
                                </VBtn>
                            </VCol>
                        </VRow>
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
</style>
