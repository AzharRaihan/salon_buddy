<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useCustomerAuth } from '@/composables/useCustomerAuth'
import { toast } from 'vue3-toastify'
import BookAppointmentBtn from '../../frontend/mini-components/BookAppointmentBtn.vue'
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
    profileForm.photo = convertDataURLtoFile(croppedImage.value, 'cropped.jpg')
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
    toast('Only JPEG, JPG, PNG, and WEBP formats are allowed.', { type: 'error' })
    return false
  }
  if (file.size > MAX_FILE_SIZE) {
    toast('File size must be less than or equal to 1 MB.', { type: 'error' })
    return false
  }
  return true
}

function validateImageDimensions(img) {
  return true;
  if (img.width < MIN_WIDTH || img.height < MIN_HEIGHT) {
    toast(`Image dimensions must be at least ${MIN_WIDTH}px × ${MIN_HEIGHT}px.`, { type: 'error' })
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
      toast('Invalid image file.', { type: 'error' })
      event.target.value = ''
    }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}

const resetImage = () => {
  profileForm.photo = defaultAvater
  previewImage.value = defaultAvater
  if (refInputEl.value) {
    refInputEl.value.value = ''
  }
  imageSrc.value = null
  showCropperModal.value = false
}

// Cropper End

const { 
  customerData, 
  updateCustomerProfile, 
  isLoading,
  customerAccessToken
} = useCustomerAuth()

// Profile form data
const profileForm = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  date_of_birth: '',
  date_of_anniversary: '',
  photo: defaultAvater
})

// Password form data
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Error handling
const profileErrors = ref({})
const passwordErrors = ref({})
const isProfileLoading = ref(false)
const isPasswordLoading = ref(false)

// Computed customer info
const customerInfo = computed(() => {
  if (customerData.value) {
    return {
      name: customerData.value.name || '',
      email: customerData.value.email || '',
      phone: customerData.value.phone || '',
      address: customerData.value.address || '',
      photo: customerData.value.photo_url || defaultAvater
    }
  }
  return {
    name: '',
    email: '',
    phone: '',
    address: '',
    photo: defaultAvater
  }
})

// Initialize form with customer data
const initializeForm = () => {
  if (customerData.value) {
    profileForm.name = customerData.value.name || ''
    profileForm.email = customerData.value.email || ''
    profileForm.phone = customerData.value.phone || ''
    profileForm.address = customerData.value.address || ''
    profileForm.date_of_birth = customerData.value.date_of_birth || ''
    profileForm.date_of_anniversary = customerData.value.date_of_anniversary || ''
    profileForm.photo = customerData.value.photo_url || defaultAvater
    previewImage.value = customerData.value.photo_url || defaultAvater
    imageSrc.value = customerData.value.photo_url || defaultAvater
  }
}


// Clear errors
const clearProfileError = (field) => {
  if (profileErrors.value[field]) {
    delete profileErrors.value[field]
  }
}

const clearPasswordError = (field) => {
  if (passwordErrors.value[field]) {
    delete passwordErrors.value[field]
  }
}

// Save profile changes
const saveProfile = async () => {
  isProfileLoading.value = true
  profileErrors.value = {}

  try {
    const formData = new FormData()
    Object.keys(profileForm).forEach(key => {
      if (key === 'photo') {
        if (profileForm[key] !== defaultAvater && profileForm[key] instanceof File) {
          formData.append('photo', profileForm[key])
        }
      } else {
        formData.append(key, profileForm[key])
      }
    })
    formData.append('_method', 'PUT')

    const result = await updateCustomerProfile(formData)

    if (result.success) {
      toast('Profile updated successfully!', {
        type: 'success',
        position: 'top-right',
        autoClose: 3000
      })
    } else {
      toast(result.message || 'Failed to update profile', {
        type: 'error',
        position: 'top-right',
        autoClose: 3000
      })
      
      if (result.errors) {
        profileErrors.value = result.errors
      }
    }
  } catch (error) {
    console.error('Profile update error:', error)
    toast('Something went wrong. Please try again.', {
      type: 'error',
      position: 'top-right',
      autoClose: 3000
    })
  } finally {
    isProfileLoading.value = false
  }
}

// Change password
const changePassword = async () => {
  isPasswordLoading.value = true
  passwordErrors.value = {}

  if (passwordForm.password !== passwordForm.password_confirmation) {
    passwordErrors.value.password_confirmation = ['Passwords do not match']
    isPasswordLoading.value = false
    return
  }

  try {
    const response = await $api('/customer/change-password', {
      method: 'POST',
      body: passwordForm,
      headers: {
          'Authorization': `Bearer ${customerAccessToken.value}`
      },
      onResponseError({ response }) {
          toast(response._data.message, {
              type: 'error',
          })
          isPasswordLoading.value = false
          return Promise.reject(response._data)
      },
    })

    const { status, message } = response

    if (status === 'error') {
        toast(message, {
            type: 'error',
        })
        isPasswordLoading.value = false
        return
    }

    toast('Password changed successfully! Please login again.', {
        type: "success",
    })
    isPasswordLoading.value = false
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (error) {
    if (error.errors) {
        // Show each validation error as a toast
        for (const [field, messages] of Object.entries(error.errors)) {
            messages.forEach(msg => {
                toast(msg, { type: 'error' })
            })
        }
    } else {
      // Show general error if no field-specific errors
      toast(error.message, {
          type: 'error',
      })
    }
    isPasswordLoading.value = false
    return
  } finally {
    isPasswordLoading.value = false
  }
}

const changePhoto = () => {
  refInputEl.value?.click()
}

onMounted(() => {
  initializeForm()
})
</script>

<template>
    <div class="customer-panel-content-body customer-panel-content-body-wrapper">
        <h4 class="profile-setting-title">{{ t('Profile Setting') }}</h4>
        <div class="profile-info">
            <div class="avatar">
                <div class="change-trigger" @click="changePhoto">
                  <VIcon icon="tabler-camera" />
                </div>
                <img 
                  :src="previewImage" 
                  :alt="customerInfo.name"
                  @error="$event.target.src = defaultAvater"
                >
            </div>
            <!-- Hidden file input for image upload -->
            <input 
              ref="refInputEl" 
              type="file" 
              name="photo" 
              accept=".jpeg,.png,.jpg,.webp" 
              hidden 
              @input="changeImage"
            >
            <div class="info">
                <h5>{{ customerInfo.name || 'Customer' }}</h5>
                <p>{{ customerInfo.email || 'No email provided' }}</p>
                <p>{{ customerInfo.phone || 'No phone provided' }}</p>
                <p>{{ customerInfo.address || 'No address provided' }}</p>
            </div>
        </div>

        <div class="profile-setting-form">
            <form @submit.prevent="saveProfile">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="first_name">{{ t('Full Name') }} <span class="text-danger">*</span></label>
                            <input 
                              type="text" 
                              id="first_name" 
                              v-model="profileForm.name"
                              @focus="clearProfileError('name')"
                              :placeholder="t('Enter your first name')" 
                              class="form-control"
                              :class="{ 'is-invalid': profileErrors.name }"
                              required
                            >
                            <div v-if="profileErrors.name" class="invalid-feedback d-block">
                              {{ profileErrors.name[0] }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="phone">{{ t('Phone') }} <span class="text-danger">*</span></label>
                            <input 
                              v-model="profileForm.phone"
                              @input="clearProfileError('phone')"
                              type="tel" 
                              id="phone" 
                              :placeholder="t('Enter your phone')" 
                              class="form-control"
                              :class="{ 'is-invalid': profileErrors.phone }"
                            >
                            <div v-if="profileErrors.phone" class="invalid-feedback d-block">
                              {{ profileErrors.phone[0] }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="email">{{ t('Email') }} <span class="text-danger">*</span></label>
                            <input 
                              v-model="profileForm.email"
                              @input="clearProfileError('email')"
                              type="email" 
                              id="email" 
                              :placeholder="t('Enter your email')" 
                              class="form-control"
                              :class="{ 'is-invalid': profileErrors.email }"
                              required
                              readonly
                            >
                            <div v-if="profileErrors.email" class="invalid-feedback d-block">
                              {{ profileErrors.email[0] }}
                            </div>
                            <small class="text-muted">{{ t('Email cannot be changed') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="address">{{ t('Address') }}</label>
                            <textarea 
                              v-model="profileForm.address"
                              @input="clearProfileError('address')"
                              id="address" 
                              :placeholder="t('Enter your address')" 
                              class="form-control"
                              :class="{ 'is-invalid': profileErrors.address }"
                            ></textarea>
                            <div v-if="profileErrors.address" class="invalid-feedback d-block">
                              {{ profileErrors.address[0] }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="date_of_birth">{{ t('Date of Birth') }}</label>
                            <input 
                              v-model="profileForm.date_of_birth"
                              type="date" 
                              id="date_of_birth" 
                              class="form-control"
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group secondary-input">
                            <label for="date_of_anniversary">{{ t('Anniversary Date') }}</label>
                            <input 
                              v-model="profileForm.date_of_anniversary"
                              type="date" 
                              id="date_of_anniversary" 
                              class="form-control"
                            >
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button 
                          type="submit" 
                          class="btn common-animation-button common-animation-profile"
                          :disabled="isProfileLoading"
                        >
                          <span v-if="isProfileLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                          {{ isProfileLoading ? t('Saving...') : t('Save Changes') }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="change-password-section">
                <h4 class="profile-setting-title">{{ t('Change Password') }}</h4>
                <form @submit.prevent="changePassword">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group secondary-input">
                                <label for="current_password">{{ t('Current Password') }} <span class="text-danger">*</span></label>
                                <input 
                                  v-model="passwordForm.current_password"
                                  @input="clearPasswordError('current_password')"
                                  type="password" 
                                  id="current_password" 
                                  :placeholder="t('Enter your current password')" 
                                  class="form-control"
                                  :class="{ 'is-invalid': passwordErrors.current_password }"
                                >
                                <div v-if="passwordErrors.current_password" class="invalid-feedback d-block">
                                  {{ passwordErrors.current_password[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group secondary-input">
                                <label for="new_password">{{ t('New Password') }} <span class="text-danger">*</span></label>
                                <input 
                                  v-model="passwordForm.password"
                                  @input="clearPasswordError('password')"
                                  type="password" 
                                  id="new_password" 
                                  :placeholder="t('Enter your new password')" 
                                  class="form-control"
                                  :class="{ 'is-invalid': passwordErrors.password }"
                                >
                                <div v-if="passwordErrors.password" class="invalid-feedback d-block">
                                  {{ passwordErrors.password[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group secondary-input">
                                <label for="confirm_password">{{ t('Confirm Password') }} <span class="text-danger">*</span></label>
                                <input 
                                  v-model="passwordForm.password_confirmation"
                                  @input="clearPasswordError('password_confirmation')"
                                  type="password" 
                                  id="confirm_password" 
                                  :placeholder="t('Enter your confirm password')" 
                                  class="form-control"
                                  :class="{ 'is-invalid': passwordErrors.password_confirmation }"
                                >
                                <div v-if="passwordErrors.password_confirmation" class="invalid-feedback d-block">
                                  {{ passwordErrors.password_confirmation[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button 
                              type="submit" 
                              class="btn common-animation-button common-animation-profile"
                              :disabled="isPasswordLoading"
                            >
                              <span v-if="isPasswordLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                              {{ isPasswordLoading ? t('Changing...') : t('Change Password') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Image Cropper Modal -->
        <VDialog v-model="showCropperModal" persistent max-width="800px">
            <VCard class="modal-card modal-card-md">
                <VCardTitle>{{ t('Crop Image') }}</VCardTitle>
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
                        {{ t('Crop & Save') }}  
                    </VBtn>
                    <VBtn color="error" variant="tonal" @click="cancelCrop">
                        <VIcon start icon="tabler-x" />
                        {{ t('Cancel') }}
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </div>
</template>

<style scoped>
.profile-info {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 2rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.avatar img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}

.info h5 {
  margin-bottom: 0.5rem;
  color: #333;
}

.info p {
  margin-bottom: 0.25rem;
  color: #666;
  font-size: 0.9rem;
}

.change-password-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #e9ecef;
}

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.text-muted {
  font-size: 0.8rem;
}
.avatar {
  position: relative;
}
.avatar .change-trigger {
  width: 30px;
  height: 30px;
  position: absolute;
  bottom: 0;
  right: 0;
  background-color: #FFFFFF;
  border: 1px solid #E4E4E4;
  color: #000 !important;
  padding: 0.5rem;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.change-trigger:hover {
  background-color: #dddddd;
}
.avatar .change-trigger i {
  font-size: 16px;
}

.cropper {
  height: 255px;
  background: #DDD;
}



.common-animation-profile {
  border-radius: 25px;
  padding: 10px 20px;
  font-size: 16px;
  font-weight: 500;
  color: white;
  background: var(--primary-bg-color);
}
.common-animation-profile::before {
  background-color: var(--primary-bg-hover-color);
}
.common-animation-profile::after {
  color: white;
  background: var(--primary-bg-color);
}
.common-animation-profile:hover {
  color: var(--color-white);
}


</style>
