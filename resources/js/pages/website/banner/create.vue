<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref } from 'vue';
import defaultBanner from '@images/system-config/default-picture.png';
import { useI18n } from 'vue-i18n';

const { t } = useI18n()

const router = useRouter()
const loadings = ref(false)
const refInputEl = ref()
const previewImage = ref(defaultBanner)

const form = ref({
    banner_tag: '',
    banner_title: '', 
    banner_description: '',
    banner_image: defaultBanner,
    status: 'Enabled'
})

const bannerTagError = ref('')
const bannerTitleError = ref('')
const bannerDescriptionError = ref('')
const statusError = ref('')

const validateForm = () => {
    let isValid = true

    // Basic form validation
    bannerTagError.value = form.value.banner_tag ? '' : t('Banner tag is required')
    bannerTitleError.value = form.value.banner_title ? '' : t('Banner title is required')
    bannerDescriptionError.value = form.value.banner_description ? '' : t('Banner description is required')
    statusError.value = form.value.status ? '' : t('Banner status is required')

    // Check if any errors exist
    if (bannerTagError.value || bannerTitleError.value || bannerDescriptionError.value || statusError.value) {
        isValid = false
    }

    return isValid
}

const createBanner = async () => {
    if (!validateForm()) {
        toast(t('Please fill all required fields'), {
            type: 'error'
        })
        loadings.value = false
        return
    }

    try {
        loadings.value = true
        const formData = new FormData()
        
        // Append form fields
        Object.keys(form.value).forEach(key => {
            if (key == 'banner_image') {
                if (form.value.banner_image instanceof File) {
                    formData.append('banner_image', form.value.banner_image)
                }
            } else {
                formData.append(key, form.value[key])
            }
        })

        const response = await $api('/banners', {
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

        const { status, message } = response

        // if (response.success) {
        //     toast(t('Banner created successfully'), {
        //         type: 'success'
        //     })
        // } else {
        //     throw new Error(response.message || t('Failed to create banner'))
        //     loadings.value = false
        // }
        // router.push({ name: 'website-banner' })
        // loadings.value = false

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
        router.push({ name: 'website-banner' })

    } catch (err) {
        console.log("the error" + err)
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
    } finally {
        loadings.value = false
    }
}

const resetForm = () => {
    form.value = {
        banner_tag: '',
        banner_title: '',
        banner_description: '',
        banner_image: defaultBanner,
        status: 'Enabled'
    }
    previewImage.value = defaultBanner
    
    // Reset error messages
    bannerTagError.value = ''
    bannerTitleError.value = ''
    bannerDescriptionError.value = ''
    statusError.value = ''
}

const changeImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = e => {
            previewImage.value = e.target.result
            form.value.banner_image = file
        }
        reader.readAsDataURL(file)
    }
}

const resetImage = () => {
    form.value.banner_image = defaultBanner
    previewImage.value = defaultBanner
    if (refInputEl.value) {
        refInputEl.value.value = ''
    }
}

</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Create Banner')">
                <VCardText>
                    <VForm class="mt-3" @submit.prevent="createBanner">
                        <VRow>
                            <!-- Banner Tag -->
                            <VCol cols="12" md="6">
                                <AppTextField 
                                    v-model="form.banner_tag" 
                                    :label="t('Banner Tag')" :required="true"
                                    type="text"
                                    :placeholder="t('Enter banner tag')"
                                    :error-messages="bannerTagError"
                                />
                            </VCol>

                            <!-- Banner Title -->
                            <VCol cols="12" md="6">
                                <AppTextField 
                                    v-model="form.banner_title" 
                                    :label="t('Banner Title')" :required="true"
                                    type="text"
                                    :placeholder="t('Enter banner title')"
                                    :error-messages="bannerTitleError"
                                />
                            </VCol>

                            <!-- Banner Description -->
                            <VCol cols="12">
                                <AppTextarea 
                                    v-model="form.banner_description" 
                                    :label="t('Banner Description')" :required="true"
                                    type="text"
                                    :placeholder="t('Enter banner description')"
                                    :error-messages="bannerDescriptionError"
                                />
                            </VCol>

                            <!-- Banner Status -->
                            <VCol cols="12" md="6">
                                <AppSelect 
                                    v-model="form.status" 
                                    :label="t('Banner Status')" :required="true"
                                    :items="['Enabled', 'Disabled']"
                                    :error-messages="statusError"
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
                                                <span class="d-none d-sm-block">{{ t('Upload Banner') }}</span>
                                            </VBtn>

                                            <input ref="refInputEl" type="file" name="banner_image" accept=".jpeg,.png,.jpg,GIF" hidden @input="changeImage">

                                            <VBtn type="reset" size="small" color="secondary" variant="tonal" @click="resetImage">
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
                                <VBtn type="submit" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Submit') }}
                                </VBtn>
                                <VBtn color="primary" variant="tonal" type="reset" @click.prevent="router.push({ name: 'website-banner' })">
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
</template>
