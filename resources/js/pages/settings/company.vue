<script setup>
import settings from '@core/json/settings.json'
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import defaultAvater from '@images/system-config/default-picture.png';

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
    print_formate: '56mm',
    over_sale: null,
})
const loadings = ref(false)
const errors = ref({})
const timezones = ref([])
const refInputEl = ref()
const previewImage = ref(defaultAvater)

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
        print_formate: '56mm',
        over_sale: null,
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
                print_formate: res.data.print_formate ? res.data.print_formate.toLowerCase().replace(/\s/g, '') : null,
                over_sale: res.data.over_sale || null,
            }

            // Set preview image
            previewImage.value = res.data.logo_url || defaultAvater
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


const changeImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            previewImage.value = e.target.result
            form.value.logo = file
        }
        reader.readAsDataURL(file)
    }
}

const resetImage = () => {
    form.value.logo = defaultAvater
    previewImage.value = defaultAvater
    if (refInputEl.value) {
        refInputEl.value.value = ''
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

                            <!-- Website -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.website" :label="t('Website')" :error-messages="errors.website"
                                    :placeholder="t('Enter website')" />
                            </VCol>

                            <!-- Address -->
                            <VCol cols="12" md="4">
                                <AppTextField v-model="form.address" :label="t('Address')" :error-messages="errors.address"
                                    :placeholder="t('Enter address')" />
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
                                        { title: '2 Digit', value: '2' },
                                        { title: '3 Digit', value: '3' }
                                    ]"
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
                                <AppAutocomplete v-model="form.over_sale" :label="t('Over Sale')" :required="true"
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
                                    :label="t('Printer Format')" 
                                    :required="true"
                                        :items="[
                                            { title: '56mm', value: '56mm' },
                                            { title: '80mm', value: '80mm' },
                                        ]"
                                    item-title="title"
                                    :error-messages="errors.print_formate" :placeholder="t('Select printer format')"
                                    clearable
                                />
                            </VCol>

                            

                            <!-- Image Upload -->
                            <VCol cols="12">
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
    </VRow>
</template>
