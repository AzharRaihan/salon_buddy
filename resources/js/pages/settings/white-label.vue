<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import defaultAvater from '@images/system-config/default-picture.png';

const baseUrl = import.meta.env.VITE_APP_URL

const { t } = useI18n()

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
    if (!form.value.company_website) errors.value.company_website = t('Company website is required')

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
            getWhiteLabelSettings()
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
                                <AppTextField v-model="form.company_website" :label="t('Company Website')" :required="true"
                                    :error-messages="errors.company_website" :placeholder="t('Enter company website')" />
                            </VCol>

                            <!-- Favicon -->
                            <VCol cols="12" md="6">
                                <!-- <DropZone v-model="form.favicon" :image_url="form.favicon_url"
                                    :title="t('Drag and drop favicon here')" :subtitle="t('or')" :buttonText="t('Browse Favicon')" /> -->
                                <VCardText class="d-flex">
                                    <VAvatar rounded size="100" class="me-6" :image="previewFav" />
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

                            <!-- Logo -->
                            <VCol cols="12" md="6">
                                <!-- <DropZone v-model="form.logo" :image_url="form.logo_url" title="Drag and drop logo here"
                                    :subtitle="t('or')" :buttonText="t('Browse Logo')" /> -->
                                <VCardText class="d-flex">
                                    <div class="company-logo">
                                        <VAvatar rounded size="100" class="me-6" :image="previewLogo" />
                                    </div>
                                    <form class="d-flex flex-column justify-center gap-4">
                                        <div class="d-flex flex-wrap gap-4">
                                            <VBtn color="primary" size="small" @click="refInputElLogo?.click()">
                                                <VIcon icon="tabler-cloud-upload" class="d-sm-none" />
                                                <span class="d-none d-sm-block">{{ $t('Company logo') }}</span>
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
