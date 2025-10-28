<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted } from 'vue';
import defaultBanner from '@images/system-config/default-picture.png';
import { useI18n } from 'vue-i18n'
import { useWebsiteSettingsStore } from '@/stores/websiteSetting.js'

// Image Cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

const { t } = useI18n()

const router = useRouter()
const websiteSettingsStore = useWebsiteSettingsStore()
const loadings = ref(false)
const refInputEl = ref()
const headerLogoPreviewImage = ref(defaultBanner)
const footerLogoPreviewImage = ref(defaultBanner)
const favIconPreviewImage = ref(defaultBanner)
const commonBannerPreviewImage = ref(defaultBanner)
const loginPreviewImage = ref(defaultBanner)
const googleMapPreviewImage = ref(defaultBanner)

// Image Cropper variables
const showHeaderCropperModal = ref(false)
const showFooterCropperModal = ref(false)
const headerImageSrc = ref(null)
const footerImageSrc = ref(null)
const headerCroppedImage = ref(null)
const footerCroppedImage = ref(null)
const headerCropPreview = ref(null)
const footerCropPreview = ref(null)
let headerCropperRef = null
let footerCropperRef = null
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
const MAX_FILE_SIZE = 2 * 1024 * 1024 // 2MB
const MIN_WIDTH = 100
const MIN_HEIGHT = 50

// Cropper functions
function onHeaderCrop({ canvas }) {
    headerCropperRef = canvas
    headerCropPreview.value = canvas.toDataURL()
}

function onFooterCrop({ canvas }) {
    footerCropperRef = canvas
    footerCropPreview.value = canvas.toDataURL()
}

function getHeaderCroppedImage() {
    if (headerCropperRef) {
        headerCroppedImage.value = headerCropperRef.toDataURL()
        headerLogoPreviewImage.value = headerCroppedImage.value
        form.value.header_logo = convertDataURLtoFile(headerCroppedImage.value, 'header-cropped.jpg')
        showHeaderCropperModal.value = false
    }
}

function getFooterCroppedImage() {
    if (footerCropperRef) {
        footerCroppedImage.value = footerCropperRef.toDataURL()
        footerLogoPreviewImage.value = footerCroppedImage.value
        form.value.footer_logo = convertDataURLtoFile(footerCroppedImage.value, 'footer-cropped.jpg')
        showFooterCropperModal.value = false
    }
}

function convertDataURLtoFile(dataURL, filename) {
    const arr = dataURL.split(',')
    const mime = arr[0].match(/:(.*?);/)[1]
    const bstr = atob(arr[1])
    let n = bstr.length
    const u8arr = new Uint8Array(n)
    while(n--) {
        u8arr[n] = bstr.charCodeAt(n)
    }
    return new File([u8arr], filename, {type: mime})
}

function cancelHeaderCrop() {
    showHeaderCropperModal.value = false
    headerImageSrc.value = null
    headerCropPreview.value = null
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

function cancelFooterCrop() {
    showFooterCropperModal.value = false
    footerImageSrc.value = null
    footerCropPreview.value = null
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
        toast(t('File size must be less than or equal to 2 MB.'), { type: 'error' })
        return false
    }
    return true
}

function validateImageDimensions(img) {
    return true; // Disabled dimension validation for logos
}

const languageOptions = [
    { title: 'English', value: 'en' },
    { title: 'Bengali', value: 'bn' },
    { title: 'Spanish', value: 'es' },
    { title: 'French', value: 'fr' },
    { title: 'German', value: 'de' },
    { title: 'Chinese', value: 'zh' },
    { title: 'Japanese', value: 'ja' },
    { title: 'Korean', value: 'ko' },
]

const socialMediaPlatforms = [
    { name: 'Facebook'},
    { name: 'Twitter'},
    { name: 'Instagram'},
    { name: 'YouTube'},
    { name: 'TikTok'}
]

const form = ref({
    email: '',
    phone: '',
    address: '',
    languages: [],
    social_media: socialMediaPlatforms.map(platform => ({
        name: platform.name,
        url: '',
        is_active: false
    })),
    testimonial_title: '',
    testimonial_heading: '',
    common_banner_image: defaultBanner,
    login_image: defaultBanner,
    google_map_url: '',
    open_day_start: '',
    open_day_end: '',
    open_day_start_time: '',
    open_day_end_time: '',
    footer_copyright: '',
    footer_mini_description: '',
    header_logo: defaultBanner,
    footer_logo: defaultBanner,
    website_title: '',
    favicon: defaultBanner,
})

const errors = ref({
    email: '',
    phone: '',
    address: '',
    languages: '',
    social_media: Array(socialMediaPlatforms.length).fill(''),
    testimonial_title: '',
    testimonial_heading: '',
    common_banner_image: '',
    login_image: '',
    google_map_url: '',
    open_day_start: '',
    open_day_end: '',
    open_day_start_time: '',
    open_day_end_time: '',
    footer_copyright: '',
    footer_mini_description: '',
    header_logo: '',
    footer_logo: '',
    website_title: '',
    favicon: '',
})

// Fetch website settings
const fetchSettings = async () => {
    try {
        loadings.value = true
        const response = await $api('/website-settings')

        if (response.success && response.data) {
            const data = response.data
            let savedSocialMedia = []
            
            try {
                // Handle both string and already parsed object
                savedSocialMedia = typeof data.social_media == 'string' 
                    ? JSON.parse(data.social_media.replace(/\\/g, ''))
                    : data.social_media
            } catch (e) {
                console.error('Error parsing social media:', e)
                savedSocialMedia = []
            }
            
            form.value = {
                email: data.email || '',
                phone: data.phone || '',
                address: data.address || '',
                languages: typeof data.languages == 'string' ? JSON.parse(data.languages) : (data.languages || []),
                social_media: socialMediaPlatforms.map(platform => {
                    const saved = savedSocialMedia.find(sm => sm.name == platform.name)
                    return {
                        name: platform.name,
                        url: saved?.url || '',
                        is_active: saved?.is_active || false
                    }
                }),
                testimonial_title: data.testimonial_title || '',
                testimonial_heading: data.testimonial_heading || '',
                common_banner_image: data.common_banner_image || defaultBanner,
                login_image: data.login_image || defaultBanner,
                google_map_url: data.google_map_url || '',
                open_day_start: data.open_day_start || '',
                open_day_end: data.open_day_end || '',
                open_day_start_time: data.open_day_start_time || '',
                open_day_end_time: data.open_day_end_time || '',
                footer_copyright: data.footer_copyright || '',
                footer_mini_description: data.footer_mini_description || '',
                header_logo: data.header_logo_url || defaultBanner,
                footer_logo: data.footer_logo_url || defaultBanner,
                website_title: data.website_title || '',
                favicon: data.favicon_url || defaultBanner,
            }

            commonBannerPreviewImage.value = data.common_banner_image_url || defaultBanner  
            loginPreviewImage.value = data.login_image_url || defaultBanner
            headerLogoPreviewImage.value = data.header_logo_url || defaultBanner
            footerLogoPreviewImage.value = data.footer_logo_url || defaultBanner
            favIconPreviewImage.value = data.favicon_url || defaultBanner
        }
    } catch (err) {
        console.error('Error fetching settings:', err)
        toast('Error loading settings', { type: 'error' })
    } finally {
        loadings.value = false
    }
}


const changeCommonBannerImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            commonBannerPreviewImage.value = e.target.result
            form.value.common_banner_image = file
        }
        reader.readAsDataURL(file)
    }
}

const changeLoginImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            loginPreviewImage.value = e.target.result
            form.value.login_image = file
        }
        reader.readAsDataURL(file)
    }
}



const resetCommonBannerImage = () => {
    form.value.common_banner_image = defaultBanner
    commonBannerPreviewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

const resetLoginImage = () => {
    form.value.login_image = defaultBanner
    loginPreviewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

const changeHeaderLogoImage = (event) => {
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
            headerImageSrc.value = e.target.result
            showHeaderCropperModal.value = true
        }
        img.onerror = () => {
            toast(t('Invalid image file.'), { type: 'error' })
            event.target.value = ''
        }
        img.src = e.target.result
    }
    reader.readAsDataURL(file)
}

const resetHeaderLogoImage = () => {
    form.value.header_logo = defaultBanner
    headerLogoPreviewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
    headerImageSrc.value = null
    showHeaderCropperModal.value = false
}

const changeFooterLogoImage = (event) => {
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
            footerImageSrc.value = e.target.result
            showFooterCropperModal.value = true
        }
        img.onerror = () => {
            toast(t('Invalid image file.'), { type: 'error' })
            event.target.value = ''
        }
        img.src = e.target.result
    }
    reader.readAsDataURL(file)
}

const resetFooterLogoImage = () => {
    form.value.footer_logo = defaultBanner
    footerLogoPreviewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
    footerImageSrc.value = null
    showFooterCropperModal.value = false
}

const changeFavIconImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            favIconPreviewImage.value = e.target.result
            form.value.favicon = file
        }
        reader.readAsDataURL(file)
    }
}

const resetFavIconImage = () => {
    form.value.favicon = defaultBanner
    favIconPreviewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

onMounted(() => {
    fetchSettings()
})

const validateForm = () => {
    let isValid = true
    
    // Reset all errors first
    errors.value = {
        email: '',
        phone: '',
        address: '',
        languages: '',
        social_media: Array(socialMediaPlatforms.length).fill(''),
        testimonial_title: '',
        testimonial_heading: '',
        common_banner_image: '',
        login_image: ''
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!form.value.email) {
        errors.value.email = 'Email is required'
        isValid = false
    } else if (!emailRegex.test(form.value.email)) {
        errors.value.email = 'Invalid email format'
        isValid = false
    }

    // Website title validation
    if (!form.value.website_title) {
        errors.value.website_title = 'Website title is required'
        isValid = false
    }
    
    // Phone validation
    if (!form.value.phone) {
        errors.value.phone = 'Phone number is required'
        isValid = false
    }
    
    // Address validation
    if (!form.value.address) {
        errors.value.address = 'Address is required'
        isValid = false
    }
    
    // Languages validation
    if (form.value.languages.length == 0) {
        errors.value.languages = 'Please select at least one language'
        isValid = false
    }
    
    // Social Media validation
    form.value.social_media.forEach((social, index) => {
        if (social.is_active && !social.url) {
            errors.value.social_media[index] = `${social.name} URL is required when active`
            isValid = false
        }
    })

    // Testimonial validation
    if (!form.value.testimonial_title) {
        errors.value.testimonial_title = 'Testimonial title is required'
        isValid = false
    }

    if (!form.value.testimonial_heading) {
        errors.value.testimonial_heading = 'Testimonial heading is required'
        isValid = false
    }


    return isValid
}

const saveSettings = async () => {
    if (!validateForm()) {
        toast('Please fill all required fields correctly', {
            type: 'error'
        })
        return
    }

    try {
        loadings.value = true
        const formData = new FormData()
        
        // Append form fields
        Object.keys(form.value).forEach(key => {
            if (['common_banner_image', 'login_image', 'header_logo', 'footer_logo', 'favicon'].includes(key)) {
                if (form.value[key] instanceof File) {
                    formData.append(key, form.value[key])
                }
            } else if (key == 'social_media') {
                formData.append(key, JSON.stringify(form.value[key]))
            } else if (key == 'languages') {
                formData.append(key, JSON.stringify(form.value[key]))
            } else {
                formData.append(key, form.value[key] || '')
            }
        })

        const response = await $api('/website-settings-update', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            },
        })

        if (response.success) {
            toast('Settings updated successfully', {
                type: 'success'
            })
            await fetchSettings() // Refresh data after update
            // Refresh website settings store to update title and favicon
            await websiteSettingsStore.resetSettings()
        } else {
            throw new Error(response.message || 'Failed to update settings')
        }
    } catch (err) {
        console.error('Error updating settings:', err)
        toast(err.message || 'Error updating settings', {
            type: 'error'
        })
    } finally {
        loadings.value = false
    }
}

const resetForm = () => {
    fetchSettings() // Reset to original data
    
    // Reset error messages
    errors.value = {
        email: '',
        phone: '',
        address: '',
        languages: '',
        social_media: Array(socialMediaPlatforms.length).fill(''),
        testimonial_title: '',
        testimonial_heading: '',
        common_banner_image: '',
        login_image: '',
        google_map_url: '',
        open_day_start: '',
        open_day_end: '',
        open_day_start_time: '',
        open_day_end_time: '',
        footer_copyright: '',
        footer_mini_description: '',
        header_logo: '',
        footer_logo: '',
        website_title: '',
        favicon: '',
    }
}

</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard class="mb-4">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="saveSettings">
                        <VRow>
                            <!-- Basic Settings -->
                            <VCol cols="12">   
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Basic Settings') }}</h5>
                            </VCol>
                            <!-- Email -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    v-model="form.email"
                                    :label="t('Email Address')"
                                    type="email"
                                    :placeholder="t('Enter email address')"
                                    :error-messages="errors.email"
                                    :required="true"
                                />
                            </VCol>

                            <!-- Phone -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField 
                                    v-model="form.phone"
                                    :label="t('Phone Number')"
                                    type="text"
                                    :placeholder="t('Enter phone number')"
                                    :error-messages="errors.phone"
                                    :required="true"
                                />
                            </VCol>

                            <!-- Address -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea
                                    v-model="form.address"
                                    :label="t('Address')"
                                    :placeholder="t('Enter full address')"
                                    :error-messages="errors.address"
                                    rows="3"
                                    :required="true"
                                />
                            </VCol>

                            <!-- Languages -->
                            <VCol cols="12">
                                <VSelect
                                    v-model="form.languages"
                                    :items="languageOptions"
                                    :label="t('Languages')"
                                    multiple
                                    chips
                                    :error-messages="errors.languages"
                                    :required="true"
                                />
                            </VCol>

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
                            <!-- Google Map Location -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea
                                    v-model="form.google_map_url"
                                    :label="t('Google Map Location')"
                                    :placeholder="t('Enter google map location')"
                                    :error-messages="errors.google_map_url"
                                    rows="3"
                                />
                            </VCol>

                            <!-- Opening Hours -->
                            <VCol cols="12">
                                <h6 class="text-h6 mb-4">{{ t('Opening Hours') }}</h6>
                            </VCol>
                            <VCol cols="12" md="6" lg="3">
                                <AppAutocomplete
                                    v-model="form.open_day_start"
                                    :label="t('Opening Day')"
                                    :items="[
                                        { title: 'Monday', value: 'Monday' },
                                        { title: 'Tuesday', value: 'Tuesday' },
                                        { title: 'Wednesday', value: 'Wednesday' },
                                        { title: 'Thursday', value: 'Thursday' },
                                        { title: 'Friday', value: 'Friday' },
                                        { title: 'Saturday', value: 'Saturday' },
                                        { title: 'Sunday', value: 'Sunday' }
                                    ]"
                                    :placeholder="t('Select opening day')"
                                    :error-messages="errors.open_day_start"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="3">
                                <AppAutocomplete
                                    v-model="form.open_day_end"
                                    :label="t('Closing Day')"
                                    :items="[
                                        { title: 'Monday', value: 'Monday' },
                                        { title: 'Tuesday', value: 'Tuesday' },
                                        { title: 'Wednesday', value: 'Wednesday' },
                                        { title: 'Thursday', value: 'Thursday' },
                                        { title: 'Friday', value: 'Friday' },
                                        { title: 'Saturday', value: 'Saturday' },
                                        { title: 'Sunday', value: 'Sunday' }
                                    ]"
                                    :placeholder="t('Select closing day')"
                                    :error-messages="errors.open_day_end"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="3">
                                <AppTextField
                                    v-model="form.open_day_start_time"
                                    :label="t('Opening Time')"
                                    type="time"
                                    :placeholder="t('Select opening time')"
                                    :error-messages="errors.open_day_start_time"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="3">
                                <AppTextField
                                    v-model="form.open_day_end_time"
                                    :label="t('Closing Time')"
                                    type="time"
                                    :placeholder="t('Select closing time')"
                                    :error-messages="errors.open_day_end_time"
                                />
                            </VCol>

                            <!-- Website Title and Favicon -->
                            <VCol cols="12">   
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Website Title and Favicon') }}</h5>
                            </VCol>
                            <!-- Footer -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.website_title" required="true" :label="t('Website Title')" :error-messages="errors.website_title"
                                    :placeholder="t('Enter website title')" />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" class="mb-4">
                                <VCardText class="d-flex">
                                    <div class="company-logo">
                                        <VAvatar rounded size="100" class="me-6" :image="favIconPreviewImage" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="$refs.faviconInput?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ t('Favicon') }}</span>
                                            </VBtn>

                                            <input ref="faviconInput" type="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeFavIconImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetFavIconImage">
                                                <span class="d-none d-sm-block">{{ t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>

                            <!-- Website Basic Settings -->
                            <VCol cols="12">   
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Logo & Copyright') }}</h5>
                            </VCol>
                            <!-- Footer -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextField v-model="form.footer_copyright" :label="t('Copyright/Footer Text')" :error-messages="errors.footer_copyright"
                                    :placeholder="t('Enter copyright/footer text')" />
                            </VCol>

                            <!-- Footer Mini Description -->
                            <VCol cols="12" md="6" lg="4">
                                <AppTextarea v-model="form.footer_mini_description" :label="t('Footer Mini Description')" :error-messages="errors.footer_mini_description"
                                    :placeholder="t('Enter footer mini description')" rows="3" />
                            </VCol>

                            <VCol cols="12" md="6" lg="4" class="mb-4">
                                <VCardText class="d-flex">
                                    <div class="company-logo">
                                        <VAvatar rounded size="100" class="me-6" :image="headerLogoPreviewImage" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="$refs.headerLogoInput?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ t('Header Logo') }}</span>
                                            </VBtn>

                                            <input ref="headerLogoInput" type="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeHeaderLogoImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetHeaderLogoImage">
                                                <span class="d-none d-sm-block">{{ t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                            <VBtn v-if="headerImageSrc" type="reset" size="small" color="secondary" variant="tonal" @click="showHeaderCropperModal = true">
                                                <span class="d-none d-sm-block">{{ t('Crop Image') }}</span>
                                                <VIcon icon="tabler-crop" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>
                            <VCol cols="12" md="6" lg="4" class="mb-4">
                                <VCardText class="d-flex">
                                    <div class="company-logo">
                                        <VAvatar rounded size="100" class="me-6" :image="footerLogoPreviewImage" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="$refs.footerLogoInput?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ t('Footer Logo') }}</span>
                                            </VBtn>

                                            <input ref="footerLogoInput" type="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeFooterLogoImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetFooterLogoImage">
                                                <span class="d-none d-sm-block">{{ t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                            <VBtn v-if="footerImageSrc" type="reset" size="small" color="secondary" variant="tonal" @click="showFooterCropperModal = true">
                                                <span class="d-none d-sm-block">{{ t('Crop Image') }}</span>
                                                <VIcon icon="tabler-crop" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>
                            

                            <!-- Testimonial -->
                            <VCol cols="12">
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Testimonial') }}</h5>
                            </VCol>
                            <VCol cols="12" md="6" lg="4" class="mb-4" >
                                <AppTextField
                                    v-model="form.testimonial_title"
                                    :label="t('Testimonial Title')"
                                    type="text"
                                    :placeholder="t('Enter testimonial title')"
                                    :error-messages="errors.testimonial_title"
                                />
                            </VCol>
                            <VCol cols="12" md="6" lg="4" class="mb-4">
                                <AppTextField
                                    v-model="form.testimonial_heading"
                                    :label="t('Testimonial Heading')"
                                    type="text"
                                    :placeholder="t('Enter testimonial heading')"
                                    :error-messages="errors.testimonial_heading"
                                />
                            </VCol>
                            
                            <!-- Common Banner -->
                            <VCol cols="12">
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Common Banner') }}</h5>
                            </VCol>
                            <VCol md="4" class="mb-4">
                                <VCardText class="d-flex">
                                    <VAvatar rounded size="100" class="me-6" :image="commonBannerPreviewImage" />
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="$refs.commonBannerInput?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ t('Upload Banner') }}</span>
                                            </VBtn>

                                            <input ref="commonBannerInput" type="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeCommonBannerImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetCommonBannerImage">
                                                <span class="d-none d-sm-block">{{ t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>

                            <!-- Login Image -->
                            <VCol cols="12">
                                <h5 class="text-h5 mb-4 devider-title">{{ t('Login Image') }}</h5>
                            </VCol>
                            <VCol md="4" class="mb-4">
                                <VCardText class="d-flex">
                                    <VAvatar rounded size="100" class="me-6" :image="loginPreviewImage" />
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="$refs.loginInput?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ t('Upload Image') }}</span>
                                            </VBtn>

                                            <input ref="loginInput" type="file" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeLoginImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetLoginImage">
                                                <span class="d-none d-sm-block">{{ t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" @click="resetForm">
                                    <VIcon start icon="tabler-circle-minus" />
                                    {{ t('Reset') }}
                                </VBtn>
                            </VCol>

                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Header Logo Cropper Modal -->
        <VDialog v-model="showHeaderCropperModal" persistent max-width="500px">
            <VCard class="modal-card modal-card-sm">
                <VCardTitle>{{ t('Crop Header Logo') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="headerImageSrc"
                        :min-width="100"
                        :min-height="50"
                        @change="onHeaderCrop"
                    />
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn color="primary" variant="tonal" @click="getHeaderCroppedImage">
                        <VIcon start icon="tabler-crop" />
                        {{ t('Crop & Save') }}
                    </VBtn>
                    <VBtn color="error" variant="tonal" @click="cancelHeaderCrop">
                        <VIcon start icon="tabler-x" />
                        {{ t('Cancel') }}
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>

        <!-- Footer Logo Cropper Modal -->
        <VDialog v-model="showFooterCropperModal" persistent max-width="500px">
            <VCard class="modal-card modal-card-sm">
                <VCardTitle>{{ t('Crop Footer Logo') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="footerImageSrc"
                        :min-width="100"
                        :min-height="50"
                        @change="onFooterCrop"
                    />
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn color="primary" variant="tonal" @click="getFooterCroppedImage">
                        <VIcon start icon="tabler-crop" />
                        {{ t('Crop & Save') }}
                    </VBtn>
                    <VBtn color="error" variant="tonal" @click="cancelFooterCrop">
                        <VIcon start icon="tabler-x" />
                        {{ t('Cancel') }}
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </VRow>
</template>

<style scoped>
.devider-title {
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
}

.cropper {
    height: 200px;
    background: #DDD;
}
</style>