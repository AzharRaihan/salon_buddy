<script setup>
import { onMounted, ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// SMS settings form
const form = ref({
    sms_type: 'mobishastra',
    sms_api_key: '',
    sms_api_secret: '',
    sms_sender_id: '',
    sms_username: '',
    sms_password: ''
})

// Test SMS form
const testForm = ref({
    to: '',
    message: ''
})

const errors = ref({})
const testErrors = ref({})
const loadings = ref(false)
const testLoading = ref(false)

// Offcanvas state
const showOffcanvas = ref(false)

const validateForm = () => {
    errors.value = {}

    if (!form.value.sms_type) errors.value.sms_type = t('SMS type is required')
    if (!form.value.sms_api_key) errors.value.sms_api_key = t('SMS API key is required')
    if (!form.value.sms_sender_id) errors.value.sms_sender_id = t('SMS sender ID is required')
    
    // Conditional validation based on SMS type
    if (form.value.sms_type == 'twilio' && !form.value.sms_api_secret) {
        errors.value.sms_api_secret = t('SMS API secret is required for Twilio')
    }
    if (form.value.sms_type == 'mimsms') {
        if (!form.value.sms_username) errors.value.sms_username = t('SMS username is required for MiMSMS')
        if (!form.value.sms_password) errors.value.sms_password = t('SMS password is required for MiMSMS')
    }

    return Object.keys(errors.value).length == 0
}

const resetForm = () => {
    form.value = {
        sms_type: 'mobishastra',
        sms_api_key: '',
        sms_api_secret: '',
        sms_sender_id: '',
        sms_username: '',
        sms_password: ''
    }
}

const getSmsSettings = async () => {
    try {
        const res = await $api('/sms-settings', {
            method: 'GET'
        })

        if (res.success == true) {
            form.value = res.data
        }
    } catch (err) {
        console.error(err)
        toast(t('Failed to fetch SMS settings'), {
            type: 'error'
        })
    }
}

const updateSmsSettings = async () => {
    loadings.value = true
    if (!validateForm()) {
        loadings.value = false
        return
    }

    try {
        const res = await $api('/sms-settings', {
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
        getSmsSettings()
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

// Test SMS validation
const validateTestForm = () => {
    testErrors.value = {}

    if (!testForm.value.to) testErrors.value.to = t('Mobile number is required')
    if (!testForm.value.message) testErrors.value.message = t('Message is required')

    return Object.keys(testErrors.value).length == 0
}

// Test SMS function
const testSms = async () => {
    testLoading.value = true
    if (!validateTestForm()) {
        testLoading.value = false
        return
    }

    try {
        const res = await $api('/test-sms', {
            method: 'POST',
            body: testForm.value,
            headers: {
                'Accept': 'application/json',
            },
            onResponseError({ response }) {
                toast(response._data.message, {
                    type: 'error',
                })
                testLoading.value = false
                return Promise.reject(response._data)
            },
        })
        
        const { status, message } = res
        if (status == 'error') {
            toast(message, {
                type: 'error',
            })
            testLoading.value = false
            return
        }
        
        toast(message || t('Test SMS sent successfully'), {
            type: "success",
        })
        testLoading.value = false
        closeOffcanvas()
    } catch (err) {
        if (err.errors) {
            for (const [field, messages] of Object.entries(err.errors)) {
                messages.forEach(msg => {
                    toast(msg, { type: 'error' })
                })
            }
        } else {
            toast(err.message || t('Failed to send test SMS'), {
                type: 'error',
            })
        }
        testLoading.value = false
    }
}

// Offcanvas functions
const openOffcanvas = () => {
    showOffcanvas.value = true
    testForm.value = {
        to: '',
        message: 'This is a test SMS to verify SMS configuration.'
    }
    testErrors.value = {}
}

const closeOffcanvas = () => {
    showOffcanvas.value = false
    testForm.value = {
        to: '',
        message: ''
    }
    testErrors.value = {}
}

onMounted(() => {
    getSmsSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('SMS Settings')">
                <VCardText>
                    <VForm @submit.prevent="updateSmsSettings">
                        <VRow>
                            <!-- SMS Type -->
                            <VCol cols="12" md="6">
                                <AppAutocomplete 
                                    v-model="form.sms_type" 
                                    :label="t('SMS Service')"  :required="true"
                                    :items="[
                                        { title: 'Mobishastra', value: 'mobishastra' },
                                        { title: 'Twilio', value: 'twilio' },
                                        { title: 'TextLocal', value: 'textlocal' },
                                        { title: 'MiMSMS', value: 'mimsms' }
                                    ]" 
                                    :error-messages="errors.sms_type" 
                                    :placeholder="t('Select SMS service')" 
                                />
                            </VCol>

                            <!-- API Key -->
                            <VCol cols="12" md="6">
                                <AppTextField 
                                    v-model="form.sms_api_key" 
                                    :label="t('API Key')"  :required="true"
                                    :error-messages="errors.sms_api_key" 
                                    :placeholder="t('Enter API key')" 
                                />
                            </VCol>

                            <!-- API Secret (for Twilio) -->
                            <VCol v-if="form.sms_type == 'twilio'" cols="12" md="6">
                                <AppTextField 
                                    v-model="form.sms_api_secret" 
                                    :label="t('API Secret')"  :required="true"
                                    type="password"
                                    :error-messages="errors.sms_api_secret" 
                                    :placeholder="t('Enter API secret')" 
                                />
                            </VCol>

                            <!-- Sender ID -->
                            <VCol cols="12" md="6">
                                <AppTextField 
                                    v-model="form.sms_sender_id" 
                                    :label="t('Sender ID')"  :required="true"
                                    :error-messages="errors.sms_sender_id" 
                                    :placeholder="t('Enter sender ID')" 
                                />
                            </VCol>

                            <!-- Username (for MiMSMS) -->
                            <VCol v-if="form.sms_type == 'mimsms'" cols="12" md="6">
                                <AppTextField 
                                    v-model="form.sms_username" 
                                    :label="t('Username')"  :required="true"
                                    :error-messages="errors.sms_username" 
                                    :placeholder="t('Enter username')" 
                                />
                            </VCol>

                            <!-- Password (for MiMSMS) -->
                            <VCol v-if="form.sms_type == 'mimsms'" cols="12" md="6">
                                <AppTextField 
                                    v-model="form.sms_password" 
                                    :label="t('Password')"  :required="true"
                                    type="password"
                                    :error-messages="errors.sms_password" 
                                    :placeholder="t('Enter password')" 
                                />
                            </VCol>

                            <!-- Service Information -->
                            <VCol cols="12">
                                <VAlert
                                    :title="t('Service Information')"
                                    type="info"
                                    variant="tonal"
                                    class="mb-4"
                                >
                                    <template #text>
                                        <div v-if="form.sms_type == 'mobishastra'">
                                            <p><strong>Mobishastra:</strong> {{ t('Use your Mobishastra API key and sender ID') }}</p>
                                        </div>
                                        <div v-else-if="form.sms_type == 'twilio'">
                                            <p><strong>Twilio:</strong> {{ t('Use your Twilio Account SID as API Key and Auth Token as API Secret') }}</p>
                                        </div>
                                        <div v-else-if="form.sms_type == 'textlocal'">
                                            <p><strong>TextLocal:</strong> {{ t('Use your TextLocal API key and sender ID') }}</p>
                                        </div>
                                        <div v-else-if="form.sms_type == 'mimsms'">
                                            <p><strong>MiMSMS:</strong> {{ t('Use your MiMSMS username, password and sender ID') }}</p>
                                        </div>
                                    </template>
                                </VAlert>
                            </VCol>

                            <!-- Action Buttons -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                                <!-- Test SMS button -->
                                <VBtn color="secondary" variant="outlined" @click="openOffcanvas()">
                                    <VIcon start icon="tabler-message" />
                                    {{ t('Test SMS') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Offcanvas for Test SMS -->
        <VNavigationDrawer
            v-model="showOffcanvas"
            location="end"
            temporary
            width="500"
        >
            <VCard flat>
                <VCardTitle class="d-flex justify-space-between align-center pa-4">
                    <span>{{ t('Test SMS Configuration') }}</span>
                    <VBtn icon variant="text" @click="closeOffcanvas">
                        <VIcon icon="tabler-x" />
                    </VBtn>
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-4">
                    <VForm @submit.prevent="testSms">
                        <VRow>
                            <!-- Mobile Number -->
                            <VCol cols="12">
                                <AppTextField
                                    v-model="testForm.to"
                                    :label="t('Mobile Number')"
                                    type="tel"
                                    :placeholder="t('Enter recipient mobile number')"
                                    :error-messages="testErrors.to"
                                    required
                                />
                            </VCol>

                            <!-- Message -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="testForm.message"
                                    :label="t('Message')"
                                    :placeholder="t('Enter SMS message')"
                                    :error-messages="testErrors.message"
                                    rows="5"
                                    required
                                />
                            </VCol>
                        </VRow>

                        <!-- Form Actions -->
                        <VRow class="mt-4">
                            <VCol cols="12" class="d-flex gap-3">
                                <VBtn
                                    type="submit"
                                    color="primary"
                                    :loading="testLoading"
                                    :disabled="testLoading"
                                    block
                                >
                                    <VIcon start icon="tabler-send" />
                                    {{ t('Send Test SMS') }}
                                </VBtn>
                                <VBtn
                                    type="button"
                                    color="secondary"
                                    variant="outlined"
                                    @click="closeOffcanvas"
                                    block
                                >
                                    <VIcon start icon="tabler-x" />
                                    {{ t('Cancel') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VNavigationDrawer>
    </VRow>
</template> 