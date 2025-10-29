<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, computed, onMounted } from 'vue';
import defaultAvater from '@images/system-config/default-picture.png';
import AppDateTimePicker from '@core/components/date-time-picker/DemoDateTimePickerHumanFriendly.vue';
import { useI18n } from 'vue-i18n';
import { useWebsiteSettingsStore } from '@/stores/websiteSetting'

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
  imageSrc.value = null
  cropPreview.value = null
  if (refInputEl.value) {
    refInputEl.value.value = ''
  }
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
const loadings = ref(false)
const websiteSettingsStore = useWebsiteSettingsStore()
const taxIsGst = computed(() => websiteSettingsStore.getTaxIsGst)

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    photo: null,
    same_or_diff_state: null,
    date_of_birth: '',
    date_of_anniversary: '',
    gst_number: ''
})

const nameError = ref('')
const phoneError = ref('')
const emailError = ref('')
const addressError = ref('')
const sameOrDiffStateError = ref('')
const dateOfBirthError = ref('')
const dateOfAnniversaryError = ref('')
const gstNumberError = ref('')

const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    nameError.value = ''
    return true
}

const validatePhone = (phone) => {
    if (!phone) {
        phoneError.value = t('Phone is required')
        return false
    }
    if (phone.length > 25) {
        phoneError.value = t('Phone cannot exceed 25 characters')
        return false
    }
    phoneError.value = ''
    return true
}

const validateEmail = (email) => {
    if (!email) {
        emailError.value = t('Email is required')
        return false
    }
    if (email.length > 55) {
        emailError.value = t('Email cannot exceed 55 characters')
        return false
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailError.value = t('Invalid email format')
        return false
    }
    emailError.value = ''
    return true
}

const validateAddress = (address) => {
    if (address.length > 255) {
        addressError.value = t('Address cannot exceed 255 characters')
        return false
    }
    addressError.value = ''
    return true
}

const validateSameOrDiffState = (state) => {
    if (!state) {
        sameOrDiffStateError.value = t('State selection is required')
        return false
    }
    sameOrDiffStateError.value = ''
    return true
}
const validateGstNumber = (number) => {
    if (!number) {
        gstNumberError.value = t('GST number is required')
        return false
    }
    gstNumberError.value = ''
    return true
}

const validateDateOfBirth = (date) => {
    dateOfBirthError.value = ''
    return true
}

const validateDateOfAnniversary = (date) => {
    dateOfAnniversaryError.value = ''
    return true
}



const validateForm = () => {
    // Validate all fields at once
    const isNameValid = validateName(form.value.name)
    const isPhoneValid = validatePhone(form.value.phone)
    const isEmailValid = validateEmail(form.value.email)
    const isAddressValid = validateAddress(form.value.address)
    const isSameOrDiffStateValid = validateSameOrDiffState(form.value.same_or_diff_state)
    const isDateOfBirthValid = validateDateOfBirth(form.value.date_of_birth)
    const isDateOfAnniversaryValid = validateDateOfAnniversary(form.value.date_of_anniversary)
    const isGstNumberValid = validateGstNumber(form.value.gst_number)

    if (taxIsGst.value == 'Yes') {
        return isNameValid && isPhoneValid && isEmailValid && isAddressValid && 
               isSameOrDiffStateValid && isDateOfBirthValid && isDateOfAnniversaryValid && isGstNumberValid
    } else {
        return isNameValid && isPhoneValid && isEmailValid && isAddressValid && isDateOfBirthValid && isDateOfAnniversaryValid
    }
}

const resetForm = () => {
    form.value = {
        name: '',
        phone: '',
        email: '',
        address: '',
        photo: null,
        same_or_diff_state: null,
        date_of_birth: '',
        date_of_anniversary: '',
        gst_number: ''
    }
    previewImage.value = defaultAvater
    nameError.value = ''
    phoneError.value = ''
    emailError.value = ''
    addressError.value = ''
    sameOrDiffStateError.value = ''
    dateOfBirthError.value = ''
    dateOfAnniversaryError.value = ''
    gstNumberError.value = ''
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

const createCustomer = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        
        // Append all form fields
        Object.keys(form.value).forEach(key => {
            if (key == 'photo') {
                // Only append photo if it exists
                if (form.value[key]) {
                    formData.append('photo', form.value[key])
                }
            } else {
                formData.append(key, form.value[key])
            }
        })

        const res = await $api('/customers', {
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
        router.push({ name: 'customer' })
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

onMounted(async () => {
    await websiteSettingsStore.resetSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="$t('Add Customer')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createCustomer">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="$t('Name')" :required="true" type="text" :placeholder="$t('Enter Name')"
                                    :error-messages="nameError" @input="validateName($event.target.value)" />
                            </VCol>

                            <!-- Phone -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.phone" :label="$t('Phone')" :required="true" type="tel" :placeholder="$t('Enter phone')"
                                    :error-messages="phoneError" @input="validatePhone($event.target.value)" />
                            </VCol>

                            <!-- Email -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.email" :label="$t('Email')" :required="true" type="email" :placeholder="$t('Enter email')"
                                    :error-messages="emailError" @input="validateEmail($event.target.value)" />
                            </VCol>

                            <!-- Address -->
                            <VCol cols="12">
                                <AppTextField v-model="form.address" :label="$t('Address')" type="text" :placeholder="$t('Enter Address')"
                                    :error-messages="addressError" @input="validateAddress($event.target.value)" />
                            </VCol>

                            <!-- Date of Birth -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date_of_birth"
                                    :label="$t('Date of Birth')"
                                    :placeholder="$t('Select date of birth')"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d',
                                        maxDate: new Date()
                                    }"
                                    :error-messages="dateOfBirthError" @input="validateDateOfBirth($event.target.value)" />
                            </VCol>

                            <!-- Date of Anniversary -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.date_of_anniversary"
                                    :label="$t('Date of Anniversary')"
                                    :placeholder="$t('Select date of anniversary')"
                                    :config="{
                                        enableTime: false,
                                        dateFormat: 'Y-m-d'
                                    }"
                                    :error-messages="dateOfAnniversaryError" @input="validateDateOfAnniversary($event.target.value)" />
                            </VCol>

                            <!-- Same or Different State -->
                            <VCol cols="12" md="6" lg="4" v-if="taxIsGst == 'Yes'">
                                <AppAutocomplete
                                    v-model="form.same_or_diff_state" 
                                    :label="$t('State Status')" :required="true"
                                    :items="[
                                        { title: 'Same', value: 'Same' },
                                        { title: 'Different', value: 'Different' }
                                    ]"
                                    :placeholder="$t('Select state status')"
                                    :error-messages="sameOrDiffStateError"
                                    clearable
                                />
                            </VCol>

                            <!-- GST Number -->
                            <VCol cols="12" md="6" lg="4" v-if="taxIsGst == 'Yes'">
                                <AppTextField v-model="form.gst_number" :label="$t('GST Number')" :required="true" type="text" :placeholder="$t('Enter GST number')"
                                    :error-messages="gstNumberError" @input="validateGstNumber($event.target.value)" />
                            </VCol>

                            <!-- Image Upload (Required)-->
                            <VCol cols="12">
                                <VCardText class="d-flex">
                                    <!-- 👉 Image Preview -->
                                    <VAvatar rounded size="100" class="me-6" :image="previewImage" />

                                    <!-- 👉 Upload Image -->
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputEl?.click()" >
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Upload Customer Photo') }}</span>
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
                                    {{ $t('Submit') }}
                                </VBtn>
                                <VBtn type="button" @click="router.push({ name: 'customer' })" color="primary" variant="tonal">
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


        <!-- Image Cropper Modal -->
        <VDialog v-model="showCropperModal" persistent max-width="800px">
            <VCard class="modal-card modal-card-md">
                <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="imageSrc"
                        :stencil-props="{
                            aspectRatio: 250/250,
                        }"
                        @change="onCrop"
                    />
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
  height: 255px;
  background: #DDD;
}
/* .v-card-title {
  padding: 25px 25px 0px 25px !important;
}
.v-card-actions {
  padding: 0px 25px 25px 25px !important;
} */
</style>