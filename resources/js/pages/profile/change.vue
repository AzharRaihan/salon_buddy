<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { useUserData } from '@/composables/useUserData';

const { t } = useI18n()
const { userData: userDataRef, updateUserData } = useUserData()
const userData = userDataRef.value || {}

const form = ref({
  name: userData?.name || '',
  email: userData?.email || '',
  phone: userData?.phone || '',
  photo: userData?.photo_url || ''
})
const router = useRouter()
const loadings = ref(false)



// Image Cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
const showCropperModal = ref(false)
const imageSrc = ref(null)
const croppedImage = ref(null) 
const cropPreview = ref(null)
const refInputEl = ref()
const previewImage = ref(userData?.photo_url || '')
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
  form.value.photo = userData?.photo_url || ''
  previewImage.value = userData?.photo_url || ''
  if (refInputEl.value) {
    refInputEl.value.value = ''
  }
  imageSrc.value = null
  showCropperModal.value = false
}
// Cropper End

// reset avatar image
const resetAvatar = () => {
  form.value.photo = userData?.photo_url || ''
}

const nameError = ref('')
const emailError = ref('')
const phoneError = ref('')

const validateName = (name) => {
  if (!name) {
    nameError.value = t('Name is required')
    return false
  }
  nameError.value = ''
  return true
}

const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!email) {
    emailError.value = t('Email is required')
    return false
  }
  if (!emailRegex.test(email)) {
    emailError.value = t('Please enter a valid email address')
    return false
  }
  emailError.value = ''
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

const resetForm = () => {
  form.value = {
    name: userData?.name || '',
    email: userData?.email || '',
    phone: userData?.phone || ''
  }
}

const updateProfile = async () => {
  loadings.value = true
  if (!validateName(form.value.name) ||
    !validateEmail(form.value.email) ||
    !validatePhone(form.value.phone)) {
    loadings.value = false
    return
  }

  try {
    // const formData = new FormData()
    // formData.append('user_id', userData?.id || '')
    // formData.append('name', form.value.name)
    // formData.append('email', form.value.email)
    // formData.append('phone', form.value.phone)

    // // If photo is a base64 string, convert it to a file
    // if (form.value.photo && form.value.photo.startsWith('data:image')) {
    //   const response = await fetch(form.value.photo)
    //   const blob = await response.blob()
    //   formData.append('photo', blob, 'profile.jpg')
    // }

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

    const res = await $api('/update-profile', {
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

    const { status, message, user } = res

    if (status == 'error') {
      toast(message, {
        type: 'error',
      })
      loadings.value = false
      return
    }
    
    console.log('Profile updated successfully. New user data:', user)
    updateUserData(user)
    console.log('updateUserData called')
    
    toast(message, {
      type: 'success',
    });
    loadings.value = false
  }
  catch (err) {
    console.error(err)
    loadings.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard :title="$t('Change Profile')">
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VCol cols="12">
            <VCardText class="d-flex">
              <!-- 👉 Image Preview -->
              <VAvatar rounded size="100" class="me-6" :image="previewImage" />
              <!-- 👉 Upload Image -->
              <form class="d-flex flex-column justify-center gap-4">
                <div class="d-flex flex-wrap gap-4">
                  <VBtn color="primary" size="small" @click="refInputEl?.click()" >
                      <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                      <span class="d-none d-sm-block">{{ $t('Upload Profile Image') }}</span>
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
                    {{ $t('Allowed JPG, PNG. Max size of 1 MB Required') }}
                </p>
                <p class="text-body-1 mb-0">
                    {{ $t('Recommended size: 250px x 250px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
                </p>
              </form>
            </VCardText>
          </VCol>

          <!-- 👉 Upload Photo -->
          <!-- <form class="d-flex flex-column justify-center gap-4">
            <div class="d-flex flex-wrap gap-4">
              <VBtn color="primary" size="small" @click="refInputEl?.click()">
                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                <span class="d-none d-sm-block">{{ $t('Upload new photo') }}</span>
              </VBtn>

              <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeAvatar">

              <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetAvatar">
                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                <VIcon icon="tabler-refresh" class="d-sm-none" />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              {{ $t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
            </p>
          </form> -->
        </VCardText>
        <VCardText class="pt-2">
          <VForm class="mt-3" @submit.prevent="updateProfile">
            <VRow>
              <!-- Name -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.name" :label="$t('Name')" :required="true" type="text" :placeholder="$t('Enter your name')"
                  :error-messages="nameError" @input="validateName($event.target.value)" />
              </VCol>

              <!-- Email -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.email" :label="$t('Email')" :required="true" type="email" :placeholder="$t('Enter your email')"
                  :error-messages="emailError" @input="validateEmail($event.target.value)" />
              </VCol>

              <!-- Phone -->
              <VCol cols="12" md="4">
                <AppTextField v-model="form.phone" :label="$t('Phone')" :required="true" type="text" :placeholder="$t('Enter your phone number')"
                  :error-messages="phoneError" @input="validatePhone($event.target.value)" />
              </VCol>

              <!-- Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                  <VIcon start icon="tabler-checkbox" />
                  {{ $t('Save changes') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <!-- Image Cropper Modal -->
    <VDialog v-model="showCropperModal" persistent max-width="400px">
      <VCard class="modal-card modal-card-sm">
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
  height: 300px;
  background: #DDD;
}
</style>