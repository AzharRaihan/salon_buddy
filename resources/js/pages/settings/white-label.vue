<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import defaultAvater from '@images/system-config/default-picture.png';
import { useSiteSettingsStore } from '@/stores/siteSettings.js'

const baseUrl = import.meta.env.VITE_APP_URL

const { t } = useI18n()
const siteSettingsStore = useSiteSettingsStore()

console.log(siteSettingsStore)

// Image Cropper
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
const showCropperModal = ref(false)
const imageSrc = ref(null)
const croppedImage = ref(null) 
const cropPreview = ref(null)
const refInputEl = ref()
const previewImage = ref(siteSettingsStore.getCompanyLogo || defaultAvater)
let cropperRef = null
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
const MAX_FILE_SIZE = 1 * 1024 * 1024 // 1MB
const MIN_WIDTH = 215
const MIN_HEIGHT = 40
function onCrop({ canvas }) {
  cropperRef = canvas
  cropPreview.value = canvas.toDataURL()
}
function getCroppedImage() {
  if (cropperRef) {
    croppedImage.value = cropperRef.toDataURL()
    previewImage.value = croppedImage.value
    form.value.logo = convertDataURLtoFile(croppedImage.value, 'cropped.jpg')
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
  form.value.logo = defaultAvater
  previewImage.value = defaultAvater
  if (refInputEl.value) {
    refInputEl.value.value = ''
  }
  imageSrc.value = null
  showCropperModal.value = false
}
// Cropper End



const form = ref({
    site_title: '',
    footer: '',
    company_name: '',
    company_website: '',
    favicon: defaultAvater,
    logo: defaultAvater
})

const errors = ref({})
const loadings = ref(false)
const refInputElFav = ref()
const previewFav = ref(defaultAvater)
const refInputElLogo = ref()
const previewLogo = ref(defaultAvater)

const validateForm = () => {
    errors.value = {}

    if (!form.value.site_title) errors.value.site_title = t('Site title is required')
    if (!form.value.footer) errors.value.footer = t('Footer text is required')
    if (!form.value.company_name) errors.value.company_name = t('Company name is required')
    if (!form.value.company_website) errors.value.company_website = t('Site link is required')

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        site_title: '',
        footer: '',
        company_name: '',
        company_website: '',
        favicon: defaultAvater,
        logo: defaultAvater
    }
}

const checkImage = (url, fallback) => {
    return new Promise((resolve) => {
        if (!url) {
            resolve(fallback)
            return
        }

        const img = new Image()
        img.src = url

        img.onload = () => resolve(url)       // file exists
        img.onerror = () => resolve(fallback) // fallback if missing
    })
}

const getWhiteLabelSettings = async () => {
    try {
        const res = await $api('/white-label', {
            method: 'GET'
        })

        if (res.success === true) {
            form.value = res.data

            previewFav.value = await checkImage(
                res.data.favicon_url,
                baseUrl + '/public/assets/images/system-config/default-picture.png'
            )

            previewLogo.value = await checkImage(
                res.data.logo_url,
                baseUrl + '/public/assets/images/system-config/default-picture.png'
            )
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch white label settings'), {
            type: 'error'
        })
    }
}

const updateWhiteLabelSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const formData = new FormData()
        Object.keys(form.value).forEach(key => {
            if (form.value[key] !== null && form.value[key] !== undefined) {
                formData.append(key, form.value[key])
            }
        })

        const res = await $api('/white-label', {
            method: 'POST',
            body: formData,
        })

        if (res.success == true) {
            toast(res.message, {
                type: 'success'
            })
            loadings.value = false
            await getWhiteLabelSettings()
            // Refresh site settings store to update title and favicon
            await siteSettingsStore.resetSettings()
        }
    } catch (err) {
        console.error(err)
        toast(err.response.data.message, {
            type: 'error'
        })
        loadings.value = false
    }
}

const changeImageFav = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            previewFav.value = e.target.result
            form.value.favicon = file
        }
        reader.readAsDataURL(file)
    }
}
const changeImageLogo = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            previewLogo.value = e.target.result
            form.value.logo = file
        }
        reader.readAsDataURL(file)
    }
}
const resetFav = () => {
    form.value.favicon = null
    previewFav.value = defaultAvater
    if (refInputElFav.value) {
        refInputElFav.value.value = ''
    }
}
const resetLogo = () => {
    form.value.logo = null
    previewLogo.value = defaultAvater
    if (refInputElLogo.value) {
        refInputElLogo.value.value = ''
    }
}

onMounted(() => {
    getWhiteLabelSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('White Label Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateWhiteLabelSettings" enctype="multipart/form-data">
                        <VRow>
                            <!-- Site Title -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.site_title" :label="t('Site Title')" :required="true"
                                    :error-messages="errors.site_title" :placeholder="t('Enter site title')" />
                            </VCol>

                            <!-- Footer -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.footer" :label="t('Footer Text')" :required="true" :error-messages="errors.footer"
                                    :placeholder="t('Enter footer text')" />
                            </VCol>

                            <!-- Company Name -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.company_name" :label="t('Company Name')" :required="true"
                                    :error-messages="errors.company_name" :placeholder="t('Enter company name')" />
                            </VCol>

                            <!-- Company Website -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.company_website" :label="t('Site Link')" :required="true"
                                    :error-messages="errors.company_website" :placeholder="t('Enter site link')" />
                            </VCol>

                            
                            <!-- Logo -->
                            <!-- <VCol cols="12" md="6">
                                <VCardText class="d-flex">
                                    <div class="company-logo">
                                        <VAvatar rounded size="100" class="me-6" :image="previewLogo" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputElLogo?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Site Logo') }}</span>
                                            </VBtn>

                                            <input ref="refInputElLogo" type="file" name="photo" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeImageLogo">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetLogo">
                                                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ $t('Allowed JPG, GIF or PNG. Max size of 2 MB (Logo)') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol> -->

                            <VCol cols="12">
                                <VCardText class="d-flex">
                                    <!-- 👉 Image Preview -->

                                    <div class="default-img-upload">
                                        <VAvatar rounded size="100" class="me-6" :image="previewImage" />
                                    </div>

                                    <!-- 👉 Upload Image -->
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputEl?.click()" >
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Upload Sit Logo') }}</span>
                                            </VBtn>

                                            <input ref="refInputEl" type="file" name="logo" accept=".jpeg,.png,.jpg" hidden @input="changeImage">

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
                                            {{ $t('Recommended size: 215px x 40px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>

                            <!-- Favicon -->
                            <VCol cols="12" md="6">
                                <VCardText class="d-flex">
                                    <div class="default-img-upload">
                                        <VAvatar rounded size="100" class="me-6" :image="previewFav" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputElFav?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Favicon') }}</span>
                                            </VBtn>

                                            <input ref="refInputElFav" type="file" name="photo" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeImageFav">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetFav">
                                                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ $t('Allowed JPG, GIF or PNG. Max size of 2 MB (Favicon)') }}
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

        <!-- Image Cropper Modal -->
        <VDialog v-model="showCropperModal" persistent max-width="400px">
            <VCard class="modal-card modal-card-sm">
                <VCardTitle>{{ $t('Crop Image') }}</VCardTitle>
                <VCardText>
                    <cropper
                        class="cropper"
                        :src="imageSrc"
                        :min-size="215"
                        :max-size="40"
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
  height: 100px;
  background: #DDD;
}
/* .v-card-title {
  padding: 25px 25px 0px 25px !important;
}
.v-card-actions {
  padding: 0px 25px 25px 25px !important;
} */
</style>