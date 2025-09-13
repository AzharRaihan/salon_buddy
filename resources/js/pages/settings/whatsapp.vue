<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import AppAutocomplete from '@/@core/components/app-form-elements/AppAutocomplete.vue'
import { useWhatsappSettings } from '@/composables/useWhatsappSettings'

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
                                        :label="t('App Key') + '*'"
                                        :error-messages="errors.whatsapp_app_key" 
                                        :placeholder="t('Enter app key')" 
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_auth_key" 
                                        :label="t('Auth Key') + '*'" 
                                        type="password"
                                        :error-messages="errors.whatsapp_auth_key" 
                                        :placeholder="t('Enter auth key')" 
                                    />
                                </VCol>
                            </template>

                            <!-- Twilio Settings -->
                            <template v-if="form.whatsapp_type == 'Twilio'">
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_account_sid" 
                                        :label="t('Account SID') + '*'"
                                        :error-messages="errors.whatsapp_account_sid" 
                                        :placeholder="t('Enter account SID')" 
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_auth_token" 
                                        :label="t('Auth Token') + '*'" 
                                        type="password"
                                        :error-messages="errors.whatsapp_auth_token" 
                                        :placeholder="t('Enter auth token')" 
                                    />
                                </VCol>
                                <VCol cols="12" md="6">
                                    <AppTextField 
                                        v-model="form.whatsapp_from_number" 
                                        :label="t('From Phone Number') + '*'" 
                                        :error-messages="errors.whatsapp_from_number" 
                                        :placeholder="t('Enter from phone number (e.g., +1234567890)')" 
                                    />
                                </VCol>
                            </template>

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
