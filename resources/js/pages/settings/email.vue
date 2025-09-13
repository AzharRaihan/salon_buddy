<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = ref({
    mail_type: 'SMTP',
    host_address: '',
    mail_port: '',
    encryption: 'tls',
    mail_username: '',
    mail_password: '',
    mail_from: '',
    mail_from_name: '',
    mail_api_key: ''
})

const errors = ref({})
const loadings = ref(false)

const validateForm = () => {
    errors.value = {}

    if (!form.value.mail_type) errors.value.mail_type = t('Mail type is required')
    if (!form.value.host_address) errors.value.host_address = t('Host address is required')
    if (!form.value.mail_port) errors.value.mail_port = t('Mail port is required')
    if (!form.value.encryption) errors.value.encryption = t('Encryption is required')
    if (!form.value.mail_username) errors.value.mail_username = t('Mail username is required')
    if (!form.value.mail_password) errors.value.mail_password = t('Mail password is required')
    if (!form.value.mail_from) errors.value.mail_from = t('Mail from is required')
    if (!form.value.mail_from_name) errors.value.mail_from_name = t('Mail from name is required')
    if ((form.value.mail_type == 'Mailgun' || form.value.mail_type == 'Sendinblue') && !form.value.mail_api_key) errors.value.mail_api_key = t('Mail API key is required')

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        mail_type: 'SMTP',
        host_address: '',
        mail_port: '',
        encryption: 'tls',
        mail_username: '',
        mail_password: '',
        mail_from: '',
        mail_from_name: '',
        mail_api_key: ''
    }
}

const getMailSettings = async () => {
    try {
        const res = await $api('/mail-settings', {
            method: 'GET'
        })

        if (res.success == true) {
            form.value = res.data
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch mail settings'), {
            type: 'error'
        })
    }
}

const updateMailSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/mail-settings', {
            method: 'POST',
            body: form.value,
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
        getMailSettings()
    } catch (err) {
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

onMounted(() => {
    getMailSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('Mail Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateMailSettings">
                        <VRow>
                            <!-- Mail Type -->
                            <VCol cols="12" md="6">
                                <AppSelect v-model="form.mail_type" :label="t('Email Type') + '*'" :items="[
                                    { title: 'SMTP', value: 'SMTP' },
                                    { title: 'Mailgun', value: 'Mailgun' },
                                    { title: 'Sendinblue', value: 'Sendinblue' }
                                ]" :error-messages="errors.mail_type" :placeholder="t('Select email type')" />
                            </VCol>

                            <!-- Host Address -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.host_address" :label="t('Host Address') + '*'"
                                    :error-messages="errors.host_address" :placeholder="t('Enter host address')" />
                            </VCol>

                            <!-- Port -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.mail_port" :label="t('Port') + '*'"
                                    :error-messages="errors.mail_port" :placeholder="t('Enter port')" />
                            </VCol>

                            <!-- Encryption -->
                            <VCol cols="12" md="6">
                                <AppSelect v-model="form.encryption" :label="t('Encryption') + '*'" :items="[
                                    { title: 'TLS', value: 'tls' },
                                    { title: 'SSL', value: 'ssl' }
                                ]" :error-messages="errors.encryption" :placeholder="t('Select encryption')" />
                            </VCol>

                            <!-- Mail Username -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.mail_username" :label="t('Username') + '*'"
                                    :error-messages="errors.mail_username" :placeholder="t('Enter username')" />
                            </VCol>

                            <!-- Mail Password -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.mail_password" :label="t('Password') + '*'" type="password"
                                    :error-messages="errors.mail_password" :placeholder="t('Enter password')" />
                            </VCol>

                            <!-- Mail From -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.mail_from" :label="t('From Email') + '*'"
                                    :error-messages="errors.mail_from" :placeholder="t('Enter from email')" />
                            </VCol>

                            <!-- Mail From Name -->
                            <VCol cols="12" md="6">
                                <AppTextField v-model="form.mail_from_name" :label="t('From Name') + '*'"
                                    :error-messages="errors.mail_from_name" :placeholder="t('Enter from name')" />
                            </VCol>

                            <!-- Mail API Key (only for Mailgun) -->
                            <VCol v-if="form.mail_type == 'Mailgun' || form.mail_type == 'Sendinblue'" cols="12" md="6">
                                <AppTextField v-model="form.mail_api_key" :label="t('API Key') + '*'"
                                    :error-messages="errors.mail_api_key" :placeholder="t('Enter API key')" />
                            </VCol>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                                <VBtn color="error" variant="tonal" @click="resetForm">
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
