<script setup>
import settings from '@core/json/settings.json'
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import defaultAvater from '@images/system-config/default-picture.png';



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
const MIN_WIDTH = 200
const MIN_HEIGHT = 100
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


const { t } = useI18n()


const form = ref({
    name: '',
    email: '',
    phone: '',
    website: '',
    address: '', 
    currency: '',
    currency_position: null,
    precision: null,
    thousand_separator: null,
    decimal_separator: null,
    date_format: null,
    timezone: null,
    logo: defaultAvater,
    print_formate: null,
    over_sale: null,
    use_website: null,
    default_email_select: null,
    default_sms_select: null,
    default_whatsapp_select: null,
})
const loadings = ref(false)
const errors = ref({})
const timezones = ref([])

const validateForm = () => {
    errors.value = {}

    if (!form.value.name) errors.value.name = t('Company name is required')
    if (!form.value.email) errors.value.email = t('Email is required')
    if (!form.value.phone) errors.value.phone = t('Phone is required')
    if (!form.value.currency) errors.value.currency = t('Currency is required')
    if (!form.value.currency_position) errors.value.currency_position = t('Currency position is required')
    if (!form.value.precision) errors.value.precision = t('Precision is required')
    if (!form.value.thousand_separator) errors.value.thousand_separator = t('Thousand separator is required')
    if (!form.value.decimal_separator) errors.value.decimal_separator = t('Decimal separator is required')
    if (!form.value.date_format) errors.value.date_format = t('Date format is required')
    if (!form.value.timezone) errors.value.timezone = t('Timezone is required')
    if (!form.value.print_formate) errors.value.print_formate = t('Printer format is required')
    if (!form.value.over_sale) errors.value.over_sale = t('Over sale is required')
    if (!form.value.use_website) errors.value.use_website = t('Use website is required')
    if (!form.value.address) errors.value.address = t('Address is required')
    if (!form.value.default_email_select) errors.value.default_email_select = t('Default email select is required')
    if (!form.value.default_sms_select) errors.value.default_sms_select = t('Default SMS select is required')
    if (!form.value.default_whatsapp_select) errors.value.default_whatsapp_select = t('Default WhatsApp select is required')

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        phone: '',
        website: '',
        address: '',
        currency: null,
        currency_position: null,
        precision: null,
        thousand_separator: null,
        decimal_separator: null,
        date_format: null,
        timezone: null,
        logo: defaultAvater,
        logo_url: null,
        print_formate: null,
        over_sale: null,
        use_website: null,
        default_email_select: null,
        default_sms_select: null,
        default_whatsapp_select: null,
    }
}

const getTimezones = async () => {
    try {
        const res = await $api('/get-timezone-list')
        if (res.success == true) {
            timezones.value = res.data.map(timezone => ({
                title: timezone,
                value: timezone
            }))
        }
    } catch (err) {
        console.error(err)
        toast('Failed to fetch timezones', {
            type: 'error'
        })
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


const getCompanySettings = async () => {
    try {
        const res = await $api('/get-company-info')
        if (res.success == true) {
            form.value = {
                ...form.value,
                name: res.data.name,
                email: res.data.email,
                phone: res.data.phone,
                website: res.data.website,
                address: res.data.address,
                currency: res.data.currency,
                currency_position: res.data.currency_position,
                precision: res.data.precision,
                thousand_separator: res.data.thousand_separator,
                decimal_separator: res.data.decimal_separator,
                date_format: res.data.date_format,
                timezone: res.data.timezone,
                logo: res.data.logo || defaultAvater,
                print_formate: res.data.print_formate ? res.data.print_formate.trim() : null,
                over_sale: res.data.over_sale || null,
                // trim space
                use_website: res.data.use_website ? res.data.use_website.trim() : null,
                default_email_select: res.data.default_email_select ? res.data.default_email_select.trim() : null,
                default_sms_select: res.data.default_sms_select ? res.data.default_sms_select.trim() : null,
                default_whatsapp_select: res.data.default_whatsapp_select ? res.data.default_whatsapp_select.trim() : null,
            }

            // Set preview image
            previewImage.value = await checkImage(
                res.data.logo_url,
                defaultAvater
            )
        }
    } catch (err) {
        console.error(err)
        toast('Failed to fetch company settings', {
            type: 'error'
        })
    }
}

const updateCompanySettings = async () => {
    try {
        loadings.value = true
        if (!validateForm()) {
            loadings.value = false
            return
        }

        const formData = new FormData()
        Object.keys(form.value).forEach(key => {
            if (key !== 'logo_url' && form.value[key] !== null) { // Skip logo_url when creating FormData
                formData.append(key, form.value[key])
            }
        })

        const res = await $api('/company-info', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })

        if (res.success == true) {
            toast(res.message, {
                type: 'success'
            })
            await getCompanySettings() // Refresh data after successful update
        }
    } catch (err) {
        console.error(err)
        toast(err?.response?.data?.message || 'An error occurred while updating settings', {
            type: 'error'
        })
    } finally {
        loadings.value = false
    }
}


onMounted(() => {
    getCompanySettings()
    getTimezones()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Company Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateCompanySettings" enctype="multipart/form-data">
                        <VRow>
                            <!-- Company Name -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.name" :label="t('Company Name')" :required="true" :error-messages="errors.name"
                                    :placeholder="t('Enter company name')" />
                            </VCol>


                            <!-- Email -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.email" :label="t('Email')" :required="true" type="email"
                                    :error-messages="errors.email" :placeholder="t('Enter email')" />
                            </VCol>

                            <!-- Phone -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.phone" :label="t('Phone')" :required="true" :error-messages="errors.phone"
                                    :placeholder="t('Enter phone number')" />
                            </VCol>

                            <!-- Address -->
                            <VCol cols="12" md="4">
                                <AppTextarea v-model="form.address" :required="true" :label="t('Address')" :error-messages="errors.address"
                                    :placeholder="t('Enter address')" />
                            </VCol>

                            <!-- Website -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.website" :label="t('Website')" :error-messages="errors.website"
                                    :placeholder="t('Enter website')" />
                            </VCol>


                            <!-- Currency -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.currency" :label="t('Currency')" :required="true" :error-messages="errors.currency"
                                    :placeholder="t('Enter currency')" />
                            </VCol>

                            <!-- Currency Position -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.currency_position" :label="t('Currency Position')" :required="true"
                                    :items="[
                                        { title: 'Before Amount', value: 'Before Amount' },
                                        { title: 'After Amount', value: 'After Amount' }
                                    ]"
                                    
                                    :error-messages="errors.currency_position" :placeholder="t('Select currency position')"
                                    clearable
                                    />
                            </VCol>

                            <!-- Precision -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.precision" :label="t('Precision')" :required="true"
                                    :items="[
                                        { title: '2 Digit', value: 2 },
                                        { title: '3 Digit', value: 3 }
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.precision" :placeholder="t('Select precision')"
                                    clearable
                                    />
                            </VCol>

                            <!-- Thousand Separator -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.thousand_separator" :label="t('Thousand Separator')" :required="true"
                                    :items="[
                                        { title: 'Dot (.)', value: '.' },
                                        { title: 'Comma (,)', value: ',' },
                                        { title: 'Space ( )', value: ' ' }
                                    ]"
                                    :error-messages="errors.thousand_separator"
                                    :placeholder="t('Select thousand separator')"
                                    clearable
                                    />
                            </VCol>

                            <!-- Decimal Separator -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.decimal_separator" :label="t('Decimal Separator')" :required="true"
                                    :items="[
                                        { title: 'Dot (.)', value: '.' },
                                        { title: 'Comma (,)', value: ',' },
                                        { title: 'Space ( )', value: ' ' }
                                    ]"
                                    :error-messages="errors.decimal_separator" :placeholder="t('Select decimal separator')"
                                    clearable
                                    />
                            </VCol>

                            <!-- Date Format -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.date_format" :label="t('Date Format')" :required="true"
                                    :items="[
                                        { title: 'd/m/Y', value: 'd/m/Y' },
                                        { title: 'm/d/Y', value: 'm/d/Y' },
                                        { title: 'Y/m/d', value: 'Y/m/d' }
                                    ]"
                                    :error-messages="errors.date_format" :placeholder="t('Select date format')"
                                    clearable
                                    />
                            </VCol>

                            <!-- Timezone -->
                            <VCol cols="12" md="4">
                                <AppAutocomplete v-model="form.timezone" :label="t('Timezone')" :required="true"
                                    :items="timezones"
                                    :error-messages="errors.timezone" :placeholder="t('Select timezone')"
                                    clearable
                                    />
                            </VCol>

                            <VCol cols="12" md="4">
                                <!-- write a guide for over sale -->
                                <AppAutocomplete 
                                :tooltipShow="true"
                                :tooltipTitle="$t('Over Sale Yes means that allows you to sell products that are not in stock')"
                                v-model="form.over_sale" :label="t('Over Sale')" :required="true"
                                    :items="[
                                        { title: 'Yes', value: 'Yes' },
                                        { title: 'No', value: 'No' }
                                    ]"
                                    :error-messages="errors.over_sale" :placeholder="t('Select over sale')"
                                    clearable
                                    />
                            </VCol>

                            <VCol cols="12" md="4">
                                <AppAutocomplete 
                                    v-model="form.print_formate"
                                    :tooltipShow="true"
                                    :tooltipTitle="$t('Select 56mm for 56mm printer and 80mm for 80mm printer')"
                                    :label="t('Printer Format')" 
                                    :required="true"
                                    :items="[
                                        { title: '56mm', value: '56mm' },
                                        { title: '80mm', value: '80mm' },
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.print_formate" :placeholder="t('Select printer format')"
                                    clearable
                                />
                            </VCol>

                            <VCol cols="12" md="4">
                                <AppAutocomplete 
                                    v-model="form.use_website"
                                    :tooltipShow="true"
                                    :tooltipTitle="$t('Use Website Yes means that allows you to use website')"
                                    :label="t('Enable Website')" 
                                    :required="true"
                                    :items="[
                                        { title: 'Yes', value: 'Yes' },
                                        { title: 'No', value: 'No' },
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.use_website" :placeholder="t('Select use website')"
                                    clearable
                                />
                            </VCol>

                            <VCol cols="12" md="4">
                                <AppAutocomplete 
                                    v-model="form.default_email_select"
                                    :tooltipShow="true"
                                    :tooltipTitle="$t('If Yes, email checkbox will be selected by default in bookings and POS')"
                                    :label="t('Default Email Select')" 
                                    :required="true"
                                    :items="[
                                        { title: 'Yes', value: 'Yes' },
                                        { title: 'No', value: 'No' },
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.default_email_select" :placeholder="t('Select default email')"
                                    clearable
                                />
                            </VCol>

                            <VCol cols="12" md="4">
                                <AppAutocomplete 
                                    v-model="form.default_sms_select"
                                    :tooltipShow="true"
                                    :tooltipTitle="$t('If Yes, SMS checkbox will be selected by default in bookings and POS')"
                                    :label="t('Default SMS Select')" 
                                    :required="true"
                                    :items="[
                                        { title: 'Yes', value: 'Yes' },
                                        { title: 'No', value: 'No' },
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.default_sms_select" :placeholder="t('Select default SMS')"
                                    clearable
                                />
                            </VCol>

                            <VCol cols="12" md="4">
                                <AppAutocomplete 
                                    v-model="form.default_whatsapp_select"
                                    :tooltipShow="true"
                                    :tooltipTitle="$t('If Yes, WhatsApp checkbox will be selected by default in bookings and POS')"
                                    :label="t('Default WhatsApp Select')" 
                                    :required="true"
                                    :items="[
                                        { title: 'Yes', value: 'Yes' },
                                        { title: 'No', value: 'No' },
                                    ]"
                                    item-title="title"
                                    item-value="value"
                                    :error-messages="errors.default_whatsapp_select" :placeholder="t('Select default WhatsApp')"
                                    clearable
                                />
                            </VCol>


                            <!-- Image Upload (Required)-->
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
                                                <span class="d-none d-sm-block">{{ $t('Invoice Logo') }}</span>
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
                                            {{ $t('Recommended size: 200px x 70px') }} - <small>{{ $t("Use the exact size for best results, but don't use less.") }}</small>
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol>
                            

                            <!-- Image Upload -->
                            <!-- <VCol cols="12">
                                <VCardText class="d-flex">
                                    <VAvatar rounded size="100" class="me-6" :image="previewImage" />
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputEl?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Invoice Logo') }}</span>
                                            </VBtn>

                                            <input ref="refInputEl" type="file" name="photo" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetImage">
                                                <span class="d-none d-sm-block">{{ $t('Reset') }}</span>
                                                <VIcon icon="tabler-refresh" class="d-sm-none" />
                                            </VBtn>
                                        </div>
                                        <p class="text-body-1 mb-0">
                                            {{ $t('Allowed JPG, JPEG, PNG, WEBP. Max size of 2 MB (Optional)') }}
                                        </p>
                                    </form>
                                </VCardText>
                            </VCol> -->

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
  height: 100px;
  background: #DDD;
}
.v-card-title {
    padding: 25px 25px 16px 25px !important;
    border-bottom: 1px solid #dbdbdb;
    padding-bottom: 10px;
}
</style>