<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = ref({
    google_enabled: false,
    google_client_id: '',
    google_client_secret: '',
    google_redirect_url: '',
    google_app_url: '',
    facebook_enabled: false,
    facebook_client_id: '',
    facebook_client_secret: '',
    facebook_redirect_url: '',
    facebook_app_url: '',
})

const errors = ref({})
const loadings = ref(false)

const validateForm = () => {
    errors.value = {}

    if (form.value.google_enabled) {
        if (!form.value.google_client_id) errors.value.google_client_id = t('Client ID is required')
        if (!form.value.google_client_secret) errors.value.google_client_secret = t('Client Secret is required')
        if (!form.value.google_redirect_url) errors.value.google_redirect_url = t('Redirect URL is required')
        if (!form.value.google_app_url) errors.value.google_app_url = t('App URL is required')
    }

    if (form.value.facebook_enabled) {
        if (!form.value.facebook_client_id) errors.value.facebook_client_id = t('Client ID is required')
        if (!form.value.facebook_client_secret) errors.value.facebook_client_secret = t('Client Secret is required')
        if (!form.value.facebook_redirect_url) errors.value.facebook_redirect_url = t('Redirect URL is required')
        if (!form.value.facebook_app_url) errors.value.facebook_app_url = t('App URL is required')
    }

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        google_enabled: false,
        google_client_id: '',
        google_client_secret: '',
        google_redirect_url: '',
        google_app_url: '',
        facebook_enabled: false,
        facebook_client_id: '',
        facebook_client_secret: '',
        facebook_redirect_url: '',
        facebook_app_url: '',
    }
}

const getSocialAuthSettings = async () => {
    try {
        const res = await $api('/social-auth-settings', {
            method: 'GET'
        })

        console.log(res.data)

        if (res.success == true) {
            form.value = res.data
            form.value.google_enabled = res.data.google_enabled == 1
            form.value.google_client_id = res.data.google_client_id
            form.value.google_client_secret = res.data.google_client_secret
            form.value.google_redirect_url = res.data.google_redirect_url
            form.value.google_app_url = res.data.google_app_url
            form.value.facebook_enabled = res.data.facebook_enabled == 1
            form.value.facebook_client_id = res.data.facebook_client_id
            form.value.facebook_client_secret = res.data.facebook_client_secret
            form.value.facebook_redirect_url = res.data.facebook_redirect_url
            form.value.facebook_app_url = res.data.facebook_app_url
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch social auth settings'), {
            type: 'error'
        })
    }
}

const updateSocialAuthSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/social-auth-settings', {
            method: 'POST',
            body: form.value,
        })

        if (res.success == true) {
            toast(res.message, {
                type: 'success'
            })
            loadings.value = false
            getSocialAuthSettings()
        }
    } catch (err) {
        console.error(err)
        toast(err.response.data.message, {
            type: 'error'
        })
        loadings.value = false
    }
}

onMounted(() => {
    getSocialAuthSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Social Auth Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateSocialAuthSettings">
                        <VRow>
                            <!-- Google Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.google_enabled" :label="t('Google')" />
                                    </VCol>

                                    <VCol v-if="form.google_enabled" cols="12" class="pt-0">
                                        <VRow>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.google_client_id" :label="t('Client ID')"
                                                    :error-messages="errors.google_client_id"
                                                    :required="true"
                                                    :placeholder="t('Enter google client id')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.google_client_secret" :label="t('Client Secret')"
                                                    :error-messages="errors.google_client_secret"
                                                    :required="true"
                                                    :placeholder="t('Enter google client secret')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.google_redirect_url" :label="t('Redirect URL')"
                                                    :error-messages="errors.google_redirect_url"
                                                    :required="true"
                                                    :placeholder="t('Enter google redirect url')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.google_app_url" :label="t('App URL')"
                                                    :error-messages="errors.google_app_url"
                                                    :required="true"
                                                    :placeholder="t('Enter google app url')" />
                                            </VCol>

                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>

                            <!-- Facebook Section -->
                            <VCol cols="12">
                                <VRow>
                                    <VCol cols="12">
                                        <VCheckbox v-model="form.facebook_enabled" :label="t('Facebook')" />
                                    </VCol>

                                    <VCol v-if="form.facebook_enabled" cols="12" class="pt-0">
                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.facebook_client_id" :label="t('Client ID')"
                                                    :error-messages="errors.facebook_client_id"
                                                    :required="true"
                                                    :placeholder="t('Enter facebook api key')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.facebook_client_secret" :label="t('Client Secret')"
                                                    :error-messages="errors.facebook_client_secret"
                                                    :required="true"
                                                    :placeholder="t('Enter facebook api secret')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.facebook_redirect_url" :label="t('Redirect URL')"
                                                    :error-messages="errors.facebook_redirect_url"
                                                    :required="true"
                                                    :placeholder="t('Enter facebook redirect url')" />
                                            </VCol>

                                            <VCol cols="12" md="6">
                                                <AppTextField v-model="form.facebook_app_url" :label="t('App URL')"
                                                    :error-messages="errors.facebook_app_url"
                                                    :required="true"
                                                    :placeholder="t('Enter facebook app url')" />
                                            </VCol>

                                        </VRow>
                                    </VCol>
                                </VRow>
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
