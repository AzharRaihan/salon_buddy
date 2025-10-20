import { ref } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

export function useWhatsappSettings() {
    const { t } = useI18n()

    const form = ref({
        whatsapp_type: 'RC Soft',
        whatsapp_auth_key: '',
        whatsapp_app_key: '',
        whatsapp_account_sid: '',
        whatsapp_auth_token: '',
        whatsapp_from_number: ''
    })

    const errors = ref({})
    const loadings = ref(false)

    const validateForm = () => {
        errors.value = {}

        if (!form.value.whatsapp_type) {
            errors.value.whatsapp_type = t('WhatsApp type is required')
        }
        
        if (form.value.whatsapp_type === 'RC Soft') {
            if (!form.value.whatsapp_app_key) {
                errors.value.whatsapp_app_key = t('App key is required')
            }
            if (!form.value.whatsapp_auth_key) {
                errors.value.whatsapp_auth_key = t('Auth key is required')
            }
        }
        
        if (form.value.whatsapp_type === 'Twilio') {
            if (!form.value.whatsapp_account_sid) {
                errors.value.whatsapp_account_sid = t('Account SID is required')
            }
            if (!form.value.whatsapp_auth_token) {
                errors.value.whatsapp_auth_token = t('Auth token is required')
            }
            if (!form.value.whatsapp_from_number) {
                errors.value.whatsapp_from_number = t('From phone number is required')
            }
        }

        return Object.keys(errors.value).length === 0
    }

    const resetForm = () => {
        form.value = {
            whatsapp_type: 'RC Soft',
            whatsapp_auth_key: '',
            whatsapp_app_key: '',
            whatsapp_account_sid: '',
            whatsapp_auth_token: '',
            whatsapp_from_number: ''
        }
        errors.value = {}
    }

    const getWhatsappSettings = async () => {
        try {
            const res = await $api('/whatsapp-settings', {
                method: 'GET'
            })

            if (res.success === true) {
                form.value = res.data
            }
        } catch (err) {
            console.error(err)
            toast(t('Failed to fetch WhatsApp settings'), {
                type: 'error'
            })
        }
    }

    const updateWhatsappSettings = async () => {
        loadings.value = true
        
        if (!validateForm()) {
            loadings.value = false
            return false
        }

        try {
            const res = await $api('/whatsapp-settings', {
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
            if (status === 'error') {
                toast(message, {
                    type: 'error',
                })
                loadings.value = false
                return false
            }
            
            toast(message, {
                type: "success",
            })
            loadings.value = false
            await getWhatsappSettings()
            return true
            
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
            return false
        }
    }

    const getProviderOptions = () => [
        { title: 'RC Soft', value: 'RC Soft' },
        { title: 'Twilio', value: 'Twilio' },
    ]

    return {
        form,
        errors,
        loadings,
        validateForm,
        resetForm,
        getWhatsappSettings,
        updateWhatsappSettings,
        getProviderOptions
    }
}
