<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import AppAutocomplete from '@/@core/components/app-form-elements/AppAutocomplete.vue'
import { useWhatsappSettings } from '@/composables/useWhatsappSettings'
import { toast } from 'vue3-toastify'

const { t } = useI18n()
const {
    form,
    errors,
    loadings,
    validateForm,
    resetForm,
    getWhatsappSettings,
    updateWhatsappSettings,
    getProviderOptions
} = useWhatsappSettings()

const handleSubmit = async () => {
    await updateWhatsappSettings()
}

// Test whatsapp form
const testForm = ref({
    to: '',
    message: ''
})


const testErrors = ref({})
const testLoading = ref(false)
// Offcanvas state
const showOffcanvas = ref(false)


// Test whatsapp validation
const validateTestForm = () => {
    testErrors.value = {}

    if (!testForm.value.to) testErrors.value.to = t('Whatsapp number is required')
    if (!testForm.value.message) testErrors.value.message = t('Whatsapp message is required')

    return Object.keys(testErrors.value).length == 0
}

// Test whatsapp function
const testWhatsapp = async () => {
    testLoading.value = true
    if (!validateTestForm()) {
        testLoading.value = false
        return
    }

    try {
        const res = await $api('/test-whatsapp', {
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
        
        toast(message || t('Test whatsapp sent successfully'), {
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
            toast(err.message || t('Failed to send test whatsapp'), {
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
        message: 'This is a test whatsapp to verify whatsapp configuration.'
    }
    testErrors.value = {}
}

const closeOffcanvas = () => {
    showOffcanvas.value = false
    testForm.value = {
        to: '',
        message: 'This is a test whatsapp to verify whatsapp configuration.'
    }
    testErrors.value = {}
}


onMounted(() => {
    getWhatsappSettings()
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <VCard :title="t('WhatsApp Settings')">
                <VCardText>
                    <VForm @submit.prevent="handleSubmit">
                        <VRow>
                            <!-- WhatsApp Type -->
                            <VCol cols="12" md="6">
                                <AppAutocomplete 
                                    v-model="form.whatsapp_type" 
                                    :label="t('WhatsApp Provider')" 
                                    :required="true" 
                                    :items="getProviderOptions()"
                                    :error-messages="errors.whatsapp_type" 
                                    :placeholder="t('Select WhatsApp provider')" 
                                />
                            </VCol>

                            <!-- RC Soft Settings -->
                            <template v-if="form.whatsapp_type == 'RC Soft'">
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_app_key" 
                                        :label="t('App Key')"
                                        :error-messages="errors.whatsapp_app_key" 
                                        :placeholder="t('Enter app key')" 
                                        :required="true"
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_auth_key" 
                                        :label="t('Auth Key')" 
                                        type="password"
                                        :error-messages="errors.whatsapp_auth_key" 
                                        :placeholder="t('Enter auth key')" 
                                        :required="true"
                                    />
                                </VCol>
                            </template>

                            <!-- Twilio Settings -->
                            <template v-if="form.whatsapp_type == 'Twilio'">
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_account_sid" 
                                        :label="t('Account SID')"
                                        :error-messages="errors.whatsapp_account_sid" 
                                        :placeholder="t('Enter account SID')" 
                                        :required="true"
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_auth_token" 
                                        :label="t('Auth Token')" 
                                        type="password"
                                        :error-messages="errors.whatsapp_auth_token" 
                                        :placeholder="t('Enter auth token')" 
                                        :required="true"
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_from_number" 
                                        :label="t('From Phone Number')" 
                                        :error-messages="errors.whatsapp_from_number" 
                                        :placeholder="t('Enter from phone number (e.g., +1234567890)')" 
                                        :required="true"
                                    />
                                </VCol>
                            </template>

                            <!-- Form Actions -->
                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit" color="primary" :loading="loadings" :disabled="loadings">
                                    <VIcon start icon="tabler-checkbox" />
                                    {{ t('Save Changes') }}
                                </VBtn>
                                <!-- Test WhatsApp button -->
                                <VBtn color="secondary" variant="outlined" @click="openOffcanvas()">
                                    <VIcon start icon="tabler-message" />
                                    {{ t('Test WhatsApp') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Offcanvas for Test WhatsApp -->
        <VNavigationDrawer
            v-model="showOffcanvas"
            location="end"
            temporary
            width="500"
        >
            <VCard flat>
                <VCardTitle class="d-flex justify-space-between align-center pa-4">
                    <span>{{ t('Test WhatsApp Configuration') }}</span>
                    <VBtn icon variant="text" @click="closeOffcanvas">
                        <VIcon icon="tabler-x" />
                    </VBtn>
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-4">
                    <VForm @submit.prevent="testWhatsapp">
                        <VRow>
                            <!-- Mobile Number -->
                            <VCol cols="12">
                                <AppTextField
                                    v-model="testForm.to"
                                    :label="t('Whatsapp Number')"
                                    type="tel"
                                    :placeholder="t('Enter recipient whatsapp number')"
                                    :error-messages="testErrors.to"
                                    required
                                />
                            </VCol>

                            <!-- Message -->
                            <VCol cols="12">
                                <AppTextarea
                                    v-model="testForm.message"
                                    :label="t('Message')"
                                    :placeholder="t('Enter whatsapp message')"
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
                                    {{ t('Send Test WhatsApp') }}
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
