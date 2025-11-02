<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import defaultAvater from '@images/system-config/default-picture.png';

// Image Cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

const { t } = useI18n()

const router = useRouter()
const loadings = ref(false)

// Image Cropper Variables
const showCropperModal = ref(false)
const imageSrc = ref(null)
const croppedImage = ref(null) 
const cropPreview = ref(null)
const refInputEl = ref()
const previewImage = ref(defaultAvater)
let cropperRef = null
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
const MAX_FILE_SIZE = 1 * 1024 * 1024 // 1MB
const MIN_WIDTH = 200
const MIN_HEIGHT = 200

// Cropper Functions
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

const socialMediaPlatforms = [
    { name: 'Facebook'},
    { name: 'Instagram'},
    { name: 'YouTube'},
    { name: 'TikTok'}
]

const form = ref({
    name: '',
    designation: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: null,
    branch_id: [],
    salary: 0,
    service_id: [],
    commission: 0,
    overtime_hour_rate: 0,
    status: null,
    will_login: 'Yes',
    photo: defaultAvater,
    age: '',
    gender: null,
    qualification: '',
    experience: '',
    description: '',
    social_media: socialMediaPlatforms.map(platform => ({
        name: platform.name,
        url: '',
        is_active: false
    })),
})

const nameError = ref('')
const designationError = ref('')
const emailError = ref('')
const passwordError = ref('')
const roleError = ref('')
const passwordConfirmationError = ref('')
const phoneError = ref('')
const branchError = ref('')
const salaryError = ref('')
const serviceError = ref('')
const statusError = ref('')
const ageError = ref('')
const genderError = ref('')
const qualificationError = ref('')
const experienceError = ref('')
const descriptionError = ref('')

// Add social media error handling
const errors = ref({
    social_media: Array(socialMediaPlatforms.length).fill(''),
})

const validateName = (name) => {
    if (!name) {
        nameError.value = t('Name is required')
        return false
    }
    nameError.value = ''
    return true
}

const validateDesignation = (designation) => {
    if (!designation) {
        designationError.value = t('Designation is required')
        return false
    }
    designationError.value = ''
    return true
}

const validateEmail = (email) => {
    if (!email) {
        emailError.value = t('Email is required')
        return false
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailError.value = t('Please enter a valid email')
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

const validatePassword = (password) => {
    if (!password && form.value.will_login == 'Yes') {
        passwordError.value = t('Password is required')
        return false
    }
    if (password && password.length < 6 && form.value.will_login == 'Yes') {
        passwordError.value = t('Password must be at least 6 characters')
        return false
    }

    passwordError.value = ''
    return true
}

const validatePasswordConfirmation = (passwordConfirmation) => {
    if (passwordConfirmation !== form.value.password && form.value.will_login == 'Yes') {
        passwordConfirmationError.value = t('Password confirmation does not match')
        return false
    }
    passwordConfirmationError.value = ''
    return true
}

const validateRole = (role) => {
    if (!role) {
        roleError.value = t('Role is required')
        return false
    }
    roleError.value = ''
    return true
}

const validateBranch = (branch) => {
    if (!branch || branch.length == 0 && form.value.will_login == 'Yes') {
        branchError.value = t('At least one branch is required')
        return false
    }
    branchError.value = ''
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

const validateService = (service) => {
    if (!service || service.length == 0) {
        serviceError.value = t('At least one service is required')
        return false
    }
    serviceError.value = ''
    return true
}

const validateAge = (age) => {
    if (age.length > 10) {
        ageError.value = t('Age must be less than 10 characters (eg: 25)')
        return false
    }
    ageError.value = ''
    return true
}
const validateGender = (gender) => {
    if (!gender) {
        genderError.value = t('Gender is required')
        return false
    }
    genderError.value = ''
    return true
}
const validateExperience = (experience) => {
    if (!experience) {
        experienceError.value = t('Experience is required')
        return false
    }
    if(experience.length > 25) {
        experienceError.value = t('Experience must be less than 25 characters (eg: 6 years)')
        return false
    }
    experienceError.value = ''
    return true
}
const validateQualification = (qualification) => {
    if (qualification.length > 255) {
        qualificationError.value = t('Qualification must be less than 255 characters')
        return false
    }
    qualificationError.value = ''
    return true
}
const validateDescription = (description) => {
    if (description.length > 255) {
        descriptionError.value = t('Description must be less than 255 characters')
        return false
    }
    descriptionError.value = ''
    return true
}

// Add social media validation
const validateSocialMedia = () => {
    let isValid = true
    errors.value.social_media = Array(socialMediaPlatforms.length).fill('')
    
    form.value.social_media.forEach((social, index) => {
        if (social.is_active && !social.url.trim()) {
            errors.value.social_media[index] = `${social.name} ${t('URL is required when active')}`
            isValid = false
        } else if (social.is_active && social.url.trim()) {
            // Basic URL validation
            const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/
            if (!urlPattern.test(social.url.trim())) {
                errors.value.social_media[index] = t('Please enter a valid') + ' ' + social.name + ' ' + t('URL')
                isValid = false
            }
        }
    })
    
    return isValid
}

const roles = ref([])
const branches = ref([])
const services = ref([])

// Fetch roles when component mounts
const fetchRoles = async () => {
    try {
        const response = await $api('/roles')
        roles.value = [
            // { title: 'Select Role', value: '' },
            ...response.data.roles.map(role => ({
                title: role.name,
                value: role.id
            }))
        ]
    } catch (error) {
        console.error('Error fetching roles:', error)
        toast(t('Failed to load roles'), {
            type: 'error'
        })
    }
}

// Fetch branches when component mounts
const fetchBranches = async () => {
    try {
        const response = await $api('/get-branch-list')
        branches.value = [
            ...response.data.map(branch => ({
                title: branch.branch_name,
                value: branch.id
            }))
        ]
    } catch (error) {
        console.error('Error fetching branches:', error)
        toast(t('Failed to load branches'), {
            type: 'error'
        })
    }
}

// Fetch services when component mounts
const fetchServices = async () => {
    try {
        const response = await $api('/get-service-type-item-list')
        services.value = [
            ...response.data.map(service => ({
                title: service.name,
                value: service.id
            }))
        ]
    } catch (error) {
        console.error('Error fetching services:', error)
        toast(t('Failed to load services'), {
            type: 'error'
        })
    }
}

onMounted(() => {
    fetchRoles()
    fetchBranches()
    fetchServices()
})

const resetForm = () => {
    form.value = {
        name: '',
        designation: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        role: '',
        branch_id: [],
        salary: '',
        service_id: [],
        commission: '',
        overtime_hour_rate: '',
        status: '',
        will_login: 'Yes',
        photo: defaultAvater,
        age: '',
        gender: '',
        qualification: '',
        experience: '',
        description: '',
        social_media: socialMediaPlatforms.map(platform => ({
            name: platform.name,
            url: '',
            is_active: false
        })),
    }
    nameError.value = ''
    designationError.value = ''
    emailError.value = ''
    passwordError.value = ''
    roleError.value = ''
    phoneError.value = ''
    branchError.value = ''
    salaryError.value = ''
    serviceError.value = ''
    errors.value.social_media = Array(socialMediaPlatforms.length).fill('')
    
    // Reset image
    resetImage()
}

const validateForm = () => {
    const isNameValid = validateName(form.value.name)
    const isDesignationValid = validateDesignation(form.value.designation)
    const isEmailValid = validateEmail(form.value.email)
    const isPhoneValid = validatePhone(form.value.phone)
    const isPasswordValid = validatePassword(form.value.password)
    const isPasswordConfirmationValid = validatePasswordConfirmation(form.value.password_confirmation)
    const isRoleValid = validateRole(form.value.role)
    const isBranchValid = validateBranch(form.value.branch_id)
    const isServiceValid = validateService(form.value.service_id)
    const isSocialMediaValid = validateSocialMedia()
    const isStatusValid = validateStatus(form.value.status)

    const isAgeValid = validateAge(form.value.age)
    const isGenderValid = validateGender(form.value.gender)
    const isQualificationValid = validateQualification(form.value.qualification)
    const isExperienceValid = validateExperience(form.value.experience)
    const isDescriptionValid = validateDescription(form.value.description)

    return isNameValid && isDesignationValid && isEmailValid && isPhoneValid && isPasswordValid && isPasswordConfirmationValid && isRoleValid && isBranchValid && isStatusValid &&
    isServiceValid && isSocialMediaValid && isAgeValid && isGenderValid && isQualificationValid && isExperienceValid && isDescriptionValid
}

const createUser = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        
        // Append basic form fields
        Object.keys(form.value).forEach(key => {
            if (key == 'photo') {
                if (form.value.photo instanceof File) {
                    formData.append('photo', form.value.photo)
                }
            } else if (key == 'social_media') {
                formData.append('social_media', JSON.stringify(form.value.social_media))
            } else if (key == 'branch_id' || key == 'service_id') {
                // Handle arrays
                if (Array.isArray(form.value[key])) {
                    formData.append(key, JSON.stringify(form.value[key]))
                } else {
                    formData.append(key, form.value[key])
                }
            } else {
                formData.append(key, form.value[key])
            }
        })

        const res = await $api('/users', {
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
        router.push({ name: 'employee' })
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
            <VCard :title="t('Add Employee')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createUser">
                        <VRow>
                            <!-- Name -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.name" :label="t('Name')" :required="true" type="text" :placeholder="t('Enter Name')"
                                    :error-messages="nameError" @input="validateName($event.target.value)"
                                    />
                            </VCol>
                            <!-- Email -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.email" :label="t('Email')" :required="true" type="email" :placeholder="t('Enter email')"
                                    :error-messages="emailError" @input="validateEmail($event.target.value)" />
                            </VCol>
                            <!-- Phone -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.phone" :label="t('Phone')" :required="true" type="text"
                                    :placeholder="t('Enter phone number')" :error-messages="phoneError" @input="validatePhone($event.target.value)" />
                            </VCol>
                            
                            <!-- Role -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.role"
                                    :items="roles"
                                    :label="t('Role')"
                                    :required="true"
                                    :placeholder="t('Select role')"
                                    :error-messages="roleError"
                                    @change="validateRole($event)"
                                    :initial-placeholder="true"
                                    clearable
                                     />
                            </VCol>

                            <!-- Designation -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.designation" :label="t('Designation')" :required="true" type="text" :placeholder="t('Enter Designation')"
                                    :error-messages="designationError" @input="validateDesignation($event.target.value)" />
                            </VCol>

                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.age" :label="t('Age')" type="number" :placeholder="t('Enter Age')" :error-messages="ageError" />
                            </VCol>
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.gender" :required="true"
                                    :items="[{title: 'Male', value: 'Male'}, {title: 'Female', value: 'Female'}, {title: 'Other', value: 'Other'}]" 
                                    :label="t('Gender')"
                                    :placeholder="t('Select Gender')"
                                    :error-messages="genderError" />
                            </VCol>
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.experience" :label="t('Experience')" type="text" :required="true" :placeholder="t('Enter Experience')" :error-messages="experienceError" />
                            </VCol>

                            <!-- Salary -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.salary" :label="t('Salary')" type="number" :placeholder="t('Enter Salary')"
                                    :error-messages="salaryError" @input="validateSalary($event.target.value)" />
                            </VCol>

                            <!-- Commission -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.commission" :label="t('Commission')" type="number" :placeholder="t('Enter Commission')" min="0" />
                            </VCol>
                            
                            <!-- Overtime Hour -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.overtime_hour_rate" :label="t('Hourly Rate For Overtime')" type="number" :placeholder="t('Enter Hourly Rate For Overtime')" />
                            </VCol>

                            
                            <!-- Service -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.service_id"
                                    :items="services"
                                    :label="t('Service')"
                                    :required="true"
                                    :placeholder="t('Select services')"
                                    :error-messages="serviceError"
                                    @change="validateService($event)"
                                    chips
                                    multiple
                                    closable-chips />
                            </VCol>

                            <!-- Status -->
                            <VCol cols="12" md="6" lg="4">
                                <AppAutocomplete v-model="form.status" :required="true"
                                    :items="[{title: 'Active', value: 'Active'}, {title: 'Inactive', value: 'Inactive'}]" 
                                    :label="t('Status')"
                                    :placeholder="t('Select status')"
                                    :error-messages="statusError"
                                    @change="validateStatus($event)" />
                            </VCol>
                        </VRow>


                        <!-- Team Details -->
                        <VRow>
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.qualification" :label="t('Qualification')" type="text" :placeholder="t('Enter Qualification')" :error-messages="qualificationError" />
                            </VCol>
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.description" :label="t('Description')" type="text" :placeholder="t('Enter Description')" :error-messages="descriptionError" />
                            </VCol>
                        </VRow>

                        <VRow>
                            <!-- Social Media -->
                            <VCol cols="12">
                                <h6 class="text-h6 mb-4">{{ t('Social Media Links') }}</h6>
                                <VRow>
                                    <VCol md="4" v-for="(social, index) in form.social_media" :key="social.name" class="mb-4">
                                        <AppTextField
                                            v-model="social.url"
                                            :label="`${social.name} ${t('URL')}`"
                                            type="url"
                                            :placeholder="`${t('Enter')} ${social.name} ${t('profile URL')}`"
                                            :disabled="!social.is_active"
                                            :error-messages="errors.social_media[index]"
                                            class="flex-grow-1"
                                        />
                                        <VSwitch
                                            v-model="social.is_active"
                                            :label="social.name"
                                            color="primary"
                                            class="flex-grow-0"
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                        <!-- Image Upload Section -->
                        <VRow>
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
                        </VRow>


                        <VRow>
                            <!-- Will Login -->
                            <VCol cols="12" md="6" lg="4">
                                <VRadioGroup v-model="form.will_login" :label="t('Will Login?')" inline>
                                    <VRadio :label="t('Yes')" value="Yes" />
                                    <VRadio :label="t('No')" value="No" />
                                </VRadioGroup>
                            </VCol>
                        </VRow>

                        <VRow>
                            <!-- Password fields only shown if will_login is Yes -->
                            <template v-if="form.will_login == 'Yes'">
                                <!-- Password -->
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField v-model="form.password" :label="t('Password')" type="password" 
                                        :required="true"
                                        :placeholder="t('Enter Password')" :error-messages="passwordError"
                                        @input="validatePassword($event.target.value)" />
                                </VCol>
                                <!-- Password Confirmation -->
                                <VCol cols="12" md="6" lg="4">
                                    <AppTextField v-model="form.password_confirmation" :label="t('Confirm Password')"
                                        :required="true"
                                        type="password" :placeholder="t('Confirm Password')"
                                        :error-messages="passwordConfirmationError"
                                        @input="validatePasswordConfirmation($event.target.value)" />
                                </VCol>

                                <!-- Branch -->
                                <VCol cols="12" md="6" lg="4">
                                    <AppAutocomplete v-model="form.branch_id"
                                        :items="branches"
                                        :label="t('Branch')"
                                        :required="true"
                                        :placeholder="t('Select branches')"
                                        :error-messages="branchError"
                                        @change="validateBranch($event)"
                                        chips
                                        multiple
                                        closable-chips />
                                </VCol>

                            </template>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'employee' })">
                                    <VIcon start icon="tabler-arrow-back" />
                                        {{ t('Back') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" type="reset" @click.prevent="resetForm">
                                    <VIcon start icon="tabler-refresh" />
                                    {{ t('Reset') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>

    <!-- Image Cropper Modal -->
    <VDialog v-model="showCropperModal" persistent max-width="400px">
        <VCard class="modal-card modal-card-md">
            <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
            <VCardText>
                <cropper
                    class="cropper"
                    :src="imageSrc"
                    :stencil-props="{
                        aspectRatio: 200/200,
                    }"
                    :min-size="200"
                    :max-size="200"
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
</template>

<style scoped>
.cropper {
  height: 260px;
  background: #DDD;
}
</style>

