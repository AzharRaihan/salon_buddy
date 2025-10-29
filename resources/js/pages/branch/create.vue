<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import defaultAvater from '../../../../public/assets/images/default-images/branch.png';

import { useI18n } from 'vue-i18n';
const { t } = useI18n()

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
const MIN_WIDTH = 420
const MIN_HEIGHT = 400
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



const router = useRouter()
const loadings = ref(false)
const form = ref({
    branch_name: '',
    branch_code: '',
    address: '',
    phone: '',
    email: '',
    active_status: null,
    photo: defaultAvater,
    photo_url: defaultAvater,
    open_day_start: null,
    open_day_end: null,
    open_day_start_time: null,
    open_day_end_time: null,
})
const branchNameError = ref('')
const branchCodeError = ref('')
const addressError = ref('')
const phoneError = ref('')
const emailError = ref('')
const activeStatusError = ref('')
const openDayStartError = ref('')
const openDayEndError = ref('')
const openDayStartTimeError = ref('')
const openDayEndTimeError = ref('')
const photoError = ref('')

// Fetch branch code on mount
const fetchBranchCode = async () => {
    try {
        const res = await $api('/generate-branch-code')
        form.value.branch_code = res.data
    } catch (err) {
        console.error('Error fetching branch code:', err)
        toast('Error generating branch code', {
            type: 'error'
        })
    }
}

onMounted(() => {
    fetchBranchCode()
})

const validateName = (name) => {
    if (!name) {
        branchNameError.value = t('Branch Name is required')
        return false
    }
    branchNameError.value = ''
    return true
}
const validateCode = (code) => {
    if (!code) {
        branchCodeError.value = t('Branch Code is required')
        return false
    }
    branchCodeError.value = ''
    return true
}
const validateAddress = (address) => {
    if (!address) {
        addressError.value = t('Address is required')
        return false
    }
    addressError.value = ''
    return true
}
const validatePhone = (phone) => {
    if (!phone) {
        phoneError.value = t('Phone is required')
        return false
    }
    phoneError.value = ''
    return true
}
const validateEmail = (email) => {
    if (email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(email)) {
            emailError.value = t('Please enter a valid email address')
            return false
        }
    }
    emailError.value = ''
    return true
}
const validateActiveStatus = (activeStatus) => {
    if (!activeStatus) {
        activeStatusError.value = t('Status is required')
        return false
    }
    activeStatusError.value = ''
    return true
}
const validateOpenDayStart = (openDayStart) => {
    if (!openDayStart) {
        openDayStartError.value = t('Open day start is required')
        return false
    }
    openDayStartError.value = ''
    return true
}   
const validateOpenDayEnd = (openDayEnd) => {
    if (!openDayEnd) {
        openDayEndError.value = t('Open day end is required')
        return false
    }
    openDayEndError.value = ''
    return true
}   
const validateOpenDayStartTime = (openDayStartTime) => {
    if (!openDayStartTime) {
        openDayStartTimeError.value = t('Open day start time is required')
        return false
    }
    openDayStartTimeError.value = ''
    return true
}       
const validateOpenDayEndTime = (openDayEndTime) => {
    if (!openDayEndTime) {
        openDayEndTimeError.value = t('Open day end time is required')
        return false
    }
    openDayEndTimeError.value = ''
    return true
}
const validatePhoto = (photo) => {
    if (!photo) {
        photoError.value = t('Photo is required')
        return false
    }
    photoError.value = ''
    return true
}
const validateForm = () => {
    // Validate all fields at once
    const isNameValid = validateName(form.value.branch_name)
    const isCodeValid = validateCode(form.value.branch_code)
    const isAddressValid = validateAddress(form.value.address)
    const isPhoneValid = validatePhone(form.value.phone)
    const isEmailValid = validateEmail(form.value.email)
    const isStatusValid = validateActiveStatus(form.value.active_status)
    const isOpenDayStartValid = validateOpenDayStart(form.value.open_day_start)
    const isOpenDayEndValid = validateOpenDayEnd(form.value.open_day_end)
    const isOpenDayStartTimeValid = validateOpenDayStartTime(form.value.open_day_start_time)
    const isOpenDayEndTimeValid = validateOpenDayEndTime(form.value.open_day_end_time)
    const isPhotoValid = validatePhoto(form.value.photo)

    return isNameValid && isCodeValid && isAddressValid && isPhoneValid && isEmailValid && isStatusValid && isOpenDayStartValid && isOpenDayEndValid && isOpenDayStartTimeValid && isOpenDayEndTimeValid && isPhotoValid
}

const resetForm = () => {
    form.value = {
        branch_name: '',
        branch_code: '',
        address: '',
        phone: '',
        email: '',
        active_status: null,
        photo: defaultAvater,
        photo_url: defaultAvater,
        open_day_start: null,
        open_day_end: null,
        open_day_start_time: null,
        open_day_end_time: null,
    }
    branchNameError.value = ''
    branchCodeError.value = ''
    addressError.value = ''
    phoneError.value = ''
    emailError.value = ''
    activeStatusError.value = ''
    openDayStartError.value = ''
    openDayEndError.value = ''
    openDayStartTimeError.value = ''
    openDayEndTimeError.value = ''
    photoError.value = ''
    // Fetch new branch code after reset
    fetchBranchCode()
}

const createBranch = async () => {
    loadings.value = true
    
    // Validate all fields first
    const isValid = validateForm()
    
    if (!isValid) {
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

        const res = await $api('/branches', {
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
        router.push({ name: 'branch' })
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
            <VCard :title="$t('Add Branch')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createBranch">
                        <VRow>

                            <!-- Branch Code -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField
                                    v-model="form.branch_code"
                                    :label="$t('Branch Code')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter branch code')"
                                    :error-messages="branchCodeError"
                                    readonly
                                />
                            </VCol>

                            <!-- Branch Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField
                                    v-model="form.branch_name"
                                    :label="$t('Branch Name')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter branch name')"
                                    :error-messages="branchNameError"
                                />
                            </VCol>

                            <!-- Phone -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField
                                    v-model="form.phone"
                                    :label="$t('Phone')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter phone')"
                                    :error-messages="phoneError"
                                />
                            </VCol>

                            <!-- Email -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField
                                    v-model="form.email"
                                    :label="$t('Email')"
                                    type="email"
                                    :placeholder="$t('Enter email')"
                                    :error-messages="emailError"
                                />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.active_status"
                                    :items="['Active', 'Inactive']"
                                    :label="$t('Status')" :required="true"
                                    :placeholder="$t('Select Status')"
                                    :error-messages="activeStatusError"
                                    clearable
                                />
                            </VCol>

                            <!-- Address -->
                            <VCol cols="12">
                                <AppTextField
                                    v-model="form.address"
                                    :label="$t('Address')" :required="true"
                                    type="text"
                                    :placeholder="$t('Enter address')"
                                    :error-messages="addressError"
                                />
                            </VCol>

                            

                            <!-- Start Day -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.open_day_start"
                                    :items="['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']"
                                    :label="$t('Open Day Start')" :required="true"
                                    :placeholder="$t('Select Open Day Start')"
                                    :error-messages="openDayStartError"
                                    clearable
                                />
                            </VCol>
                            
                            <!-- End Day -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.open_day_end"
                                    :items="['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']"
                                    :label="$t('Open Day End')" :required="true"
                                    :placeholder="$t('Select Open Day End')"
                                    :error-messages="openDayEndError"
                                    clearable
                                />
                            </VCol>

                            <!-- Start Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.open_day_start_time"
                                    :label="$t('Open Day Start Time')" :required="true"
                                    :placeholder="$t('Select time')"
                                    :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
                                    :error-messages="openDayStartTimeError"
                                />
                            </VCol>

                            <!-- End Time -->
                            <VCol cols="12" md="6" lg="4">
                                <AppDateTimePicker
                                    v-model="form.open_day_end_time"
                                    :label="$t('Open Day End Time')" :required="true"
                                    :placeholder="$t('Select time')"
                                    :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
                                    :error-messages="openDayEndTimeError"
                                />
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
                                                <span class="d-none d-sm-block">{{ $t('Upload Branch Image') }}</span>
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
                                            {{ $t('Recommended size: 420px x 400px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
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
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'branch' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                    {{ $t('Back') }}
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
                            aspectRatio: 420/400,
                        }"
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
  height: 400px;
  background: #DDD;
}
.v-card-title {
    padding: 25px 25px 16px 25px !important;
    border-bottom: 1px solid #dbdbdb;
    padding-bottom: 10px;
}
</style>