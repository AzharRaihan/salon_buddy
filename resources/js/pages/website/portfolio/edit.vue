<script setup>
import { useRouter, useRoute } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import defaultAvater from '@images/system-config/default-picture.png';
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
const MIN_WIDTH = 300
const MIN_HEIGHT = 300
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



const router = useRouter()
const route = useRoute()
const loadings = ref(false)

const form = ref({
    title: '',
    description: null, // Changed from '' to null
    status: null,
    photo: defaultAvater,
    photo_url: defaultAvater,
    position: null
})

const titleError = ref('')
const descriptionError = ref('')
const statusError = ref(null)
const photoError = ref('')
const positionError = ref('')
// Fetch category data on mount
const fetchPortfolio = async () => {
    try {
        const res = await $api(`/portfolios/${route.query.id}`, {
            method: 'GET',
        })

        const { data } = res

        form.value = {
            title: data.title,
            description: data.description || null, // Ensure null if empty
            status: data.status,
            photo: defaultAvater,
            photo_url: data.photo_url || defaultAvater,
            position: data.position
        }
        // Set preview image
        previewImage.value = data.photo_url || defaultAvater
        imageSrc.value = data.photo_url || defaultAvater
    } catch (err) {
        console.error(err)
        toast('Error fetching portfolio data', {
            type: 'error'
        })
        router.push({ name: 'website-portfolio' })
    }
}

onMounted(() => {
    fetchPortfolio()
})


const validateTitle = (title) => {
    if (!title) {
        titleError.value = t('Title is required')
        return false
    }
    if (title && title.length > 55) {
        titleError.value = t('Title cannot exceed 55 characters')
        return false
    }
    titleError.value = ''
    return true
}

const validateDescription = (description) => {
    if (!description) {
        descriptionError.value = t('Description is required')
        return false
    }
    if (description && description.length > 255) {
        descriptionError.value = t('Description cannot exceed 255 characters')
        return false
    }
    descriptionError.value = ''
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
const validatePhoto = (photo) => {
    if (!photo) {
        photoError.value = t('Photo is required')
        return false
    }
    photoError.value = ''
    return true
}

const validatePosition = (position) => {

    if (!position) {
        positionError.value = t('Position is required')
        return false
    }
    positionError.value = ''
    return true
}

const validateForm = () => {
    // Validate all fields at once
    const isTitleValid = validateTitle(form.value.title)
    const isDescriptionValid = validateDescription(form.value.description)
    const isStatusValid = validateStatus(form.value.status)
    const isPhotoValid = validatePhoto(form.value.photo)
    const isPositionValid = validatePosition(form.value.position)

    return isTitleValid && isDescriptionValid && isStatusValid && isPhotoValid && isPositionValid
}

const updatePortfolio = async () => {
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
                if (form.value[key] !== defaultAvater) { // Only append if photo has changed
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

        const res = await $api(`/portfolios/${route.query.id}`, {
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
        router.push({ name: 'website-portfolio' })
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
            <VCard :title="$t('Edit Portfolio')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="updatePortfolio">
                        <VRow>

                            <!-- Title -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.title" :label="$t('Title')" :required="true" type="text" :placeholder="$t('Enter Title')"
                                    :error-messages="titleError" @input="validateTitle($event.target.value)" />
                            </VCol>

                            <!-- Description -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.description" :label="$t('Description')" type="text" :required="true"
                                    :placeholder="$t('Enter Description')" :error-messages="descriptionError" 
                                    @input="validateDescription($event.target.value)" />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.status"
                                    :label="$t('Status')" :required="true"
                                    :items="[
                                        { title: 'Enabled', value: 'Enabled' },
                                        { title: 'Disabled', value: 'Disabled' }
                                    ]"
                                    :placeholder="$t('Select Status')"
                                    :error-messages="statusError"
                                    @update:modelValue="validateStatus"
                                    clearable
                                />
                            </VCol>

                            <!-- Position -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete
                                    v-model="form.position"
                                    :label="$t('Position')" :required="true"
                                    :items="[
                                        { title: '1', value: '1' },
                                        { title: '2', value: '2' },
                                        { title: '3', value: '3' },
                                        { title: '4', value: '4' },
                                        { title: '5', value: '5' },
                                    ]"
                                    :placeholder="$t('Select Position')"
                                    :error-messages="positionError"
                                    @update:modelValue="validatePosition"
                                    clearable
                                />
                            </VCol>

                             <!-- Image Upload (Optional) -->
                             <VCol cols="12">
                                <VCardText class="d-flex">
                                    <!-- 👉 Image Preview -->
                                    <VAvatar rounded size="100" class="me-6" :image="previewImage" />

                                    <!-- 👉 Upload Image -->
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputEl?.click()" >
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Upload Image') }}</span>
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
                                        <p class="text-body-1 mb-0 required-error" v-if="photoError">{{ photoError }}</p>
                                        <p class="text-body-1 mb-0">
                                            {{ $t('Recommended size: 65px x 65px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
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
                                <VBtn color="primary" variant="tonal" @click="router.push({ name: 'website-portfolio' })">
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
        <VDialog v-model="showCropperModal" persistent max-width="800px">
            <VCard class="modal-card modal-card-lg">
                <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="imageSrc"
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
  height: 600px;
  background: #DDD;
}
/* .v-card-title {
  padding: 25px 25px 0px 25px !important;
}
.v-card-actions {
  padding: 0px 25px 25px 25px !important;
} */
</style>